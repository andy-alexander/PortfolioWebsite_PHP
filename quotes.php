<?php
/* Include Files *********************/
session_start();
include("login.php");
include("StockDownloader.php");
/*************************************/
if (!$logged_in){
	echo "<script language=\"javascript\" type=\"text/javascript\">
	window.location = 'main.php';
	</script>";
	}
	else{
		displayQuote();
	}

function displayQuote()	{

	if(!isset($_GET["stock"]))
		header("Location: main.php");
	$s = array();
	$s[0] = $_GET["stock"];
	if(!$stock = StockDownloader::download($s))
		echo "Error downloading stock data.  Please try again.";
	else
	{   $style = $_SESSION['option_name'];
		header('Content-Type: text/html;charset=utf-8');
		echo "
		<html xmlns=\"http://www.w3.org/1999/xhtml\">
		<head>
		<title>".$stock[0]->name()." (".$stock[0]->symbol().")</title>
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
		</script>
		</head>
		<body>
		
		<div id=\"header\"><div id=\"header2\">
	            <div id=\"logo\">
	                <h1>StockNet</h1>
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
				<h2></h2>
	      		<p>Lookup additional quotes</p>
	            <br>
				<form action=\"quotes.php\" name=\"myForm\" onsubmit=\"return checkscript();\" method=\"get\">
				<input type=\"text\" name=\"stock\" maxlength=\"10\" value=\"Enter stock symbol\"
				onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Enter stock 		 symbol':this.value;\">
				<input type=\"submit\" name=\"getquote\" value=\"Get Quote\">
				</form>
				</div><!-- sidebar -->
				
				 <div id=\"content\">			
		                <div class=\"post\">
		                    <h2>Quote</h2>
		                        <p>";
		$stock[0]->display();
		echo "</p>
		                    </div>
		                </div>
						 </div><!-- content -->                    
				            <div class=\"clearing\">&nbsp;</div>   
				    </div></div><!-- main --><!-- main2 -->
		</body>
		</html>
		";
	}

}


?>