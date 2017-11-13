<?php
include("autoloader.php");

$cat = new Cat("tabby");
echo "Hello, my name is " . $cat ->getName();

$db = new Database();
$conn = $db ->getConnection();

if($conn){
    echo "connected";
}

//test account

$account = new Account();
$login = $account-> authenticate('username1','password');
//$registration = $account -> register("username6","user3@email.com", "password","password");

if ($login){
    echo "success";
}

else {
    echo "login failed";
}

//if($registration){
   // echo "successfully added";
//}
//else{
  //  echo "registration failed";
//}
?>