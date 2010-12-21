<?php
/**
 * @package D
 * @subpackage D_SessionDriver
 */

/**
 * The driver interface, each session driver must implement this interface.
 */
interface D_SessionDriver_Interface
{
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
    public function open($savePath, $sessionName);


    /**
     * Close function, this works like a destructor in classes and
     * is executed when the session operation is done.
     *
     * @return boolean
     */
    public function close();


    /**
     * Read function must return string value always to make save handler
     * work as expected. Return empty string if there is no data to read.
     * Return values from other handlers are converted to boolean expression,
     * TRUE for success, FALSE for failure.
     *
     * @return string
     */
    public function read($id);


    /**
     * Write the payload.
     *
     * @param string $id
     * @param mixed $payload
     * @return
     */
    public function write($id, $payload);


    /**
     * The destroy handler, this is executed when a session is destroyed with
     * session_destroy() and takes the session id as its only parameter.
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id);


    /**
     * The garbage collector, this is executed when the session garbage
     * collector is executed and takes the max session lifetime as its only parameter.
     *
     * @param int $ttl
     * @return boolean
     */
    public function gc($ttl);
}
