<?php
/* Include Files *********************/
session_start();
include("login.php");
include("StockDownloader.php");
/*************************************/
if (!$logged_in){
	echo 'You are not logged in.';
	}
	else{

		if(isset($_POST['addsymbol'])){
		   /* Make sure all fields were entered */
		   if(!$_POST['stock']){
				echo "<script language=\"javascript\" type=\"text/javascript\">
				alert('You didn't enter a stock symbol');
				</script>";
			  redirectHere();
		   }
		   if(stockNotFound($_POST['stock'])){
				echo "<script language=\"javascript\" type=\"text/javascript\">
				alert('Stock ".$_POST['stock']." was not found in database');
				</script>";
				redirectHere();

			}

			addToWatchlist($_POST['stock']);
			redirectHere();

		}
	   	if(isset($_GET['removesymbol'])){
			removeFromWatchlist($_GET['removesymbol']);
			redirectHere();
		}
		else{
			displayWatchlist();
		}

	}

// redirect to watchlist without submitting post request
function redirectHere()
{
	echo "<script language=\"javascript\" type=\"text/javascript\">
	window.location = 'watchlist.php';
	</script>";
}

function stockNotFound($symbol){
	   $symbol = strtoupper($symbol);
	   global $db;
	   $q = "select id_stock from stocks where symbol = '$symbol'";
	   $result = $db->send_sql($q);
	   return (mysql_numrows($result) < 1);
	}
function addToWatchlist($symbol){
	$symbol = strtoupper($symbol);
	global $db;
	$user = $_SESSION['username'];
	$q = "select id_user from users where username = '$user'";
	$result = $db->send_sql($q);
	$dbarray = mysql_fetch_array($result);
    $userid= $dbarray['id_user'];
	$q = "select id_stock from stocks where symbol = '$symbol'";
	$result = $db->send_sql($q);
	$dbarray = mysql_fetch_array($result);
    $stockid= $dbarray['id_stock'];
	$q = "select w.id_stock from stock_in_watchlist AS w, stocks AS s, users AS u where w.id_stock = '$stockid' and w.id_user = '$userid'";
	   $result = $db->send_sql($q);
	   if(mysql_numrows($result) > 0){
			echo "<script language=\"javascript\" type=\"text/javascript\">
			alert('Symbol already exists in your watchlist');
			</script>";
		}
		else{
			$q = "INSERT INTO stock_in_watchlist (id_user, id_stock) VALUES ('$userid', '$stockid')";
			if($result = $db->send_sql($q)){
				echo "<script language=\"javascript\" type=\"text/javascript\">
				alert('Stock ".$symbol." added successfully to your watchlist');
				</script>";
			}
			else {
				echo "<script language=\"javascript\" type=\"text/javascript\">
				alert('Error adding stock to watchlist');
				</script>";
			}
		}
	}

function displayWatchlist()	{
	$style = $_SESSION['option_name'];
	echo "
	<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
	<title>StockNet Watchlist</title>
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

	function checksymbol() {
		if (document.addstock.stock.value == \"Enter stock symbol\") {
			// something is wrong
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
	<h3>Add stock symbol to watchlist</h3>

    <p>
	<form action=\"$_SERVER[PHP_SELF]\" name=\"addstock\" onsubmit=\"return checksymbol();\" method=\"post\">
	<input type=\"text\" name=\"stock\" maxlength=\"10\" value=\"Enter stock symbol\"
	onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Enter stock symbol':this.value;\">
	<input type=\"submit\" name=\"addsymbol\" value=\"Add Symbol\">
	</form>
	</p>
	<br>
	<form action=\"quotes.php\" name=\"myForm\" onsubmit=\"return checkscript();\" method=\"get\">
	<input type=\"text\" name=\"stock\" maxlength=\"10\" value=\"Enter stock symbol\"
	onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Enter stock symbol':this.value;\">
	<input type=\"submit\" name=\"getquote\" value=\"Get Quote\">
	</form>
	</div><!-- sidebar -->


	";
	global $db;
	$user = $_SESSION['username'];
	$display_name = $_SESSION['display_name'];
	$q = "select id_user from users where username = '$user'";
	$result = $db->send_sql($q);
	$dbarray = mysql_fetch_array($result);
    $userid= $dbarray['id_user'];
	$q = "select s.symbol from stock_in_watchlist AS w, stocks AS s where w.id_stock = s.id_stock and w.id_user = '$userid' ORDER BY s.symbol ASC";
	$result = $db->send_sql($q);
	$num=mysql_num_rows($result);
	  if($num < 1){
			 echo "<div id=\"content\">
	                <div class=\"post\">
	                    <h2>".$display_name."'s WatchList</h2>
						<div class=\"entry\">
						<p>There are no symbols in your watchlist.<br>
						   Add symbols using the menu on the left.
						</p>
						</div>
						</div>
						</div><!-- content -->
						<div class=\"clearing\">&nbsp;</div>
						</div></div><!-- main --><!-- main2 -->
						</body>
						</html>
						";
		}
		else {
			$symbols = array();
			$i=0;
			 echo "<div id=\"content\">
	                <div class=\"post\">
	                    <h2>".$display_name."'s WatchList</h2>
						<div class=\"entry\">";
			//echo $display_name."'s WATCHLIST";
			//echo "<table>";
			while ($i < $num) {

			$stock = mysql_result($result,$i,"symbol");
			$symbols[$i]=$stock;

			$i++;

			}

			if(!$stocks = StockDownloader::download($symbols))
				echo "error";
			else

				echo "<table class=\"mypad\">";
				echo "<tr>";
				echo "<th></th><th>Symbol</th><th>Price</th><th>Change(%)</th>";
				echo "</tr>";
				foreach($stocks as $stock)
				{
					$stock->displaywatchlist();
				}

			echo "</table>";

		}
		echo"
		</div>
		</div>
		</div><!-- content -->
		<div class=\"clearing\">&nbsp;</div>
		</div></div><!-- main --><!-- main2 -->
		</body>
		</html>
		";

}

function removeFromWatchlist($symbol) {
	   $symbol = strtoupper($symbol);
	   global $db;
	   $user = $_SESSION['username'];
	   $q = "select id_user from users where username = '$user'";
	   $result = $db->send_sql($q);
       $dbarray = mysql_fetch_array($result);
       $userid= $dbarray['id_user'];
	   $q = "select id_stock from stocks where symbol = '$symbol'";
	   $result = $db->send_sql($q);
	   $dbarray = mysql_fetch_array($result);
       $stockid= $dbarray['id_stock'];
	   $q = "select id_stock from stock_in_watchlist where id_stock = '$stockid' and id_user = '$userid'";
	   $result = $db->send_sql($q);
	   $num = mysql_numrows($result);
	if($num<1){
		echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Stock ".$symbol." was not found in your watchlist');
		</script>";
	}
	else{
		$q = "DELETE FROM stock_in_watchlist WHERE id_stock = '$stockid' and id_user = '$userid'";
		if($result = $db->send_sql($q)){
			echo "<script language=\"javascript\" type=\"text/javascript\">
			alert('Stock ".$symbol." was removed successfully from your watchlist');
			</script>";
		}
		else {
			echo "<script language=\"javascript\" type=\"text/javascript\">
			alert('Error removing stock from watchlist');
			</script>";
		}
	}
}

?>