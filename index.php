<?php
session_save_path("./sessions");
session_start(); 
require_once('./functions/Database.php');
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
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
      <style type="text/css">
         html,body{
            min_height :100% !important;
            height: 100%;
         }
         body{
            background-color: #f5f5f5;
         }
         .form-signin{
            width: 300px;
            padding: 19px 29px 29px ;
            margin: 0  20px 0 100px;
            background-color: #fff;
            border: 1px solid #e5e5e5;
            -webkit-border-radius: 10px; 
               -moz-border-radius: 10px;
                    border-radous: 10px;
            -webkit-box-shadow: 0 10px 20px rgba(0,0,0,.05);
               -moz-box-shadow: 0 10px 20px rgba(0,0,0,.05);
                    box-shadow: 0 10px 20px rgba(0,0,0,.05);
         }
         .welcome{
            color: rgba(0,0,155,200);
            padding-left: 80px;
         }
         .signin-head{
            padding-left: 0px;
         }
         .form-signin .signin-head .checkbox{
            margin-bottom: 10px;
         }
         .form-signin input[type="email"]
         .form-signin input[type="password"]{
            font-size: 16px;
            height: auto;
            margin-bottom: 15px;
            padding: 9px;
         }
         .signin-links{
            margin-left:80px;
            font-size: 16px;
            padding: 15px 0px 10px 30px;
         }
         #wrap{
            min_height: 100%;
            height: auto !important;
            height:100%;
            margin: 0 auto -60px;
         }
         #push
         {
            height:360px;
         }
         #footer{
            margin-left: -100px;
            font-size : 16px;
            padding: 20px 0px 20px 50px;
            background-color: rgba(255,255,255,255);
         }

   

      </style>
      <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

         
            
            
            
   </head>
      <body>
         <div id="wrap">
            <h1 class="welcome">Flight Schedule System</h1>
            <form action="signing_in.php" method="POST" class="form-signin"> 
               <h1 class="signin-head">Sign in</h1>
               Email<br>
               <input type="text" name="email"><br>
               Password<br>
               <input type="password" name="password"><br>
               <input type="checkbox" name="remember" class="checkbox"> Remember me<br>
               <button type="submit" class="btn btn-large btn-primary">Sign in</button>
            </form>
            <label class="signin-links">
               <a href="sign_up.php">Sign up</a><br>
               <a href="forget_pwd.php">Forgot your password?</a><br>
            </label>

            <div id="push"></div>
         </div>

         <div id="footer">
            <div class="container">
               <p class="footer_msg">Create by Liang-Chi Tseng, Belongs to <a href="http://jinjya-304.nctucs.net/">NCTU JinJya-304</a></p>
            </div>
         </div>
      
         <script src="/bootstrap/js/bootstrapSourceJS?v=XkouIldXq_pPzIiEbylJIGwgBv2qYDSef1Dnn06aIkQ1">
         </script>
      </body>
</html>
DOC_HTML;
}

