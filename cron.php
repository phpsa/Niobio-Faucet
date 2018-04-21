<?php
//Cron to run every 5 minutes: - automate payments.
require_once '_common.php';
use \RedBeanPHP\R as DB;




//connect to the RPC:
try{
    $rpcWallet = new ForkNoteWalletd(Config::get('rpc_url'));
    $status = $rpcWallet->getStatus();
    if(!$status || empty($status['blockCount'])){
        throw new Exception("RPC Failure");
    }
}catch(Exception $e){
    die("RPC Not responding correctly at the moment");
}
$rpcAddress = Config::get('rpc_wallet_address');//$config['rpc_wallet_address'];

$balance = $rpcWallet->getBalance($rpcAddress);
$total = $rpcWallet->bigIntToDecimal($balance['availableBalance'] + $balance['lockedAmount']);
//Ok do we have an available balance
$humanBalance = $rpcWallet->bigIntToDecimal($balance['availableBalance']);
$max = 10;


if($humanBalance > 1){
    while($humanBalance > 1){
        $payment  = DB::findOne( 'payouts', ' paid = 0 and payout_amount <= ? and (error is null or error = "") order by id asc', [$humanBalance]);
        if(!$payment){
            die("No More payouts found");
        }
        
        $transfer = array(
            'amount' => $rpcWallet->decimalToBigInt($payment->payout_amount) - 1000000,
            'address' => $payment->payout_address
        );

        //Make the payment::
        try {
            $status = $rpcWallet->sendTransaction($rpcAddress, [$transfer], $payment->payment_id);

            if(!$status){
                $payment->error = "Investigate payment";
                DB::store($payment);
            }else{
      
                $payment->transaction_id = $status['transactionHash'];
                $payment->paid = '1';
                DB::store($payment);
                DB::exec("update `wallet` set `paidout` = `paidout` + {$payment->payout_amount}, `pending` = `pending` -  {$payment->payout_amount}");
            }
        }catch(Exception $e){
            $payment->error = $e->getMessage();
            DB::store($payment);
        }
   
        //We need to update our main balance::
        $balance = $rpcWallet->getBalance();
        $total = $rpcWallet->bigIntToDecimal($balance['availableBalance'] + $balance['lockedAmount']);
        $humanBalance = $rpcWallet->bigIntToDecimal($balance['availableBalance']);


    
        $wallet  = DB::findOne( 'wallet');
        $wallet->balance = $total;
        DB::store($wallet);
        $max--;
        if($max <= 0){
            die("Run Completed");
        }
    }
}

echo 'Done';
