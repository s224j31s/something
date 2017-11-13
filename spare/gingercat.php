<?php
class GingerCat extends Cat{
    private $colour = "ginger";
    public function __toString(){
        $myname = $this->getName();
        $mycolour = $this -> colour;
        $phrase = "my ginger cat's name is $myname <br>
        my ginger cat's colour is $mycolour";
        
        return $phrase;
    }
    
}
?>