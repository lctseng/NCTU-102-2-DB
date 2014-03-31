<?php
namespace lct\func;



function load_db_data()
{
   return file("db_connect.conf");
}


function create_db_link()
{
   $db_data = @load_db_data();
   $db_data = array_map(trim,$db_data);
   $db_host = array_shift($db_data);
   $db_name = array_shift($db_data);
   $db_user = array_shift($db_data);
   $db_password = array_shift($db_data);

   $dsn = "mysql:host=$db_host;dbname=$db_name";
   try{
      $db = new \PDO($dsn,$db_user,$db_password);
   }
   catch (\PDOException $ex)
   {
      return false;
   }
   return $db;

}


?>
