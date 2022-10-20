<?php
namespace GurmesoftCargo;

use Exception;

class Client
{
    public $name;
    public $logo;
    public $key;

    public function __construct(string $company, array $options)
    {
        $class       = "\\GurmesoftCargo\\Companies\\$company";
        $this->class = new $class($options);
        $this->name  = $this->company($company, 'name');
        $this->logo  = $this->company($company, 'logo');
        $this->key   = $this->company($company, 'key');
    }

    public function createShipment(\GurmesoftCargo\Shipment $shipment)
    {
        return $this->class->createShipment($shipment);
    }

    public function cancelShipment(string $barcode)
    {
        $this->empty($barcode);
        return $this->class->cancelShipment($barcode);
    }

    public function infoShipment(string $barcode)
    {
        $this->empty($barcode);
        return $this->class->infoShipment($barcode);
    }

    private function empty($param)
    {
        if (empty($param)) {
            throw new Exception($this->name . " exception barcode cannot be empty.");
        }
    }

    private function company($company, $prop)
    {
        $companies = array(
            'Yurtici'   => array(
                'name'      => 'Yurtiçi Kargo',
                'key'       => 'yurtici',
                'logo'      => '',
            ),
            'Mng'       => array(
                'name'      => 'MNG Kargo',
                'key'       => 'mng',
                'logo'      => '',
            ),
            'Aras'       => array(
                'name'      => 'Aras Kargo',
                'key'       => 'aras',
                'logo'      => '',
            ),
            'Ptt'       => array(
                'name'      => 'Ptt Kargo',
                'key'       => 'ptt',
                'logo'      => '',
            ),
        );
        
        return $companies[$company][$prop];
    }
}
