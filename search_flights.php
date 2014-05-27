<?php
session_save_path("./sessions");
session_start();
require_once("./functions/Database.php");

#  var_dump($_POST);

$errmsg='Search Specific Flights';
$table_str='';
$msg_id='normal-msg';
$sql_show = '(NONE)';

if(isset($_POST['btn_ok'])){
   # 檢查登入
   $logged_in = false;
   if (\lct\func\check_user_valid($_SESSION["email"])){
      $logged_in = true;
   }

   $from = $_POST['depart_name'];
   $to = $_POST['dest_name'];
   $table_str = '';
   $errmsg = '';
   $msg_id='error-msg';
   $tr_type = $_POST['transfer_radio'];
   $order = $_POST['sort_order'];
   $night = false;
   $night_str = "Yes";
   if($_POST['night']==="NO"){
      $night = true;
      $night_str = "No";
   }
   if($tr_type==='2'){
      $result = \lct\func\search_transfer_2($from,$to,$order.'',$night); 
   }
   else if($tr_type==='1'){
      $result = \lct\func\search_transfer_1($from,$to,$order.'',$night); 
   }
   else{ 
      $result = \lct\func\search_transfer_0($from,$to,$order.'',$night); 
   }
   $sql_show = $result['sql'];

   if(isset($result['errmsg'])){
      $table_str = '';
      $errmsg = $result['errmsg'];
   }
   else{
      $search_data = $result['data'];
      $count = 1;
      if(count($search_data) > 0){
         $msg_id='normal-msg';
         $errmsg = "Displaying result : From '${_POST['depart_name']}' to '${_POST['dest_name']}' , limit max transfer times = ${_POST['transfer_radio']} ,overnight = $night_str ,order by $order. ";
         # 有資料才顯示
         $table_str.= <<<HTML_DOC
<table class="table table-hover" id="outter-table">
<tr class="info">
   <td>Result </td>
   <td>Favorite</td>
   <td>
      <table id="inner-table">    
         <tr>
            <td id="inner-td">Flight Number</td>
            <td id="inner-td">Departure Airport</td>
            <td id="inner-td-long">Destination Airport</td>
            <td id="inner-td-long">Departure Time</td>
            <td id="inner-td-long">Arrival Time</td>
            <td id="inner-td-long">Flight Time</td>
         </tr>
      </table>
   </td>
   <td id="inner-td-long">Total Flight Time</td>
   <td id="inner-td-long">Transfer Time</td>
   <td id="inner-td-long">Total Journey Time</td>
   <td id="inner-td">Price</td>
</tr>
HTML_DOC;
          
         foreach($search_data as $flight_set){
            $table_str .= "<tr class='success'>";
            $table_str .= "<td>";
            $table_str .= "$count";
            $table_str .= "</td>";
            if($logged_in){
               $id_string = '';
               foreach($flight_set['set'] as $flight){
                  $id_string .= "${flight['id']} ";
               }
               $table_str .= <<<HTML_DOC
<td>
   <form action="index.php" method="post">   
      <button type="submit" name="str_add_favorite" class="btn btn-warning btn-large" value="$id_string">Add</button>
   </form>
</td>
HTML_DOC;
            }
            else{
               $table_str.= '<td><a id="btn-main" class="btn btn-large disabled" style="width:65px;">Invalid</a></td>';
            }
            $table_str .= "<td>";
            $table_str .= "<table id='inner-table'>";
            #echo "<br> Result $count <br>";
            foreach($flight_set['set'] as $flight){
               #var_dump($flight);
               #echo "<br>";
               $depart_date = sprintf("%s %02d:%02d",$flight['depart_d'],$flight['depart_h'],$flight['depart_m']);
               $arrive_date = sprintf("%s %02d:%02d",$flight['arrive_d'],$flight['arrive_h'],$flight['arrive_m']);
               $table_str.= <<<HTML_DOC
<tr>
   <td id="inner-td">${flight['num']}</td> 
   <td id="inner-td">${flight['depart']}</td>
   <td id="inner-td">${flight['dest']}</td>
   <td id="inner-td-long">$depart_date</td>
   <td id="inner-td-long">$arrive_date</td>
   <td id="inner-td-long">${flight['f_time']}</td>
</tr>
HTML_DOC;
            }
            $table_str .= "</table>";
            $table_str .= "</td>";
            $table_str .= <<<HTML_DOC
<td id="inner-td-long">${flight_set['flight_time']}</td>
<td id="inner-td-long">${flight_set['transfer_time']}</td>
<td id="inner-td-long">${flight_set['total_time']}</td>
<td id="inner-td">${flight_set['total_price']}</td>

HTML_DOC;
            #echo "<br>";
            #echo "Flight Time:";
            #echo $flight_set['flight_time'];
            #echo "<br>";
            #echo "Transfer Time:";
            #echo $flight_set['transfer_time'];
            #echo "<br>";
            #echo "Total Price:";
            #echo $flight_set['total_price'];
            #echo "<br>";
            $count += 1;
            $table_str .= "</tr>";
         }
         unset($count);
         $table_str.=<<<HTML_DOC
</table>
HTML_DOC;
      }
      else{
         $table_str = '';
         $errmsg = 'No match result.';
      }
   }
}

