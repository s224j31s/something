<?php
include("../includes/database.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){
  //get the country being requested
  $selected_country = $_POST["country"];
  $query = "SELECT sub_id,sub_region_code,sub_region_name FROM countries_subdivisions WHERE country_code=?";
  $statement = $connection->prepare($query);
  $statement->bind_param("s", $selected_country );
  $statement->execute();
  $result = $statement->get_result();
  //check if there are results
  if($result->num_rows > 0){
    $sub_regions = array();
    while($row = $result->fetch_assoc()){
      $id = $row["sub_id"];
      $code = $row["sub_region_code"];
      $name = utf8_encode($row["sub_region_name"]);
      $region = array("id"=>$id,"code"=>$code,"name"=>$name);
      array_push($sub_regions, $region);
    }
    //output the array as JSON
    echo json_encode($sub_regions);
  }
}
?>