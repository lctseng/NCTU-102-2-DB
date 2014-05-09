<?php
session_save_path("./sessions");
session_start(); 
require_once('./functions/Database.php');
require_once('./functions/IOProcessing.php');

# echo \lct\func\to_time_zone_display(22);

if(\lct\func\check_user_valid($_SESSION['email']) && $_SESSION['is_admin']>0){
   $_SESSION['country_error_msg'] = "";
   $_SESSION['country_edit_msg'] = "";
   if($_POST['btn_add']==="on"){
      $add_result = \lct\func\add_country($_POST['new_name'],$_POST['new_full_name']);
      $_SESSION['country_error_msg'] = $add_result['error_msg'];
   }
   else if(isset($_POST['btn_delete'])){
      \lct\func\delete_country($_POST['btn_delete']);
   }
   else if(isset($_POST['btn_edit_req'])){
      $_SESSION['country_edit_id'] = $_POST['btn_edit_req'];
   }
   else if(isset($_POST['btn_save']) && $_POST['btn_save']==$_SESSION['country_edit_id']){
      $edit_result = \lct\func\edit_country($_SESSION['country_edit_id'],$_POST['edit_name'],$_POST['edit_full_name']);
      if($edit_result['result']){
         $_SESSION['country_edit_id'] = -1;
      }
      $_SESSION['country_edit_msg'] = $edit_result['error_msg'];
   }
   else if(isset($_POST['btn_cancel']) && $_POST['btn_cancel']==$_SESSION['country_edit_id']){
      $_SESSION['country_edit_id'] = -1;
   }
   show_valid_page();
}
else{ 
   show_err_page("Permission Denied","Only admin can do country manage.");
}



function show_valid_page(){
   
   $tr_list_str = "";
   $country_list = \lct\func\get_country_list();
   $need_edit_id = $_SESSION['country_edit_id'];

   $format_show = <<<DOC_HTML
<tr id="content-tr">
   <td>%d</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
</tr>
DOC_HTML;
   foreach($country_list as $info){
      if($info['id']!=$need_edit_id){
         # Edit
         $edit = <<<DOC_HTML
<form action="country_manage.php" method="post"> 
   <button type="submit" name="btn_edit_req" class="btn btn-primary btn-large" style="width:100px;" value="${info['id']}">Edit</button>
</form>

DOC_HTML;
         # Delete
         $delete = <<<DOC_HTML
<form action="country_manage.php" method="post"> 
   <button type="submit" name="btn_delete" class="btn btn-danger btn-large" style="width:100px;" value="${info['id']}">Delete</button>
</form>
DOC_HTML;

         $tr_list_str .= sprintf($format_show,$info['id'],$info['name'],$info['full_name'],$edit,$delete);
      }
      else{ ## THIS ID NEED TO BE EDIT!
         # Save
         $save = <<<DOC_HTML
<form action="country_manage.php" method="post"> 
   <button type="submit" name="btn_save" class="btn btn-primary btn-large" style="width:100px;" value="${info['id']}">Save</button>
</form>
DOC_HTML;
         $cancel = <<<DOC_HTML
<form action="country_manage.php" method="post"> 
   <button type="submit" name="btn_cancel" class="btn btn-danger btn-large" style="width:100px;" value="${info['id']}">Cancel</button>
</form>
DOC_HTML;
         $format_modify = <<<DOC_HTML
<tr id="content-tr">
   <form action="country_manage.php" method="post">
      <td>%d</td>
      <td>
         <input type="text" value="%s" name="edit_name" placeholder="Name"></input>
      </td>
      <td>
         <input type="text" value="%s" name="edit_full_name" placeholder="Full Name"></input>
      </td>
      <td>%s</td>
      <td>%s</td>
   </form>
</tr>
DOC_HTML;
         $tr_list_str .= sprintf($format_modify,$info['id'],$info['name'],$info['full_name'],$save,$cancel);
      }
   }
   $error_msg = $_SESSION['country_error_msg'];
   $_SESSION['country_error_msg'] = "";
   $edit_error_msg = $_SESSION['country_edit_msg'];
   $_SESSION['country_edit_msg'] = "";
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>Country Management</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
   <style type="text/css">
      body{
         margin-left:50px;
         width:1000px; 
      }
      table
      {
         width:1000px;
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
      #title-cell{
         width:180px;
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
   <p style='font-family:verdana;font-size:32px;font-weight: bold;'>Country Management   
   <form action="country_manage.php" method="post" class="form-inline">
      <fieldset>
         <legend>Add New Country & Modify</legend>
         <label>Add New Country : </label><label id="error-msg">$error_msg</label><br>
         <input type="text" name="new_name" placeholder="Name"></input>
         <input type="text" name="new_full_name" placeholder="Full Name"></input>
         <br></br>
         <button type="submit" name="btn_add" value="on" class="btn btn-primary"><i class="icon-home icon-white"></i> Add Country</button>
         <button type="button"  class="btn btn-success" onclick="javascript:location.href='index.php'"><i class="icon-th-list icon-white"></i> Back to Flight List</button>
         <label id="error-msg">$edit_error_msg</label><br>
      </fieldset>
   </form>
   <table class="table table-hover ">
      <tr class="info" id="title-row">
         <td id="title-cell">ID</td>
         <td id="title-cell">Name</td>
         <td id="title-cell">Full Name</td>
         <td id="title-cell"></td>
         <td id="title-cell"></td>
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
      <meta charset="utf-8" http-equiv="refresh" content="3; url=country_manage.php">
      <title>Country Management Error</title>
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
      <a href="country_manage.php">Back to country management page</a><br>
   </body>
</html>



ERR_HTML;
}




?>
