<?php
/**
 * @package D
 * @subpackage D_SessionHandler
 */

/**
 * The session interface
 */
class D_SessionHandler {

  /**
   * List of SessionHandlerDriver drivers.
   * @var array
   */
  protected $_drivers = array();
  
  /**
   * The session ID from when the session started,
   * prior to calling session_regenerate_id();
   * @var string
   */
  protected $_currentSessionId  = '';
  
  /**
   * Contains the driver name which performed the last
   * read call.
   * @var string
   */
  protected $_readDriver        = '';


  /**
   * Construct, calling ->_init()
   */
  final public function __construct() {
    $this->_init();
    $this->_registerHandler();
  }


  /**
   * Add a driver, the order is important. First come, first serve!
   * All drivers is being written to. But when reading, the first that can 
   * satisfy is used and the remaining are ignored.
   * 
   * @param D_SessionDriver_Interface $driver
   * @return SessionHandler
   */
  public function addDriver(D_SessionDriver_Abstract $driver) {
    $driver->setHandler( $this );
    $this->_drivers[] = $driver;
    return $this;
  }


  /**
   * Solving the write-back problem
   */
  final public function __destruct() {
    session_write_close();
  }


  /**
   * Return whether or not the session has been regenerated.
   * @return bool
   */
  public function changedSID() {
    return ($this->_currentSessionId === session_id());
  }
  
  
  /**
   * Return the ID, prior to calling session_regenerate_id();
   * @return string
   */
  public function getOldSID() {
    return $this->_currentSessionId;
  }


  /**
   * Return the most recent generated session id 
   * @return string
   */
  public function getNewSID() {
    return session_id();
  }
  
  
  /**
   * Return the name of the driver that handled the
   * read operation, if empty no read has been performed
   * or no driver could satisfy the request
   * 
   * @return string 
   */
  public function getLastReadDriver() {
    return $this->_readDriver;
  }

  //---------------------------------------------------------------------------
  // Protected methods
  //---------------------------------------------------------------------------

  
  /**
   * Child constructor implementation
   */  
  protected function _init() {}
  
  /**
   * Registering the handler
   * @return bool
   */
  protected function _registerHandler() {

    return  session_set_save_handler(
              array($this, 'open'),
              array($this, 'close'),
              array($this, 'read'),
              array($this, 'write'),
              array($this, 'destroy'),
              array($this, 'gc')
            );
  }


  //---------------------------------------------------------------------------
  // Session handler methods
  //---------------------------------------------------------------------------

  
  /**
   * Open function, this works like a constructor in classes and 
   * is executed when the session is being opened. The open function 
   * expects two parameters, where the first is the save path and 
   * the second is the session name. 
   * 
   * @param string $savePath
   * @param string $sessionName
   * @return string
   */
  public function open($savePath, $sessionName) {
    
    $this->_currentSessionId = session_id();

    $retVal = '';
    foreach($this->_drivers as $driver) {
      try {
        $retVal = $driver->open($savePath, $sessionName);
      } catch(D_Exception_Runtime $e) {}
    }
    
    return $retVal;
  }


  /**
   * Close function, this works like a destructor in classes and 
   * is executed when the session operation is done.
   * 
   * @return boolean
   */
  public function close() {
    foreach($this->_drivers as $driver) {
      $driver->close();
    }
    
    return true;
  }


  /**
   * Starting the session, reading the data.
   * 
   * Read function must return string value always to make save handler 
   * work as expected. Return empty string if there is no data to read. 
   * Return values from other handlers are converted to boolean expression, 
   * TRUE for success, FALSE for failure.
   *  
   * @return string
   */
  public function read($id) {
    foreach($this->_drivers as $driver) {
      $retVal = $driver->read($id);
      if (is_string($retVal)) {
        $this->_readDriver = get_class($driver);
        return $retVal;
      }
    }
    
    return false;
  }


  /**
   * Write the data
   * 
   * @param string $id
   * @param mixed $payload
   * @return boolean
   */
  public function write($id, $payload) {
    if (gettype($id) !== 'string') {
      settype($id, 'string');
    }

    $retVal = false;
    foreach($this->_drivers as $driver) {
      $r = $driver->write($id, $payload);
      if ($r === true) {
        $retVal = $r;
      }
    }

    return $retVal;
  }


  /**
   * The destroy handler, this is executed when a session is destroyed with 
   * session_destroy() and takes the session id as its only parameter.
   * 
   * @param string $id
   * @return boolean 
   */
  public function destroy($id) {
    foreach($this->_drivers as $driver) {
      $driver->destroy($id);
    }
    
    return true;
  }


  /**
   * The garbage collector, this is executed when the session garbage 
   * collector is executed and takes the max session lifetime (in seconds) as its only parameter.
   * 
   * @param int $ttl
   * @return 
   */
  public function gc($ttl) {
    foreach($this->_drivers as $driver) {
      $driver->gc((int) $ttl);
    }
    
    return true;
  }
}
