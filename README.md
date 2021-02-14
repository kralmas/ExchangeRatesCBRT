# ExchangeRatesCBRT
This is example of how to get exchange rates from CBRT (Central Bank of the Republic of Turkey)

### Sample Code
The most basic use of the library is as follows;
```php
include_once("exchangeRatesCBRT.php");
$cbrt = new exchangeRatesCBRT();
$currencyList = $cbrt->getCurrency();
print_r($currencyList);

```