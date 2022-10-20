<?php

namespace GurmesoftCargo\Companies;

use Exception;

class Ptt extends \GurmesoftCargo\Companies\BaseCompany
{
    public function __construct(array $options)
    {
        $this->prepare($options);
    }

    private function prepare($options)
    {
        $this->url = 'https://pttws.ptt.gov.tr/PttVeriYukleme/services/Sorgu?wsdl';

        if (isset($options['live']) && !$options['live']) {
            $this->url = 'https://pttws.ptt.gov.tr/PttVeriYukleme/services/Sorgu?wsdl';
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

    public function getBarcode($barkodIncrementId)
    {
        $carpanSplit = [1, 3, 1, 3, 1, 3, 1, 3, 1, 3, 1, 3];
        $barkodSplit = str_split($barkodIncrementId);

        if (count($barkodSplit) != 12) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += $carpanSplit[$i] * $barkodSplit[$i];
        }

        $nearest = (int)ceil($sum / 10) * 10;
        $checkDigit = $nearest - $sum;

        array_push($barkodSplit, $checkDigit);

        return implode('', $barkodSplit);
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

        $barcode = $this->getBarcode($shipment->getBarcode());

        if (!$barcode) {
            return false;
        }

        $payment = array(
            'gonderici-odemeli' => 'MH',
            'alici-odemeli' => 'UA',
            'kapida-odemeli' => 'N',
        );

        $paymentMethod = $shipment->getPaymentMethod();

        $type = $payment[$paymentMethod];

        $pttShipment = [
            'input' => [
                'dosyaAdi' => date('Ymd-His-') . uniqid(),
                'gonderiTip' => 'NORMAL',
                'gonderiTur' => 'KARGO',
                'kullanici' => 'PttWs',
                'musteriId' => $this->apiKey,
                'sifre' => $this->apiPass,
                'dongu' => [
                    'aAdres' => $shipment->getAddress(),
                    'agirlik' => '',
                    'aliciAdi' => $shipment->getFirstName() . ' ' . $shipment->getLastName(),
                    'aliciIlAdi' => $shipment->getCity(),
                    'aliciIlceAdi' => $shipment->getDistrict(),
                    'aliciSms' => $shipment->getPhone(),
                    'aliciEmail' => $shipment->getMail(),
                    'barkodNo' => $barcode,
                    'boy' => '',
                    'deger_ucreti' => $shipment->getTotalPriceByPaymentMethod(),
                    'musteriReferansNo' => "",
                    'odemesekli' => $type,
                    'odeme_sart_ucreti' => "",
                    'rezerve1' => "",
                    'yukseklik' => "",
                ]
            ]
        ];

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->kabulEkle2($pttShipment);
        } catch (Exception $e) {
            return $result->setErrorMessage($e->getMessage());
        }

        $result->setResponse($response);

        if ($response->return->aciklama == 'BASARILI') {
            $result->setErrorMessage($response->return->aciklama);
        } else {
            // $result->setResponse($response);
            $result->setOperationMessage('Sipariş oluşturma işlemi başarılı!')
                ->setBarcode($shipment->getBarcode())
                ->setIsSuccess(true);
        }

        return $result;
    }

    public function cancelShipment($barcode)
    {
        $result = new \GurmesoftCargo\Result;

        try {
            \var_dump([
                'inpDelete' => [
                    'musteri_no' => $this->apiKey,
                    'dosyaAdi' => date('Ymd-His-') . uniqid(),
                    'sifre'     => $this->apiPass,
                    'barcode' => $barcode
                ]
            ]);

            $response = $this->soapClient()->barkodVeriSil([
                'inpDelete' => [
                    'musteri_no' => $this->apiKey,
                    'dosyaAdi' => date('Ymd-His-') . uniqid(),
                    'sifre'     => $this->apiPass,
                    'barcode' => $barcode
                ]
            ]);
        } catch (Exception $e) {
            $result->setErrorMessage($e->getMessage());
            return $result;
        }

        if ($response->MusteriSiparisIptalResult != '1') {
            $result->setErrorMessage($response->pWsError);
        } else {
            $result->setErrorMessage('Silme işlemi başarılı!')
                ->setBarcode($barcode)
                ->setIsSuccess(true);
        }

        $result->setResponse($response);

        return $result;
    }

    public function infoShipment($barcode)
    {
        $this->url = 'https://pttws.ptt.gov.tr/GonderiHareketV2/services/Sorgu?wsdl';

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->barkodSorgu([
                'input' => [
                    'musteri_no' => $this->apiKey,
                    'sifre'     => $this->apiPass,
                    'barcode' => $barcode
                ]
            ]);
        } catch (Exception $e) {
            $result->setErrorMessage($e->getMessage());
            return $result;
        }

        $result->setResponse($response);

        if ($response->return->hataKodu == 1) {
            $result->setOperationMessage($response->return->aciklama)
                ->setOperationCode($response->return->sonIslemAciklama)
                ->setBarcode($barcode)
                ->setIsSuccess(true);
        } else {
            $result->setErrorMessage($response->return->aciklama);
        }

        return $result;
    }
}
