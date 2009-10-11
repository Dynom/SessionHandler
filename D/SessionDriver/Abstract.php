<?php
/**
 * @package D
 * @subpackage D_SessionDriver
 */

/**
 * The template class for our drivers
 */
abstract class D_SessionDriver_Abstract implements D_SessionDriver_Interface {

  /**
   * The instance of our handler
   * 
   * @var D_SessionHandler
   */
  protected $_handler;
  
  
  /**
   * Add a reference to our handler
   * 
   * @param D_SessionHandler $handler
   * @return D_SessionDriver_Abstract
   */
  public function setHandler(D_SessionHandler $handler) {
    $this->_handler = $handler;
    return $this;
  }
  
  
}
