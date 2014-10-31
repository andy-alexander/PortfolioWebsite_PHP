<?php
/* Include Files *********************/
session_start();
include("login.php");
include("portfolioClass.php");
/*************************************/
if (!$logged_in){
	echo "<script language=\"javascript\" type=\"text/javascript\">
	window.location = 'main.php';
	</script>";
	}
	else{
		if(isset($_POST['subtrade']))
			addStockTransaction();
		else if(isset($_POST['subcash']))
			addCashTransaction();
		displayPortfolio();
	}

// display a javascript alert
function alert($text)
{
	echo "<script language=\"javascript\" type=\"text/javascript\">
	alert('".$text."');
	</script>";
}

// validate input and add stock transaction to database
function addStockTransaction()
{
	if($_POST['subtrade'] == "Cancel")
		die(displayPortfolio());
   	$shares = $_POST['shares'];
   	$price = $_POST['price'];
  	if(is_numeric($shares) && ($shares = intval($shares)) > 0)
   	{
		if($_POST['type'] == "sell")
			$shares = -1 * $shares;
		if(is_numeric($price) && ($price = floatval($price)) > 0)
		{
			$portfolio = new Portfolio();
			if($shares * $price > $portfolio->cashBalanceNumeric())
				alert("You do not have enough cash to buy that many shares");
			else if($portfolio->numShares($_POST['stock']) < -1 * $shares)
				alert("You do not have that many shares to sell");
			else
				if($portfolio->addStockTransaction($_POST['stock'], $shares, $price))
					if($shares > 0)
						alert("Successful purchased ".$shares." shares of ".$_POST['stock']." at a total price of ".number_format($shares * $price, 2));
					else
						alert("Successful sold ".-1*$shares." shares of ".$_POST['stock']." at a total price of ".number_format(-1 * $shares * $price, 2));
				else
					if($shares > 0)
						alert("Could not make purchase of ".$_POST['stock']);
					else
						alert("Could not make sale of ".$_POST['stock']);
		}
		else
			alert("The price must be a positive decimal");
   	}
   	else
   		alert("The number of shares must be a positive integer");
	// redirect to portfolio without submitting post request
	echo "<script language=\"javascript\" type=\"text/javascript\">
	window.location = 'portfolio.php';
	</script>";
}

// validate input and add cash transaction to database
function addCashTransaction()
{
	if($_POST['subcash'] == "Cancel")
		die(displayPortfolio());
   	$amount = $_POST['amount'];
   	if(is_numeric($amount) && ($amount = floatval($amount)) > 0)
   	{
		if($_POST['type'] == "cashout")
			$amount = -1 * $amount;
		$portfolio = new Portfolio();
		if($portfolio->cashBalanceNumeric() < -1 * $amount)
			alert("You do not have enough cash to withdraw that amount");
		else
		{
			if($portfolio->addCashTransaction($amount))
				if($amount > 0)
					alert("Successfuly deposited ".number_format($amount, 2)." into your account");
				else
					alert("Successfuly withdrew ".number_format(-1 * $amount, 2)." from your account");
			else
				alert("Error making cash transaction");
		}
   	}
   	else
   		alert("The amount must be a positive decimal");
	// redirect to portfolio without submitting post request
	echo "<script language=\"javascript\" type=\"text/javascript\">
	window.location = 'portfolio.php';
	</script>";
}

// display the page
function displayPortfolio()	{
	$style = $_SESSION['option_name'];
	$portfolio = new Portfolio($_SESSION["username"]);
	echo "
	<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
	<title>StockNet Portfolio</title>
	<link href=\"".$style."\" rel=\"stylesheet\" type=\"text/css\" />
	<script language=\"javascript\" type=\"text/javascript\">
	function checkscript() {
		if (document.myForm.stock.value == \"Enter stock symbol\") {
			// something is wrong
			alert(\"Please enter a stock symbol!\");
			return false;
		}
		return true;
	}
	
	function check() {
		if (document.transaction.stock.value == \"Enter stock symbol\"){
			alert(\"Please enter a stock symbol!\");
			return false;
		}
		   return true;
	}
	
	</script>
	</head>
	<body>
	<div id=\"header\"><div id=\"header2\">
            <div id=\"logo\">
                <h1><a href=\"#\">StockNet</a></h1>
                <p id=\"subtitle\">Template designed by WebTemplateOcean.com</p>
            </div>
            <div id=\"menu\">
                <ul>
					<li><a href=\"profile.php\">Profile</a></li>
                    <li><a href=\"watchlist.php\">Watchlist</a></li>
                    <li><a href=\"portfolio.php\">Portfolio</a></li>
                    <li><a href=\"logout.php\">Log Out</a></li>
                </ul>
            </div>
    </div></div><!-- header --><!-- header2 -->
	<div id=\"main\"><div id=\"main2\">
    <div id=\"sidebar\">
	<h2>Welcome ".$_SESSION['display_name']."</a></h2>
	<h3>Manage portfolio</h3>

    <p>
	<form action=\"transaction.php\"  method=\"post\"  onsubmit=\"return check();\" name=\"transaction\">
	<input type=\"text\" name=\"stock\" maxlength=\"10\" value=\"Enter stock symbol\"
	onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Enter stock symbol':this.value;\">
	<input type=\"submit\" name=\"type\" value=\"Trade\">
	</form>
	<br>

	<form action=\"transaction.php\"  method=\"post\" name=\"cashtransaction\">
	<input type=\"submit\" name=\"type\" value=\"Make Cash Transaction\">
	</form>
	</p>
	<br>
	<form action=\"quotes.php\" name=\"myForm\" onsubmit=\"return checkscript();\" method=\"get\">
	<input type=\"text\" name=\"stock\" maxlength=\"10\" value=\"Enter stock symbol\"
	onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Enter stock symbol':this.value;\">
	<input type=\"submit\" name=\"getquote\" value=\"Get Quote\">
	</form>
	</div>
	<!-- sidebar -->
	";

	echo "
	<div id=\"content\">
	<div class=\"post\">
	<h2>".$_SESSION["display_name"]."'s Portfolio</h2>
	<p>
	";
	$portfolio->display();
	echo "
	</p>
	</div>
	</div><!-- content -->
	<div class=\"clearing\">&nbsp;</div>
	</div></div><!-- main --><!-- main2 -->
	</body>
	</html>
	";

}

?>