show_search_page();

function show_search_page()
{
   global $errmsg;
   global $table_str;
   global $msg_id;
   global $sql_show;
   $airport_select_str = create_airport_select_option_str();
echo <<<DOC_HTML
<!doctype html>
<html lang="en">
<head>
 
   <meta charset="utf-8">
   <title>Search Flights</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
   <script src="bootstrap/jQuery/jquery-1.11.1.min.js"></script>
   <script src="bootstrap/js/bootstrap.min.js"></script>
   <style>
      body {
         margin-left:50px;
         padding-top: 40px;
         padding-bottom: 40px;
         background-color: #f5f5f5;
      }
      .form-signin input[type="text"]{
           font-size: 16px;
           height: auto;
           margin-bottom: 15px;
           padding: 7px 9px;
      }
      .form-signin select{
         width:80px;
      }
      #error-msg{
        font-weight : bold;
       color:rgb(255,0,0);
      }
      #normal-msg{
        font-weight : bold;
       color:rgb(0,0,255);
      }
      #inner-td{
         width:150px;
      }
      #inner-td-long{
         width:180px;
      }
      #inner-table{
         width:1200px;
      }
      #outter-table{
         width:1800px;
      }
      #td-contain{
      }

      #select-airport{
         width:200px;
      }
   </style>
   <script> 
      $(document).ready(function(){
         $("#sql_btn").click(function(){
          $("#sql").animate({
            height:'toggle'
          });
        });
      });
   </script> 
</head>
 

<body>
      <h1>Search Flights</h1>
      <label id="$msg_id">$errmsg</label><br>
      <form action="search_flights.php" class="form-signin"  method="POST">
         Departure Airport Full Name<br>
         <select id="select-airport" name='depart_name'>
            $airport_select_str
         </select><br>
         Destination Airport Full Name<br>
         <select id="select-airport" name='dest_name'>
            $airport_select_str
         </select><br>
         Transfer Option<br>
         <label class="radio">
            <input type="radio" name="transfer_radio" id="transfer-radio" value="0" checked>
            Direct flight, no transfer.
         </label>
         <label class="radio">
            <input type="radio" name="transfer_radio" id="transfer-radio" value="1">
            Transfer no more than one time.
         </label>
         <label class="radio">
            <input type="radio" name="transfer_radio" id="transfer-radio" value="2">
            Transfer no more than two time.
         </label>
         
         <label class="checkbox">
            <input type="checkbox" name="night" value="YES">
            No overnight
         </label>

         Sort By:<br>
         <select id="select-airport" name='sort_order'>
            <option selected  value='total_price'>Total Price</option>
            <option  value='depart_time'>Departure Time</option>
            <option  value='arrive_time'>Arrival Time</option>
            <option  value='total_time'>Total Journey Time</option>
            <option  value='flight_time'>Total Flight Time</option>
            <option  value='transfer_time'>Total Transfer Time</option>
         </select><br>
         <button type="submit" class="btn btn-primary"  name="btn_ok" value="btn_search"><i class="icon-search icon-white"></i>Search</button>
         <button type="button" class="btn btn-danger"  onclick="javascript:location.href='index.php'"><i class="icon-ban-circle icon-white"></i> Back to Main Page</button>
      </form>
      <button id="sql_btn" class="btn btn-primary">Display SQL</button>
      <div id="sql" style="display:none">
        $sql_show
      </div>
      <br>
      <br>
      $table_str
   </body>

</html>

DOC_HTML;
}


function create_airport_select_option_str(){
   $op_str = '';
   $country_set = \lct\func\get_country_airport_set();
      $op_str.=<<<DOC_HTML
   <option disabled selected value="">Select Airport</option>
DOC_HTML;
   foreach($country_set as $set){
      $op_str.=<<<DOC_HTML
   <option id="normal-msg" disabled value="" >--${set['country']}</option>
DOC_HTML;
      foreach($set['airports'] as $airport){   
      $op_str.=<<<DOC_HTML
   <option value="$airport" >$airport</option>
DOC_HTML;
      }
   }
   return $op_str;
}




?>
