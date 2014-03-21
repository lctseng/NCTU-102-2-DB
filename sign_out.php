<?php
session_save_path("./sessions");
session_start();
session_destroy();
?>
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="1; url=index.php">
      <title>Redirecting...</title>
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
      <h2>You have signed out</h2>
      <h2>Redirect in 1 seconds....</h2>
      Or click <a href="index.php">here</a> to redirect.<br>
   </body>

</html>
