# Gurmesoft/Cargo

Gurmesoft için üretilmiş kargo entegrasyon pakedi.Yurtiçi, Mng, Ptt, Sürat, Aras ve Bolt kargo desteği mevcuttur.

## Adım 1 

`composer.json` dosyası oluşturulur yada var olan dosyadaki uygun objelere ekleme yapılır.

```json
{
    "require": {
        "gurmesoft/cargo": "dev-master"
    },
    "repositories": [
        {
            "type" : "github",
            "url" : "https://github.com/gurmesoft/gurmesoft-cargo"
        }
    ]    
}
```

## Adım 2

`composer` kullanılarak paket çağırılır

```bash
composer require gurmesoft/cargo:dev-master
```

## Adım 3 

Kullanım için `vendor/autoload.php` dosyası dahil edilir ve client türetilerek hazır hale getirilir.

```php
<?php 

require 'vendor/autoload.php';

$options = array(
    'live'      => false,               // Test ortamı için gereklidir. 
    'apiKey'    => 'XXXXXXXX',          // Kargo firması tarafından verilen anahtar,kullanıcı vb.
    'apiPass'   => 'XXXXXXXX',          // Kargo firması tarafından verilen şifre,gizli anahtar vb.  
);

$yurtici    = new \GurmesoftCargo\Client('Yurtici', $options);
$shipment   = new \GurmesoftCargo\Shipment;

$shipment->setBarcode('123123123123')   // Eşsiz barkod numaranız her gönderi için yenisini türetiniz.
->setInvoice('123123123123')            // Gönderi fatura numarası 
->setFirstName('Fikret')                // Alıcı ad
->setLastName('Çin')                    // Alıcı soyad
->setPhone('xxxxxxxxx')                 // Alıcı telefon
->setCity('16')                         // Alıcı il plaka kodu örn .01,16,81 
->setDistrict('Karacabey')              // Alıcı ilçe bilgisi
->setAddress('Tav. Mh 89Sk. No5 D2')    // Alıcı adres bilgisi
->setMail('cinfikret@gmail.com');       // Alıcı e-posta (opsiyonel)

$result = $yurtici->createShipment($shipment);


```



