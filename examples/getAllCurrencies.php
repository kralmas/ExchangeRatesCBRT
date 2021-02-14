<?PHP
include_once("../exchangeRatesCBRT.php");
$cbrt = new exchangeRatesCBRT();

$currencyList = $cbrt->getCurrency(array(
	"date" => date("Y-m-d"),	// [Default: Today=date('Y-m-d')] You can give specific date for that date's currency rates
	"currency" => "ALL",		// [Default: ALL] ALL: Retrun all currency rates on the CBRT xml. USD: Return only USD rates
	"maxGoBack" => 10			// [Default: 10] If there is no page on the date you selected, the previous day's exchange rates are tried to be taken as much as this number times. 
));

if ($cbrt->getErrorCount() > 0) {
	// If Return Any Error Message
	echo '<textarea style="width: 800px; height: 200px;">';
	echo $cbrt->getErrorMessages();
	echo '</textarea><br>';
}

echo '<textarea style="width: 800px; height: 200px;">';
print_r($currencyList);
echo '</textarea>';
?>