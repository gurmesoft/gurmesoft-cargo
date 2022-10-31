<?php

namespace GurmesoftCargo\Companies;

use Exception;

class Yurtici extends \GurmesoftCargo\Companies\BaseCompany
{
    public function __construct(array $options)
    {
        $this->prepare($options);
    }

    private function prepare($options)
    {
        $this->url = 'http://webservices.yurticikargo.com:8080/KOPSWebServices/ShippingOrderDispatcherServices?wsdl';

        if (isset($options['live']) && !$options['live']) {
            $this->url = 'http://webservices.yurticikargo.com:8080/KOPSWebServices/ShippingOrderDispatcherServices?wsdl';
        }

        if (isset($options['apiKey']) && !empty($options['apiKey'])) {
            $this->apiKey = $options['apiKey'];
        }

        if (isset($options['apiPass']) && !empty($options['apiPass'])) {
            $this->apiPass = $options['apiPass'];
        }

        $this->check(array(
            'apiKey',
            'apiPass',
        ), $this, false);
    }

    public function createShipment($shipment)
    {
        $this->check(array(
            'barcode',
            'firstName',
            'lastName',
            'address',
            'city',
            'district',
            'phone',
        ), $shipment, true);

        $payment = array(
            'kapida-odemeli' => 0,
            'kapida-odemeli-kredi' => 1,
        );

        $paymentMethod = $shipment->getPaymentMethod();
        
        $type = isset($payment[$paymentMethod]) ? $payment[$paymentMethod] : 2;
        

        $creditRole = '';
        $selectedCredit = '';

        if ($type == 1) {
            if (!empty($shipment->getCreditRole())) {
                $creditRole =  $shipment->getCreditRole();
            } else {
                $creditRole = 0;
            }

            if (!empty($shipment->getSelectedCredit())) {
                $selectedCredit =  $shipment->getSelectedCredit();
            } else {
                $selectedCredit = 1;
            }
        }

        $yurticiShipment = array(
            'wsUserName'        => $this->apiKey,
            'wsPassword'        => $this->apiPass,
            'wsLanguage'        => 'TR',
            'userLanguage'      => 'TR',
            'ShippingOrderVO'   => array(
                'cargoKey'              => $shipment->getBarcode(),
                'invoiceKey'            => $shipment->getInvoice(),
                'receiverCustName'      => $shipment->getFirstName() . ' ' . $shipment->getLastName(),
                'receiverAddress'       => $shipment->getAddress(),
                'cityName'              => $this->getCity($shipment->getCity()),
                'townName'              => $shipment->getDistrict(),
                'receiverPhone1'        => $shipment->getPhone(),
                'emailAddress'          => $shipment->getMail(),
                'taxOfficeId'           => '',
                'taxNumber'             => '',
                'taxOfficeName'         => '',
                'desi'                  => '',
                'kg'                    => '',
                'cargoCount'            => '',
                'waybillNo'             => $shipment->getWaybill(),
                'specialField1'         => '',
                'specialField2'         => '',
                'specialField3'         => '',
                'ttCollectionType'      => $type === 0 || $type === 1 ? $type : '',
                'ttInvoiceAmount'       => ($type === 0 || $type === 1) && $shipment->getTotalPriceByPaymentMethod() ? $shipment->getTotalPriceByPaymentMethod() : "",
                'ttDocumentId'          => $type === 0 || $type === 1 ? $shipment->getInvoice() : '',
                'ttDocumentSaveType'    => '',
                'dcCreditRule'          => $creditRole,
                'dcSelectedCredit'      => $selectedCredit,
                'description'           => '',
                'orgGeoCode'            => '',
                'privilegeOrder'        => '',
                'custProdId'            => '',
                'orgReceiverCustId'     => ''
            )
        );

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->createShipment($yurticiShipment);
        } catch (Exception $e) {
            return $result->setErrorMessage($e->getMessage());
        }

        $result->setResponse($response);
        $response = $response->ShippingOrderResultVO;

