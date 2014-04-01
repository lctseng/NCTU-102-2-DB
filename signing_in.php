<?php
session_save_path("./sessions");
session_start();
require_once("./functions/Database.php");
require_once("./functions/IOProcessing.php");

function show_err_page($err_title,$err_msg = "")
{
   echo <<<ERR_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="3; url=index.php">
      <title>Sign In Error</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
      <style type="text/css">
      body {
        padding-left: 50px;
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
        background-attachment: fixed; 
        background-image: url("img/character.png"); 
        background-repeat: no-repeat; 
      }
      </style> 
   </head>
   <body>
      <h1>$err_title</h1>
      <p>$err_msg</p>
      <p>Try again!</p>
      <p>Redirect in 3 seconds...</p>
      <a href="index.php">Back to sign in page</a><br>
   </body>
</html>



ERR_HTML;
}

function show_success_page()
{
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="1; url=index.php">
      <title>Success Signed In</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
      <style type="text/css">
      body {
        padding-left: 50px;
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
        background-attachment: fixed; 
        background-image: url("img/character.png"); 
        background-repeat: no-repeat; 
      }
      </style>
   </head>
   <body>
      <h1>Signed In Success</h1>
      You are success signed in.Please wait for redirect in 1 seconds...<br>
      Or you can click <a href="index.php">here</a> to go to main page.<br>
   <body>
</html>


DOC_HTML;
}



$db_data = @\lct\func\load_db_data();


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
      $user_email = $_POST['email'];
      $user_pass = $_POST['password'];
      $user_remember = $_POST['remember'];
      
      if(!\lct\func\account_check($user_email)){
         show_err_page("Account Format Error","Account cannot contain space, and it cannot be empty.");
      }
      else if(strlen($user_pass)==0)
      {
         show_err_page("Password cannot be Empty");
      }
      else
      {

         # SQL
         $sql = "SELECT * FROM `User`"
              . " WHERE `account` = ?";
         $sth = $db->prepare($sql);
         $result = $sth->execute(array($user_email));
         if(!$result)
         {
            show_err_page("Server Error","Cannot vertify your infomation.");
         }
         else
         {
            if($obj = $sth->fetchObject())
            {
               $hash_pass = $obj->password;
               if(\lct\func\pwd_hash($user_email,$user_pass)===$hash_pass){
                  \lct\func\on_login($user_email,$obj->id,$obj->is_admin,false);
                  show_success_page();
               }
               else
               {
                  show_err_page("Invalid password for user $user_email");
               }

            }
            else{      
               show_err_page("Sign in Error","Account not exist.");
            }
         }
      }

   }
   else
   {
      show_err_page("Database Connect Error!",$err_msg);
   }

}
else
{
   echo "ERR!";
   show_err_page("Database Info Error!","Cannot load database connection info.");
}
   


?>
