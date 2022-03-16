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

Gönderi oluşturma `vendor/autoload.php` dosyası dahil edilir ve firma türetilerek hazır hale getirilir.

```php
<?php 

require 'vendor/autoload.php';

$options = array(
    'live'      => false,                       // Test ortamı için gereklidir. 
    'apiKey'    => 'XXXXXXXX',                  // Kargo firması tarafından verilen anahtar,kullanıcı vb.
    'apiPass'   => 'XXXXXXXX',                  // Kargo firması tarafından verilen şifre,gizli anahtar vb.  
);

$yurtici    = new \GurmesoftCargo\Client('Yurtici', $options);
$shipment   = new \GurmesoftCargo\Shipment;

$shipment->setBarcode('XXXXXXXXXXXX')           // Eşsiz barkod numaranız her gönderi için yenisini türetiniz.
->setInvoice('XXXXXXXXXXXX')                    // Gönderi fatura numarası 
->getWaybill('XXXXXXXXXXXX')                    // İrsaliye No (Ticari gönderilerde zorunludur)
->setFirstName('Fikret')                        // Alıcı ad
->setLastName('Çin')                            // Alıcı soyad
->setPhone('5527161084')                        // Alıcı telefon
->setCity('16')                                 // Alıcı il plaka kodu örn. 01,16,81 
->setDistrict('Nilüfer')                        // Alıcı ilçe bilgisi
->setAddress('Ertuğrul Cd. Eker İş Hanı D13')   // Alıcı adres bilgisi
->setPostcode('16120')                          // Alıcı posta kodu bilgisi (Opsiyonel)
->setMail('info@gurmesoft.com');                // Alıcı e-posta (Opsiyonel)

$result = $yurtici->createShipment($shipment);

$result->getResponse();                         // Kargo firmasından gelen tüm cevabı incelemek için kullanılır.

if ($result->isSuccess()) {      
    $result->getBarcode();                      // Kargo firmasının barkod ürettiği senaryolarda barkodu taşır.
} else {
    echo $result->getErrorCode();               // Hata kodunu döndürür.
    echo $result->getErrorMessage();            // Hata mesajını döndürür.
}

```



