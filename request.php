<?php
require_once 'classes/recaptcha.php';
require_once 'classes/jsonRPCClient.php';
require_once 'config.php';

$link = new PDO('mysql:host=' . $hostDB . ';dbname=' . $database, $userDB, $passwordDB);

function randomize($min, $max)
{

    $onein10K = rand(0,10000);
    $prize = $min;

    //$jackpot
    if($onein10K == 10000){
       return $max;
    }

    else if($onein10K >= 9998){
        $prize = $max * 0.10;
    }

    else if($onein10K >= 9994){
        $prize = $max * 0.01;
    }

    else if($onein10K >= 9986 ){
        $prize = $max * 0.001;
    }

    else if($onein10K >= 9886){
        $prize = $max * 0.0001;
    }
    if($prize < $min){
        $prize = $min;
    }

    $prize = $prize + (mt_rand(0, 32767) / 32767);

    return round($prize, 5);
}




//Instantiate the Recaptcha class as $recaptcha
$recaptcha = new Recaptcha($keys);
if ($recaptcha->set()) {
    if ($recaptcha->verify($_POST['g-recaptcha-response'])) {
        //Checking address and payment ID characters
        $wallet = $str = trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['wallet']));
        $paymentidPost = $str = trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['paymentid']));
        //Getting user IP
        $direccionIP = $_SERVER['REMOTE_ADDR'];


        if (empty($wallet) OR (strlen($wallet) < 34)) {
            header('Location: ./?msg=wallet');
            exit();
        }

        if (empty($paymentidPost)) {
            $paymentID = '';
        } else {
            if ((strlen($paymentidPost) > 64) OR (strlen($paymentidPost) < 64)) {
                header('Location: ./?msg=paymentID');
                exit();
            } else {
                $paymentID = $paymentidPost;
            }
        }

        //Looking for cleared address or not
        $clave = array_search($wallet, $clearedAddresses);

        if (empty($clave)) {
            $queryCheck = "SELECT `id` FROM `payouts` WHERE `timestamp` > NOW() - INTERVAL " . $rewardEvery . " HOUR AND ( `ip_address` = '$direccionIP' OR `payout_address` = '$wallet')";
			} else {
            $queryCheck = "SELECT `id` FROM `payouts` WHERE `timestamp` > NOW() - INTERVAL " . $rewardEvery . " HOUR AND ( `ip_address` = '$direccionIP' OR `payment_id` = '$paymentidPost')";
            }
            
  

        $resultCheck = $link->query($queryCheck);
        if ($resultCheck->rowCount()) {
            header('Location: ./?msg=notYet');
            exit();
        }

        $query = "SELECT * FROM `wallet`";
        $result = $link->query($query,  PDO::FETCH_ASSOC);
        $balance = $result->fetchObject();
        $balanceDisponible = $balance->balance;
        $lockedBalance = $balance->pending;
        $dividirEntre = 1;
        $hasta = ($balanceDisponible + $lockedBalance) / $dividirEntre;



        if ($hasta > $maxReward) {
            $hasta = $maxReward;
        }
        if ($hasta < ((float) $minReward)) {
            header('Location: ./?msg=dry');
            exit();
        }

        $aleatorio = randomize($minReward, $hasta);

        if($hasta < $aleatorio){
            $aleatorio = $hasta;
        }

        


      /*  $destination = array('amount' => $cantidadEnviar, 'address' => $wallet);
        $date = new DateTime();
        $timestampUnix = $date->getTimestamp() + 5;
        $peticion = array(
            'destinations' => $destination,
            'payment_id' => $paymentID,
            'fee' => $transactionFee,
            'mixin' => 1, // need to increase mixin later
            'unlock_time' => 0
        );

        $transferencia = $bitcoin->transfer($peticion);

        if ($transferencia == 'Bad address') {
            header('Location: ./?msg=wallet');
            exit();
        }*/

       $query = "INSERT INTO `payouts` (`payout_amount`,`ip_address`,`payout_address`,`payment_id`,`timestamp`) VALUES ('$aleatorio','$direccionIP','$wallet','$paymentID',NOW());";
       $link->query($query);
       $query = "update `wallet` set `pending` = `pending` + $aleatorio";
       $link->query($query);
        //Get our balance:::
    
        $query = "SELECT sum(payout_amount) as total  FROM `payouts` where `payout_address` = '" . $wallet . "' and paid = '0'";
      
        $result = $link->query($query,  PDO::FETCH_ASSOC);
        $pending = $result->fetchObject();


        $link->exec($query);
        header('Location: ./?msg=success&amount=' . $aleatorio . '&pending=' . round($pending->total,5));
        exit();
        


    } else {
        header('Location: ./?msg=captcha');
        exit();
    }
} else {
    header('Location: ./?msg=captcha');
    exit();
}

exit();
