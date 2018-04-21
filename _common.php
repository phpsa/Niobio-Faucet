<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'classes/jsonRPCClient.php';
require_once 'classes/ForkNoteWalletd.php';
require_once 'classes/recaptcha.php';
require_once 'config.php';
use function theodorejb\ResponsiveCaptcha\checkAnswer;
use function theodorejb\ResponsiveCaptcha\randomQuestion;
use \RedBeanPHP\R as DB;

DB::setup('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'], $config['db']['user'], $config['db']['password']);

//Set our timezone data:::


class Config
{

    public static $config = array();

    public static function set($configs)
    {
        self::$config = $configs;
    }

    public static function get($key)
    {
        return isset(self::$config[$key]) ? self::$config[$key] : false;
    }

}
Config::set($config);

$tz = Config::get('timezone');
if($tz){
    date_default_timezone_set($tz);
}

function getCurrentExchangeRate()
{
    if (!file_exists('images/spesbtc.txt') || time() - filemtime('images/spesbtc.txt') >= 60 * 15) {
        file_put_contents('images/spesbtc.txt', file_get_contents("https://cryptohub.online/api/market/ticker/SPES/"));
    }
    $pair = json_decode(file_get_contents('images/spesbtc.txt'));
    //echo '<pre>$pair: '; print_r($pair->BTC_SPES->last); echo '</pre>'; die();
    return sprintf("%.8f", $pair->BTC_SPES->last > 0 ? $pair->BTC_SPES->last : '0.00000001');
}

function getWalletBalance($decimals = 10)
{
    $wallet = DB::find('wallet')[1];
    $total = ($wallet->balance - $wallet->pending);
    
    return number_format(round($total, $decimals), $decimals, ".", "");
}

function totalPayments()
{
    return DB::getCell('SELECT COUNT(*) as total FROM `payouts`');
}

function totalPayed($decimals = 8)
{
    $total = DB::getCell('SELECT sum(payout_amount) as total FROM `payouts`');
    return round($total, $decimals);
}

function getSecondCaptcha()
{
    $qa         = randomQuestion();
    $realAnswer = $qa->getAnswer();

    $_SESSION['cap_word_answer'] = $realAnswer;

    return $qa->getQuestion();

}

function randomize()
{

    $min = Config::get('minReward');
    $max = Config::get('maxReward');
    $walletBalance = getWalletBalance();
    if($walletBalance < $max){
        $max = $walletBalance;
    }

    $onein10K = rand(0, 10000);
    $prize    = $min;

    //$jackpot
    if ($onein10K == 10000) {
        return $max;
    } else if ($onein10K >= 9998) {
        $prize = $max * 0.8;
    } else if ($onein10K >= 9994) {
        $prize = $max * 0.6;
    } else if ($onein10K >= 9986) {
        $prize = $max * 0.4;
    } else if ($onein10K >= 9886) {
        $prize = $max * 0.2;
    }
    if ($prize < $min) {
        $prize = $min;
    }

    $prize = $prize;

    return array('number' => $onein10K, 'prize' => round($prize, 10));
}

function confirmCaptcha()
{
    //Instantiate the Recaptcha class as $recaptcha
    $recaptcha = new Recaptcha(Config::get('recaptcha'));
    $google_captcha = filter_input(INPUT_POST, "g-recaptcha-response");
    $word_captcha = filter_input(INPUT_POST, "human_verification");


  /*  echo $google_captcha . '<br />';
    echo $word_captcha . '<br />';
var_dump($recaptcha->set());
echo '<br />';
var_dump()*/

    //test captcha
    if(
        empty($google_captcha) || 
        empty($word_captcha) ||
        !$recaptcha->set() ||
        !$recaptcha->verify($google_captcha) ||
        !checkAnswer($word_captcha,  $_SESSION['cap_word_answer'])
        )
        {
            return false;
        }
    return true;
}

function sanitizeWallet($address){
    return trim(preg_replace('/[^a-zA-Z0-9]/', '', $address));
}


function get_ip_address(){
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){

        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe

                if (filter_var($ip, FILTER_VALIDATE_IP) !== false){
                    return $ip;
                }
            }
        }
    }
}

function verifyPaymentIdRequired($wallet, $paymentID)
{
    $clave = array_search($wallet, Config::get('clearedAddresses'));
    if($clave){
        if(empty($paymentID)){
            return false;
        }
    }
    return true;
}

function verifyClaimTime($wallet,$paymentID = ''){
    $clave = array_search($wallet, Config::get('clearedAddresses'));
    $rewardInterval = Config::get('rewardEvery');

    $query = "Select `id` from `payouts` where `timestamp` > NOW() - INTERVAL {$rewardInterval} Hour AND (";
    $query .= " `ip_address` = '" . get_ip_address() . "' ";
    if($clave){
        $query .= " OR `payment_id` = '{$paymentID}')";
    }else{
        $query .= " OR `payout_address` = '{$wallet}')";
    }

    $id = DB::getCell( $query );

    return $id ? false : true;
        
}

function addPrizeToDatabase($prize,$wallet,$paymentID){

   

    $payout = DB::dispense('payouts');
    $payout->payout_amount = $prize['prize'];
    $payout->ip_address = get_ip_address();
    $payout->payout_address = $wallet;
    $payout->payment_id = $paymentID;
    $payout->timestamp = DATE("Y-m-d H:i:s");
    $payout->drawn = $prize['number'];
    DB::store($payout);

    //upate our pending table!
    DB::exec("update `wallet` set `pending` = `pending` +  $prize");
    
}

function lastPayed($limit = 5){
    return DB::getAll("Select * from payouts where paid = 1 and transaction_id != '' and transaction_id is not null order by id desc limit $limit");
    
}

function topEarners($limit = 5){
    return DB::getAll("select *, sum(payout_amount) as total, count(*) as spins from payouts group by concat(payout_address,payment_id) order by sum(payout_amount) desc limit $limit");
}

function getEarningAds(){
    $ads = array(
        '<a href="https://faucethub.io/r/41582654"><img src="https://faucethub.io/assets/img/banners/1.gif"></a>',
        '<a href="https://freebitco.in/?r=10148588" target="_blank"><img class="img-responsive" src="https://static1.freebitco.in/banners/468x60-3.png" /></a>',
        ' <a href="http://freedoge.co.in/?r=1528473" target="_blank"><img class="img-responsive" src="http://static1.freedoge.co.in/banners/468x60-3.png" /></a>'
    );
    shuffle($ads);
    return $ads;
}

$earning_ads = getEarningAds();