<?php
class Products extends Database{
  private $conn;
  //base query for products
  private $query = "SELECT 
      products.id AS id,
      products.name AS name,
      products.description AS description,
      products.price AS price,
      images.image_file AS image
      FROM products 
      INNER JOIN products_images 
      ON products.id=products_images.product_id
      INNER JOIN images
      ON images.image_id = products_images.image_id";
  private $grouping ="GROUP BY products.id";
  private $order = "ORDER BY products.id";
  private $limit = "LIMIT ? OFFSET ?";
  private $params_array = array();
  private $page_size;
  private $page_number;
  protected $products = array();
  
  //constructor
  public function __construct(){
    //initialise parent class which is "Database"
    parent::__construct();
    $this -> conn = $this -> connection;
  }
  public function getProductById($product_id){
    //return an array the product with all images
    $product_query = $this -> query ." ". "WHERE products.id=?";
    $statement = $this -> conn -> prepare($product_query);
    $statement -> bind_param("i",$product_id);
    $statement -> execute();
    $result = $statement -> get_result();
    if( $result -> num_rows == 0){
      return false;
    }
    else{
      $product = array();
      while( $row = $result -> fetch_assoc() ){
        array_push($product,$row);
      }
      return $product;
    }
  }
  
  //get all products
  public function getProducts($categories = NULL,$size = 8,$page = 1){
    if(count($categories) > 0){
      //if categories specified send to getCategories
      $this -> getCategories($categories);
    }
    else{
      //no categories specified so get all products
      $query = $this -> query . " ORDER BY products.id ASC LIMIT ? OFFSET ? ";
      //prepare the statement
      $statement = $this -> conn -> prepare($query);
      $statement -> bind_param("ii",$size,$page);
      $statement -> execute();
      $result = $statement -> get_result();
      if($result -> num_rows > 0){
        while( $row = $result -> fetch_assoc() ){
          array_push($this -> products, $row);
        }
        return $this -> products;
      }
      else{
        return false;
      }
    }
  }
  //get categories
  public function getCategories($categories){
    //check if categories is an array if not, return false
    if(gettype($categories) !== "array"){
      return false;
    }
    else{
      $categorised_query = $this -> query . "
      INNER JOIN products_categories
      ON products.id = products_categories.product_id
      WHERE ";
      //construct query using categories
      $cat_count = count($categories);
      $param_vars = array();
      for($i=0; $i<$cat_count; $i++){
        //build query
        $categorised_query = $categorised_query . "products_categories.category_id=?";
        if($i !== $cat_count - 1){
          $categorised_query = $categorised_query . " " . " OR ";
        }
        //build parameter string
        $param_string = $param_string . "i";
        
        //add variables to parameter array to be passed to database as query parameters
        $this -> addParam($categories[$i],true);
      }
      //add grouping to the query
      $categorised_query = $categorised_query . " " . $this -> grouping;
      //add parameter string to parameter array
      $this -> addParam($param_string,false);
      
      //prepare query
      $statement = $this -> conn -> prepare($categorised_query);
      
      //pass parameter array to statement
      call_user_func_array(array($statement,'bind_param'), $this -> params_array);
      $statement -> execute();
      $result = $statement -> get_result();
      if($result -> num_rows > 0){
        while($row = $result -> fetch_assoc() ){
          array_push( $this -> products, $row );
        }
      }
      return $this -> products;
      print_r($this -> products);
    }
  }
  private function addParam( &$param, $side ){
    if( $side ){
      //array_push($this -> params_array, $param);
      $this -> params_array[$param] = &$param;
    }
    else{
      array_unshift($this -> params_array, $param);
    }
  }

  public function getCount(){
    return count($this -> products);
  }
  public function output(){
    return $this -> products;
  }
  
}
?>