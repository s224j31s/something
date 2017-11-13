<?php
include("autoloader.php");
session_start();

//See if there are any GET parameters passed
if(count($_GET) > 0){
  //construct GET parameters as string
  //to add to link to register page, in case user does not already have an account
  $params = array();
  foreach($_GET as $name => $value){
    $params[$name] = $value;
  }
  $url_params = http_build_query($params);
  $register_url = "register.php" . "?" . $url_params;
}
?>
<!doctype html>
<html>
<?php
$page_title = "Login to your account";
include("includes/head.php");
?>
  <body>
    <?php include("includes/navigation.php"); ?>
    <div class="container">
      <div class="row">
        <div class="col-md-4 col-md-offset-4">
          <form id="login-form" action="login.php" method="post">
            <h2>Login to your account</h2>
            <div class="form-group">
              <label for="user">Email Address or Username</label>
              <input class="form-control" type="text" id="user" name="user" placeholder="you@email.com or username">
            </div>
            <div class="form-group">
              <label for="password">Your Password</label>
              <input class="form-control" type="password" id="password" name="password" placeholder="your password">
            </div>
            <p>Don't have an account? <a href="<?php echo $register_url; ?>">Sign Up</a></p>
            <div class="text-center">
              <button type="submit" name="submit" value="login" class="btn btn-info">Login</button>
            </div>
          </form>
          <?php
          if(count($errors) > 0 || $message){
            //see which class to be used with alert
            if(count($errors) > 0){ 
              $class="alert-warning";
              $feedback = implode(" ",$errors);
            }
            if($message){
              $class="alert-success";
              $feedback = $message;
            }
            echo "<div class=\"alert $class\">
              $feedback
            </div>";
          }
          ?>
          
        </div>
      </div>
    </div>
    <script>
      <?php
      $js_vars = new GetToVars($_GET);
      echo $js_vars;
      ?>
    </script>
    <script src="js/login.js"></script>
  </body>
</html>

<template id="login-template">
  <div class="alert alert-dismissable">
    <button class="close" type="button" data-dismiss="alert">
      &times;
    </button>
    <p class="message"></p>
  </div>
</template>