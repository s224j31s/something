<?php
//unset session variables
//to access session, it needs to be started
session_start();
//unset varibles
unset($_SESSION["username"]);
unset($_SESSION["email"]);
unset($_SESSION["profile_image"]);

//redirect user to home page
header("location: index.php");
?>