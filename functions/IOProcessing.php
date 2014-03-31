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









?>
