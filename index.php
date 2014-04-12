<?php
session_save_path("./sessions");
session_start(); 
require_once('./functions/Database.php');
require_once('./functions/IOProcessing.php');
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
               <i class="icon-bookmark"></i>Account<br>
               <input type="text" name="email"><br>
               <i class="icon-certificate"></i>Password<br>
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
   $total_width = "1300px";
   if($_SESSION["is_admin"]>0&&!\lct\func\is_sheet())
   {
      $total_width = "1600px";
      $admin = true;
      $p_class = "Administrator";
      $extra_button=<<<EXTRA_HTML
<button type="button" id="btn-new" class="btn btn-success" onclick="javascript:location.href='new_plane.php'"><i class="icon-plane icon-white"></i> New Plane</button>
<button type="button" id="btn-new" class="btn btn-success" onclick="javascript:location.href='user_manage.php'"><i class="icon-user icon-white"></i> User Management</button>
<button type="button" id="btn-new" class="btn btn-success" onclick="javascript:location.href='airport_manage.php'"><i class="icon-map-marker icon-white"></i> Airport Management</button>
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
   $extra_plane_str_list = "";
   if($_SESSION['sheet']){
      $plane_data = \lct\func\load_sheet_plane_data($_SESSION['uid'],true);
      $extra_plane_data = \lct\func\load_sheet_plane_data($_SESSION['uid'],false);
   }
   else{
      $plane_data = load_plane_data(false);
   }




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
<tr id="content-tr">
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
         <input type="number" name="price" value=%d>
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
         <a id="btn-main" class="btn btn-large disabled" style="width:60px;">Invalid
         </a>
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
   $index = 0;
   $meta_list = array($plane_data,$extra_plane_data);
   $meta_str = array("","");
   foreach ($meta_list as $list_data):
   if(!isset($list_data)){
      continue;
   }
   foreach ($list_data as $info):      
      
      $favorite_btn = "";
      $favorite_btn_class = "";
      $favorite_btn_text = "";
      if(\lct\func\is_favorite($_SESSION['uid'],$info['id']))
      {
         $favorite_img="<img src='img/InFavorite.png'>";
         $favorite_btn_class .= "btn btn-warning btn-large";
         $favorite_btn_text .= "Cancel";
      }
      else{ 
         $favorite_img="<img src='img/UnFavorite.png'>";
         $favorite_btn_class .= "btn btn-success btn-large";
         $favorite_btn_text .= "Favorite";
      }
      $favorite_btn .= <<<EXTRA_HTML
<form action="index.php" method="POST">
   <button type="submit" id="btn-main" class="$favorite_btn_class"  style= "width:100px;" name="btn_favorite" value="%d">$favorite_btn_text
   </button>
<form>
EXTRA_HTML;
      
      $format_show = <<<DOC_HTML
<tr id="content-tr">
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s</td>
   <td>%s %02d:%02d</td>
   <td>%s %02d:%02d</td>
   <td>
      $favorite_btn
   </td>
   $p_extra_td_show
</tr>
DOC_HTML;

      $format = $format_show;
      if($admin&&$_POST['btn_modify'] && $_POST['btn_modify']==$info['id']){
         $format = $format_modify;
      }
      $plane_str_list.=sprintf($format,"$favorite_img ".$info['id'],$info['num'],$info['depart'],$info['dest'],$info['price'],$info['depart_d'],$info['depart_h'],$info['depart_m'],$info['arrive_d'],$info['arrive_h'],$info['arrive_m'],$info['id'],$info['id'],$info['id']); 
   endforeach;   
   $meta_str[$index] = $plane_str_list;
   $index = $index + 1;
   $plane_str_list = "";
   endforeach;
   unset($index);
   $origin_plane_str_list = $meta_str[0];
   $extra_plane_str_list = $meta_str[1];
   
   $btn_sheet = "";
   if($_SESSION['sheet']){
      $page_title="Compare Sheet";
      $btn_sheet.=<<<DOC_HTML
<button type="submit" name="btn_sheet" class="btn btn-success" value="off"><i class="icon-th-list icon-white"></i> Goto Flight List
</button>
DOC_HTML;

   }
   else{
      $page_title="Flight List";
      $btn_sheet .= <<<DOC_HTML
<button type="submit" name="btn_sheet" class="btn btn-primary" value="on"><i class="icon-heart icon-white"></i> Goto Compare Sheet
</button>




DOC_HTML;
   }

   $script_post = \lct\func\post_script_string(); 

   $asc_valid_str = "";
   $desc_valid_str = "";
   if($_SESSION['sort']['type']==1){
      $desc_valid_str = "selected"; 
   }
   else{
      $asc_valid_str = "selected";
   }

   $sort_key_option_str = "";
   $sort_key_names = array("ID","Flight Number","Departure","Destination","Price","Departure Date","Arrival Date");
   $key_sz = count($sort_key_names);
   for($i=0;$i<$key_sz;$i++)
   {
      $selected_str = "";
      if($_SESSION['sort']['key']==$i){
         $selected_str = "selected";
      }
      $sort_key_option_str.=<<<DOC_HTML
<option value="$i" $selected_str>$sort_key_names[$i]</option>
DOC_HTML;
   }
   $sort_key_str = <<<DOC_HTML
<select onchange="post_to_url('index.php', {'sort_key':this.value});">
   $sort_key_option_str
</select>
DOC_HTML;

   $sort_type_str = <<<DOC_HTML
<select onchange="post_to_url('index.php', {'sort_type':this.value});">
  <option value="0" $asc_valid_str>Ascending</option>
  <option value="1" $desc_valid_str>Descending</option>
</select>



DOC_HTML;
   $search_sel_0 = "";
   $search_sel_1 = "";
   $search_sel_2 = "";
   switch($_SESSION['search']['key']){
   case -1:case 0:default:
      $search_sel_0 = "selected";
      break;
   case 1:
      $search_sel_1 = "selected";
      break;
   case 2:
      $search_sel_2 = "selected";
      break;
   }

   $pre_word = '"'.$_SESSION['search']['word'].'"';
   
   $ori_table_str = <<<DOC_HTML
   <table class="table table-hover ">
      <tr class="info" id="title-row">
         <td id="title-cell" style='width:50px;'>ID</td>
         <td id="title-cell">Flight Number</td>
         <td id="title-cell">Departure</td>
         <td id="title-cell">Destination</td>
         <td id="title-cell">Price</td>
         <td id="title-cell" style='width:250px;'>Depart Date</td>
         <td id="title-cell" style='width:250px;'>Arrive Date</td>
         <td id="favorite-ctrl" >Favorite</td> 
         $p_extra_th
      </tr>
      $origin_plane_str_list 
   </table>
 


DOC_HTML;
   $ext_table_str = "";
   if($_SESSION['sheet'])
   {
      $ext_table_str = <<<DOC_HTML
<h1>Flight Not in Favorite</h1>
   <table class="table table-hover ">
      <tr class="info" id="title-row">
         <td id="title-cell" style='width:50px;'>ID</td>
         <td id="title-cell">Flight Number</td>
         <td id="title-cell">Departure</td>
         <td id="title-cell">Destination</td>
         <td id="title-cell">Price</td>
         <td id="title-cell" style='width:250px;'>Depart Date</td>
         <td id="title-cell" style='width:250px;'>Arrive Date</td>
         <td id="favorite-ctrl" >Favorite</td> 
      </tr>
      $extra_plane_str_list
   </table>





DOC_HTML;
   }
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
         width:$total_width;
      }
      table
      {
         width:$total_width;
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
         width:90px;
      }
      #title-row{
         font-weight : bold;
      }
      #title-cell{
      }
      #favorite-ctrl
      {
      }
      #content-tr{
         height:100px;
      }
   </style>
   <script>
      $script_post
   </script>
