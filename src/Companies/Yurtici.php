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
                'ttInvoiceAmount'       => '',
                'ttDocumentId'          => '',
                'ttCollectionType'      => '',
                'ttDocumentSaveType'    => '',
                'dcSelectedCredit'      => '',
                'dcCreditRule'          => '',
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
            $result->setErrorMessage($e->getMessage);
            return $result;
        }

        $result->setResponse($response);
        if (isset($response->ShippingOrderResultVO)) {
            $response = $response->ShippingOrderResultVO;
            if (isset($response->outFlag) && $response->outFlag === '0') {
                $result->setBarcode($shipment->getBarcode())->setIsSuccess(true);
            } elseif (isset($response->outFlag) && $response->outFlag === '1' && isset($response->shippingOrderDetailVO)) {
                $result->setErrorMessage($response->shippingOrderDetailVO->errMessage)->setErrorCode($response->shippingOrderDetailVO->errCode);
            } else {
                $result->setErrorMessage($response->outResult)->setErrorCode($response->errCode);
            }
        }

        return $result;
    }

    public function cancelShipment()
    {
    }

    public function infoShipment()
    {
    }
}
