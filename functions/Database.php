<?php
namespace lct\func;

require_once("transfer_sql.php");


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

function add_user($uname,$pwd,$pwd_confirm,$admin){
   $add_result = false;
   $error_msg = "";
   if(!\lct\func\account_check($uname)){ # Check account format
      $error_msg = "Account cannot contains space, and it cannot be empty.";
   }
   else if(strlen($pwd)==0 || strlen($pwd_confirm)==0){
      $error_msg = "Password cannot be empty.";
   }
   else if($pwd !== $pwd_confirm) # Password Same Check
   {
      $error_msg = "Password not match.";
   }
   else if(strlen(\lct\func\escape_html_tag($uname))!=strlen($uname)){
      $error_msg = "Account cannot contains illegal characters.";
   }
   else if(strlen(\lct\func\escape_html_tag($pwd))!=strlen($pwd)){
      $error_msg = "Password cannot contains illegal characters.";
   }
   else{
         #Convert admin bit
         $is_admin = 0;
         if($admin==="on")
         {
            $is_admin = 1;
         }
         # SQL
         $db = create_db_link();
         $sql = "INSERT INTO `User` (account,password,is_admin)"
              . " VALUES(?, ?, ?)";
         $sth = $db->prepare($sql);
         $result = $sth->execute(array($uname,\lct\func\pwd_hash($uname,$pwd),$is_admin));
         if($result){
            $add_result = true;
         }
         else{
            $error_msg = "Account already in use.";
         }
   }
   return array("result"=>$add_result,"error_msg"=>$error_msg);
}

function delete_user($uid){
   $db = create_db_link();
   if($db){
      $sql = "DELETE FROM `User` WHERE `id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($uid));
   }
}

