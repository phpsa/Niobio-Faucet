<?php
require_once 'classes/recaptcha.php';
require_once 'config.php';

try {
    $link = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'], $config['db']['user'], $config['db']['password']);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?><!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title><?php echo $config['title']; ?></title>
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

<script src='https://www.google.com/recaptcha/api.js'></script>


    <!--ANALYTICS HERE!!-->
    <!-- Global site tag (gtag.js) - Google Analytics -->

</head>

<body>

<div class='container'>

    <div id='login-form'>


        <h3><a href='./'><img src='<?php echo $config['logo']; ?>' ></a><br/><br/> <?php echo $config['subtitle']; ?></h3>

<p><a href="http://myspes.org/" target="_blank">SpesCoin Information</a> | <a href="https://github.com/SpesCoin/SpesCoin-GUI-Wallet" target="_blank">SpesCoin Wallet</a> </p>

        <fieldset>

            <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
            <center><a href="http://freedoge.co.in/?r=1528473" target="_blank"><img class="img-responsive" src="http://static1.freedoge.co.in/banners/468x60-3.png" /></a></center><br />


            <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->

            <br/>


            <?php

$query             = "SELECT * FROM `wallet`";
$result            = $link->query($query, PDO::FETCH_ASSOC);
$balance           = $result->fetchObject();
$balanceDisponible = $balance->balance - $balance->pending;
$lockedBalance     = $balance->pending;
$dividirEntre      = 1;
$totalBCN          = ($balanceDisponible + $lockedBalance) / $dividirEntre;

//Available Balance
$balanceDisponibleFaucet = number_format(round($balanceDisponible / $dividirEntre, 5), 5, '.', '');
?>

            <form action='request.php' method='POST'>

                <?php if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];

    if ($mensaje == 'captcha') {
        ?>
                        <div id='alert' class='alert alert-error radius'>
                            Captcha Invalid - Please try again
                        </div>
                    <?php } else if ($mensaje == 'wallet') {?>

                        <div id='alert' class='alert alert-error radius'>
                            Wallet not entered correctly
                        </div>
                    <?php } else if ($mensaje == 'success') {?>

                        <div class='alert alert-success radius'>
                        You spun <b><?php echo $_GET['draw']; ?></b> and have been awarded with <strong><?php echo $_GET['amount']; ?></strong> SpesCoin.<br/><br/>
                        We are currently doing payments manually once per day, so you should recieve your payout within 24 hours.<br />
                        Your Current Balance is: <?php echo $_GET['pending']; ?><br >
                        There is a 0.0001 SpesCoin Transaction charge per payout so you will recieve <?php echo round($_GET['pending'] - 0.0001, 10); ?><br />

                        </div>
                    <?php } else if ($mensaje == 'paymentID') {?>

                        <div id='alert' class='alert alert-error radius'>
                        Please check again your payment ID. <br>It should have 64 characters with no special chars.
                        </div>
                    <?php } else if ($mensaje == 'notYet') {?>

                        <div id='alert' class='alert alert-warning radius'>
                        You requested a reward less than an hour ago.
                        </div>
                    <?php } else if ($mensaje == 'dry') {?>

                        <div id='alert' class='alert alert-warning radius'>
                        Faucet is empty or balance is lower than reward. <br> Wait for a reload or donation.
                        </div>
                    <?php }?>

                <?php }?>
                <div class='alert alert-info radius'>
                    Available Balance: <?php echo $balanceDisponibleFaucet ?> SpesCoin.<br>
                    <?php

$query  = 'select sum(`payout_amount`) as sum from payouts';
$result = $link->query($query);
$dato   = $result->fetchObject();

$query2  = 'SELECT COUNT(*) as total FROM `payouts`';
$result2 = $link->query($query2);
$dato2   = $result2->fetchObject();

?>

                    Already Paid: <?php echo round($dato->sum / $dividirEntre, 10); ?> in <?php echo $dato2->total; ?> payouts.
                </div>
                <p>You can win anything from <?php echo $config['minReward']; ?> to <?php echo $config['maxReward']; ?> SPES every hour</p>

                <?php if ($balanceDisponibleFaucet < 10) {?>
                    <div class='alert alert-warning radius'>
                    Faucet is empty or balance is lower than reward. <br> Wait for a reload or donation.
                    </div>

                <?php } elseif (!$link) {

    // $link = mysqli_connect($hostDB, $userDB, $passwordDB, $database);

    die('DB Error' . mysql_error());
} else {?>
                <h3>What is SpesCoin</h3>
                    <p>SpesCoin endeavours to help non-governmental charities reach their goals, mainly focused on disaster charities and children’s charities</p>
                    <p><a href="https://myspes.org" target="_blank">More about SpesCoin</a></p>

                <p>Need A Wallet?<br /><a href="https://github.com/SpesCoin/SpesCoin-GUI-Wallet" target="_blank">Official SpesCoin Wallet</a> </p>

                    <input id="wallet" type='text' name='wallet' required placeholder='SpesCoin Wallet Recieve Address'>

                    <input id="paymID" type='text' name='paymentid' placeholder="Payment ID (Optional)">
                    <br/>
                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->


                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
                    <br/>
                    <div class="g-recaptcha" data-sitekey="<?php echo $config['recaptcha']['site_key']; ?>"></div>

                    <center><a href="https://freebitco.in/?r=10148588" target="_blank"><img class="img-responsive" src="https://static1.freebitco.in/banners/468x60-3.png" /></a></center><br />

                    <center><input id="submt" disabled="disabled" type='submit' value='Get SpesCoin'></center>
                    <br>

                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
                         <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->

                <?php }?>
                <br>

            <h3>What can I Earn</h3>
            <table class="table table-striped">
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
$('#submt').attr('disabled',false);
if (typeof(Storage) !== "undefined") {
    $('#wallet').on("change", function(e) {
        localStorage.setItem("wallet", $(this).val());
    });
    $('#paymID').on("change", function(e) {
        localStorage.setItem("paymID", $(this).val());
    });
    $('#wallet').val(localStorage.getItem("wallet"));
    $('#paymID').val(localStorage.getItem("paymID"));
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