</head>
<body>
   <button type="button" id="btn-out" class="btn btn-info" onclick="javascript:location.href='sign_out.php'"><i class="icon-refresh icon-white"></i> Sign out</button>
   <p class="main-user">Welcome, <b> ${_SESSION["email"]}</b> !</p>
   <form action="index.php" method="POST">
   <p style='font-family:verdana;font-size:32px;font-weight: bold;'>$page_title  
   $btn_sheet </p>
   </form><br>
   Order By:
   $sort_key_str
   $sort_type_str<br>
   <form action="index.php" method="post" class="form-search">
      Search:
      <select name="search_key">
         <option value="0" $search_sel_0>Flight Number</option> 
         <option value="1" $search_sel_1>Departure</option>
         <option value="2" $search_sel_2>Destination</option>
      </select>
      <div class="input-append">
         <input type="text" name="search_word" value=$pre_word class="span2 search-query"> 
         <button type="submit" class="btn btn-primary" name="btn_search" value="on"><i class="icon-search icon-white"></i> Search</button>
      </div>
      <button name="btn_end_search" class="btn btn-danger" value="on"><i class="icon-ban-circle icon-white"></i> End Search</button>
   </form>
   $extra_button
   $ori_table_str
   $ext_table_str
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
      $sql = "INSERT INTO `Flight` (flight_number,departure,destination,price,departure_date,depart_hour,depart_min,arrival_date,arrive_hour,arrive_min)"
        . " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $sth = $db->prepare($sql);
      #echo "SQL:$sql";
      $result = $sth->execute(array(escape_html_tag(($post['flight_num'])),escape_html_tag($post['depart']),escape_html_tag($post['dest']),$post['price'],$post['depart_date'],$post['depart_hour'],$post['depart_min'],$post['arrive_date'],$post['arrive_hour'],$post['arrive_min']));
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
         price = ?,
         departure_date = ?,
         depart_hour = ?,
         depart_min = ?,
         arrival_date = ?,
         arrive_hour = ?,
         arrive_min = ?"
        . " WHERE `Flight`.`id`=? ";
      $sth = $db->prepare($sql);
      #echo "SQL:$sql";
      $result = $sth->execute(array(escape_html_tag($post['flight_num']),escape_html_tag($post['depart']),escape_html_tag($post['dest']),$post['price'],$post['depart_date'],$post['depart_hour'],$post['depart_min'],$post['arrive_date'],$post['arrive_hour'],$post['arrive_min'],$post['btn_save']));
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
         $sql_data = \lct\func\get_sort_sql();
         $sql = $sql_data['sql'];
         $sth = $db->prepare($sql);
         $sth->execute($sql_data['args']);
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
         $info['price'] = $result->price;
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