        if ($response->outFlag === '0' && !isset($response->shippingOrderDetailVO->errCode)) {
            $result->setOperationMessage($response->outResult)
            ->setOperationCode($response->jobId)
            ->setBarcode($response->shippingOrderDetailVO->cargoKey)
            ->setIsSuccess(true);
        } elseif ($response->outFlag !== '0' && isset($response->shippingOrderDetailVO->errCode)) {
            $result->setErrorMessage($response->shippingOrderDetailVO->errMessage)->setErrorCode($response->shippingOrderDetailVO->errCode);
        } else {
            $result->setErrorMessage($response->outResult)->setErrorCode($response->errCode);
        }

        return $result;
    }

    public function cancelShipment($barcode)
    {
        $yurticiShipment = array(
            'wsUserName'        => $this->apiKey,
            'wsPassword'        => $this->apiPass,
            'wsLanguage'        => 'TR',
            'userLanguage'      => 'TR',
            'cargoKeys'         => $barcode,
        );

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->cancelShipment($yurticiShipment);
        } catch (Exception $e) {
            $result->setErrorMessage($e->getMessage());
            return $result;
        }

        $result->setResponse($response);
        $response = $response->ShippingOrderResultVO;

        if ($response->outFlag === '0' && isset($response->shippingCancelDetailVO->errCode)) {
            $result->setErrorMessage($response->shippingCancelDetailVO->errMessage)->setErrorCode($response->shippingCancelDetailVO->errCode);
        } elseif ($response->outFlag === '0' && !isset($response->shippingCancelDetailVO->errCode)) {
            $result->setOperationMessage($response->shippingCancelDetailVO->operationMessage)
            ->setOperationCode($response->shippingCancelDetailVO->operationCode)
            ->setBarcode($response->shippingCancelDetailVO->cargoKey)
            ->setIsSuccess(true);
        } else {
            $result->setErrorMessage($response->outResult)->setErrorCode($response->errCode);
        }
       

        return $result;
    }

    public function infoShipment($barcode)
    {
        $yurticiShipment = array(
            'wsUserName'        => $this->apiKey,
            'wsPassword'        => $this->apiPass,
            'wsLanguage'        => 'TR',
            'userLanguage'      => 'TR',
            'keys'              => $barcode,
            'keyType'           => '0',
            'addHistoricalData' => false,
            'onlyTracking'      => true,
        );

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->queryShipment($yurticiShipment);
        } catch (Exception $e) {
            $result->setErrorMessage($e->getMessage());
            return $result;
        }

        $result->setResponse($response);
        $response = $response->ShippingDeliveryVO;

        if ($response->outFlag === '0' && !isset($response->shippingDeliveryDetailVO->errCode)) {
            $this->manageResult($result, $response->shippingDeliveryDetailVO);
        } elseif ($response->outFlag === '0' && isset($response->shippingDeliveryDetailVO->errCode)) {
            $result->setErrorMessage($response->shippingDeliveryDetailVO->errMessage)->setErrorCode($response->shippingDeliveryDetailVO->errCode);
        } else {
            $result->setErrorMessage($response->outResult)->setErrorCode($response->errCode);
        }

        return $result;
    }

    public function manageResult(&$result, $response)
    {
        $status = [
            "Kargo İşlem Görmemiş." => 'on-hold',
            "Kargo teslim edilmiştir." => 'complated'
        ];

        if(empty($status[$response->operationMessage])) {
            $status[$response->operationMessage] ='processing';
        }
        

        $result->setOperationMessage($status[$response->operationMessage])
        ->setOperationCode($response->operationCode)
        ->setBarcode($response->cargoKey)
        ->setIsSuccess(true);

        if ($response->operationCode > 0 && isset($response->shippingDeliveryItemDetailVO)) {
            $trackingUrl    = $response->shippingDeliveryItemDetailVO->trackingUrl;
            $exploded       = explode('code=', $trackingUrl);
            $trackingCode   = $exploded[1];
            $result->setTrackingUrl($trackingUrl)->setTrackingCode($trackingCode);
        }
    }
}
