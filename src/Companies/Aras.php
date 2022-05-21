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
            'invoice',
            'firstName',
            'lastName',
            'address',
            'city',
            'district',
            'phone',
        ), $shipment, true);

        $payment = array(
            'gonderici-odemeli' => 3,
            'alici-odemeli' => 2,
            'kapida-odemeli' => 0,
            'kapida-odemeli-kredi' => 1,
        );

        $paymentMethod = $shipment->getPaymentMethod();
        
        $type = $payment[$paymentMethod];

        $arasShipment = [
            "TradingWaybillNumber" => $this->randStr(32),
            "InvoiceNumber" => $shipment->getInvoice(),
            "IntegrationCode" => $this->randStr(32),
            "ReceiverName" => $shipment->getFirstName() . ' ' . $shipment->getLastName(),
            "ReceiverAddress" => $shipment->getAddress(),
            "ReceiverCityName" => $this->getCity($shipment->getCity()),
            "ReceiverTownName" => $shipment->getDistrict(),
            "ReceiverPhone1" => $shipment->getPhone(),
            "PieceCount" => 1,
            "IsCod" => $type == 1 || $type == 0 ? "1" : "0",
            "CodAmount" => ($type == 0 || $type == 1) && $shipment->getTotalPriceByPaymentMethod() ? $shipment->getTotalPriceByPaymentMethod() : "" ,
            "CodCollectionType" => $type == 1 ? "1" :"0",
            "CodBillingType" => "0",
            "PayorTypeCode" => $type == 2 ,
            "IsWorldWide" => '0',
            "PieceDetails" => [
                "PieceDetail" => [
                    "BarcodeNumber" => $shipment->getBarcode(),
                ]
            ]
        ];

            $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->createShipment($arasShipment);
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
        $result->setOperationMessage($response->operationMessage)
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

    public function randStr($length = 10)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }

        return $str;
    }
}
