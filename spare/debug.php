<?php
class Account extends Database{
    private $conn;
    private $errors;
    public function __construct(){
        $db = new Database();
        $this -> conn = $db -> getConnection();
    }

    public function register($name, $email, $password){
        $query = "INSERT INTO accounts(username,email,password,stauts,created) VALUES (?,?,?,1,NOW())";
        
        $errors = array();
        
        $name = trim($name);
        
        if(strlen($name) > 16 || strlen($name)< 6){
            $errors["username"] = "username should be between 6 and 16 characters";
        }
        
        if( filter_var($email,FILTER_VALIDATE_EMAIL)=== false){
            $errors["email"] = "invalid email address";
        }
        
        if($password1 != $password2){
            $errors["password"]= "passwords do not match";
        }
        elseif(strlen($password) < 8){
            $errors["password"] ="password should be at least 8 characters";
        } 
        
        if (count($errors)==0){
            $hash = password_hash($password1,PASSWORD_DEFAULT);
            $statement = $this -> conn ->prepare($query);
            $statement -> bind_param("sss",$name, $email, $hash);
            
            if($statement -> execute()){
                 return true;
            }
            else{
                return false;
            }
           
        }
        else{
            $this -> errors = $errors;
            return false;
        }
        
    }
}


?>