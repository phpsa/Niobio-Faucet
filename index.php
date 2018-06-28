<?php
require_once '_common.php';

?><!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title><?php echo $config['title']; ?></title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='shortcut icon' href='images/favicon.ico'>
    <link rel='icon' type='image/icon' href='images/spes.ico'>

    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
    <link rel='stylesheet' href='/css/style.css'>

    <script>var isAdBlockActive = true;</script>
    <script src='/js/advertisement.js'></script>
    <script>
        if (isAdBlockActive) {
            window.location = './adblocker.php'
        }
    </script>

<script src='https://www.google.com/recaptcha/api.js'></script>

<?php if($config['ga_analytics']): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $config['ga_analytics']; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo $config['ga_analytics']; ?>');
</script>
<?php endif ;?>
</head>

<body>

<div class='container'>

    <div id='login-form'>


        <h3><a href='./'><img src='<?php echo $config['logo']; ?>' ></a><br/><br/> <?php echo $config['subtitle']; ?></h3>

    <p style="margin-top:10px;"><a href="http://myspes.org/" target="_blank">SpesCoin Information</a> | <a href="https://github.com/SpesCoin/SpesCoin-GUI-Wallet/releases" target="_blank">SpesCoin Wallet</a> | <a target="_blank" href="https://cryptohub.online/?ref=22726">Exchange</a> </p>
        <?php
            $exch_rate = getCurrentExchangeRate();
            if($exch_rate): ?>
                <h3><center><strong>1 SPES = <?php echo $exch_rate;?> BTC</h3>
        <?php endif; ?>

        <fieldset>

            <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
            <center>
            <?php echo $earning_ads[0]; ?>

            </center><br />

            <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->

            <br/>


            <form action='request.php' method='POST'>

                <?php if (isset($_GET['msg'])) {
    $message = $_GET['msg'];

    if ($message == 'captcha') {
        ?>
                        <div id='alert' class='alert alert-error radius'>
                            Captcha Invalid - Please try again
                        </div>
                    <?php } else if ($message == 'wallet') {?>

                        <div id='alert' class='alert alert-error radius'>
                            Wallet not entered correctly
                        </div>
                    <?php } else if ($message == 'success') {?>


                        <?php $prize = fetchPrizeFromDatabase(filter_input(INPUT_GET, 'tx'));
                        if(!$prize): ?>
                        <div class='alert alert-error radius'>
    Error Validating Prize.
                        </div>
                    <?php else: ?>

                        <div class='alert alert-success radius'>

                        <?php if($prize->charity_address): ?>
                        <?php $charity_share_value = round($prize->payout_amount * 0.15, 10) ; ?>
                        Congratulations!!! - You spun <b><?php echo $prize->drawn; ?></b>
                        and have been awarded with <strong><?php echo $prize->payout_amount; ?></strong> SpesCoin.<br/><br/>
                        There is a 0.0001 SpesCoin Transaction charge per payout so you will receive <?php echo round($prize->payout_amount - $charity_share_value - 0.0001, 10); ?><br />
                        Your Charity will receive <?php echo $charity_share_value;?>

                        <?php else: ?>

                        Congratulations!!! - You spun <b><?php echo $prize->drawn; ?></b>
                        and have been awarded with <strong><?php echo $prize->payout_amount; ?></strong> SpesCoin.<br/><br/>
                        There is a 0.0001 SpesCoin Transaction charge per payout so you will receive <?php echo round($prize->payout_amount - 0.0001, 10); ?><br />
                    <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php } else if ($message == 'paymentID') {?>

                        <div id='alert' class='alert alert-error radius'>
                        Please check again your payment ID. <br>It should have 64 characters with no special chars.
                        </div>
                    <?php } else if ($message == 'notYet') {?>

                        <div id='alert' class='alert alert-warning radius'>
                        You requested a reward less than an hour ago.
                        </div>
                    <?php } else if ($message == 'dry') {?>

                        <div id='alert' class='alert alert-warning radius'>
                        Faucet is empty or balance is lower than reward. <br> Wait for a reload or donation.
                        </div>
                    <?php }?>

                <?php }?>
                <div class='alert alert-info radius'>
                    Available Balance: <?php echo getWalletBalance(10); ?> SpesCoin.<br>
                    Already Paid: <?php echo totalPayed(); ?> in <?php echo totalPayments(); ?> payouts.
                </div>
                <p>You can win anything from <?php echo $config['minReward']; ?> to <?php echo $config['maxReward']; ?> SPES every hour</p>

                <?php if (getWalletBalance(10) < 10) {?>
                    <div class='alert alert-warning radius'>
                    Faucet is empty or balance is lower than reward. <br> Wait for a reload or donation.
                    </div>

                <?php } else {?>
                <h3>What is SpesCoin</h3>
                    <p>SpesCoin endeavours to help non-governmental charities reach their goals, mainly focused on disaster charities and children’s charities</p>
                    <p><a href="https://myspes.org" target="_blank">More about SpesCoin</a></p>

                <p>Need A Wallet?<br /><a href="https://github.com/SpesCoin/SpesCoin-GUI-Wallet/releases" target="_blank">Official SpesCoin Wallet</a> </p>

                    <input id="wallet" type='text' name='wallet' required placeholder='SpesCoin Wallet receive Address'>

                    <input id="paymID" type='text' name='paymentid' placeholder="Payment ID (Optional)">

                    <p>Donate 15% of winnings to charity & double your chance of higher winnings</p>

                    <select name="charity" id="charitydonation">
                            <option value="">No Donation</div>
                            <?php foreach(Config::get('charities') as $charity => $charity_add): ?>
                            <option value="<?php echo $charity; ?>"><?php echo $charity; ?></option>
                            <?php endforeach; ?>
                    </select>
                    <br/>
                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->

                    <div class="row">
    <div class="col-md-12">
            <h3>What can I Earn</h3>
            <table class="table table-striped nocharity">
                   <thead>
                        <tr>
                            <th>Number</th>
                            <th>Prize</th>
                        </tr>
                   </thead>
                   <tbody>
                    <tr>
                    <td>
0 - 9885
                    </td>
                    <td>
1 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9886 - 9985
                    </td>
                    <td>
2 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9986 - 9993
                    </td>
                    <td>
4 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9994 - 9997
                    </td>
                    <td>
6 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9998 - 9999
                    </td>
                    <td>
8 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
10000
                    </td>
                    <td>
10 Spes
                    </td>
                    </tr>
                   </tbody>
            </table>

 <table class="table table-striped charity">
                   <thead>
                        <tr>
                            <th>Number</th>
                            <th>Prize</th>
                        </tr>
                   </thead>
                   <tbody>

<tr>
                    <td>
0000
                    </td>
                    <td>
10 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
0001-0002
                    </td>
                    <td>
8 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
0003 - 0007
                    </td>
                    <td>
6 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
0008 - 0015
                    </td>
                    <td>
4 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
0016 - 0115
                    </td>
                    <td>
2 Spes
                    </td>
                    </tr>

                    <tr>
                    <td>
0117 - 9885
                    </td>
                    <td>
1 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9886 - 9985
                    </td>
                    <td>
2 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9986 - 9993
                    </td>
                    <td>
4 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9994 - 9997
                    </td>
                    <td>
6 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
9998 - 9999
                    </td>
                    <td>
8 Spes
                    </td>
                    </tr>
                    <tr>
                    <td>
10000
                    </td>
                    <td>
10 Spes
                    </td>
                    </tr>
                   </tbody>
            </table>

            </div>
            </div>


                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
                    <br/>
                    <div class="g-recaptcha" data-sitekey="<?php echo $config['recaptcha']['site_key']; ?>"></div>

                    <center>
                    <?php echo $earning_ads[1]; ?>
                    </center><br />


                    <div class='alert alert-info radius'>
                        Did you remember your PaymentID if you are using an exchange?
                    </div>
                    <label><?php echo getSecondCaptcha(); ?></label>
                    <input id="human_verification" type="text" name="human_verification" required placeholder="" />
<p>
                    <center><input id="submt" disabled="disabled" type='submit' value='Claim Your SpesCoin'></center>
                    <br>

                    <?php echo $earning_ads[2]; ?>
                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
                         <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->

                <?php }?>
                <br>

