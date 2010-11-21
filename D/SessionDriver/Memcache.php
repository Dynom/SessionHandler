<?php
/**
 * @package D
 * @subpackage D_SessionDriver
 */


/**
 * Memcache session driver
 */
class D_SessionDriver_Memcache extends D_SessionDriver_Abstract {

    /**
     * @var Memcache
     */
    protected $_backend = NULL;

    /**
     * The configuration
     * @var array
     */
    protected $_config  = array(
        'hostname'        => '',
        'port'            => 11211, 
        'timeout_connect' => 2,     // 2 seconds, (don't go higher)
        'timeout_write'   => 86400, // 24h
        'flags_write'     => NULL,
        'persistent'      => true
    );

    /**
     * Construct, config options are:
     * - hostname
     * - port
     * - timeout_connect
     * - timeout_write
     * - flags_write
     * - persistent
     *
     * @param array $config
     * @return
     */
    public function __construct( array $config ) {
        $this->_config  = array_merge($this->_config, $config);
    }


    /**
     * Open up the backend
     *
     * @param string $savePath
     * @param string $sessionName
     * @return boolean
     */
    public function open($savePath, $sessionName) {

        $this->_backend = new Memcache;

        // Open a persistent or regular connection
        if ($this->_config['persistent']) {
            $this->_backend->pconnect(
                $this->_config['hostname'],
                $this->_config['port'],
                $this->_config['timeout_connect']
            );
        } else {
            $this->_backend->connect(
                $this->_config['hostname'],
                $this->_config['port'],
                $this->_config['timeout_connect']
            );
        }
    }


    /**
     * Close the backend
     *
     * @return boolean
     */
    public function close() {
        if (is_object($this->_backend)) {
            return $this->_backend->close();
        }
        return false;
    }


    /**
     * Read the session from our backend
     *
     * @param string $id
     * @return string
     */
    public function read($id) {
        return $this->_backend->get($id);
    }


    /**
     * Set the session
     *
     * @param string $id
     * @param string $payload
     * @return boolean
     */
    public function write($id, $payload) {
        return $this->_backend->set(
            $id,
            $payload,
            $this->_config['flags_write'],
            $this->_config['timeout_write']
        );
    }


    /**
     * Delete a session from the backend
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id) {
        return $this->_backend->delete($id);
    }


    /**
     * @todo Does the memcache backend need this? If so what to do ?
     *
     * @param int $ttl
     * @return boolean
     */
    public function gc($ttl) {
        return true;
    }
}
