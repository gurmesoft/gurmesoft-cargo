<?php

namespace GurmesoftCargo\Companies;

use Exception;

class Aras extends \GurmesoftCargo\Companies\BaseCompany
{
    public function __construct(array $options)
    {
        $this->prepare($options);
    }

    private function prepare($options)
    {
        $this->url = 'https://customerservicestest.araskargo.com.tr/arascargoservice/arascargoservice.asmx?wsdl';
        
        if (isset($options['live']) && !$options['live']) {
            $this->url = 'https://customerws.araskargo.com.tr/arascargoservice.asmx?wsdl';
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

        if ($type == 1) {
            $CodCollectionType = '1';
        } elseif ($type == 0) {
            $CodCollectionType= '0';
        } else {
            $CodCollectionType = '';
        }

        $randomNumber = $this->randStr(16);

        $arasShipment = [
            "UserName" =>  $this->apiKey,
            "Password" =>  $this->apiPass,
            "TradingWaybillNumber" => $shipment->getWaybill() ? $shipment->getWaybill() : $randomNumber,
            "InvoiceNumber" => $shipment->getInvoice(),
            "IntegrationCode" => $shipment->getBarcode() ? $shipment->getBarcode() : $randomNumber,
            "ReceiverName" => $shipment->getFirstName() . ' ' . $shipment->getLastName(),
            "ReceiverAddress" => $shipment->getAddress(),
            "ReceiverCityName" => $this->getCity($shipment->getCity()),
            "ReceiverTownName" => $shipment->getDistrict(),
            "ReceiverPhone1" => $shipment->getPhone(),
            "PieceCount" => 1,
            "IsCod" => $type == 1 || $type == 0 ? "1" : "0",
            "CodAmount" => ($type == 0 || $type == 1) && $shipment->getTotalPriceByPaymentMethod() ? $shipment->getTotalPriceByPaymentMethod() : "" ,
            "CodCollectionType" => $CodCollectionType,
            "CodBillingType" => "0",
            "PayorTypeCode" => $type == 3 ? 1 : 0 ,
            "IsWorldWide" => '0',
            "PieceDetails" => [
                "PieceDetail" => [
                    "VolumetricWeight" => "1",
                    "Weight" => "1",
                    "BarcodeNumber" => $shipment->getBarcode(),
                ]
            ]
        ];
       
        $result = new \GurmesoftCargo\Result;
        
        try {
            $requestBody = array(
                "orderInfo" => array(
                    "Order"=> $arasShipment
                ),
                'userName' => $this->apiKey,
                'password' => $this->apiPass
            );

            $response = $this->soapClient()->SetOrder($requestBody);
        } catch (Exception $e) {
            return $result->setErrorMessage($e->getMessage());
        }
        
        $result->setResponse($response);
    
        if ($response->SetOrderResult->OrderResultInfo->ResultCode != '0') {
            $result->setErrorMessage($response->SetOrderResult->OrderResultInfo->ResultMessage)
            ->setErrorCode($response->SetOrderResult->OrderResultInfo->ResultCode);
        } else {
            $result->setOperationMessage($response->SetOrderResult->OrderResultInfo->ResultMessage)
            ->setOperationCode($response->SetOrderResult->OrderResultInfo->OrgReceiverCustId)
            ->setBarcode($shipment->getBarcode())
            ->setIsSuccess(true);
        }

        return $result;
    }

    public function cancelShipment($barcode)
    {
        $yurticiShipment = array(
            'userName' => $this->apiKey,
            'password' => $this->apiPass,
            'integrationCode' => $barcode
        );
        
        try {
            $result = new \GurmesoftCargo\Result;

            $response = $this->soapClient()->CancelDispatch($yurticiShipment);
        } catch (Exception $e) {
            $result->setErrorMessage($e->getMessage());
            return $result;
        }

        $result->setResponse($response);

        if ($response->CancelDispatchResult->ResultCode != '0') {
            $result->setErrorMessage($response->CancelDispatchResult->ResultMessage)
            ->setErrorCode($response->CancelDispatchResult->ResultCode);
        } else {
            $result->setOperationMessage($response->CancelDispatchResult->ResultMessage)
            ->setIsSuccess(true);
        }
        return $result;
    }

    public function infoShipment($barcode)
    {
        $yurticiShipment = array(
            'userName' => $this->apiKey,
            'password' => $this->apiPass,
            'integrationCode' => $barcode
        );

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->GetOrderWithIntegrationCode($yurticiShipment);
        } catch (Exception $e) {
            $result->setErrorMessage($e->getMessage());
            return $result;
        }

        $result->setResponse($response);

        if ($response->CancelDispatchResult->ResultCode != '0') {
            $result->setErrorMessage($response->CancelDispatchResult->ResultMessage)
            ->setErrorCode($response->CancelDispatchResult->ResultCode);
        } else {
            $result->setOperationMessage($response->CancelDispatchResult->ResultMessage)
            ->setIsSuccess(true);
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
