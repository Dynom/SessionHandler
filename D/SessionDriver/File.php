<?php
/**
 * @package D
 * @subpackage D_SessionDriver
 * @author Tim de Wolf
 */


/**
 * File driver
 */
class D_SessionDriver_File extends D_SessionDriver_Abstract {

    /**
     * @var File
     */
    protected $_backend = NULL;

    /**
     * The configuration
     * @var array
     */
    protected $_config  = array(
        'save_path' => '/tmp/', 
        'name_prefix' => 'isess_'
    );


    /**
     * Construct, config options are:
     * - save_path
     * - name_prefix
     *
     * @param array $config
     * @return
     */
    public function __construct( array $config ) {
        $this->_config = array_merge($this->_config, $config);

        $this->_config['save_path'] = $this->_normalizeSavePath(
            $this->_config['save_path']
        );
        
        if ( ! strlen($this->_config['name_prefix'])) {
            throw new Exception(
                'Name prefix can not be empty, this makes GC too hard'
            );
        }
    }
    
    
    /**
     * Normalize the path, making sure it exists, readable and ends with a 
     * separator.
     * @exception Exception is thrown when we got an invalid path
     * @param string $path
     */
    public function _normalizeSavePath($path) {
        $realPath = realpath($path);
        if ($realPath === false) {
            throw new Exception(
                'Invalid save_path directive, "'. $path .'" doesn\'t exists.'
            );
        }

        return $realPath . DIRECTORY_SEPARATOR;
    }


    /**
     * Open up the backend
     *
     * @param string $savePath
     * @param string $sessionName
     * @return boolean
     */
    public function open($savePath, $sessionName) {
        return (bool) is_writable($this->_config['save_path']); // I've chosen not to use ini here but fileConfig.
    }


    /**
     * Close the backend
     *
     * @return boolean
     */
    public function close() {
        return (is_resource($this->_backend)) ? fclose($this->_backend) : false;
    }


    /**
     * Read the session from our backend
     *
     * @param string $id
     * @return string
     */
    public function read($id) {
        $file = $this->_config['save_path'] . $this->_config['name_prefix'] . $id;
        $c = null;

        if (is_file($file)) {
            $fh = fopen($file,'r');
            $c = fread($fh, filesize($file));
        }

        return $c;
    }


    /**
     * Set the session
     *
     * @param string $id
     * @param string $payload
     * @return boolean
     */
    public function write($id, $payload) {
        $file = $this->_config['save_path'] . $this->_config['name_prefix'] . $id;
        $fh = fopen($file, 'w');
        fwrite($fh, $payload);
        
        return (bool) fclose($fh);
    }


    /**
     * Delete a session from the backend
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id) {
        $file = $this->_config['save_path'] . $this->_config['name_prefix'] . $id;
        return (bool) unlink($file);
    }


    /**
     * Purge old files
     * @param int $ttl
     * @return boolean
     */
    public function gc($ttl) {
        $expr = $this->_config['save_path'] . $this->_config['name_prefix'] . '*';
        foreach (glob($expr) as $path) {
            if (filemtime($path) < (time() - $ttl)) {
                unlink($path);
            }
        }

        return true;
    }
}
