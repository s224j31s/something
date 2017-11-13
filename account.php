<?php
session_start();
include("includes/database.php");
//if user is not logged in, eg no session vars, redirect to login page
if(isset($_SESSION["email"])==false || isset($_SESSION["id"])==false){
  //user has not logged in redirect to login page
  header("location:login2.php");
  exit();
}

//## handle account update here
//when request is a POST request
if($_SERVER["REQUEST_METHOD"]=="POST"){
  
  //## get and validate data from the form
  
  //get account id from session
  $account_id = $_SESSION["id"];
  
  //collect all errors in an array
  $errors = array();
  
  //collect successful updates
  $success = array();
  
  //### username check
  //username errors
  $username_errors = array();
  //trim removes spaces from the start and end of a string
  $username = trim($_POST["username"]);
  if(empty($username)){
    $username_errors["empty"] = "user name is empty";
  }
  if(strlen($username) < 6 || strlen($username) > 16){
    $username_errors["short"] = "user name should be between 6 and 16 characters";
  }
  //add all username errors to main errors array if there is any
  //implode converts the array into a string with the second parameter as the separator
  if(count($username_errors) > 0){
    $errors["username"] = implode($username_errors," ");
  }
  //### email check
  $email = trim($_POST["email"]);
  if(filter_var($email,FILTER_VALIDATE_EMAIL)==false){
    $errors["email"] = "email address is invalid";
  }
  
  //### password check
  //check if a new password was submitted (fields are blank if not)
  //trim removes spaces before and after the string
  $password1 = trim($_POST["password1"]);
  $password2 = trim($_POST["password2"]);
  //if both password fields have been filled
  if(strlen($password1) > 0 && strlen($password2) > 0){
    //check if they are not the same
    if($password1 !== $password2){
      $errors["password"] = "passwords are not the same";
    }
    else{
    //if they are the same set $updatepassword flag to true
      $updatepassword=true;
    }
  }
  //===============================================PROFILE IMAGE
  //check if there is an image uploaded
  if( $_FILES["profile-image"]["tmp_name"] ){
    //create an array to collect image errors
    $image_errors = array();
    
    //get the temp name of file
    $uploaded_file = $_FILES["profile-image"]["tmp_name"];
    
    //check for exif data (theoretically non image files do not have exif data)
    $img_type = exif_imagetype($uploaded_file);
    if($img_type > 3){
      array_push( $image_errors, "only jpg,png or gif can be used");
    }
    
    //get the file dimensions (image width and height)
    $img_dim = getimagesize($uploaded_file);
    if($img_dim === false){
      array_push( $image_errors, "file is not an image");
    }
    
    //check image file size (in MB)
    $img_size = filesize($uploaded_file);
    if($img_size > 1048576*2){
      array_push( $image_errors, "max size is 2MB" );
    }
    
    //check if file name starts with a dot
    if( strpos($_FILES["profile-image"]["name"],".",0 )===0 ){
      array_push( $image_errors, "illegal file type" );
    }
    
    //if there are image errors
    if( count($image_errors) > 0 ){
      $errors["image"] = implode($image_errors," and ");
    }
    //if there are no errors
    else{
      $uploaded_name = $_FILES["profile-image"]["name"];
      $filename = pathinfo($uploaded_name,PATHINFO_FILENAME);
      $fileextension = strtolower( pathinfo($uploaded_name,PATHINFO_EXTENSION) );
      $newfile = uniqid("image_",true) . "." . $fileextension;
      //move uploaded file to destination directory
      move_uploaded_file($_FILES["profile-image"]["tmp_name"],"profile_images/".$newfile);
      
      //insert image name into database
      $profile_image_query = "UPDATE accounts SET profile_image=? WHERE id=?";
      $statement = $connection -> prepare( $profile_image_query  );
      $statement -> bind_param("si",$newfile,$account_id);
      if( $statement -> execute() === false ){
        $errors["profile"] = "error updating profile image";
      }
    }
  }
  
  
  
  //if there are no errors
  if(count($errors)==0){
    //set updating to false, to make the form populated from the database, after update has been carried out
    $updating = false;
    //update accounts table without updating password
    //prevent update with password set to blank
    if($updatepassword==false){
      $account_update_query = "UPDATE accounts SET username=?,email=? WHERE id=?";
      $acct_statement = $connection->prepare($account_update_query);
      $acct_statement->bind_param("ssi",$username,$email,$account_id);
    }
    //update accounts table with new password
    else{
      $account_update_query = "UPDATE accounts SET username=?,email=?,password=? WHERE id=?";
      $password = password_hash($password1,PASSWORD_DEFAULT);
      $acct_statement = $connection->prepare($account_update_query);
      $acct_statement->bind_param("sssi",$username,$email,$password,$account_id);
    }
    
    $acct_statement->execute();
    
    //if update is successful
    if($acct_statement->affected_rows > 0){
      //update session variable for username
      $_SESSION["email"] = $email;
      $_SESSION["username"] = $username;
    }
    //check for errors from database 
    if($connection->errno===0){
      $success["account"] = true;
    }
    else{
       $success["account"] = false;
    }
  }
  else{
    //if there are errors in the data
    //set updating to true to make the form populated by user's input
    $updating = true;
  }
  
  //handle personal details update get POST data
  $firstname = $_POST["firstname"];
  $lastname = $_POST["lastname"];
  $unit = $_POST["unit"];
  $stnumber = $_POST["number"];
  $street = $_POST["street"];
  $suburb = $_POST["suburb"];
  $postcode = $_POST["postcode"];
  $state = $_POST["state"];
  $country = $_POST["country"];
  
  //create a query to insert (if not exist) or update (if it already exists)
  $details_query = "INSERT INTO user_details 
  (account_id,
  first_name,
  last_name,
  street_number,
  unit_number,
  street_name,
  suburb,
  state,country,
  post_code)
  VALUES (?,?,?,?,?,?,?,?,?,?)
  ON DUPLICATE KEY UPDATE
  account_id = VALUES(account_id), 
  first_name = VALUES(first_name), 
  last_name = VALUES(last_name), 
  street_number = VALUES(street_number), 
  unit_number = VALUES(unit_number), 
  street_name = VALUES(street_name), 
  suburb = VALUES(suburb), 
  state = VALUES(state), 
  country = VALUES(country), 
  post_code= VALUES(post_code)";
  
  $details_statement = $connection->prepare($details_query);
  $details_statement->bind_param("isssssssss",$account_id,$firstname,$lastname,$stnumber,$unit,$street,$suburb,$state,$country,$postcode);
  
  $details_statement->execute();
  if($details_statement->affected_rows > 0){
    //print success message
    //add success message
  }

  //check for errors from database 
  if($connection->errno===0){
    $success["details"] = true;
  }
  else{
     $success["details"] = false;
  }
}

