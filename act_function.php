<?php
// multiple output
function multiout($port,$val){
  global $fp,$mysleep;
  for($dev=0;$dev<4;$dev++)fwrite($fp[$dev],chr($port).chr($val),2);
  usleep($mysleep);
}

// multiple input
function multiin($port){
  global $fp,$mysleep;
  multiout($port,0x00);
  for($dev=0;$dev<4;$dev++)$myin[$dev]=ord(fread($fp[$dev],1));
  return $myin;
}

// key check status
function key_checkstatus($mykey){
  global $inkey,$maskin,$keybasefordev,$keyassigneddev;
  $dev=$keyassigneddev[$mykey];
  if($inkey[$dev] & $maskin[$mykey-$keybasefordev[$dev]])return 0;
  else return 1;
}

// rele set
function myreleset($r,$val){
  global $rele,$rele_time;
  if($val)$rele[$r]=1;
  else $rele[$r]=0;
  $rele_time[$r]=mytime_up();
}

// system uptime with resolution of 10ms
function mytime_up(){
  list($usec,$sec)=explode(" ",microtime());
  return $sec*100+(int)($usec*100);
}

// hours and minute
function mytime_hhmm(){
  $x2[0]=(int)date("H");
  $x2[1]=(int)date("i");
  return $x2;
}

// time string out
function mytime_print($t){
  global $mytime_ref;
  $x1=$mytime_ref+(int)(((int)$t)/100);
  $x2=((int)$t)-100*((int)(((int)$t)/100));
  return date("d-m-Y/H:i:s",$x1).".".sprintf("%02d",$x2);
}

// load configuration
function myconfig(){
  global $nact,$passwd,$config_version,$act,$mydir;
  include $mydir."config.php";
  $nact=sizeof($act);
}
?>
