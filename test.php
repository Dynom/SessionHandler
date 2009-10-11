<?php

// Registering autoload for the D lib
spl_autoload_register('_D_autoload');
function _D_autoload($className) {
  $path = str_replace('_', '/', $className) .'.php';
  if (is_readable($path)) {
    include $path;
  }
}

// Config
$memcacheConfig = array(
                    'hostname'        => 'localhost'
                  );

$mysqlConfig    = array(
                    'table_name'  => 'phpsession',
                    'username'    => 'd',
                    'password'    => 'd',
                    'database'    => 'phpsession',
                    'hostname'    => 'localhost',
                  );

ini_set('session.save_handler', 'user');

// Init the handler
$handler = new D_SessionHandler();

// Registering the drivers
// Note: Order matters ! (fifo)
$handler
  ->addDriver( new D_SessionDriver_Memcache($memcacheConfig) )
  ->addDriver( new D_SessionDriver_Mysql($mysqlConfig) );
  


session_start();
session_regenerate_id();
$_SESSION['time'] = time();  


echo '<pre>';
var_dump(
  $_SESSION,
  session_id(),
  $handler->getLastReadDriver()
);
