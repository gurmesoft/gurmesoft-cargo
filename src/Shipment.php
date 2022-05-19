<?php

namespace GurmesoftCargo;

class Shipment
{
    protected $barcode;
    protected $invoice;
    protected $waybill;
    protected $firstName;
    protected $lastName;
    protected $phone;
    protected $mail;
    protected $address;
    protected $city;
    protected $district;
    protected $totalPriceByPaymentMethod;
    protected $paymentMethod;
    protected $creditRole;
    protected $selectedCredit;
    protected $postcode;

    public function setBarcode(string $param)
    {
        $this->barcode = $param;
        return $this;
    }

    public function setInvoice(string $param)
    {
        $this->invoice = $param;
        return $this;
    }

    public function setWaybill(string $param)
    {
        $this->waybill = $param;
        return $this;
    }

    public function setFirstName(string $param)
    {
        $this->firstName = $param;
        return $this;
    }

    public function setLastName(string $param)
    {
        $this->lastName = $param;
        return $this;
    }

    public function setPhone(string $param)
    {
        $this->phone = $param;
        return $this;
    }

    public function setMail(string $param)
    {
        $this->mail = $param;
        return $this;
    }
    public function setAddress(string $param)
    {
        $this->address = $param;
        return $this;
    }

    public function setCity(string $param)
    {
        $this->city = $param;
        return $this;
    }

    public function setDistrict(string $param)
    {
        $this->district = $param;
        return $this;
    }

    public function setPostcode(string $param)
    {
        $this->postcode = $param;
        return $this;
    }

    public function setPaymentMethod(string $param)
    {
        $this->paymentMethod = $param;
        return $this;
    }

    public function setTotalPriceByPaymentMethod(bool $param)
    {
        $this->totalPriceByPaymentMethod = $param;
        return $this;
    }

    public function setCreditRole(bool $param)
    {
        $this->creditRole = $param;
        return $this;
    }

    public function setSelectedCredit(bool $param)
    {
        $this->selectedCredit = $param;
        return $this;
    }

    public function getBarcode()
    {
        return $this->barcode;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function getWaybill()
    {
        return $this->waybill;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getMail()
    {
        return $this->mail;
    }
    public function getAddress()
    {
        return $this->address;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getDistrict()
    {
        return $this->district;
    }
    
    public function getPostcode()
    {
        return $this->postcode;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function getTotalPriceByPaymentMethod()
    {
        return $this->totalPriceByPaymentMethod;
    }

    public function getCreditRole()
    {
        return $this->creditRole;
    }

    public function getSelectedCredit()
    {
        return $this->selectedCredit;
    }
}
