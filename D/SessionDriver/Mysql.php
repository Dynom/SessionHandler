<?php
/**
 * @package D
 * @subpackage D_SessionDriver
 */


/**
 * Mysql session driver
 * The table required:
    CREATE TABLE "phpsession" (
      "id" varchar(32) NOT NULL default '',
      "created" datetime NOT NULL default '0000-00-00 00:00:00',
      "updated" datetime NOT NULL default '0000-00-00 00:00:00',
      "data" text,
      PRIMARY KEY  ("id"),
      KEY "updated" ("updated")
    );
 */
class D_SessionDriver_Mysql implements D_SessionDriver_Interface {
  
  /**
   * Mysql connection resource
   * @var Resource
   */
  protected $_conn    = NULL;
  
  /**
   * The config
   * @var array
   */
  protected $_config  = array(
                          'table_name'  => 'phpsession',
                          'username'    => '',
                          'password'    => '',
                          'database'    => '',
                          'hostname'    => 'localhost'
                        );

  /**
   * The data for the current request,
   * needed to figure some stuff out :->
   * @var array
   */
  protected $_data    = array('id'=>NULL);


  /**
   * Construct, config options are:
   * - table_name
   * - username
   * - password
   * - database
   * - hostname (Include the port if you have a non-default one)
   * 
   * @param array $config
   * @return 
   */
  public function __construct( $config ) {
    $this->_config = array_merge($this->_config, $config);
  }
  

  /**
   * Open up the backend
   * 
   * @param string $savePath
   * @param string $sessionName
   * @return boolean
   */
  public function open($savePath, $sessionName) {
    $this->_conn =  mysql_connect(
                      $this->_config['hostname'], 
                      $this->_config['username'], 
                      $this->_config['password']
                    );

    if ($this->_conn) {
      if ( ! mysql_select_db($this->_config['database'], $this->_conn)) {
        throw new D_Exception_Runtime('Unable to init mysql backend.');
      }
    }
  }
  

  /**
   * Close the backend
   * 
   * @return boolean 
   */
  public function close() {
    return mysql_close( $this->_conn );
  }


  /**
   * Read the session from our backend
   * 
   * @param string $id
   * @return string
   */
  public function read($id) {
    $sql    = sprintf(
                'SELECT data FROM %s WHERE id = %s',
                $this->_config['table_name'],
                mysql_real_escape_string($id)
              );

    $query  = mysql_query($sql, $this->_conn);
    if ($query) {
      $this->_data = mysql_fetch_assoc($query);
      return (string) $this->_data['data'];
    }
    
    return NULL;
  }
  

  /**
   * write the session to our backend
   * 
   * @param string $id
   * @param string $payload
   * @return boolean 
   */
  public function write($id, $payload) {
    $sql =  sprintf(
              'REPLACE INTO %s (id, created, updated, data) VALUES '.
              '("%s", NOW(), NOW(), "%s")',
              $this->_config['table_name'],
              mysql_real_escape_string($id),
              mysql_real_escape_string($payload)
            ); 

    return (bool) mysql_query($sql, $this->_conn);
  }


  /**
   * Delete a session from the backend
   * 
   * @param string $id
   * @return boolean
   */
  public function destroy($id) {
    $sql =  sprintf(
              'DELETE FROM %s WHERE id = "%s"',
              $this->_config['table_name'],
              mysql_real_escape_string($id)
            );

    return (bool) mysql_query($sql, $this->_conn);
  }
  
  
  /**
   * Garbage collection on the backend.
   * 
   * @param int $ttl
   * @return boolean
   */
  public function gc($ttl) {
    $sql =  sprintf(
              'DELETE FROM %s WHERE updated < "%s"',
              $this->_config['table_name'],
              date('Y-m-d H:i:s', (time() - $ttl))
            );

    return (bool) mysql_query($sql, $this->_conn);
  }
}