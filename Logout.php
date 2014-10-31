<?php
session_start(); 
include("login.php");

/**
 * Delete cookies - the time must be in the past,
 * so just negate what you added when creating the
 * cookie.
 */
   if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass']) && isset($_COOKIE['cookid']) && isset($_COOKIE['cookdisplayname']) && isset($_COOKIE['optionname'])){
   setcookie("cookname", "", time()-60*60*24*100, "/");
   setcookie("cookpass", "", time()-60*60*24*100, "/");
   setcookie("cookid", "", time()-60*60*24*100, "/");
   setcookie("cookdisplayname", "", time()-60*60*24*100, "/");
   setcookie("optionname", "", time()-60*60*24*100, "/");
}
echo "
<html>
<title>Logging Out</title>
<body>"
;


if(!$logged_in){
	 echo "<script language=\"javascript\" type=\"text/javascript\">
					alert('You are not currently logged in! Log out failed.');
					window.location =\"Main.php\";
					</script>";
					die();
}
else{
   /* Kill session variables */
   unset($_SESSION['username']);
   unset($_SESSION['password']);
   unset($_SESSION['id_user']);
   unset($_SESSION['option_name']);
   unset($_SESSION['display_name']);
   $_SESSION = array(); // reset session array
   session_destroy();   // destroy session.

   	 echo "<script language=\"javascript\" type=\"text/javascript\">
					alert('You were successfully logged out.');
					window.location =\"Main.php\";
					</script>";
					die();
}

?>

</body>
</html>