<?php

namespace GurmesoftCargo\Companies;

use Exception;

class Mng extends \GurmesoftCargo\Companies\BaseCompany
{
    public function __construct(array $options)
    {
        $this->prepare($options);
    }

    private function prepare($options)
    {
        $this->url = 'http://service.mngkargo.com.tr/musterikargosiparis/musterikargosiparis.asmx?wsdl';

        if (isset($options['live']) && !$options['live']) {
            $this->url = 'http://service.mngkargo.com.tr/musterikargosiparis/musterikargosiparis.asmx?wsdl';
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
            'gonderici-odemeli' => 'X',
            'alici-odemeli' => 'P',
            'kapida-odemeli' => 'U',
        );

        $paymentMethod = $shipment->getPaymentMethod();

        $type = $payment[$paymentMethod];

        $list = '1:1:1:kargo:1:;';

        $gonderi = [

            'KisiKurum'                => $siparis->get_shipping_first_name() . ' ' . $siparis->get_shipping_last_name(),
            'AliciAdresi'              => $siparis->get_shipping_address_1() . ' ' . $siparis->get_shipping_address_2(),
            'Il'                       => WC()->countries->get_states($siparis->get_billing_country())[$siparis->get_billing_state()],
            'Ilce'                     => $siparis->get_shipping_city(),
            'TelefonCep'               => $siparis->get_billing_phone(),
            'AliciKodu'                => '',
            'KargoTuru'                => 3,
            'Odemetipi'                => (int) $arr['gonderi_tipi'],
            'TeslimSekli'              => 1,
            'TasimaSekli'              => 1,
            'Pazaryerimi'              => false,
            'EntegrasyonSozlesme'      => '',
            'IrsaliyeSeriNo'           => '',
            'IrsaliyeSiraNo'           => '',
            'OzelKargoTakipNo'         => (string) $takipNo,
            'ReferansNo'               => '',
            'Adet'                     => 1,
            'KapidanOdemeTahsilatTipi' => 0,
            'KapidanOdemeTutari'       => 0,
            'BirimDesi'                => 0,
            'BirimKg'                  => 0,
        ];

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->SiparisGirisiDetayliV3($mngShipment);
        } catch (Exception $e) {
            return $result->setErrorMessage($e->getMessage());
        }

        if ($response->SiparisGirisiDetayliV3Result != '1') {
            if (isset($result->ShippingOrderResultVO)) {
                $result->setErrorMessage($response->ShippingOrderResultVO->shippingOrderDetailVO->errMessage);
            } else {
                $result->setErrorMessage($response->SiparisGirisiDetayliV3Result);
            }
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
            $response = $this->soapClient()->MusteriSiparisIptal([
                'pKullaniciAdi' => $this->apiKey,
                'pSifre'     => $this->apiPass,
                'pMusteriSiparisNo' => $barcode,
                'pSiparisTarihi' => ''
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
        $mngShipment = array(
            'pMusteriNo' => $this->apiKey,
            'pSifre' => $this->apiPass,
            'pSiparisNo' => '',
            'pGonderiNo' => $barcode,
            'pFaturaSeri' => '',
            'pFaturaNo' => '',
            'pIrsaliyeNo' => '',
            'pEFaturaNo' => '',
            'pRaporType' => '',
        );

        $result = new \GurmesoftCargo\Result;

        try {
            $response = $this->soapClient()->KargoBilgileriByReferans($mngShipment);
        } catch (Exception $e) {
            $result->setErrorMessage($e->getMessage());
            return $result;
        }

        if (isset($response->KargoBilgileriByReferansResult)) {
            $orderFromMNG = simplexml_load_string($response->KargoBilgileriByReferansResult->any)->NewDataSet->Table1;
            $result->setResponse($orderFromMNG);

            $result->setOperationMessage($orderFromMNG->KARGO_STATU_ACIKLAMA)
                ->setOperationCode($orderFromMNG->KARGO_STATU)
                ->setBarcode($barcode)
                ->setIsSuccess(true);

            if ($orderFromMNG->KARGO_STATU > 0) {
                $result->setTrackingUrl($orderFromMNG->KARGO_TAKIP_URL)->setTrackingCode($orderFromMNG->MNG_GONDERI_NO);
            }
        } elseif (!empty($response->pWsError)) {
            $result->setErrorMessage($response->pWsError);
        } else {
            $result->setErrorMessage('Barkot sorgusunda bir hata oluştu!');
        }

        return $result;
    }
}