if($updating==false){
  //If user is not updating their data, run the query below to populate
  //form with data from data stored in database
  //Get users details
  $account_id = $_SESSION["id"];
  $user_query = "SELECT 
                accounts.email AS email,
                accounts.username AS username,
                accounts.profile_image AS profile_image,
                user_details.first_name AS firstname,
                user_details.last_name AS lastname,
                user_details.unit_number AS unit,
                user_details.street_number AS number,
                user_details.street_name AS street,
                user_details.suburb AS suburb,
                user_details.post_code AS postcode,
                user_details.state AS state,
                user_details.country AS country
                FROM accounts 
                LEFT JOIN user_details 
                ON accounts.id = user_details.account_id 
                WHERE accounts.id =?";
  //prepare query
  $statement = $connection->prepare($user_query);
  //bind parameters (variables to query)
  //bind with the account id in session
  $statement->bind_param("s", $_SESSION["id"]);
  //execute query
  $statement->execute();
  //get the results
  $result = $statement->get_result();
  //get the data as an array using fetch_assoc()
  $userdata = $result->fetch_assoc();
  //get account details from userdata array
  $username = $userdata["username"];
  $email = $userdata["email"];
  $profile_image = $userdata["profile_image"];
  //get personal details from userdata array
  $firstname = $userdata["firstname"];
  $lastname = $userdata["lastname"];
  $unit = $userdata["unit"];
  $stnumber = $userdata["number"];
  $street = $userdata["street"];
  $suburb = $userdata["suburb"];
  $postcode = $userdata["postcode"];
  $state = $userdata["state"];
  $country = $userdata["country"];
}

