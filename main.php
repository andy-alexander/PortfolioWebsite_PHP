<?php 
/* Include Files *********************/
session_start(); 
include("login.php");
/*************************************/
echo "
<html>
<title>StockNet Main Page</title>
<body>";
displayLogin();
echo "
</body>
</html>
"
;
?>