<?php

$faucetTitle = 'Nióbio Cash Faucet';
$faucetSubtitle = 'A cada 12 horas você pode obter Nióbio Cash grátis';
$logo = 'images/nbrcoin.png';

//Faucet address for donations
$faucetAddress = 'N5VprmAbmZDXsccjLCNZdJLuxibqADRw5SUtJq5vgDJALeybsF4ScefXzbzq9uMmtNYimp9jDSxFNRDzLdGJjuDYGJ5Q7wC';

//Reward time in hours
$rewardEvery = '12';
//Max reward and min reward as decimals Ex: Min = 10.0 & Max = 20.0
$minReward = '0.01'; //Remember that the minimum for an eobot deposit is 5 BCN as reward.
$maxReward = '0.3';
//Transaction fee is set to 0.01 BCN for every request.


//Database connection

$userDB = 'niobio';
$database = 'niobio';
$passwordDB = 'm#4jt6$Pkl7x';
$hostDB = '127.0.0.1';

//Recaptcha Keys. You can get yours here: https://www.google.com/recaptcha/
$keys = array(
    'site_key' => '6Le0AkEUAAAAAA0xp4QvxfLz6QBXFvQi1ifi_-79',
    'secret_key' => '6Le0AkEUAAAAAGB5SCY8NbG5Fs7bBy7kvp-tiVo2'
);

//Addresses that can request more than one time but with a different payment ID.
$clearedAddresses = array(/*'Eobot' => '22694R3K1JvGf1m98pBsbaXCA3ULQz4xdQiYHgnNAdsVDqZDjiTH9CMj6QHhKD232wPeYtfypNzp5TX5L3NcGGSmJ8pWnPJ',
	'Poloniex' => '25cZNQYVAi3issDCoa6fWA2Aogd4FgPhYdpX3p8KLfhKC6sN8s6Q9WpcW4778TPwcUS5jEM25JrQvjD3XjsvXuNHSWhYUsu',
	'HitBTC' => '24zavX3Bi2PiKGWLKh4bPGTiMsn4iHf3Y6JnKCF6V1PeBpDpuwiAMZ8di7ok6B5SQT6UXUtQgusruCoXbqUZm8VJAfq2xKK'*/
);
