<?php
/* Include Files *********************/
session_start();
include("login.php");
//include("StockDownloader.php");
/*************************************/
if (!$logged_in){
	echo "<script language=\"javascript\" type=\"text/javascript\">
	window.location = 'main.php';
	</script>";
	}
	else if (isset($_POST["styleselection"])){
		changeStyle();
		die(displayProfile());
	}
	else {
		displayProfile();	
	}

function displayProfile()	{

 		$style = $_SESSION['option_name'];
		header('Content-Type: text/html;charset=utf-8');
		echo "
		<html xmlns=\"http://www.w3.org/1999/xhtml\">
		<head>
		<title>Profile</title>
		<link href=\"".$style."\" rel=\"stylesheet\" type=\"text/css\" />
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
				<h2>User Information</h2>
	      		<p>Username: ".$_SESSION['username']."</p>
				<p>Display Name: ".$_SESSION['display_name']."</p>
	            <br>
				<form action=\"quotes.php\" name=\"myForm\" onsubmit=\"return checkscript();\" method=\"get\">
				<input type=\"text\" name=\"stock\" maxlength=\"10\" value=\"Enter stock symbol\"
				onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Enter stock 		 symbol':this.value;\">
				<input type=\"submit\" name=\"getquote\" value=\"Get Quote\">
				</form>
				</div><!-- sidebar -->
				
				 <div id=\"content\">			
		                <div class=\"post\">
		                    <h2>Change website style</h2>
		                    <p>
							<form action=\"$_SERVER[PHP_SELF]\" name=\"changebackground\" method=\"post\">
							<select name=\"styleselection\">
								<option value=\"1\">Default (Blue)</option>
								<option value=\"2\">Green</option>
								<option value=\"3\">Dark grey</option>	
								<option value=\"4\">Red</option>	
							</select>
							<input type=\"submit\" name=\"addsymbol\" value=\"Change Background\">
							</form>
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

function changestyle() {
	global $db;
	$q = "select option_name from display_options where id_display = '$_POST[styleselection]'";
	$result = $db->send_sql($q);
	$dbarray = mysql_fetch_array($result);
    $style= $dbarray['option_name'];
	$currentstyle = $_SESSION['option_name'];
	if ($currentstyle == $style) {
		echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Selected style already used for your profile!');
		</script>";
	}
	else{
		$q = "update users set id_display='$_POST[styleselection]' where username = '$_SESSION[username]'";
		$result = $db->send_sql($q);
		$_SESSION['option_name']=$style;
		
	}
		
	
}


?>