<?php
   session_start();  
?>

<?php
function show_sign_in_page()
{
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Flight Schedule</title>
      </head>
      <body>
         <h1>Sign in</h1>
         <form action="signing_in.php" method="POST">
            Email<br>
            <input type="text" name="email"><br>
            Password<br>
            <input type="password" name="password"><br>
            <input type="checkbox" name="remember"> Remember me<br>
            <button type="submit">Sign in</button>
         </form>
         <a href="sign_up.php">Sign up</a><br>
         <a href="forget_pwd.php">Forgot your password?</a><br>
         
      </body>
</html>
DOC_HTML;
}

function show_signed_in_page(){
   # Privilege control
   $p_class = "Normal User";
   $extra_button = "";
   if($_SESSION["is_admin"]>0)
   {
      $p_class = "Administrator";
      $extra_button=<<<EXTRA_HTML
<button type="button" onclick="javascript:location.href='new_plane.php'">New Plane</button>
EXTRA_HTML;
   }
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Signed In</title>
   </head>
   <body>
      
      <button type="button" onclick="javascript:location.href='sign_out.php'">Sign out</button> 
      <h1>You have signed in as ${_SESSION["email"]}</h1>
      <h2>Your privilege is $p_class</h2> 
      $extra_button
   </body>

</html>





DOC_HTML;
}


function show_err_page($err_title,$err_msg="",$re_url="index.php",$page_name="proper page"){
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="3; url=$re_url">
      <title>Error!</title>
   </head>
   <body>
      <h1>$err_title</h1>
      <p>$err_msg</p>
      <p>Try again!</p>
      <p>Redirect in 3 seconds...</p>
      <a href="$re_url">Redirect to $page_name</a><br>
   </body>
</html>
DOC_HTML;
}

function check_str_valid($str)
{
   if(strlen($str)<=0){
      return false;
   }
   if(substr_count($str," ")>0){
      return false;
   }
   if(substr_count($str,"\t")>0){
      return false;
   }
   return true;
}

function check_array_str_valid($array)
{
   foreach ($array as $str):
      if(!check_str_valid($str))
      {
         return false;
      }
   endforeach;
   return true;
}


if ($_SESSION["email"])
{
   #var_dump($_POST);
   if($_POST["btn_trigger"])
   {
      unset($_POST['btn_trigger']);
      if(check_array_str_valid($_POST))
      {
         echo "Success!<br>"; 
      }
      else
      {
         show_err_page("Data Format Error!","All field must not be empty,and cannot contain spaces.","new_plane.php","plane adding page");
      }
      # Process SQL
   } 
   else{
      show_signed_in_page();
   }   
   #echo "You have sign in as ${_SESSION['email']}";  
}
else
{
   show_sign_in_page();
}

?>
