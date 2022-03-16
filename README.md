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

```



