# Learning PHP concepts

This is a project about learning PHP and MySQL, Feel free to check it out

Classes->Objects

class Cat{
    $mycat = new Cat();
    $mycatname= $mycat->name; //fluffy
    $mycatcolour = $mycat->colour;//Error
    $mycatcolour=$mycat->getColour(); //calcio
    
    public function__construct(){
        
    }
    
    private $colour = "calico"

    protected $name= "fluffy"

public function getColour(){
    return $this->colour;
}

}



#public refers to visibility(outside class)

#protected-visible to instances of the class

#To access class members 

##from inside class use $this -> membername

##from outside class use $instance->membername name


#Why
## Encapsulation-prevents variable/function anme collision
## Modularity-use modular code
## Portability-use code across projets
## Inheritance/Polymorphism-One class can inherit or extend another's properties
