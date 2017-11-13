<?php
class Account extends Database{
    private $conn;
    private $errors;
    public function __construct(){
        $db = new Database();
        $this -> conn = $db -> getConnection();
    }
    public function register($name,$email,$password1,$password2){
        $query = "INSERT INTO accounts (username,email,password,status,created)
                VALUES (?,?,?,1,NOW())";
                
        //check values of arguments
        $errors = array();
        //check user name
        $name = trim($name);
        if( strlen($name) > 16 || strlen($name) < 6 ){
            $errors["username"] = "user name should be between 6 and 16 characters";
        }
        
        //check email address
        if( filter_var($email,FILTER_VALIDATE_EMAIL) === false ){
            $errors["email"] = "invalid email address";
        }
        
        //check passwords
        if( $password1 != $password2 ){
            $errors["password"] = "passwords are not the same";
        }
        elseif( strlen($password1) < 8 ){
            $errors["password"] = "password should be at least 8 characters";
        }
        
        //if there are no errors
        if( count($errors) == 0 ){
            //insert data to database
            $hash = password_hash($password1,PASSWORD_DEFAULT);
            $statement = $this -> conn -> prepare($query);
            $statement -> bind_param("sss",$name,$email,$hash);
            //check if success
            if($statement -> execute() ){
                return true;
            }
            else{
                //check for error type
                if($this -> conn -> errno == "1062"){
                    //1062 = duplicate email or username error
                    //check if error message contains "username"
                    $errormsg = $this -> conn -> error;
                    if( strstr($errormsg,"username") ){
                        $errors["username"] = "username already used";
                    }
                    if( strstr($errormsg,"email")  ){
                        $errors["email"] = "email address already used";
                    }
                }
                $this -> errors = $errors;
                return false;
            }
        }
        else{
            $this -> errors = $errors;
            return false;
        }
    }
    
    public function authenticate($nameemail,$password){
        //return true if successful
        $query = "SELECT id,username,email,password,profile_image 
                FROM accounts 
                WHERE username=? 
                OR 
                email=?";
                
        $statement = $this -> conn -> prepare($query);
        $statement -> bind_param("ss",$nameemail,$nameemail);
        if( $statement -> execute() ){
            $result = $statement -> get_result();
            $user = $result -> fetch_assoc();
            $id = $user["id"];
            $username = $user["username"];
            $profile_image = $user["profile_image"];
            $email = $user["email"];
            $stored_hash = $user["password"];
            
            //check if password matches stored hash
            if( password_verify($password,$stored_hash) ){
                //log user in and return true
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
                $_SESSION["profile_image"] = $profile_image;
                //check if user is admin
                // $this -> isUserAdmin($id);
                 return true;
            }
            else{
                return false;
            }
            
        }
        else{
            return false;
        }
        //return false if not
        //add errors to array
    }
    private function isUserAdmin($id){
      //return true if admin false if not
      $query = "SELECT id FROM admin WHERE userid = ?";
      $statement = $this->conn -> prepare($query);
      $statement -> bind_param("i",$id);
      $statement -> execute();
      $result = $statement -> get_result();
      //if user exists in admin table
      if($result -> num_rows > 0){
        $_SESSION["admin"] = 1;
        //set last login
        
      }
      //user is not admin
      else{
        
      }
    }
}
?>