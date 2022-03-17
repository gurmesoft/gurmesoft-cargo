<?php
namespace GurmesoftCargo;

use Exception;

class Client
{
    public function __construct(string $company, array $options)
    {
        $class = "\\GurmesoftCargo\\Companies\\$company";
        $this->class = new $class($options);
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
            throw new Exception(__CLASS__ . " exception barcode cannot be empty.");
        }
    }
}
