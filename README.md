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
`vendor/autoload.php` dosyası dahil edilir ve firma türetilerek hazır hale getirilir.
```php
<?php 

require 'vendor/autoload.php';

$options = array(
    'live'      => false,                       // Test ortamı için gereklidir. 
    'apiKey'    => 'XXXXXXXX',                  // Kargo firması tarafından verilen anahtar,kullanıcı vb.
    'apiPass'   => 'XXXXXXXX',                  // Kargo firması tarafından verilen şifre,gizli anahtar vb.  
);

$yurtici    = new \GurmesoftCargo\Client('Yurtici', $options);
```

### Gönderi oluşturma 
```php
<?php 

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
    echo $result->getBarcode();                 // Kargo firmasının barkod ürettiği senaryolarda barkodu taşır.
    echo $result->getOperationMessage();        // Kargo firmasından dönen başarılı yanıtı taşır.
    echo $result->getOperationCode();           // Kargo firmasından dönen başarılı yanıtın kodunu taşır.
} else {
    echo $result->getErrorCode();               // Hata kodunu döndürür.
    echo $result->getErrorMessage();            // Hata mesajını döndürür.
}
```

### Gönderinin durumunu sorgulama

```php
<?php 

$barcode = 'XXXXXXXXXXXX';                      // Başarılı gönderi oluşturma sonucu kayıt edilen barkod
$result  = $yurtici->infoShipment($barcode);    // Dönen cevabı gönderi oluşturmadaki methodlar ile inceleyebilirsiniz.
```

### Gönderinin iptali
```php
<?php 

$barcode = 'XXXXXXXXXXXX';                      // Başarılı gönderi oluşturma sonucu kayıt edilen barkod
$result  = $yurtici->cancelShipment($barcode);  // Dönen cevabı gönderi oluşturmadaki methodlar ile inceleyebilirsiniz.
```



