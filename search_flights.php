<?php
session_save_path("./sessions");
session_start();
require_once("./functions/Database.php");

# var_dump($_POST);

$errmsg='';
$table_str='';
$msg_id='';

if(isset($_POST['btn_ok'])){
   $from = $_POST['depart_name'];
   $to = $_POST['dest_name'];
   $table_str = '';
   $errmsg = '';
   $msg_id='error-msg';
   $result = \lct\func\search_transfer_2($from,$to); 
   if(isset($result['errmsg'])){
      $table_str = '';
      $errmsg = $result['errmsg'];
   }
   else{
      $search_data = $result['data'];
      $count = 1;
      if(count($search_data) > 0){
         $msg_id='normal-msg';
         $errmsg = "Displaying Result.";
         # 有資料才顯示
         $table_str.= <<<HTML_DOC
<table class="table table-hover" id="outter-table">
<tr class="info">
   <td>
      Result
   </td>
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
   <td id="inner-td">Price</td>
</tr>
HTML_DOC;
          
         foreach($search_data as $flight_set){
            $table_str .= "<tr class='success'>";
            $table_str .= "<td>";
            $table_str .= "$count";
            $table_str .= "</td>";
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
   echo <<<DOC_HTML
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Search Flights</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
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
            width:1000px;
         }
         #outter-table{
            width:1500px;
         }
         #td-contain{
         }
      </style>
   </head>
   <body>
      <h1>Search Flights</h1>
      <label id="$msg_id">$errmsg</label><br>
      <form action="search_flights.php" class="form-signin"  method="POST">
         Departure Airport Full Name<br>
         <input type="text" name="depart_name" placeholder="Departure"></input><br>
         Destination Airport Full Name<br>
         <input type="text" name="dest_name" placeholder="Destination"></input><br> 
         Transfer Option<br>
         <label class="radio">
            <input type="radio" name="tansfer_radio" id="transfer-radio" value="0" checked>
            Direct flight, No Transfer.
         </label>
         <label class="radio">
            <input type="radio" name="tansfer_radio" id="transfer-radio" value="1">
            Transfer one time.
         </label>
         <label class="radio">
            <input type="radio" name="tansfer_radio" id="transfer-radio" value="2">
            Transfer two time.
         </label>
         <button type="submit" class="btn btn-primary"  name="btn_ok" value="btn_search"><i class="icon-search icon-white"></i>Search</button>
         <button type="button" class="btn btn-danger"  onclick="javascript:location.href='index.php'"><i class="icon-ban-circle icon-white"></i> Cancel</button>
      </form>
      $table_str
   </body>

</html>

DOC_HTML;
}





?>
