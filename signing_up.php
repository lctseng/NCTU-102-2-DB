<?php
session_start();

function show_err_page($err_title,$err_msg = "")
{
   echo <<<ERR_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>DB Error</title>
   </head>
   <body>
      <h1>$err_title</h1>
      <p>$err_msg</p>
      <p>Try again!</p>
      <a href="sign_up.php">Back to sign up page</a><br>
   </body>
</html>



ERR_HTML;
}

function load_db_data()
{
   return file("db_connect.conf");
}

$db_data = @load_db_data();

if($db_data)
{
   $db_data = array_map(trim,$db_data);
   $db_host = array_shift($db_data);
   $db_name = array_shift($db_data);
   $db_user = array_shift($db_data);
   $db_password = array_shift($db_data);


   $err_msg = "";
   $dsn = "mysql:host=$db_host;dbname=$db_name";
   try
   {
      $db = new PDO($dsn,$db_user,$db_password);
   }
   catch (PDOException $ex)
   {
      $err_msg = $ex->getMessage();
   }
   if($db)
   {
      echo "DB Connect OK!";
      $user_email = $_POST['email'];
      $_SESSION['email'] = $user_email;
   }
   else
   {
      show_err_page("Database Connect Error!",$err_msg);
   }

}
else
{
   show_err_page("Database Info Error!","Cannot load database connection info.");
}
   
#echo "${_POST['email']}<br>";
#echo "${_POST['password']}<br>";
#echo "${_POST['password_confirm']}<br>";
#echo "${_POST['is_admin']}<br>";
   


?>
