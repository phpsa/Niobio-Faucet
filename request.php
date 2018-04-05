<?php
require_once 'classes/Faucet.php';




//Instantiate the Recaptcha class as $recaptcha

if ($faucet->verifyCaptcha()) {

        //Checking address and payment ID characters
        $wallet = $str = trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['wallet']));
        $paymentidPost = $str = trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['paymentid']));
        //Getting user IP
        $direccionIP = $_SERVER['REMOTE_ADDR'];


        if (empty($wallet) OR (strlen($wallet) < 97)) {
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


        if (!$faucet->verifyClaimValidity($wallet, $paymentidPost)){
            header('Location: ./?msg=notYet');
            exit();
        }

        if($faucet->disposable_balance < 1){
            header('Location: ./?msg=dry');
            exit();
        }
      

       /* $bitcoin = new jsonRPCClient('http://127.0.0.1:8070/json_rpc');
        $balance = $bitcoin->getbalance();
        $balanceDisponible = $balance['available_balance'];
        $transactionFee = 100000000;
        $dividirEntre = 1000000000000;
        $hasta = number_format(round($balanceDisponible / $dividirEntre, 12), 2, '.', '');

        if ($hasta > $maxReward) {
            $hasta = $maxReward;
        }
        if ($hasta < ((float) $minReward + 0.1)) {
            header('Location: ./?msg=dry');
            exit();
        }*/

        $prize = $faucet->generateRandomPrize();
      
        $result = $faucet->transferPrize($wallet, $paymentID, $prize);
  
        echo '<pre>$result: '; print_r($result); echo '</pre>'; die();
        $aleatorio = randomize($minReward, $hasta);

        $cantidadEnviar = ($aleatorio * $dividirEntre) - $transactionFee;


        $destination = array('amount' => $cantidadEnviar, 'address' => $wallet);
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
        }

        if (array_key_exists('tx_hash', $transferencia)) {
            $query = "INSERT INTO `payouts` (`payout_amount`,`ip_address`,`payout_address`,`payment_id`,`timestamp`) VALUES ('$cantidadEnviar','$direccionIP','$wallet','$paymentID',NOW());";

            $link->exec($query);
            header('Location: ./?msg=success&txid=' . $transferencia['tx_hash'] . '&amount=' . $aleatorio);
            exit();
        }


    
} else {
    header('Location: ./?msg=captcha');
    exit();
}

exit();
