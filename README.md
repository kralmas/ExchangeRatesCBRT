# ExchangeRatesCBRT
This is example of how to get exchange rates from CBRT (Central Bank of the Republic of Turkey)

### Örnek Kod
Kütüphanenin en temel kullanımı aşağıdaki gibidir;
```php
include_once("exchangeRatesCBRT.php");
$cbrt = new exchangeRatesCBRT();
$currencyList = $cbrt->getCurrency();
print_r($currencyList);

```