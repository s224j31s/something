<?php
class WishList extends Database{
  private $user_id;
  private $conn;
  private $list_id;
  public function __construct($user_id = NULL){
    parent::__construct();
    $this -> conn = $this -> getConnection();
    //set user_id if supplied in constructor
    if($user_id){
      $this -> user_id = $user_id;
    }
    elseif($_SESSION["id"]){
      //if user_id is not supplied, use one from session
     $this -> user_id = $_SESSION["id"];
    }
    $this -> createList();
  }
  private function createList(){
    $query = "INSERT INTO wishlist (account_id) VALUES (?)";
    $statement = $this -> conn -> prepare($query);
    $statement -> bind_param("i",$this -> user_id);
    if( $statement -> execute() ){
      //query successful, list created
      //get the id of the list
      $this -> list_id = $this -> conn -> insert_id;
      return true;
    }
    else{
      //insert error means list already exists
      $error = $this -> conn -> errno;
      if($error == "1062"){
        //list already exists for the account
        //get the id of the list
        $query = "SELECT id FROM wishlist WHERE account_id=?";
        $liststatement = $this -> conn -> prepare ($query);
        $liststatement -> bind_param("i",$this -> user_id);
        $liststatement -> execute();
        $result = $liststatement -> get_result();
        $list_obj = $result -> fetch_assoc();
        $this -> list_id = $list_obj["id"];
        return true;
      }
      else{
        //another error
        return false;
      }
    }
  }
  
  public function addItem($product_id){
    $query = "INSERT INTO wishlist_items 
    (wishlist_id, product_id) 
    VALUES (?,?)";
    if($this -> list_id){
      //if the user has a list and is logged in
      //add item to the list_items table
      $statement = $this -> conn -> prepare($query);
      $statement -> bind_param("ii",$this->list_id,$product_id);
      if( $statement -> execute() ){
        return true;
      }
    }
    else{
      //there is no list
      return false;
    }
  }
  public function removeItem($product_id){
    $query = "DELETE FROM wishlist_items WHERE product_id = ? AND wishlist_id=?";
    //remove the product from the wishlist_items table
    $statement = $this -> conn -> prepare($query);
    $statement -> bind_param("ii", $product_id, $this -> list_id);
    if( $statement -> execute() ){
      return true;
    }
    else{
      return false;
    }
  }
  public function getList(){
    //query to get products from list, with prices and images
    $query = "SELECT
    wishlist_items.wishlist_id AS list_id,
    wishlist_items.product_id AS product_id,
    wishlist.account_id AS account_id,
    products.name AS name,
    products.price AS price,
    products.description AS description,
    images.image_file AS image_file
    FROM wishlist_items
    INNER JOIN wishlist
    ON wishlist_items.wishlist_id = wishlist.id
    INNER JOIN products
    ON wishlist_items.product_id = products.id
    INNER JOIN products_images
    ON wishlist_items.product_id = products_images.product_id
    INNER JOIN images
    ON products_images.image_id = images.image_id
    WHERE wishlist.account_id = ? 
    GROUP BY wishlist_items.product_id";
    //send query to database
    $statement = $this -> conn -> prepare($query);
    $statement -> bind_param("i",$this -> user_id);
    if( $statement -> execute() ){
      $result = $statement -> get_result();
      $listitems = array();
      while( $row = $result -> fetch_assoc() ){
        array_push($listitems,$row);
      }
      return $listitems;
    }
  }
  public function getCount(){
    //query to count items belonging to a user
    $count_query = "SELECT COUNT(product_id) AS total 
                    FROM wishlist_items
                    INNER JOIN wishlist 
                    ON wishlist_items.wishlist_id = wishlist.id
                    WHERE wishlist.account_id = ?";
    //run the query
    $statement = $this -> conn -> prepare($count_query);
    $statement -> bind_param( "i",$this -> user_id );
    $statement -> execute();
    $result = $statement -> get_result();
    $row = $result -> fetch_assoc();
    $count = $row["total"];
    return $count;
  }
  public function getListId(){
    if( $this -> list_id ){
      return $this -> list_id;
    }
    else{
      return false;
    }
  }
}
?>