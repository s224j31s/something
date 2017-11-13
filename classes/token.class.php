<?php
// this class generates random tokens for general use
class Token{
  private $token;
  private $length;
  public function __construct($length=16){
    $this -> length = $length;
    $this -> generate();
  }
  private function generate(){
    if(function_exists('random_bytes')){
      $bytes = random_bytes($this->length);
    }
    else{
      $bytes = openssl_random_pseudo_bytes($this->length);
    }
    $this -> token = bin2hex($bytes);
  }
  public function __toString(){
    return $this -> token;
  }
}
?>