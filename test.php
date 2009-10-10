<?php

//include 'D/SessionHandler.php';
//include 'D/SessionDriver/Memcache.php';
//include 'D/SessionDriver/Mysql.php';

spl_autoload_register('_D_autoload');
function _D_autoload($className) {
  $path = str_replace('_', '/', $className) .'.php';
  if (is_file($path)) {
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


// Handler init
$handler = new D_SessionHandler();

// Order matters ! First In, First Used, on fail, the second is used.
$handler
  ->addDriver( new D_SessionDriver_Memcache($memcacheConfig) )
  ->addDriver( new D_SessionDriver_Mysql($mysqlConfig) );




// Done, start playing with your session!

session_start();

echo $_SESSION['fubar'] ."<br>\n";

$_SESSION['fubar']    = time();
//$_SESSION['payload']  = range(0,1000);

echo $_SESSION['fubar'];