//Get data for countries select element
$countries_query = "SELECT id,country_code,country_name FROM countries";
$countries_result = $connection->query($countries_query);
if($countries_result->num_rows > 0){
  $countries = array();
  while($row = $countries_result->fetch_assoc()){
    array_push($countries,$row);
  }
}
//set default country
$default_country_code = "AU";
if($country){
  $default_country_code = $country;
}

//Get data for states/subdivisions
$regions_query = "SELECT sub_id, 
                  country_code, 
                  sub_region_code, 
                  sub_region_name
                  FROM countries_subdivisions
                  WHERE country_code=?";
$region_statement = $connection->prepare($regions_query);
$region_statement->bind_param("s",$default_country_code);
$region_statement->execute();
$region_result = $region_statement->get_result();

?>
<!doctype html>
<html>
  <?php include("includes/head.php"); ?>
  <body>
    <?php include("includes/navigation.php"); ?>
    <div class="container">
      <form id="account-update" action="account.php" method="post" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-6">
            <h2>Account Details</h2>
              <div class="form-group">
                
                <div class="profile-group">
                  <img id="profile-preview" src = "<?php echo "profile_images/".$profile_image ?>" style="max-width:150px;">
                  <label for="profile-image" class="btn btn-default profile-btn">
                    <span class="glyphicon glyphicon-pencil"></span>
                    <input type="file" id="profile-image" name="profile-image" style="display:none;">
                  </label>
                </div>
                <span id="profile-img-info"></span>
                <span id="image-errors" class="help-block"></span>
                
              </div>
              <?php 
                // $username = $userdata["username"];
                if($errors["username"]){
                  $user_error_class = "has-error";
                }
              ?>
              <div class="form-group <?php echo $user_error_class; ?>">
                <label for="username">Username</label>
                
                <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>">
                <span class="help-block">
                  <?php echo $errors["username"]; ?>
                </span>
              </div>
              <?php 
                // $email = $userdata["email"];
                if($errors["email"]){
                  $email_error_class = "has-error";
                }
              ?>
              <div class="form-group <?php echo $email_error_class; ?>">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>">
                <span class="help-block">
                  <?php echo $errors["email"]; ?>
                </span>
              </div>
              <?php 
                // $email = $userdata["email"];
                if($errors["password"]){
                  $pw_error_class = "has-error";
                  $password1 = str_repeat("a", strlen($password1));
                  $password2 = str_repeat("a", strlen($password2));
                }
              ?>
              <div class="form-group <?php echo $pw_error_class; ?>">
                <label for="password">New Password</label>
                <input type="password" class="form-control" id="password1" name="password1" placeholder="minimum 8 characters" value="<?php echo $password1; ?>">
              </div>
              <div class="form-group <?php echo $pw_error_class; ?>">
                <label for="password">Retype New password</label>
                <input type="password" class="form-control" id="password2" name="password2" placeholder="retype your new password" value="<?php echo $password2; ?>">
                <span class="help-block">
                  <?php echo $errors["password"]; ?>
                </span>
              </div>
              
          </div>
          <div class="col-md-6">
            <h2>Personal Details</h2>
            <!--First Name-->
            <div class="form-group">
              <?php 
                //$firstname = $userdata["firstname"]
                
                ?>
              <label for="first-name">First Name</label>
              <input type="text" class="form-control" id="first-name" name="firstname" placeholder="First Name" value="<?php echo $firstname; ?>">
            </div>
            <!--Last Name-->
            <div class="form-group">
              <?php 
                //$lastname = $userdata["lastname"]
                
              ?>
              <label for="last-name">Last Name</label>
              <input type="text" class="form-control" id="last-name" name="lastname" placeholder="Last Name" value="<?php echo $lastname; ?>">
            </div>
            <!--Street Number Unit Number Street Name-->
            <div class="row">
              <!--Unit Number-->
              <div class="col-md-2">
                <div class="form-group">
                  <label for="unit-number">Unit</label>
                  <input  type="text" class="form-control" id="unit-number" name="unit" placeholder="6" value="<?php echo $unit; ?>">
                </div>
              </div>
              <!--Street Number-->
              <div class="col-md-2">
                <div class="form-group">
                  <label for="street-number">Number</label>
                  <input  type="text" class="form-control" id="street-number" name="number" placeholder="42" value="<?php echo $stnumber; ?>">
                </div>
              </div>
              <!--Street Name-->
              <div class="col-md-8">
                <div class="form-group">
                  <label for="street-name">Street</label>
                  <input type="text" class="form-control" id="street-name" name="street" placeholder="Easy Street" value="<?php echo $street; ?>">
                </div>
              </div>
            </div>
            <!--Suburb Postcode and State-->
            <div class="row">
              <!--Suburb-->
              <div class="col-md-4">
                <div class="form-group">
                  <label for="suburb">Suburb</label>
                  <input  type="text" class="form-control" id="suburb" name="suburb" placeholder="East Sydney" value="<?php echo $suburb; ?>">
                </div>
              </div>
              <!--Postcode-->
              <div class="col-md-3">
                <div class="form-group">
                  <label for="postcode">Postcode</label>
                  <input  type="text" class="form-control" id="postcode" name="postcode" placeholder="2000" value="<?php echo $postcode; ?>">
                </div>
              </div>
              <!--State-->
              <div class="col-md-5">
                <div class="form-group">
                  <label for="state">State</label>
                  <!--<input  type="text" class="form-control" id="state" name="state" placeholder="New South Wales" value="<?php echo $state; ?>">-->
                  <select name="state" class="form-control" id="state" placeholder="state or province">
                    <?php
                    // <option value="default-state">Default</option>
                    if($region_result->num_rows > 0){
                      while($region = $region_result->fetch_assoc()){
                        $id = $region["sub_id"];
                        $code = $region["sub_region_code"];
                        $country_code = $region["country_code"];
                        $name = $region["sub_region_name"];
                        if($code == $state){
                          $selected = "selected";
                        }
                        else{
                          $selected = "";
                        }
                        echo "<option $selected value=\"$code\">$name</option>";
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="country">Country</label>
              <select id="country" class="form-control" name="country">
                <?php
                  //$default_country_code = "AU";
                  if($country){
                    $default_country_code = $country;
                  }
                  foreach($countries as $country){
                    $name = $country["country_name"];
                    $code = $country["country_code"];
                    $id = $country["id"];
                    if($code == $default_country_code){
                      $selected = "selected";
                    }
                    else{
                      $selected = "";
                    }
                    echo "<option data-id=\"$id\" $selected value=\"$code\">$name ($code)</option>";
                  }
                ?>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <?php
            // if($errors["update"]){
            //   echo "<div class=\"alert alert-warning\">".$errors["update"]."</div>";
            // }
            if($success["account"] && $success["details"]){
              $alert_type = "success";
              $alert_message = "Update successful";
            }
            else{
              $alert_type = "warning";
              $alert_message = "Something went wrong";
            }
            if(count($success) == 2){
              echo "<div class=\"alert alert-$alert_type alert-dismissible\" role=\"alert\">
                  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                  <span aria-hidden=\"true\">&times;</span>
                  </button>
                    $alert_message
                  </div>";
            }
            ?>
          </div>
          <div class="col-md-6 text-right">
            <button type="submit" id="update-btn" class="btn btn-primary">
              Update My Details
            </button>
          </div>
        </div>
      </form>
    </div>
    <!--add states.js file-->
    <script src="js/states.js"></script>
    <script src="js/profile.js"></script>
  </body>
</html>