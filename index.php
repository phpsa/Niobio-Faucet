<?php

require_once 'classes/Faucet.php';
?><!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title><?php echo $faucet->lang('site_title'); ?></title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='shortcut icon' href='images/favicon.ico'>
    <link rel='icon' type='image/icon' href='images/favicon.ico'>

    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
    <link rel='stylesheet' href='/css/style.css'>

    <script>var isAdBlockActive = true;</script>
    <script src='/js/advertisement.js'></script>
    <script>
        if (isAdBlockActive) {
            window.location = './adblocker.php'
        }
    </script>

    <!--Analytics Code Here-->
</head>

<body>

<div class='container'>

    <div id='login-form'>


        <h3><a href='./'><img src='<?php echo $faucet->config('logo'); ?>' height='256'></a><br/><br/> <?php echo $faucet->lang('site_header'); ?></h3>


        <fieldset>
            <div class="alert alert-info">
            SpesCoin endeavours to help non-governmental charities reach their goals, mainly focused on disaster charities and children’s charities
            </div>
            <!--h3>Ad Block 1</h3-->
            <br/>
            <form action='request.php' method='POST'>
            <?php if (isset($_GET['msg'])) {
                $message = $_GET['msg'];

                    if ($message == 'captcha') {
                    ?>
                        <div id='alert' class='alert alert-error radius'>
                          <?php echo $faucet->lang('invalid_captcha'); ?>
                        </div>
                    <?php } else if ($message == 'wallet') {?>

                        <div id='alert' class='alert alert-error radius'>
                            <?php echo $faucet->lang('invalid_wallet'); ?>
                        </div>
                    <?php } else if ($message == 'success') {?>

                        <div class='alert alert-success radius'>
                            You've earned <?php echo $_GET['amount']; ?> SpesCoin.<br/><br/>
                            We've transfered <?php echo $_GET['amount'] - 0.0001; ?> Spes. (Transfer Fee 0.0001)<br/>
                            <a target='_blank'
                               href='http://pool.myspes.org/?hash=<?php echo $_GET['txid']; ?>#blockchain_block'>Confirm in Blockchain.</a>
                        </div>
                    <?php } else if ($message == 'paymentID') {?>

                        <div id='alert' class='alert alert-error radius'>
                        <?php echo $faucet->lang('invalid_payment_id'); ?>
                        </div>
                    <?php } else if ($message == 'notYet') {?>

                        <div id='alert' class='alert alert-warning radius'>
                            <?php echo $faucet->lang('too_soon'); ?>
                        </div>
                    <?php } else if ($message == 'dry') {?>

                        <div id='alert' class='alert alert-warning radius'>
                            <?php echo $faucet->lang('no_funds'); ?>
                        </div>
                    <?php }?>

                <?php }?>
                <div class='alert alert-info radius'>
                <?php if($faucet->faucet_error): ?>
    <?php echo $faucet->faucet_error; ?>
                <?php else: ?>
                    Balance: <?php echo $faucet->disposable_balance ?> SPES.<br>
                    <?php endif; ?>
                
                    Payments: <?php echo $faucet->getTotalPayoutsValue(); ?> in <?php echo $faucet->getTotalPayoutsCount(); ?> payments.
                </div>

                <?php if ($faucet->disposable_balance < 1 ) {?>
                    <div class='alert alert-warning radius'>
                        The wallet is empty, <br> Come back later.
                    </div>

                <?php 
} else {
    ?>

<!--h3>Ad Block 2</h3-->
                    <input type='text' name='wallet' required placeholder='SPEC Wallet Address'>

                    <input type='text' name='paymentid' placeholder='PaymentID (Optional)'>
                    <br/>
                    <!--h3>Ad Block 3</h3-->
                
                    <br/>
                    <?php

    echo $faucet->recaptchaRender();
    ?>
    <!--h3>Ad Block 4</h3-->

                    <center><input type='submit' value='Get your Free SPEC'></center>
                    <br>
                    <!--h3>Ad Block 5</h3-->
                <?php }?>
                <br>


                
                </div>
                <p style='font-size:12px;'>Donate to the Faucet: <span style='font-size:10px;'><?php echo $faucet->config('address'); ?></span>
                    <br>&#169; 2018 Faucet by OmnihostNZ</p></center>
                <footer class='clearfix'>
                    <a href="https://spescoin.com">More about spescoin</a>
                </footer>
            </form>

        </fieldset>
    </div> <!-- end login-form -->

</div>
<script src='//code.jquery.com/jquery-1.11.3.min.js'></script>
<?php if (isset($_GET['msg'])) {?>
    <script>
        setTimeout(function () {
            $('#alert').fadeOut(3000, function () {
            });
        }, 10000);
    </script>
<?php }?>
</body>
</html>
