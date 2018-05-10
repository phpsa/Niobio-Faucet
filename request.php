<?php
require_once '_common.php';


if(!confirmCaptcha()){
    header('Location: ./?msg=captcha');
    exit();
}

$wallet =  sanitizeWallet(filter_input(INPUT_POST, "wallet"));
$paymentidPost = sanitizeWallet(filter_input(INPUT_POST, "paymentid"));
$charityPost = filter_input(INPUT_POST, "charity");
$charity_address = false;
if($charityPost){
    $charities = Config::get('charities');
    if(isset($charities[$charityPost])){
        $charity_address = $charities[$charityPost];
    }
}


$wallet = trim(preg_replace('/[^a-zA-Z0-9]/', '', $wallet));
if (empty($wallet) OR (strlen($wallet) < 97)) {
    header('Location: ./?msg=wallet');
    exit();
}

$paymentidPost =  trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['paymentid']));
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

if(!verifyPaymentIdRequired($wallet, $paymentID)){
    header('Location: ./?msg=paymentID');
        exit();
}

if(!verifyClaimTime($wallet,$paymentID)){
    header('Location: ./?msg=notYet');
    exit();
}


if (getWalletBalance() < (float) Config::get('minReward')) {
    header('Location: ./?msg=dry');
    exit();
}

$prizeData = randomize($charity_address);
if($prizeData['prize'] < Config::get('minReward')){
    header('Location: ./?msg=dry');
    exit();
}

$tx = addPrizeToDatabase($prizeData,$wallet,$paymentID,$charity_address);

header('Location: ./?msg=success&tx=' . $tx);

