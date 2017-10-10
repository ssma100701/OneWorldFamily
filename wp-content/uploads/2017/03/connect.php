<?php 
  $con = new mysqli("localhost", "root", "toor", "bitnami_wordpress");
 
  if($con->connect_errno > 0){
    die('Unable to connect to database [' . $con->connect_error . ']');
  } else
	echo "Successful";
?>
