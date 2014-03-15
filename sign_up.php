<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Sign Up</title>
   </head>
   <body>
      <h1>Sign up</h1>
      <form action="signing_up.php" method="POST">
         Email<br>
         <input type="text" name="email"><br>
         Password<br>
         <input type="password" name="password"><br>
         Password confirmation<br>
         <input type="password" name="password_confirm"><br>
         <input type="checkbox" name="is_admin"> Is Admin?<br>
         <button type="submit">Sign up</button>
      </form>
      <a href="index.php">Sign in</a>
   </body>

</html>
