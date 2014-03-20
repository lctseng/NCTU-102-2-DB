<?php
session_save_path("./sessions");
session_start();
session_destroy();
?>
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="0; url=index.php">
      <title>Redirecting...</title>
   </head>
   <body>
      <h2>You have signed out</h2>
      <h2>Redirect in 3 seconds....</h2>
      Or click <a href="index.php">here</a> to redirect.<br>
   </body>

</html>