function show_signed_in_page(){
   # Privilege control
   #var_dump($_POST);
   $p_class = "Normal User";
   $extra_button = "";
   $admin = false;
   $p_extra_th = "";
   $P_extra_td_show = "";
   if($_SESSION["is_admin"]>0)
   {
      $admin = true;
      $p_class = "Administrator";
      $extra_button=<<<EXTRA_HTML
<button type="button" id="btn-new" class="btn btn-success" onclick="javascript:location.href='new_plane.php'">New Plane</button>
EXTRA_HTML;
      $p_extra_th=<<<EXTRA_HTML
<td></td>
<td></td>
EXTRA_HTML;
      $p_extra_td_show=<<<EXTRA_HTML
<td>
   <form action="index.php" method="POST">
      <button type="submit" class="btn btn-primary btn-large" style= "width:100px;" name="btn_modify" value="%d">
         Modify
      </button>
   </form>
</td>
<td>
   <form action="index.php" method="POST">
      <button type="submit" class="btn btn-danger btn-large"  style= "width:100px;" name="btn_delete" value="%d">
         Delete
      </button>
   </form>
</td>
EXTRA_HTML;
   }
   # Plane list
   $plane_str_list = "";
   $plane_data = load_plane_data(false);
   $format_show = <<<DOC_HTML
<tr>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s %02d:%02d</td>
   <td>%s %02d:%02d</td>
   $p_extra_td_show
</tr>
DOC_HTML;
   $d_hour_cmd_str = "";
   $a_hour_cmd_str = "";
   #var_dump($_POST['btn_modify']);
   $m_plane_data=array_shift(load_plane_data((int)($_POST['btn_modify'])));
   $plane_data_ok = false;
   if($m_plane_data){
      $plane_data_ok = true;
   }
   #var_dump(load_plane_data((int)($_POST['btn_modify'])));
   #var_dump($m_plane_data);
   for($i=0;$i<24;$i++){
      $d_selected = "";
      $a_selected = "";
      if($plane_data_ok && $m_plane_data["depart_h"]==$i){
         $d_selected = "selected";
         #echo "Selected Hour:$i";
      }
      if($plane_data_ok && $m_plane_data['arrive_h']==$i){
         $a_selected = "selected";
      }
      $d_hour_cmd_str .= "<option $d_selected value=\"$i\">$i</option>";
      $a_hour_cmd_str .= "<option $a_selected value=\"$i\">$i</option>";
   }
   $d_min_cmd_str = "";
   $a_min_cmd_str = "";
   for($i=0;$i<60;$i++){
      $d_selected ="";
      $a_selected="";
      if($plane_data_ok && $m_plane_data['depart_m']==$i){
         $d_selected = "selected";
      }
      if($plane_data_ok && $m_plane_data['arrive_m']==$i){
         $a_selected = "selected";
      }
      $d_min_cmd_str .= "<option $d_selected value=\"$i\">$i</option>";
      $a_min_cmd_str .= "<option $a_selected value=\"$i\">$i</option>";
   }
   $format_modify = <<<DOC_HTML
<tr>
   <td>%s</td>
   <form action="index.php" method="POST">
      <td>
         <input type="text" name="flight_num" value=%s>   
      </td>
      <td>
         <input type="text" name="depart" value=%s>
      </td>
      <td>
         <input type="text" name="dest" value=%s>
      </td>
      <td>
         <input type="date" style="width:150px;" name="depart_date" value=%s ><br>
         <select class="date-select" name="depart_hour" value=%s>
               $d_hour_cmd_str
         </select>
         <select class="date-select" name="depart_min" value=%s>
               $d_min_cmd_str
         </select>
      </td>
      <td>
         <input type="date" style="width:150px;"  name="arrive_date" value=%s><br>
         <select class="date-select" name="arrive_hour" value=%s>
               $a_hour_cmd_str
         </select>
         <select class="date-select" name="arrive_min" value=%s>
               $a_min_cmd_str
         </select>
      </td>
      <td>
         <button type="submit" id="btn-main" class="btn btn-primary btn-large"  style= "width:100px;" name="btn_save" value="%d">
            Save
         </button>
      </td>
      <td>
         <button type="submit" id="btn-main" class="btn btn-danger btn-large" style= "width:100px;" name="btn_cancel">
            Cancel
         </button>
   </form>
</tr>
DOC_HTML;
   #var_dump($_POST);
   foreach ($plane_data as $info):
      $format = $format_show;
      if($admin&&$_POST['btn_modify'] && $_POST['btn_modify']==$info['id']){
         $format = $format_modify;
      }
      $plane_str_list.=sprintf($format,$info['id'],$info['num'],$info['depart'],$info['dest'],$info['depart_d'],$info['depart_h'],$info['depart_m'],$info['arrive_d'],$info['arrive_h'],$info['arrive_m'],$info['id'],$info['id']); 
   endforeach;   


echo <<<DOC_HTML
<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>Signed In</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
   <style type="text/css">
      body{
         margin-left:50px;
      }
      table
      {
         padding-left:30px;
         text-align: left;
      }
      td
      {
         padding-left:10px;
         font-family:verdana;
         width:130px;
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
      .date-select{
         height:30px;
         width: 80px;
      }
      #btn-main{
      }
      #title-row{
         font-weight : bold;
      }
      #title-cell{
         ;
      }
   </style>
