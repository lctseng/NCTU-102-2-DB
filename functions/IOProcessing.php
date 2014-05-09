<?php 
namespace lct\func;


# Encrypt password
function pwd_hash($user_name,$pwd)
{
   return md5(crypt($pwd,md5($user_name.sha1($user_name))));
}


# Check whether account format is correct
function account_check($account)
{
   if(strlen($account)==0)
   {
      return false;
   }
   if(substr_count($account," ")>0)
   {
      return false;   
   }
   if(substr_count($account,"\t")>0)
   {
      return false;
   }

   return true;
}



function post_script_string()
{
   $str = <<<DOC_HTML
function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", params[key]);

        form.appendChild(hiddenField);
    }

    document.body.appendChild(form);    // Not entirely sure if this is necessary
    form.submit();
}

DOC_HTML;
   return $str;
}


function escape_html_tag($str)
{
   return strip_tags($str);
}

function check_hour_valid($hour){
   return $hour <= 12 && $hour >= -12;
}


function to_time_zone_display($hour){
   # convert hour to time zone format
   return sprintf("%+03d:00",$hour); 
}


?>
