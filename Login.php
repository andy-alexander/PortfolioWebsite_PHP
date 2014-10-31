<?php

include('./databaseClass.php');
$db = new database();
$db->pick_db("stocknet");
$db->connect();
$style ="style.css";

/**
 * Checks whether or not the given username is in the
 * database, if so it checks if the given password is
 * the same password in the database for that user.
 * If the user doesn't exist or if the passwords don't
 * match up, it returns an error code (1 or 2). 
 * On success it returns 0.
 */
function confirmUser($username, $password){
   global $db;
   /* Verify that user is in database */
   $q = "select password from users where username = '$username'";
   $result = $db->send_sql($q);
   if(!$result || (mysql_numrows($result) < 1)){
      return 1; //Indicates username failure
   }

   /* Retrieve password from result, strip slashes */
   $dbarray = mysql_fetch_array($result);
   $dbarray['password']  = stripslashes($dbarray['password']);
   $password = stripslashes($password);

   /* Validate that password is correct */
   if($password == $dbarray['password']){
	  
      return 0; //Success! Username and password confirmed
   }
   else{
      return 2; //Indicates password failure
   }
}

/**
 * checkLogin - Checks if the user has already previously
 * logged in, and a session with the user has already been
 * established. Also checks to see if user has been remembered.
 * If so, the database is queried to make sure of the user's 
 * authenticity. Returns true if the user has logged in.
 */
function checkLogin(){
   /* Check if user has been remembered */
   if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass']) && isset($_COOKIE['cookid']) && isset($_COOKIE['cookdisplayname']) && isset($_COOKIE['optionname']))	{
      $_SESSION['username'] = $_COOKIE['cookname'];
      $_SESSION['password'] = $_COOKIE['cookpass'];
	  $_SESSION['id_user'] = $_COOKIE['cookid'];
	  $_SESSION['display_name'] = $_COOKIE['cookdisplayname'];
	  $_SESSION['option_name'] = $_COOKIE['optionname'];
   }

   /* Username and password have been set */
   if(isset($_SESSION['username']) && isset($_SESSION['password'])){
      /* Confirm that username and password are valid */
      if(confirmUser($_SESSION['username'], $_SESSION['password']) != 0){
         /* Variables are incorrect, user not logged in */
         unset($_SESSION['username']);
         unset($_SESSION['password']);
         return false;
      }
      return true;
   }
   /* User not logged in */
   else{
      return false;
   }
}

/**
 * Determines whether or not to display the login
 * form or to show the user that he is logged in
 * based on if the session variables are set.
 */
function displayLogin(){
   global $logged_in;
   global $style; 
   if($logged_in){
	  $style = $_SESSION['option_name'];
	  $name = $_SESSION['display_name'];
/*	  $username = $_SESSION['username'];
	  $q = "select d.option_name from display_options AS d, users AS u where u.username = '$username' and d.id_display=u.id_display";
	  $result = $db->send_sql($q);
	  $dbarray = mysql_fetch_array($result);
	  $dbarray['option_name']  = stripslashes($dbarray['option_name']);
	  $style = $dbarray['option_name'];
	  $_SESSION['option_name'] = $style; */
	  echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
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
			<h2>Logged In!</h2>
      		<p>Welcome <b>".$name."</b>, you are logged in.</p>
            <br>
			<form action=\"quotes.php\" name=\"myForm\" onsubmit=\"return checkscript();\" method=\"get\">
			<input type=\"text\" name=\"stock\" maxlength=\"10\" value=\"Enter stock symbol\"
			onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Enter stock 		 symbol':this.value;\">
			<input type=\"submit\" name=\"getquote\" value=\"Get Quote\">
			</form>
			</div><!-- sidebar -->    	              
            <div id=\"content\">			
                <div class=\"post\">
                    <h2>Introduction</h2>
                        <p>This is the main screen. Use menu above to navigate through site.</p>
                    </div>
                </div>
				 </div><!-- content -->                    
		            <div class=\"clearing\">&nbsp;</div>   
		    </div></div><!-- main --><!-- main2 -->
			</body>
			</html>";

   }
   else{
echo
	"<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
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
	<div id=\"sidebar\">
	<h2>Login</h2>
	<form action=\"\" method=\"post\">
	<table>
	<tr><td>Username:</td><td><input type=\"text\" name=\"user\" maxlength=\"20\"></td></tr>
	<tr><td>Password:</td><td><input type=\"password\" name=\"pass\" maxlength=\"30\"></td></tr>
	<tr><td colspan=\"2\"><input type=\"checkbox\" name=\"remember\">Remember me</td></tr>
	<tr><td><input type=\"submit\" name=\"sublogin\" value=\"Login\"></td></tr>
	</table>
	</form>
	</div><!-- sidebar --> 
	<div id=\"content\">			
        <div class=\"post\">
            <h2>Welcome to StockNet</h2>
                <p>To use the site, log in using the menu on the left.<br>
					New user? <a href=\"Registration.php\">Register</a></p>
        </div>
		 </div><!-- content -->                    
            <div class=\"clearing\">&nbsp;</div>   
    </div></div><!-- main --><!-- main2 -->
	</body>
	</html>
";
   }
}


