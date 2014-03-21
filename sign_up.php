<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Sign Up</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      
      <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
      <style type="text/css">
         body{
            padding-left: 40px;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
         }
         .form-signup {
        padding: 19px 29px 29px;
        margin: 0 auto 20px auto;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signup input[type="checkbox"]{
         height:50px;
      }
      

      

      </style>
   </head>
   <body>
      <h1>Sign up</h1>
      <form class="form-signup" action="signing_up.php" method="POST">
         Email<br>
         <input type="text" name="email"><br>
         Password<br>
         <input type="password" name="password"><br>
         Password confirmation<br>
         <input type="password" name="password_confirm"><br>
         <input type="checkbox" name="is_admin"> Is Admin?<br>
         <button class="btn btn-info btn-large" type="submit">Sign up</button>
      </form>
      <a href="index.php">Sign in</a>
   </body>

</html>
