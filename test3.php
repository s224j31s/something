<?php
session_start();
$_SESSION["id"] = 26;
// unset($_SESSION["id"]);

include("autoloader.php");

$list = new WishList();
$list -> addItem(69);

$items = $list -> getList();
foreach($items as $item){
    $name = $item["name"];
    $price = $item["price"];
    echo "<h3>$name</h3>";
    echo "<p>$price</p>";
}
?>