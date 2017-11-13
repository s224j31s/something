<?php
session_start();
include("autoloader.php");

if(isset($_SESSION["id"]) == false){
  //if user is not logged in, redirect to login page
  $login_page = "login.php";
  header( "location:".$login_page);
}

$list = new WishList($_SESSION["id"]);


$items = $list -> getList();
if( count($items) > 0 ){
  $wish_count = count($items);
}
else{
  $wish_count = 0;
}
?>
<!doctype html>
<html>
  <?php
  $page_title = "Wish List";
  include("includes/head.php");
  ?>
  <body>
    <?php include("includes/navigation.php"); ?>    
    <div class="container">
      <div class="row">
        <h2 class="col-md-12"><?php echo ucfirst($_SESSION["username"]) ."'s";?> WishList</h2>
      </div>
      <div class="row flex-row">
      <?php
      if( count($items) > 0){
      $counter = 0;
        foreach($items as $item){
          $counter++;
          if($counter == 1){
            // echo "<div class=\"row\">";
          }
            $id = $item["product_id"];
            $name = $item["name"];
            $price = $item["price"];
            $image = $item["image_file"];
            $list_id = $item["list_id"];
            $user_id = $item["account_id"];
            $description = new TrimWords($item["description"],5)."...";
            echo "<div class=\"col-md-2 col-sm-3 col-xs-6\" data-wishlist-item=\"$id\">";
            echo "<h3 class=\"product-name\">$name</h3>";
            echo "<img class=\"img-responsive\" src=\"products_images/$image\">";
            echo "<p class=\"price\">$price</p>";
            echo "<p class=\"wishlist-description\">$description</p>";
            //button toolbar
            echo "<div class=\"btn-group\">
              <button class=\"btn btn-info\" name=\"delete\" value=\"delete\" data-id=\"$id\" data-list-id=\"$list_id\" data-user-id=\"$user_id\">
              Delete
              </button>
              </div>";
            echo "</div>";
          if($counter == 6){
            // echo "</div>";
            $counter = 0;
          }
        }
      }
      ?>
      </div>
      </div>
    </div>
    <script src="js/wishlist.js"></script>
  </body>
  <template id="spinner-template">
    <span class="spinner-container">
      <img class="spinner" src="graphics/spinner.png">
    </span>
  </template>
</html>
