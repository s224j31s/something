<?php
class Cat{
    private $colour;
    protected $name;
    //__construct is called when new Cat()is delcared
    public function __construct($catname){
        //from inside class, refer to other members
        //with $this ->membername
        $this -> colour = "calico";
        $this -> name = $catname;
    }
    public function getColour(){
        return $this -> colour;
    }
    
    public function getName(){
        return $this ->name;
    }
}
?>