if (\lct\func\check_user_valid($_SESSION["email"]))
{
   #var_dump($_POST);
   if($_POST['btn_end_search']){
      \lct\func\set_search(-1,"");
   }
   else if($_POST['btn_search']){
      \lct\func\set_search($_POST['search_key'],$_POST['search_word']);  
   }
   if(isset($_POST['sort_type'])){
      \lct\func\set_sort_type($_POST['sort_type']);
   }
   if(isset($_POST['sort_key'])){ 
      \lct\func\set_sort_key($_POST['sort_key']);
   }
   if($_POST['btn_favorite'])
   {
      \lct\func\favorite_flight($_SESSION['email'],$_POST['btn_favorite']);
      #echo "Favorite:${_POST['btn_favorite']}";
      show_signed_in_page();
   }
   else if($_POST['btn_sheet']){
      if($_POST['btn_sheet']=="on"){
         $_SESSION['sheet'] = true;
      }
      else{
         $_SESSION['sheet'] = false;
      }
      show_signed_in_page();
   }
   else if($_POST['btn_save'] && $_SESSION['is_admin']>0)
   {
      #echo "UPDATE!";
      if(!check_array_str_valid($_POST))
      {
         show_err_page("Data Format Error!","All field must not be empty,and cannot contain only spaces.Or maybe there exist illegal characters.","index.php","main page");
      }
      else if(!\lct\func\check_airport_valid($_POST['depart'])){
         show_err_page("Departrue Airport Does Not Exist","Airport must be in airport list.","index.php","main page");
      } 
      else if(!\lct\func\check_airport_valid($_POST['dest'])){
         show_err_page("Destination Airport Does Not Exist","Airport must be in airport list.","index.php","main page");
      } 
      else
      {
         $sql_result = update_plane($_POST);
         show_signed_in_page();
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
      if(!check_array_str_valid($_POST))
      {
         show_err_page("Data Format Error!","All field must not be empty,and cannot contain only spaces.Or maybe there exist illegal characters.","new_plane.php","plane adding page");
      }
      # Process SQL
      else if(!\lct\func\check_airport_valid($_POST['depart'])){
         show_err_page("Departrue Airport Does Not Exist","Airport must be in airport list.","new_plane.php","plane adding page");
      } 
      else if(!\lct\func\check_airport_valid($_POST['dest'])){
         show_err_page("Destination Airport Does Not Exist","Airport must be in airport list.","new_plane.php","plane adding page");
      } 
      else{ 
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
