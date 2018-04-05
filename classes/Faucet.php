<?php
define('FAUCET_INIT', true);
ini_set('max_execution_time', 20);
require_once 'jsonRPCClient.php';
require_once 'recaptcha.php';
require_once dirname(__DIR__) . '/config.php';

class Faucet
{

    public $config;

    public $lang;

    protected $db;

    protected $wallet;

    protected $divider = 10000000000;
    protected $fee     = 1000000;

    public $faucet_error = false;

    public $available_balance;
    public $locked_balance;
    public $disposable_balance;
    public $total_balance;

    public $onein10K;
    public $prize;

    public function __construct($config)
    {
        $this->config = $config;
        $this->db     = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'], $config['db']['user'], $config['db']['password']);

        $lang = isset($config['language']) ? $config['language'] : 'en';
        if (!file_exists(__DIR__ . '/language/' . $lang . '.php')) {
            $lang = 'en';
        }
        require_once __DIR__ . '/language/' . $lang . '.php';

        $this->initialiseWallet();

    }

    public function lang($str)
    {
        return isset($this->lang[$str]) ? $this->lang[$str] : $str;
    }

    public function config($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : false;
    }

    protected function initialiseWallet()
    {
        try {
            $this->wallet            = new jsonRPCClient('http://127.0.0.1:9090/json_rpc');
            $balance                 = $this->wallet->getBalance();

            $this->available_balance = $balance['availableBalance'];
            $this->locked_balance    = $balance['lockedAmount'];

    
            $this->total_balance = ($this->available_balance + $this->locked_balance) / $this->divider;

            $this->disposable_balance = number_format(round($this->available_balance / $this->divider, 10), 10, '.', '');
        } catch (Exception $e) {
            $this->faucet_error = $e->getMessage();
        }

    }

    public function getTotalPayoutsValue()
    {
        $query  = 'SELECT SUM(payout_amount) FROM `payouts`;';
        $result = $this->db->query($query);
        $data   = $result->fetchColumn();
        
        return number_format($data / $this->divider, 10);
    }

    public function getTotalPayoutsCount()
    {
        $query2 = 'SELECT COUNT(*) FROM `payouts`;';

        $result = $this->db->query($query2);
        $data   = $result->fetchColumn();

        return $data[0];
    }

    public function recaptchaRender(){
        $recaptcha = new Recaptcha($this->config['recaptcha']);
        return $recaptcha->render();
    }

    public function verifyCaptcha(){
        return true;
        $recaptcha = new Recaptcha($this->config['recaptcha']);
        if($recaptcha->set()){
            if($recaptcha->verify($_POST['g-recaptcha-response'])){
                return true;
            }
        }
        return false;
    }


    public function verifyClaimValidity($wallet, $paymentidPost){
        $clave = array_search($wallet, $this->config('clearedAddresses'));
        $rewardEvery = $this->config('rewardEvery');
        $direccionIP = $_SERVER['REMOTE_ADDR'];
        if (empty($clave)) {
            $queryCheck = "SELECT `id` FROM `payouts` WHERE `timestamp` > NOW() - INTERVAL ' . $rewardEvery . ' HOUR AND ( `ip_address` = '$direccionIP' OR `payout_address` = '$wallet')";
			} else {
            $queryCheck = "SELECT `id` FROM `payouts` WHERE `timestamp` > NOW() - INTERVAL ' . $rewardEvery . ' HOUR AND ( `ip_address` = '$direccionIP' OR `payment_id` = '$paymentidPost')";
			}

            $resultCheck = $this->db->query($queryCheck);
            return $resultCheck->rowCount() ? false : true;
    }





    function generateRandomPrize()
    {
        $min = $this->config('minReward');
        $max = $this->config('maxReward');

        $this->onein10K = $onein10K = rand(0,10000);
        $prize = $min;
        //$jackpot
        if($onein10K == 10000){
        return $max;
        }
        else if($onein10K >= 9998){
            $prize = $max * 0.7;
        }
        else if($onein10K >= 9994){
            $prize = $max * 0.5;
        }
        else if($onein10K >= 9986 ){
            $prize = $max * 0.3;
        }
        else if($onein10K >= 9886){
            $prize = $max * 0.2;
        }
        if($prize < $min){
            $prize = $min;
        }

        return round($prize * $this->divider, 10);
}

public function transferPrize($wallet, $paymentID, $prize){
   
    $toTransfer = $prize - $this->fee;

    $destination = array('amount' => $toTransfer, 'address' => 'SvjdxW8hJcBPRdwU4PWMDFQPkvTwaexbpZ74vWdjqGZN2vJDpQ8CGY8j2qvsmMf2dAHFmh7sNqbm45qLZFLtF12A2rpYCyzyU');
    $date = new DateTime();
    $timestampUnix = $date->getTimestamp() + 5;
    $transaction = array(
        "method" => "sendTransaction",
        "params" => array(
            "addresses" => array(
                'SvkVrdcaBoEUVUXNwjxr8F7wb7GoHrcACNAkn4mnQWkhBorTnjg6J7S8bSebjRRj8L1zNyvBP2xppKRAK64f9y4328fCKsJXC'
        ) ,
            'transfers' => $destination,
           // 'paymentId' => $paymentID,
            'fee' => $this->fee,
            'anonymity' => 1, // need to increase mixin later
        //   'unlockTime' => 0
        )
    );
    echo '<pre>$transaction: '; print_r($transaction); echo '</pre>';

    $da_string = json_encode($transaction, JSON_NUMERIC_CHECK);

    var_dump($da_string);
$kech2 = curl_init('http://127.0.0.1:9090/json_rpc');
curl_setopt($kech2, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($kech2, CURLOPT_POSTFIELDS, $da_string);
curl_setopt($kech2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($kech2, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($da_string)
));
$result2 = curl_exec($kech2);

 var_dump ($result2);
 echo $result2;

 exit;


  /*  $da = array(
        "method" => "sendTransaction",
        "params" => array(
                "anonymity" => 0,
                "fee" => $fee,
                "addresses" => array(
                        $address1
                ) ,
                "transfers" => array(


                        array(
                                "amount" => $amount,
                                "address" => $receive
                        )
                )
        )
);

// $dat = array("method" => "createAddress");

$da_string = json_encode($da, JSON_NUMERIC_CHECK);
var_dump($da_string);
$kech2 = curl_init('http://localhost:8070/json_rpc');
curl_setopt($kech2, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($kech2, CURLOPT_POSTFIELDS, $da_string);
curl_setopt($kech2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($kech2, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($da_string)
));
$result2 = curl_exec($kech2);

// var_dump ($result2);
// echo $result2;*/


   // echo '<pre>$transaction: '; print_r($transaction); echo '</pre>'; die();

    $transfer = $this->wallet->sendTransaction($transaction) ;
    echo '<pre>$transaction: '; print_r($transfer); echo '</pre>'; die();

}
   

}
$faucet = new Faucet($config);