<div class="row">
            <div class="col-md-12">
            <h3>Recent Payments</h3>
            <table class="table table-bordered table-sm table-striped table">
                   <thead>
                        <tr>
                            <th>Address</th>
                            <th>Drawn</th>
                            <th>Prize</th>
                            <th>Hash</th>
                        </tr>
                   </thead>
                   <tbody>
<?php foreach(lastPayed(10) as $earner): ?>
    <tr>
    <td><?php echo $earner['payout_address'];?><?php echo ($earner['payment_id'])?':' . $earner['payment_id']:''; ?></td>
    <td><?php echo $earner['drawn']; ?></td>
    <td><?php echo $earner['paid']; ?></td>
    <td><a target="_BLANK" href="http://pool.myspes.org/?hash=<?php echo $earner['transaction_id'];?>#blockchain_transaction">View Transaction</a>
<?php endforeach; ?>
                   </tbody>
                   </tr>
                   </table>

                    </div>
                    <div class="col-md-12">
            <h3>Top Earners</h3>
            <table class="table table-bordered table-sm table-striped table">
                   <thead>
                        <tr>
                            <th>Address</th>
                            <th>Played</th>
                            <th>Earned</th>
                        </tr>
                   </thead>
                   <tbody>
                   <?php foreach(topEarners(10) as $earner): ?>
    <tr>
    <td><?php echo $earner['payout_address'];?><?php echo ($earner['payment_id'])?':' . $earner['payment_id']:''; ?></td>
    <td><?php echo $earner['spins']; ?></td>
    <td><?php echo $earner['total']; ?></td>
   <?php endforeach; ?>
                   </tbody>
                   </tr>
                   </table>

                    </div>
            </div>


                <p style='font-size:12px;'> 2019 Faucet by OmniHostNZ</p></center>
                <footer class='clearfix'>
                    Donate SpesCoin for the faucet to: <br />
                    <strong style="word-wrap: break-word;"><?php echo $config['address']; ?></strong> <br /><br />


  </footer>
            </form>

        </fieldset>
    </div> <!-- end login-form -->

