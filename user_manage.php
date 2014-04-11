<?php
session_save_path("./sessions");
session_start(); 
require_once('./functions/Database.php');
require_once('./functions/IOProcessing.php');



if(\lct\func\check_user_valid($_SESSION['email']) && $_SESSION['is_admin']>0){
   $_SESSION['error_msg'] = "";
   if($_POST['btn_add']==="on"){
      $add_result = \lct\func\add_user($_POST['new_account'],$_POST['pwd_1'],$_POST['pwd_2'],$_POST['is_admin']);
      $_SESSION['error_msg'] = $add_result['error_msg'];
   }
   else if(isset($_POST['btn_delete'])){
      \lct\func\delete_user($_POST['btn_delete']);
   }
   else if(isset($_POST['btn_promote'])){
      \lct\func\promote_user($_POST['btn_promote']);
   }
   show_valid_page();
}
else{ 
   show_err_page("Permission Denied","Only admin can do user manage.");
}



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
      if($_SESSION['uid']==$info['id']){
         $delete = '<a id="btn-main" class="btn btn-large disabled" style="width:60px;">Invalid</a>';
      }
      else{
         $delete = <<<DOC_HTML
<form action="user_manage.php" method="post"> 
   <button type="submit" name="btn_delete" class="btn btn-danger btn-large" style="width:100px;" value="${info['id']}">Delete</button>
</form>

DOC_HTML;
      } 
      $tr_list_str .= sprintf($format_show,$info['id'],$info['account'],$ident,$prom,$delete);
   }
   
   $error_msg = $_SESSION['error_msg'];
   $_SESSION['error_msg'] = "";  
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
   <button type="button" id="btn-out" class="btn btn-info" onclick="javascript:location.href='sign_out.php'"><i class="icon-refresh icon-white"></i> Sign out</button>
   <p class="main-user">Welcome, <b> ${_SESSION["email"]}</b> !</p>
   <p style='font-family:verdana;font-size:32px;font-weight: bold;'>User Management   
   <form action="user_manage.php" method="post" class="form-inline">
      <fieldset>
         <legend>Add New User & Modify</legend>
         <label>Add New User : </label><label id="error-msg">$error_msg</label><br>
         <input type="text" name="new_account" placeholder="Account"></input>
         <input type="password" name="pwd_1" placeholder="Password"></input>
         <input type="password" name="pwd_2" placeholder="Pwd Confirm"></input>
         <label class="checkbox">
            <input type="checkbox" name="is_admin"> is Admin
         </label>
         <br></br>
         <button type="submit" name="btn_add" value="on" class="btn btn-primary"><i class="icon-user icon-white"></i> Add User</button>
         <button type="button"  class="btn btn-success" onclick="javascript:location.href='index.php'"><i class="icon-th-list icon-white"></i> Back to Flight List</button>
      </fieldset>
   </form>
   <table class="table table-hover ">
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



function show_err_page($err_title,$err_msg = "")
{
   echo <<<ERR_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8" http-equiv="refresh" content="3; url=user_manage.php">
      <title>User Management Error</title>
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
      <a href="user_manage.php">Back to user management page</a><br>
   </body>
</html>



ERR_HTML;
}




?>
