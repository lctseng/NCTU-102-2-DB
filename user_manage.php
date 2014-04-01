<?php
session_save_path("./sessions");
session_start(); 
require_once('./functions/Database.php');
require_once('./functions/IOProcessing.php');


show_valid_page();

function show_valid_page(){
   
   $tr_list_str = "";
   $user_list = \lct\func\get_user_list();
   
   $format_show = <<<DOC_HTML
<tr id="content-tr">
   <td>%d</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
</tr>
DOC_HTML;
   foreach($user_list as $info){
      # Identity & Promote
      $ident = "User";
      $prom = <<<DOC_HTML
<form action="user_manage.php" method="post"> 
   <button type="submit" name="btn_promote" class="btn btn-warning btn-large" value="${info['id']}">Promote</button>
</form>
DOC_HTML;
      if($info['is_admin']>0){
         $ident = "Administrator"; 
         $prom = '<a id="btn-main" class="btn btn-large disabled" style="width:65px;">Invalid</a>';
      }

      # Delete
      $delete = <<<DOC_HTML
<form action="user_manage.php" method="post"> 
   <button type="submit" name="btn_delete" class="btn btn-danger btn-large" value="${info['id']}">Delete</button>
</form>

DOC_HTML;

      $tr_list_str .= sprintf($format_show,$info['id'],$info['account'],$ident,$prom,$delete);
   }
   
   $error_msg = "Error MESSAGE";
   
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>User Management</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
   <style type="text/css">
      body{
         margin-left:50px;
         width:800px; 
      }
      table
      {
         width:800px;
         padding-left:30px;
         text-align: left;
      }
      td
      {
         padding-left:10px;
         font-family:verdana;
      }
      input
      {
         width:100px;
      }
      .main-user,.main-priv{
         padding: 10px 0px 10px 0px;
         font-size: 24px;
      }
      .main-priv{
         color: rgb(255,0,0);
      }

      #btn-out{
        margin: 20px 20px 20px 0px; 
      }
      #btn-new{
         margin: 20px 20px 20px 0px;
      }
      #btn-main{
         width:90px;
      }
      #title-row{
         font-weight : bold;
      }
      #error-msg{
         font-weight : bold;
         color:rgb(255,0,0);
      }
   </style>
   <script>
      <!--$script_post-->
   </script>
</head>
<body>
   <button type="button" id="btn-out" class="btn btn-info" onclick="javascript:location.href='sign_out.php'">Sign out</button>
   <p class="main-user">Welcome, <b> ${_SESSION["email"]}</b> !</p>
   <p style='font-family:verdana;font-size:32px;font-weight: bold;'>User Management   
   <form action="index.php" method="POST" class="form-inline">
   <form action="user_manage.php" method="post">
      <fieldset>
         <legend>Add New User & Modify</legend>
         <label>Add New User : </label><label id="error-msg">$error_msg</label><br>
         <input type="text" name="new_account" placeholder="Account"></input>
         <input type="password" name="pwd_1" placeholder="Password"></input>
         <input type="password" name="pwd_2" placeholder="Pwd Confirm"></input>
         <label class="checkbox">
            <input type="checkbox"> is Admin
         </label>
         <br></br>
         <button type="submit" class="btn btn-primary">Add User</button>
      </fieldset>
   </form>
   <table class="table table-striped ">
      <tr class="info" id="title-row">
         <td id="title-cell" style='width:50px;'>ID</td>
         <td id="title-cell">Account</td>
         <td id="title-cell">Identity</td>
         <td id="title-cell">Change to Admin</td>
         <td id="title-cell">Delete</td>
      </tr>
      $tr_list_str
   </table>
   $extra_button
</body>

</html>
DOC_HTML;
}




?>