/**
 * Checks to see if the user has submitted his
 * username and password through the login form,
 * if so, checks authenticity in database and
 * creates session.
 */
if(isset($_POST['sublogin'])){
   /* Check that all fields were typed in */
   if(!$_POST['user'] || !$_POST['pass']){
      echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('You did not fill a required field');
		</script>";
	  die(displayLogin());
   }
   /* Spruce up username, check length */
   $_POST['user'] = trim($_POST['user']);
   if(strlen($_POST['user']) > 20){
	  echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Sorry, the username is longer than 20 characters, please shorten it.');
		</script>";
	  die(displayLogin());
   }

	if (preg_match ("/[\"\'\/\s]+/i", $_POST['user']) || preg_match ("/[\"\'\/\s]+/i", $_POST['pass']))
	{

	  echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Sorry, invalid characters were entered');
		</script>";
		die(displayLogin());
}

/*
   if(preg_match("/\w+/", $_POST['user']) || preg_match("/\w+/", $_POST['pass'])){
	  echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Sorry, invalid characters were entered');
		</script>";
		die(displayLogin());
}
*/

   



   /* Checks that username is in database and password is correct */
   $md5pass = md5($_POST['pass']);
   $result = confirmUser($_POST['user'], $md5pass);

   /* Check error codes */
   if($result == 1){
	  echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('That username doesn\'t exist in our database.');
		</script>";
	  die(displayLogin());
   }
   else if($result == 2){
	  echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Incorrect password, please try again.');
		</script>";
	  die(displayLogin());
   }

   /* Username and password correct, register session variables */
   $_POST['user'] = stripslashes($_POST['user']);
   $_SESSION['username'] = $_POST['user'];
   $_SESSION['password'] = $md5pass;
	$q = "select id_user, display_name from users where username = '$_POST[user]'";
	$result = $db->send_sql($q);
	$dbarray = mysql_fetch_array($result);
   $_SESSION['id_user'] = stripslashes($dbarray['id_user']);
   $_SESSION['display_name']  = stripslashes($dbarray['display_name']);

	$q = "select d.option_name from display_options AS d, users AS u where u.username = '$_POST[user]' and d.id_display=u.id_display";
	  $result = $db->send_sql($q);
	  $dbarray = mysql_fetch_array($result);
	  $dbarray['option_name']  = stripslashes($dbarray['option_name']);
	  $style = $dbarray['option_name'];
	  $_SESSION['option_name'] = $style;
	

   /**
    * If user requested to be remembered
    * two cookies are set: One to hold username,
    * and one to hold md5 encrypted password. Both are set to
    * expire in 100 days. Now, next time user visits site, they will be
    * logged in automatically.
    */
   if(isset($_POST['remember'])){
      setcookie("cookname", $_SESSION['username'], time()+60*60*24*100, "/");
      setcookie("cookpass", $_SESSION['password'], time()+60*60*24*100, "/");
	  setcookie("cookid", $_SESSION['id_user'], time()+60*60*24*100, "/");
	  setcookie("cookdisplayname", $_SESSION['display_name'], time()+60*60*24*100, "/");
	  setcookie("optionname", $_SESSION['option_name'], time()+60*60*24*100, "/");

	

   }

   /* Quick self-redirect to avoid resending data on refresh */
   echo "<meta http-equiv=\"Refresh\" content=\"0;url=".$_SERVER["PHP_SELF"]."\">";
   return;
}

/* Sets the value of the logged_in variable*/
$logged_in = checkLogin();

?>