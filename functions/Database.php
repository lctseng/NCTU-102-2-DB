<?php
namespace lct\func;



function load_db_data()
{
   return file("db_connect.conf");
}


function create_db_link()
{
   $db_data = @load_db_data();
   $db_data = array_map(trim,$db_data);
   $db_host = array_shift($db_data);
   $db_name = array_shift($db_data);
   $db_user = array_shift($db_data);
   $db_password = array_shift($db_data);

   $dsn = "mysql:host=$db_host;dbname=$db_name";
   try{
      $db = new \PDO($dsn,$db_user,$db_password);
   }
   catch (\PDOException $ex)
   {
      return false;
   }
   return $db;

}

function check_user_exist_DB($uname)
{
   $db = create_db_link();
   if($db){
      $sql = "SELECT * FROM `User` WHERE `account` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($uname));
      if($sth->fetchObject()){
         #echo "$uname Exist!";
         return true;
      }
      else
      {
         #echo "$uname NOT Exist!";
         return false;   
      }
   }
   else{
      #echo "User $uname Check Fail : DB Error!";
      return false;
   }
}


function check_user_valid($uname)
{
   #echo "Check User:$uname";
   # If empty
   if(!$uname)
   {
      return false;
   }
   
   # If not match with SESSION
   if($_SESSION['email']!=$uname)
   {
      return false;
   }
   # if current not in DB
   if(!check_user_exist_DB($uname))
   {
      return false;
   }



   return true;

}

function fetch_user_data_by_id($uid){ 
   $db = create_db_link();
   if($db){
      $sql = "SELECT * FROM `User` WHERE `id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($uid));
      if($result = $sth->fetchObject()){
         return $result;
      }
      else
      {
         return false;

      }
   }
}

function get_uid($uname){ 
   $db = create_db_link();
   if($db){
      $sql = "SELECT * FROM `User` WHERE `account` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($uname));
      if($result = $sth->fetchObject()){
         return $result->id;
      }
      else
      {
         return false;

      }
   }
}

function is_favorite_by_uname($uname,$fid){
   $uid = get_uid($uname);
   return is_favorite($uid,$fid);
}

function is_favorite($uid,$fid){   
   $db = create_db_link();
   if($db){
      $sql = "SELECT * FROM `CompareSheet` WHERE `flight_id` = ? AND `user_id` = ? ";
      $sth = $db->prepare($sql);
      $sth->execute(array($fid,$uid));
      if($result = $sth->fetchObject()){
         return true;
      }
      else
      {
         return false;   
      }
   }
}

function remove_favorite($uid,$fid){
   $db = create_db_link();
   if($db){
      $sql = "DELETE FROM `CompareSheet` WHERE `flight_id` = ? AND `user_id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($fid,$uid));
      return true;
   }
   else{
      return false;
   }
}

function mark_favorite($uid,$fid)
{
   $db = create_db_link();
   if($db){
      $sql = "INSERT INTO `CompareSheet` (flight_id,user_id) 
         VALUES (?,?)";
      $sth = $db->prepare($sql);
      $sth->execute(array($fid,$uid));
      return true;
   }
   else{
      return false;
   }
}


function favorite_flight($uname,$fid)
{
   $uid = get_uid($uname);
   #echo "User:$uid Favorite :$fid";
   if(is_favorite($uid,$fid)){
      remove_favorite($uid,$fid);
      #echo "Remove Favorite";
   }
   else{
      mark_favorite($uid,$fid);
      #echo "Mark favorite";
   }
}

function load_sheet_plane_data($uname)
{
   $uid = get_uid($uname);
   $db =  create_db_link();
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

function on_login($uname,$uid,$right,$sheet)
{
   $_SESSION['email'] = $uname;
   $_SESSION['uid'] = $uid;
   $_SESSION['is_admin'] = $right;
   $_SESSION['sheet'] = $sheet;
   get_sort_way($uid);
}

function get_sort_way($uid)
{
   # sort key
   # 0 : id
   # 1 : flight ID
   # 2 : depart
   # 3 : dest
   # 4 : price
   # 5 : depart date
   # 6 : arrival date

   # sort type
   # 0 : asc
   # 1 : desc
   
   $usr_data = fetch_user_data_by_id($uid);
   if($usr_data){
      $_SESSION['sort'] = array('key'=>$usr_data->sort_key,'type'=>$usr_data->sort_type);
   }
   else{
      $_SESSION['sort'] = array('key'=>0,'type'=>0);
   }   
}

function get_sort_sql()
{
   $sort_sql = get_append_sort_sql(); 
   $sql = "SELECT * FROM `Flight` $sort_sql";
   return $sql;
}

function get_append_sort_sql()
{
   $sort_obj = $_SESSION['sort'];
   
   # Sort Type 
   $sort_type = "ASC";
   if($sort_obj['type']==1){
     $sort_type = "DESC";
   }
  
   # Sort Key
   $main_key = "id $sort_type";
   switch($sort_obj['key']){
   case 0:
      $main_key = "id $sort_type";
      break;
   case 1:
      $main_key = "flight_number $sort_type";
      break;
   case 2:
      $main_key = "departure $sort_type";
      break;
   case 3:
      $main_key = "destination $sort_type";
      break;
   case 4:
      $main_key = "price $sort_type";
      break;
   case 5:
      $main_key = "departure_date $sort_type,depart_hour $sort_type,depart_min $sort_type";
      break;
   case 6:
      $main_key = "arrival_date $sort_type,arrive_hour $sort_type,arrive_min $sort_type";
      break;
   default:
      $main_key = "id $sort_type";
      break;
   }

   #var_dump($sort_obj);

   #echo $sort_type;
   $sql =" ORDER BY  $main_key,flight_number $sort_type";
   #echo $sql;
   return $sql;
}

function set_sort_key($key){
   $_SESSION['sort']['key'] = $key;
   
   $db = create_db_link();
   if($db){
      $sql = "UPDATE `User` SET `sort_key` = ? WHERE `id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($key,$_SESSION['uid']));
   }
   

}

function set_sort_type($type){
   if($type==1){
      $_SESSION['sort']['type'] = 1;
   } 
   else{
      $_SESSION['sort']['type'] = 0;
   }


   $db = create_db_link();
   if($db){
      $sql = "UPDATE `User` SET `sort_type` = ? WHERE `id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($type,$_SESSION['uid']));
   }
   

}


?>