</head>
<body>
   <button type="button" id="btn-out" class="btn btn-info" onclick="javascript:location.href='sign_out.php'">Sign out</button><br> 
   <p class="main-user">You have signed in as <b> ${_SESSION["email"]}</b></p>
   <p class="main-priv">Privilege:<b>$p_class</b></p>
   <p style='font-family:verdana;font-size:32px;font-weight: bold;'>Listing planes</p>
   <table class="table table-striped ">
      <tr class="info" id="title-row">
         <td id="title-cell" style='width:50px;'>ID</td>
         <td id="title-cell">Flight Number</td>
         <td id="title-cell">Departure</td>
         <td id="title-cell">Destination</td>
         <td id="title-cell" style='width:250px;'>Depart Date</td>
         <td id="title-cell" style='width:250px'>Arrive Date</td> 
         $p_extra_th
      </tr>
      $plane_str_list 
   </table>
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
   if(strlen(str_replace(" ","",$str))<=0){
      return false;
   }
   if(strlen(str_replace("\t","",$str))<=0){ 
      return false;
   }
   if(strlen(escape_html_tag($str))<=0){
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



function insert_new_plane($post)
{
   $db = \lct\func\create_db_link();
   if($db){
      $sql = "INSERT INTO `Flight` (flight_number,departure,destination,departure_date,depart_hour,depart_min,arrival_date,arrive_hour,arrive_min)"
        . " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $sth = $db->prepare($sql);
      #echo "SQL:$sql";
      $result = $sth->execute(array(escape_html_tag(($post['flight_num'])),escape_html_tag($post['depart']),escape_html_tag($post['dest']),$post['depart_date'],$post['depart_hour'],$post['depart_min'],$post['arrive_date'],$post['arrive_hour'],$post['arrive_min']));
      #echo "Result:$result";
      return $result;
   }
   else{
      return false;
   }
}



function update_plane($post)
{
   #var_dump($post);
   $db =  \lct\func\create_db_link();
   if($db){
      $sql = "UPDATE `Flight` SET 
         flight_number = ?,
         departure = ?,
         destination = ?,
         departure_date = ?,
         depart_hour = ?,
         depart_min = ?,
         arrival_date = ?,
         arrive_hour = ?,
         arrive_min = ?"
        . " WHERE `Flight`.`id`=? ";
      $sth = $db->prepare($sql);
      #echo "SQL:$sql";
      $result = $sth->execute(array(escape_html_tag($post['flight_num']),escape_html_tag($post['depart']),escape_html_tag($post['dest']),$post['depart_date'],$post['depart_hour'],$post['depart_min'],$post['arrive_date'],$post['arrive_hour'],$post['arrive_min'],$post['btn_save']));
      #echo "Result:$result";
      return $result;
   }
   else{
      return false;
   }
}



function delete_plane($post)
{
   #var_dump($post);
   $db =  \lct\func\create_db_link();
   if($db){
      $sql = "DELETE FROM `Flight`"
        . " WHERE `Flight`.`id`=?";
      $sth = $db->prepare($sql);
      #echo "SQL:$sql";
      $result = $sth->execute(array($post['btn_delete']));
      #echo "Result:$result";
      return $result;
   }
   else{
      return false;
   }
}


function escape_html_tag($str)
{
   return strip_tags($str);
   #$result = strtr($str,"<",'&lt');
   #var_dump($result);
   #return $result;
   #return strtr(strtr($str,"<","&lt;"),">","&gt;");
}

function load_plane_data($id)
{
   $db =  \lct\func\create_db_link();
   if($db)
   {
      if($id && $id > 0){
         $sql = "SELECT * FROM `Flight` WHERE `id` = ?";
         $sth = $db->prepare($sql);
         $sth->execute(array("$id"));
      }
      else{
         $sql = "SELECT * FROM `Flight` ORDER BY  `id` ASC";
         $sth = $db->prepare($sql);
         $sth->execute();
      }
      #echo $sql;
      $list = array();
      while($result = $sth->fetchObject())
      {
         $info = array();
         $info['id'] = $result->id;
         $info['num'] = escape_html_tag($result->flight_number);
         $info['depart'] = escape_html_tag($result->departure);
         $info['dest'] = escape_html_tag($result->destination);
         $info['depart_d'] = strtok($result->departure_date," ");
         $info['depart_h'] = $result->depart_hour;
         $info['depart_m'] = $result->depart_min;
         $info['arrive_d'] =  strtok($result->arrival_date," ");
         $info['arrive_h'] = $result->arrive_hour;
         $info['arrive_m'] = $result->arrive_min;
         array_push($list,$info);
      }
      return $list;
      
   }
   return array();


}


if ($_SESSION["email"])
{
   #var_dump($_POST);
   
   if($_POST['btn_save'] && $_SESSION['is_admin']>0)
   {
      #echo "UPDATE!";
      if(check_array_str_valid($_POST))
      {
         $sql_result = update_plane($_POST);
         show_signed_in_page();
      } 
      else
      {
         show_err_page("Data Format Error!","All field must not be empty,and cannot contain only spaces.Or maybe there exist illegal characters.","index.php","main page");
      }
      #UPDATE  `Flight` SET  `destination` =  '8' WHERE  `Flight`.`id` =1;
   }
   else if($_POST["btn_delete"] && $_SESSION['is_admin']>0)
   {
      $result = delete_plane($_POST);
      if($result)
      {
         show_signed_in_page();
      } 
      else
      {
         show_err_page("Cannot Delete Plane!!","Unknown reason....","index.php","main page");
      }
   }
   else if($_POST["btn_trigger"] && $_SESSION['is_admin']>0)
   {
      unset($_POST['btn_trigger']);
      if(check_array_str_valid($_POST))
      {
         $sql_result = insert_new_plane($_POST);
         if($sql_result)
         {
            show_signed_in_page();   
         }
         else
         {
            show_err_page("Server Error!","Maybe the data is corrupt.","new_plane.php","plane adding page");
         }
      }
      else
      {
         show_err_page("Data Format Error!","All field must not be empty,and cannot contain only spaces.Or maybe there exist illegal characters.","new_plane.php","plane adding page");
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
