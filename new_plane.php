<?php
session_save_path("./sessions");
session_start();
require_once("./functions/Database.php");


if (!\lct\func\check_user_valid($_SESSION["email"]))
{
   show_err_page("No Logged in.");
}
else if($_SESSION['is_admin']<=0){
   show_err_page("Permission Denied","Only admin can add new plane.");
}
else{
   show_add_new_page();
}

function show_add_new_page()
{
   $hour_cmd_str = "";
   for($i=0;$i<24;$i++)
   {
      $hour_cmd_str .= "<option value=\"$i\">$i</option>";
   }
   $min_cmd_str = "";
   for($i=0;$i<60;$i++)
   {
      $min_cmd_str .= "<option value=\"$i\">$i</option>";
   }
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Add New Plane</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
      <style>
         body {
            margin-left:50px;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
         }
         .form-signin input[type="text"]{
              font-size: 16px;
              height: auto;
              margin-bottom: 15px;
              padding: 7px 9px;
         }
         .form-signin select{
            width:80px;
         }
         
      </style>
   </head>
   <body>
      <h1>New Plane</h1>
      <form action="index.php" class="form-signin"  method="POST">
         Flight Number<br>
         <input type="text"   name="flight_num"><br>
         Departure<br>
         <input type="text"   name="depart"><br>
         Destination<br>
         <input type="text"   name="dest"><br>
         Price<br>
         <input type="number"   name="price"><br>
         Departure Date<br>
         <input type="date" name="depart_date"> - 
         <select name="depart_hour">
            $hour_cmd_str
         </select> : 
         <select name="depart_min">
            $min_cmd_str
         </select><br>
         Arrival Date<br>
         <input type="date" name="arrive_date"> - 
         <select name="arrive_hour">
            $hour_cmd_str
         </select> :
         <select name="arrive_min">
            $min_cmd_str
         </select><br>
         <input type="submit" class="btn btn-primary"  name="btn_trigger" value="Create Plane">
         <button type="button" class="btn btn-primary"  onclick="javascript:location.href='index.php'">Cancel</button>
      </form>
   </body>

</html>

DOC_HTML;
}



function show_err_page($err_title,$err_msg = "")
{
   echo <<<ERR_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="3; url=index.php">
      <title>Sign Up Error</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
      <style>
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
      <a href="index.php">Back to main page</a><br>
   </body>
</html>



ERR_HTML;
}



?>