</div>
<script src='//code.jquery.com/jquery-1.11.3.min.js'></script>
<script>


var counter = 15;
var interval = setInterval(function() {
        counter--;
        $("#submt").prop('value', 'Claim Your Spes in '+counter+' seconds');
        if (counter <= 0) {
            clearInterval(interval);
            $("#submt").prop('value', 'Claim Your SpesCoin');
            } }, 1000);

$('#human_verification').on('focus', function(e) {
    $('#submt').attr('disabled',false);

});

$('#charitydonation').on("change", function() {
    if($(this).val() === ''){
        $('.charity').hide();
        $('.nocharity').show();
    }else{
        $('.charity').show();
        $('.nocharity').hide();
    }
});


if (typeof(Storage) !== "undefined") {
    $('#wallet').on("change", function(e) {
        localStorage.setItem("wallet", $(this).val());
    });
    $('#paymID').on("change", function(e) {
        localStorage.setItem("paymID", $(this).val());
    });
	$('#charitydonation').on("change", function(e){
		localStorage.setItem("charitydonation", $(this).val());
	})

    $('#wallet').val(localStorage.getItem("wallet"));
    $('#paymID').val(localStorage.getItem("paymID"));
	$('#charitydonation').val(localStorage.getItem("charitydonation")).trigger('change');
}else{
    console.log("no Storage");
}

</script>
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
