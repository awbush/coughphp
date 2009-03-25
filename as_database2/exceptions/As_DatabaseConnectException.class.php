<?php
class As_DatabaseConnectException extends As_DatabaseException 
{
   private $driver;
   private $host;
   private $port;
   private $user;
   private $error;

   public function __construct($driver, $host, $port, $user, $error = NULL) 
   {
       parent::__construct('Unable to connect to ' . $driver . ' database server ' . $host . ':' . $port . ' as ' . $user . (isset($error) ? ': ' . $error : ''));
       $this->hostAndPort = $hostAndPort;
       $this->driver = $driver;
   }

   public function getDriver() 
   {
       return $this->driver;
   }

   public function getHost() 
   {
       return $this->host;
   }

   public function getPort() 
   {
       return $this->port;
   }

      public function getUser() 
   {
       return $this->user;
   }

   public function getError()
   {
   	   return $this->error;
   }
}
?>
