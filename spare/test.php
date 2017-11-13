<?php
//include class definition
include("classes/cat.php");
include("classes/gingercat.php");
//instantiate the class
$mycat = new Cat("fluffy");

//will create errors
//colour is a private property of cat class
//$catcolour = $mycat ->colour;
//name is a protected property of cat class
//$catname = $mycat ->name;

$catcolour = $mycat ->getColour();
echo "my cat's colour is ".$catcolour;

echo "<br>";
$catname = $mycat ->getName();
echo "my cat's name is ".$catname ."<br>";

$newcat = new GingerCat("tom");
echo $newcat;
?>