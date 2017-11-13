<?php
include("..//autoloader.php");

session_start();

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $user = $_POST["user"];
    $password = $_POST["password"];
    
    $account = new Account();
    $auth_state = $account-> authenticate($user,$password);
    
    if($auth_state == true){
        $data["success"] = true;
        
    }
    else{
        $data["success"] = false;
    }
    echo json_encode($data);
    
}
?>