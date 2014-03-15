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
   $p_class = "Normal User";
   if($_SESSION["is_admin"]>0)
   {
    $p_class = "Administrator";      
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
   </body>

</html>





DOC_HTML;
}

#echo "Session Var:<br>";
#var_dump($_SESSION);
#echo "<br>";

if ($_SESSION["email"])
{
   show_signed_in_page();
   #echo "You have sign in as ${_SESSION['email']}";  
}
else
{
   show_sign_in_page();
}

?>
