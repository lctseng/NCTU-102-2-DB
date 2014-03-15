<?php
session_start();

function show_err_page($err_title,$err_msg = "")
{
   echo <<<ERR_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="3; url=sign_up.php">
      <title>Sign Up Error</title>
   </head>
   <body>
      <h1>$err_title</h1>
      <p>$err_msg</p>
      <p>Try again!</p>
      <p>Redirect in 3 seconds...</p>
      <a href="sign_up.php">Back to sign up page</a><br>
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
      <meta charset="utf-8" http-equiv="refresh" content="3; url=index.php">
      <title>Success Signed Up</title>
   </head>
   <body>
      <h1>Signed Up Success</h1>
      You are success signed up.Please wait for redirect in 3 seconds...<br>
      Or you can click <a href="index.php">here</a> to go to main page.<br>
   <body>
</html>


DOC_HTML;
}

function load_db_data()
{
   return file("db_connect.conf");
}

function account_check($account)
{
   if(strlen($account)==0)
   {
      return false;
   }
   if(substr_count($account," ")>0)
   {
      return false;   
   }

   return true;
}

function pwd_hash($user_name,$pwd)
{
   return md5(crypt($pwd,md5($user_name.sha1($user_name))));
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
      $user_email = $_POST['email'];
      $user_pass = $_POST['password'];
      $user_pass_c = $_POST['password_confirm'];
      $user_admin = $_POST['is_admin'];
      
      if(!account_check($user_email)){
         show_err_page("Account Format Error","Account cannot contain space, and it cannot be empty.");
      }
      else if($user_pass !== $user_pass_c) # Password Same Check
      {   
         show_err_page("Password not Match");
      }
      else if(strlen($user_pass)==0)
      {
         show_err_page("Password cannot be Empty");
      }
      else
      {

         # Convert admin bit
         $is_admin = 0;
         if($user_admin==="on")
         {
            $is_admin = 1;
         }
         # SQL
         $sql = "INSERT INTO `User` (account,password,is_admin)"
              . " VALUES(?, ?, ?)";
         $sth = $db->prepare($sql);
         $result = $sth->execute(array($user_email,pwd_hash($user_email,$user_pass),$is_admin));
         if($result)
         {
            #echo "Sign up Success!<br>";
            $_SESSION['email'] = $user_email;
            $_SESSION['is_admin'] = $is_admin;
            show_success_page();
         }
         else
         {
            show_err_page("Create Account Error","Maybe the account is already used.");
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
   show_err_page("Database Info Error!","Cannot load database connection info.");
}
   
#echo "${_POST['email']}<br>";
#echo "${_POST['password']}<br>";
#echo "${_POST['password_confirm']}<br>";
#echo "${_POST['is_admin']}<br>";
   


?>
