<?php 

session_start(); 
include('./databaseClass.php');
$db = new database();
$db->pick_db("stocknet");
$db->connect();

/**
 * Returns true if the username has been taken
 * by another user, false otherwise.
 */
function usernameTaken($username){
   global $db;
   $q = "select username from users where username = '$username'";
   //$result = mysql_query($q,$db);
   $result = $db->send_sql($q);
   return (mysql_numrows($result) > 0);
}

/**
 * Inserts the given (username, password) pair
 * into the database. Returns true on success,
 * false otherwise.
 */
function addNewUser($username, $password, $display_name){
   global $db;
   $q = "INSERT INTO users (id_display, username, password, display_name) VALUES ('0','$username', '$password','$display_name')";
   //return mysql_query($q,$db);
   return $db->send_sql($q);
}

/**
 * Displays the appropriate message to the user
 * after the registration attempt. It displays a 
 * success or failure status depending on a
 * session variable set during registration.
 */
function displayStatus(){
   $uname = $_SESSION['display_name'];
   if($_SESSION['regresult']){

 echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Thank you ".$uname.". You were successfully registered! You may now log in.');
		window.location =\"main.php\";
		</script>";
	   unset($_SESSION['reguname']);
	   unset($_SESSION['registered']);
	   unset($_SESSION['regresult']);
		

/*echo "<h1>Registered!</h1>
<p>Thank you <b>".$uname."</b>, your information has been added to the database, you may now <a href=\"main.php\" title=\"Login\">log in</a>.</p>";*/

   }
   else{

 echo "<script language=\"javascript\" type=\"text/javascript\">
		alert('Registration failed - an error has occurred. Please try again later.');
		window.location =\"Registration.php\";
		</script>";
		die();
/*echo
"<h1>Registration Failed</h1>
<p>We're sorry, but an error has occurred and your registration for the username <b><? echo".$uname."</b>, could not be completed.<br>
Please try again at a later time.</p>";*/
   }
   unset($_SESSION['reguname']);
   unset($_SESSION['registered']);
   unset($_SESSION['regresult']);
}

if(isset($_SESSION['registered'])){
/**
 * This is the page that will be displayed after the
 * registration has been attempted.
 */


/*echo "<html>
<title>Registration Page</title>
<body>";*/
displayStatus();
/*echo "</body>
</html>";*/
 return;
}

/**
 * Determines whether or not to show to sign-up form
 * based on whether the form has been submitted, if it
 * has, check the database for consistency and create
 * the new account.
 */
if(isset($_POST['subjoin'])){
   /* Make sure all fields were entered */
   if(!$_POST['user'] || !$_POST['pass'] || !$_POST['display_name']){
      echo "<script language=\"javascript\" type=\"text/javascript\">
					alert('You did not fill in a required field.');
					window.location =\"Registration.php\";
					</script>";
					die();
   }

   /* Spruce up username, check length */
   $_POST['user'] = trim($_POST['user']);
   if(strlen($_POST['user']) > 20){
	 echo "<script language=\"javascript\" type=\"text/javascript\">
					alert('Sorry, the username is longer than 20 characters, please shorten it.');
					window.location =\"Registration.php\";
					</script>";
					die();
   }

	if (preg_match ("/[\"\'\/\s]+/i", $_POST['user']) || preg_match ("/[\"\'\/\s]+/i", $_POST['pass']) || preg_match ("/[\"\'\/\s]+/i", $_POST['display_name']) || preg_match ("/[\"\'\/\s]+/i", $_POST['confirmpass']))
	{

	 echo "<script language=\"javascript\" type=\"text/javascript\">
					alert('Sorry, invalid characters were entered');
					window.location =\"Registration.php\";
					</script>";
		die();
}

	

   /* Check if username is already in use */
   if(usernameTaken($_POST['user'])){
      $user = $_POST['user'];
	echo "<script language=\"javascript\" type=\"text/javascript\">
					alert('Sorry, the username: ".$user." is already taken, please pick another one.');
					window.location =\"Registration.php\";
					</script>";
					die();
   }

   /* Add the new account to the database */
   $md5pass = md5($_POST['pass']);
   $_SESSION['reguname'] = $_POST['user'];
   $_SESSION['display_name'] = $_POST['display_name'];
   $_SESSION['regresult'] = addNewUser($_POST['user'], $md5pass, $_POST['display_name']);
   $_SESSION['registered'] = true;
   echo "<meta http-equiv=\"Refresh\" content=\"0;url=$HTTP_SERVER_VARS[PHP_SELF]\">";
   return;
}
else{
/**
 * This is the page with the sign-up form, the names
 * of the input fields are important and should not
 * be changed.
 */
echo "
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
 <title>Registration Page</title>
 <link href=\"style.css\" rel=\"stylesheet\" type=\"text/css\" />
 <script language=\"javascript\" type=\"text/javascript\">
	function checkform() {
		if (document.myForm.pass.value != document.myForm.confirmpass.value) {
			// something is wrong
			alert(\"Passwords entered do not match!\");
			document.myForm.pass.value =\"\";
			document.myForm.confirmpass.value=\"\";
			document.myForm.pass.focus();
			//document.myForm.pass.select();
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
</div></div><!-- header --><!-- header2 -->	
<div id=\"main\"><div id=\"main2\">

<div id=\"content\">			
    <div class=\"post\">
		<h2>User Registration*</h2>
		<div class=\"entry\">
		<form name=\"myForm\" action=\"$_SERVER[PHP_SELF]\" onsubmit=\"return checkform();\"method=\"post\">
		<table>
		<tr><td>Username:</td><td><input type=\"text\" name=\"user\" maxlength=\"20\"></td></tr>
		<tr><td>Password:</td><td><input type=\"password\" name=\"pass\" maxlength=\"30\"></td></tr>
		<tr><td>Confirm Password:</td><td><input type=\"password\" name=\"confirmpass\" maxlength=\"30\"></td></tr>		
		<tr><td>Display Name:</td><td><input type=\"text\" name=\"display_name\" maxlength=\"30\"></td></tr>
		<tr><td colspan=\"2\" align=\"right\"><input type=\"submit\" name=\"subjoin\" value=\"Register!\"></td></tr>		
		</table>
		</form>
		<p>*All fields are required to register.</p>
		<p>Return to <a href=\"main.php\">main</a> page</br></p>
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

