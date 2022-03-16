<?php
namespace GurmesoftCargo;

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
        return $this->class->cancelShipment($barcode);
    }

    public function infoShipment(string $barcode)
    {
        return $this->class->infoShipment($barcode);
    }
}