function promote_user($uid){
   $db = create_db_link();
   if($db){
      $sql = "UPDATE `User` SET `is_admin` = 1 WHERE `id` = ? ";
      $sth = $db->prepare($sql);
      $sth->execute(array($uid));
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

function get_user_list()
{
   $info_list=array();
   $db = create_db_link();
   if($db){
      $sql = "SELECT * FROM `User`";
      $sth = $db->prepare($sql);
      $sth->execute();
      while($result=$sth->fetchObject()){
         $info = array();
         $info['id'] = $result->id;
         $info['account'] = $result->account;
         $info['is_admin'] = $result->is_admin;
         array_push($info_list,$info);
      }
   }
   return $info_list;
}


function extract_hour($db,$datetime){ 
   $key = "HOUR('$datetime')";
   $_stat = $db->query("SELECT $key");
   $obj = $_stat->fetchObject();
   return $obj->$key-12;
}

function pack_time_zone($db,$hour){
   $e_hour = $hour + 12 ;
   $key = "MAKETIME($e_hour,0,0)";
   $_stat = $db->query("SELECT $key ;");
   $obj = $_stat->fetchObject();
   return $obj->$key;
}


function get_airport_list(){
   $info_list = array();
   $db = create_db_link();
   if($db){
      $sql = "SELECT * FROM `Airport`";
      $sth = $db->prepare($sql);
      $sth->execute();
      while($result = $sth->fetchObject()){
         $info = array();
         $info['id'] = $result->id;
         $info['name'] = $result->name;
         $info['full_name'] = $result->full_name;
         $info['longitude'] = $result->longitude;
         $info['latitude'] = $result->latitude;
         $info['country'] = $result->country;
         $info['time_zone'] = extract_hour($db,$result->time_zone); 
         array_push($info_list,$info); 
      }
   }
   return $info_list;
   
}

function add_airport($name,$full_name,$longitude,$latitude,$country,$time_zone){
   $add_result = false;
   $error_msg = "";
   if(strlen($name)==0){
      $error_msg = "Name cannot be empty.";
   }
   else if(strlen(\lct\func\escape_html_tag($name))!=strlen($name)){
      $error_msg = "Name cannot contains illegal characters.";
   }
   else if(strlen(\lct\func\escape_html_tag($full_name))!=strlen($full_name)){
      $error_msg = "Full Name cannot contains illegal characters.";
   }
   else if(!\lct\func\account_check($name)){
      $error_msg = "Name cannot contain only spaces.";
   }
   else if($longitude>180.0 || $longitude<-180.0){
      $error_msg = "Longitude must set between -180.0 to 180.0";
   }
   else if($latitude>90.0 || $latitude<-90.0){
      $error_msg = "Latitude must set between -90.0 to 90.0";
   }
   else if(!check_hour_valid($time_zone)){ 
      $error_msg = "Time zone must set between -12 to +12";
   }
   else{
      $db = create_db_link();

      if($db)
      {
         $sql = "INSERT INTO `Airport` (name,full_name,longitude,latitude,country,time_zone) VALUES (?,?,?,?,?,?)";
         $sth = $db->prepare($sql);
         $result = $sth->execute(array($name,$full_name,$longitude,$latitude,$country,pack_time_zone($db,$time_zone)));
         if($result){
            $add_result = true;
            $error_msg = "";
         }
         else{
            $error_msg = "Airport Name already exists!";
         }
      }
      else{
         $error_msg = "Database Error!";
      }
   }
   return array("result"=>$add_result,"error_msg"=>$error_msg);
}

function delete_airport($id){
   $db = create_db_link();
   if($db){
      $sql = "DELETE FROM `Airport` WHERE `id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($id));
   }
}

function edit_airport($id,$name,$full_name,$longitude,$latitude,$country,$time_zone){
   $add_result = false;
   $error_msg = "";
   if(strlen($name)==0){
      $error_msg = "Name cannot be empty.";
   }
   else if(strlen(\lct\func\escape_html_tag($name))!=strlen($name)){
      $error_msg = "Name cannot contains illegal characters.";
   }
   else if(strlen(\lct\func\escape_html_tag($full_name))!=strlen($full_name)){
      $error_msg = "Full Name cannot contains illegal characters.";
   }
   else if(!\lct\func\account_check($name)){
      $error_msg = "Name cannot contain only spaces.";
   }
   else if($longitude>180.0 || $longitude<-180.0){
      $error_msg = "Longitude must set between -180.0 to 180.0";
   }
   else if($latitude>90.0 || $latitude<-90.0){
      $error_msg = "Latitude must set between -90.0 to 90.0";
   }
   else if(!check_hour_valid($time_zone)){ 
      $error_msg = "Time zone must set between -12 to +12";
   }
   else{
      $db = create_db_link();

      if($db)
      {
         $sql = "UPDATE `Airport` SET
            `name` = ?,
            `full_name` = ?,
            `longitude` = ?,
            `latitude` = ?,
            `country` = ?,
            `time_zone` = ?
             WHERE `id` = ?
            ";
         $sth = $db->prepare($sql);
         $result = $sth->execute(array($name,$full_name,$longitude,$latitude,$country,pack_time_zone($db,$time_zone),$id));
         if($result){
            $add_result = true;
            $error_msg = "";
         }
         else{
            $error_msg = "Cannot wirte to Database!";
         }
      }
      else{
         $error_msg = "Database Error!";
      }
   }
   return array("result"=>$add_result,"error_msg"=>$error_msg);
}

function check_airport_valid($airport){
   # Check whether in DB or not
   $db = create_db_link();
   if($db){
      $sql = "SELECT `id` FROM `Airport` WHERE `name` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($airport));
      if($sth->fetchObject()){
         return true;
      }
      else{
         return false;
      }
   }
   else{
      return false;
   }
}


function get_country_list(){
   $info_list = array();
   $db = create_db_link();
   if($db){
      $sql = "SELECT * FROM `Country`";
      $sth = $db->prepare($sql);
      $sth->execute();
      while($result = $sth->fetchObject()){
         $info = array();
         $info['id'] = $result->id;
         $info['name'] = $result->name;
         $info['full_name'] = $result->full_name;
         array_push($info_list,$info); 
      }
   }
   return $info_list;
   
}

function add_country($name,$full_name){
   $add_result = false;
   $error_msg = "";
   if(strlen($name)==0){
      $error_msg = "Name cannot be empty.";
   }
   else if(strlen(\lct\func\escape_html_tag($name))!=strlen($name)){
      $error_msg = "Name cannot contains illegal characters.";
   }
   else if(strlen(\lct\func\escape_html_tag($full_name))!=strlen($full_name)){
      $error_msg = "Full Name cannot contains illegal characters.";
   }
   else if(!\lct\func\account_check($name)){
      $error_msg = "Name cannot contain only spaces.";
   }
   else{
      $db = create_db_link();

      if($db)
      {
         $sql = "INSERT INTO `Country` (name,full_name) VALUES (?,?)";
         $sth = $db->prepare($sql);
         $result = $sth->execute(array($name,$full_name));
         if($result){
            $add_result = true;
            $error_msg = "";
         }
         else{
            $error_msg = "Country Name already exists!";
         }
      }
      else{
         $error_msg = "Database Error!";
      }
   }
   return array("result"=>$add_result,"error_msg"=>$error_msg);
}

function delete_country($id){
   $db = create_db_link();
   if($db){
      $sql = "DELETE FROM `Country` WHERE `id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($id));
   }
}

function edit_country($id,$name,$full_name){
   $add_result = false;
   $error_msg = "";
   if(strlen($name)==0){
      $error_msg = "Name cannot be empty.";
   }
   else if(strlen(\lct\func\escape_html_tag($name))!=strlen($name)){
      $error_msg = "Name cannot contains illegal characters.";
   }
   else if(strlen(\lct\func\escape_html_tag($full_name))!=strlen($full_name)){
      $error_msg = "Full Name cannot contains illegal characters.";
   }
   else if(!\lct\func\account_check($name)){
      $error_msg = "Name cannot contain only spaces.";
   }
   else{
      $db = create_db_link();

      if($db)
      {
         $sql = "UPDATE `Country` SET
            `name` = ?,
            `full_name` = ?
             WHERE `id` = ?
            ";
         $sth = $db->prepare($sql);
         $result = $sth->execute(array($name,$full_name,$id));
         if($result){
            $add_result = true;
            $error_msg = "";
         }
         else{
            $error_msg = "Cannot wirte to Database!";
         }
      }
      else{
         $error_msg = "Database Error!";
      }
   }
   return array("result"=>$add_result,"error_msg"=>$error_msg);
}

function check_country_valid($country){
   # Check whether in DB or not
   $db = create_db_link();
   if($db){
      $sql = "SELECT `id` FROM `Country` WHERE `name` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($country));
      if($sth->fetchObject()){
         return true;
      }
      else{
         return false;
      }
   }
   else{
      return false;
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
         echo "$uname UID ERROR!";
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


function favorite_flight($uname,$fid,$force_add = false)
{
   $uid = get_uid($uname);
   #echo "User:$uid Favorite :$fid";
   if(is_favorite($uid,$fid) && !$force_add){
      remove_favorite($uid,$fid);
      #echo "Remove Favorite";
   }
   else{
      mark_favorite($uid,$fid);
      #echo "Mark favorite";
   }
}

function get_sheet_where_data($uid,$include=true){
   $where_data = get_where_data();
   $sql = $where_data['sql'];
   if($include){
      $include_str = "";
   }
   else{   
      $include_str = "NOT";
   }
   if($sql==""){ 
      $sql .= "WHERE `id` $include_str IN (SELECT `flight_id` FROM `CompareSheet` WHERE `user_id` = $uid)";
   }
   else{
      $sql .= " AND `id` $include_str IN (SELECT `flight_id` FROM `CompareSheet` WHERE `user_id` = $uid)";
   }
   $where_data['sql'] = $sql;
   return $where_data;

}

function get_sheet_sort_sql($uid,$include=true)
{
   $where_data = get_sheet_where_data($uid,$include);
   $where_sql = $where_data['sql'];
   $where_args = $where_data['args'];
   $sort_sql = get_append_sort_sql(); 
   $sql = "SELECT * FROM `Flight` $where_sql  $sort_sql";
   return array("sql"=>$sql,"args"=>$where_args);
}

function load_sheet_plane_data($uid,$include=true)
{
   $db =  create_db_link();
   if($db)
   {
      $sql_data = get_sheet_sort_sql($uid,$include);
      $sql = $sql_data['sql'];
      $sth = $db->prepare($sql);
      $sth->execute($sql_data['args']);

         
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


function is_sheet(){
   return $_SESSION['sheet'];
}
function on_login($uname,$uid,$right,$sheet)
{
   $_SESSION['email'] = $uname;
   $_SESSION['uid'] = $uid;
   $_SESSION['is_admin'] = $right;
   $_SESSION['sheet'] = $sheet;
   $_SESSION['search'] = array('key'=>-1,'word'=>"");
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

function set_search($key,$word){
   $_SESSION['search']['key'] = $key;
   $_SESSION['search']['word'] = $word;
}

function sql_escape($str){
   return $str;
}

function get_where_data()
{
   $sql = "";
   $args = array();
   $_SESSION['search']['word'] = sql_escape($_SESSION['search']['word']);
   switch($_SESSION['search']['key']){
   case 0:
      $sql = "WHERE `flight_number` like ?";
      array_push($args,'%'.$_SESSION['search']['word']. '%');
      break;
   case 1:
      $sql = "WHERE `departure` like ?";
      array_push($args,'%'.$_SESSION['search']['word']. '%');
      break;
   case 2:
      $sql = "WHERE `destination` like ?";
      array_push($args,'%'.$_SESSION['search']['word']. '%');
      break;
   default:
      $sql = "";
      break;
   }
   return array("sql"=>$sql,"args"=>$args);
}

function get_sort_sql()
{
   $where_data = get_where_data();
   $where_sql = $where_data['sql'];
   $where_args = $where_data['args'];
   $sort_sql = get_append_sort_sql(); 
   $sql = "SELECT * FROM `Flight` $where_sql  $sort_sql";
   return array("sql"=>$sql,"args"=>$where_args);
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


function check_airport_full_name_exist($airport){
   # Check whether in DB or not
   $db = create_db_link();
   if($db){
      $sql = "SELECT `id` FROM `Airport` WHERE `full_name` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($airport));
      if($sth->fetchObject()){
         return true;
      }
      else{
         return false;
      }
   }
   else{
      return false;
   }
}


function check_airport_full_name_valid($name){
   $result = array();
   $result['is_err'] = false;
   $result['errmsg'] = '';
   $chk = check_airport_full_name_exist($name);
   if(!$chk){  
      $result['is_err'] = true;
      $result['errmsg'] = "Airport full name doesn't exist.";
   }
   return $result;
}

function search_transfer_2($from,$to,$order,$night){
   global $default_order;
   if($night){ 
      global $transfer_sql_night;
      $target_sql = $transfer_sql_night;
   } 
   else{
      global $transfer_sql_all;
      $target_sql = $transfer_sql_all;
   }
   $sql = $target_sql[2].$order.$default_order;
   return search_transfer($sql,$from,$to,2);
}
function search_transfer_1($from,$to,$order,$night){ 
   global $default_order;
   if($night){ 
      global $transfer_sql_night;
      $target_sql = $transfer_sql_night;
   } 
   else{
      global $transfer_sql_all;
      $target_sql = $transfer_sql_all;
   }
   $sql = $target_sql[1].$order.$default_order;
   return search_transfer($sql,$from,$to,1);
}
function search_transfer_0($from,$to,$order,$night){ 
   global $default_order;
   if($night){ 
      global $transfer_sql_night;
      $target_sql = $transfer_sql_night;
   } 
   else{
      global $transfer_sql_all;
      $target_sql = $transfer_sql_all;
   }
   $sql = $target_sql[0].$order.$default_order;
   return search_transfer($sql,$from,$to,0);
}

function search_transfer($transfer_sql,$from,$to,$arg_copy){
   $error_msg = null;
   $info = array(); 
   # Check name if valid or not
   # Departure
   $err_chk = check_airport_full_name_valid($from);
   if($err_chk['is_err']){
      return array('errmsg'=>'Departure '.$err_chk['errmsg']);
   }
   # Destination
   $err_chk = check_airport_full_name_valid($to);
   if($err_chk['is_err']){
      return array('errmsg'=>'Destination '.$err_chk['errmsg']);
   }

   # Translate Full Name to abbr. name
   $r_from = translate_to_abbr($from);
   $r_to = translate_to_abbr($to);


   $db = create_db_link();
   if($db){
      $sql = $transfer_sql;
      $sth = $db->prepare($sql);
      $args = array($r_from,$r_to);
      for($i=0;$i<$arg_copy;$i++){
         array_push($args,$r_from);
         array_push($args,$r_to);
      }
      $sth->execute($args);
      # var_dump($sth->errorInfo());
      while($result = $sth->fetchObject()){
         $raw_info = array();
         $raw_info['id1'] = $result->id1;
         $raw_info['id2'] = $result->id2;
         $raw_info['id3'] = $result->id3; 
         $raw_info['f_time1'] = $result->f_time1;
         $raw_info['f_time2'] = $result->f_time2;
         $raw_info['f_time3'] = $result->f_time3;
         $raw_info['total_time'] = $result->total_time;
         $raw_info['flight_time'] = $result->flight_time;
         $raw_info['transfer_time'] = $result->transfer_time;
         $raw_info['total_price'] = $result->total_price;
         
         # var_dump($raw_info);
         array_push($info,pack_flight_set($raw_info));
      }
   }
   return array('data'=>$info,'errmsg'=>$error_msg,'sql'=>$sth->queryString);
}


function translate_to_abbr($full){ 
   $db = create_db_link(); 
   if($db){
      $sql = "SELECT `name` FROM `Airport` WHERE `full_name` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($full));
      if($result = $sth->fetchObject()){
         return $result->name;
      }
      else
      {
         return '';  
      }
   }
   return '';
}

function id_to_flight_info($id){
   $db = create_db_link(); 
   if($db){
      $sql = "SELECT * FROM `Flight` WHERE `id` = ?";
      $sth = $db->prepare($sql);
      $sth->execute(array($id)); 
      if($result = $sth->fetchObject())
      {
         $info = array();
         $info['id'] = $result->id;
         $info['num'] = ($result->flight_number);
         $info['depart'] = ($result->departure);
         $info['dest'] = ($result->destination);
         $info['price'] = $result->price;
         $info['depart_d'] = strtok($result->departure_date," ");
         $info['depart_h'] = $result->depart_hour;
         $info['depart_m'] = $result->depart_min;
         $info['arrive_d'] =  strtok($result->arrival_date," ");
         $info['arrive_h'] = $result->arrive_hour;
         $info['arrive_m'] = $result->arrive_min;
      }
   }
   return $info;
  
}

function parse_flight_set($raw){
   $air_set = array();
   if(isset($raw['id1'])){
      # 將第一台飛機資訊轉換
      $set_info = id_to_flight_info($raw['id1']);
      $set_info['f_time'] = $raw['f_time1'];
      array_push($air_set,$set_info);
   }
   if(isset($raw['id2'])){
      # 將第2台飛機資訊轉換
      $set_info = id_to_flight_info($raw['id2']);
      $set_info['f_time'] = $raw['f_time2'];
      array_push($air_set,$set_info);
   }
   if(isset($raw['id3'])){
      # 將第3台飛機資訊轉換
      $set_info = id_to_flight_info($raw['id3']);
      $set_info['f_time'] = $raw['f_time3'];
      array_push($air_set,$set_info);
   }
   return $air_set;
}

function pack_flight_set($raw){
   
   $flight_set = parse_flight_set($raw);
   $info = array(
      "set"=>$flight_set,
      "total_time"=>$raw['total_time'],
      "flight_time"=>$raw['flight_time'],
      "transfer_time"=>$raw['transfer_time'],
      "total_price"=>$raw['total_price']
   );
   return $info;
}

function get_country_airport_set(){
   $country_set = array();
   $db = create_db_link(); 
   if($db){
      # 找出國家
      $sql = "SELECT * FROM `Country` ORDER BY `full_name`";
      $sth = $db->prepare($sql);
      $sth->execute(); 
      while($c_result = $sth->fetchObject())
      {
         $set = array();
         $set['country'] = $c_result->full_name;
         $airports = array();
         # 找出各機場
         $air_sql = "SELECT `full_name` FROM `Airport` WHERE `country` = '$c_result->name'  ORDER BY `full_name`";
         $air_sth = $db->prepare($air_sql);
         $air_sth->execute(array('TWN'));
         while($a_result = $air_sth->fetchObject()){
            array_push($airports,$a_result->full_name);
         }
         $set['airports'] = $airports;
         array_push($country_set,$set);
      }
   }
   return $country_set;
}


?>
