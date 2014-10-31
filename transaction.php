<?php
/* Include Files *********************/
session_start();
include("login.php");
include("StockDownloader.php");
/*************************************/
if (!$logged_in){
	echo 'You are not logged in.';
	}
else
{
	if(!isset($_POST["type"]))
	{
		echo "<script language=\"javascript\" type=\"text/javascript\">
		window.location = 'portfolio.php';
		</script>";
	}
   	else
   	{
		if($_POST["type"] == "Trade")
		{
			if(stockNotFound($_POST['stock']))
			{
				echo "<script language=\"javascript\" type=\"text/javascript\">
				alert('Stock ".$_POST['stock']." not found in database');
				window.location = 'portfolio.php';
				</script>";
			}
			else
			{
				$stock = array();
				$stock[0] = $_POST['stock'];
				$stock = StockDownloader::download($stock);
				displayTrade($stock[0]);
			}
		}
		else
			displayCash();
	}
}

// return true if symbol not in database
function stockNotFound($symbol){
   $symbol = strtoupper($symbol);
   global $db;
   $q = "select id_stock from stocks where symbol = '$symbol'";
   $result = $db->send_sql($q);
   return (mysql_numrows($result) < 1);
}

// show the form for entering a stock trade
function displayTrade($stock)	{
	$style = $_SESSION['option_name'];
	echo "
	<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
	 <title>Stock Trade</title>
	 <link href=\"".$style."\" rel=\"stylesheet\" type=\"text/css\" />
	</head>
	<body>
	<div id=\"header\"><div id=\"header2\">
			<div id=\"logo\">
				<h1>StockNet</h1>
				<p id=\"subtitle\">Template designed by WebTemplateOcean.com</p>
			</div>
	</div></div><!-- header --><!-- header2 -->
	<div id=\"main\"><div id=\"main2\">

	<div id=\"content\">
		<div class=\"post\">
			<h2>Stock Trade</h2>
			<div class=\"entry\">
			<form action=\"portfolio.php\" method=\"post\">
			<table>
			<tr><td>Symbol:</td><td>".$stock->symbol()."</td></tr>
			<tr><td>Type:</td><td><select name=\"type\"><option value=\"buy\" selected=\"selected\">Buy</option><option value=\"sell\">Sell</option></select></td></tr>
			<tr><td>Shares:</td><td><input type=\"text\" name=\"shares\" maxlength=\"20\" autocomplete=\"off\"></td></tr>
			<tr><td>Price/Share:</td><td><input type=\"text\" name=\"price\" maxlength=\"20\" value=\"".$stock->lastTrade()."\" autocomplete=\"off\" readonly></td></tr>
			<tr><td colspan=\"2\" align=\"right\"><input type=\"submit\" name=\"subtrade\" value=\"Save\"><input type=\"submit\" name=\"subtrade\" value=\"Cancel\"></td></tr>
			</table>
			<input type=\"hidden\" name=\"stock\" value=\"".$stock->symbol()."\">
			</form>
		</div>
	</div>
	</div><!-- content -->
	<div class=\"clearing\">&nbsp;</div>
	</div></div><!-- main --><!-- main2 -->
	</body>
	</html>
	";
}

// show the form for making a cash transaction
function displayCash()	{
	$style = $_SESSION['option_name'];
	echo "
	<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
	 <title>Stock Trade</title>
	 <link href=\"".$style."\" rel=\"stylesheet\" type=\"text/css\" />
	</head>
	<body>
	<div id=\"header\"><div id=\"header2\">
			<div id=\"logo\">
				<h1>StockNet</h1>
				<p id=\"subtitle\">Template designed by WebTemplateOcean.com</p>
			</div>
	</div></div><!-- header --><!-- header2 -->
	<div id=\"main\"><div id=\"main2\">

	<div id=\"content\">
		<div class=\"post\">
			<h2>Cash Transaction</h2>
			<div class=\"entry\">
			<form action=\"portfolio.php\" method=\"post\">
			<table>
			<tr><td>Type:</td><td><select name=\"type\"><option value=\"cashin\" selected=\"selected\">Cash In</option><option value=\"cashout\">Cash Out</option></select></td></tr>
			<tr><td>Amount:</td><td><input type=\"text\" name=\"amount\" maxlength=\"20\" autocomplete=\"off\"></td></tr>
			<tr><td colspan=\"2\" align=\"right\"><input type=\"submit\" name=\"subcash\" value=\"Save\"><input type=\"submit\" name=\"subcash\" value=\"Cancel\"></td></tr>
			</table>
			</form>
		</div>
	</div>
	</div><!-- content -->
	<div class=\"clearing\">&nbsp;</div>
	</div></div><!-- main --><!-- main2 -->
	</body>
	</html>
	";
}


?>