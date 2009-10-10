<?php
  
class D_SessionDriver {

  public static function factory($name) {
    if ( ! ctype_alpha($name)) {
      throw new Exception_Runtime('Invalid session driver.');
    }
    
    $class = 'SessionDriver_'. $name;
    if (class_exists($class, true)) {
      return new $class();
    } else {
      
    }
    
  } 
}
