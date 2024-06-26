<?php
$casa_version="68";
$mydir="/home/gmazzini/casa/";
date_default_timezone_set("Europe/Rome");
$mylog=$mydir."mylog";
$fplog=fopen($mylog,"a+");
$mysleep=20000;
$myloop=100;
$rele_loop=0;
$time_loop=0;
$time_loop_lastrefresh=0;
$inject_last=0;
$threelevels_time=500;
$hhmm_last=0;
$totrele=64;
$totkeydev=6;
$keyoff=0;
$fileshared="q3.php";
include $mydir."password.php";
$keybasefordev=[0,12,24,36,48,56,64];
unlink("$fileshared");
touch("$fileshared");
include "act_function.php";
$mytime_ref=time()-(int)(mytime_up()/100);
myconfig();

// Welcome
fprintf($fplog,"Casa:$casa_version, Config:$config_version, #Rules:$nact, Creator GM\n");
fprintf($fplog,"Starting on %s\n",mytime_print(mytime_up()));

// open communications
$serv=stream_socket_server("tcp://10.0.0.8:3333");
for($dev=0;$dev<4;$dev++)$mysock[$dev]=fsockopen(sprintf("10.0.0.%d",21+$dev),10001);
$mysock[4]=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_connect($mysock[4],"10.0.0.33",5000);
$mysock[5]=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_connect($mysock[5],"10.0.0.35",5000);
include "act_initialize.php";

$hhmm=mytime_hhmm();
for(;;){
  $nkey=0;
  // key scan
  for($dev=0;$dev<4;$dev++)fwrite($mysock[$dev],chr(0x047).chr(0x00),2);
  usleep($mysleep);
  for($dev=0;$dev<4;$dev++)$inlow[$dev]=ord(fread($mysock[$dev],1));
  for($dev=0;$dev<4;$dev++)fwrite($mysock[$dev],chr(0x044).chr(0x00),2);
  usleep($mysleep);
  for($dev=0;$dev<4;$dev++)$inhigh[$dev]=ord(fread($mysock[$dev],1));
  for($dev=0;$dev<4;$dev++)$inkey[$dev]=($inlow[$dev] & 0xff) | (($inhigh[$dev] & 0x0f) << 8);
  $mymsg[4]="getpara[189]=1;getpara[190]=1;getpara[191]=1;getpara[192]=1;getpara[193]=1;getpara[194]=1;getpara[195]=1;getpara[196]=1;";
  $mymsg[5]="getpara[196]=1;getpara[195]=1;getpara[194]=1;getpara[193]=1;getpara[192]=1;getpara[191]=1;getpara[190]=1;getpara[189]=1;";
  for($dev=4;$dev<6;$dev++){
    socket_write($mysock[$dev],$mymsg[$dev],strlen($mymsg[$dev]));
    $aux=socket_read($mysock[$dev],1000);
    $zs=0;
    for($ii=0;$ii<8;$ii++)$zs=($zs << 1)+1-((int)substr($aux,15+17*$ii,1));
    $inkey[$dev]=$zs;
  }

  // key analysis
  if($keyoff==1)$inkey=$oldin;
  for($dev=0;$dev<$totkeydev;$dev++){
    $diff=$inkey[$dev] ^ $oldin[$dev];
    if($diff){
      for($key=$keybasefordev[$dev+1]-$keybasefordev[$dev]-1;$key>=0;$key--){
        if($diff & $maskin[$key]){
          $key_number[$nkey]=$key+$keybasefordev[$dev];
          $key_time[$nkey]=mytime_up();
          if($inkey[$dev] & $maskin[$key])$key_state[$nkey]=0;
          else $key_state[$nkey]=1;
          $nkey++;
        }
      }
      $oldin[$dev]=$inkey[$dev];
    }
  }
  
  // post inject key release
  if($inject_last){
  	for($i=0;$i<$inject_last;$i++){
      $key_number[$nkey]=$inject_key[$i];
      $key_time[$nkey]=mytime_up();
      $key_state[$nkey]=0;
      $nkey++;
    }
    $inject_last=0;
  }

  // time loop delay
  $time_loop++;
  if($time_loop>$myloop){
    $time_loop=0;
    $mytime_ref=time()-(int)(mytime_up()/100);
    $time_loop_lastrefresh=mytime_up();
    $hhmm=mytime_hhmm();
    if($hhmm!=$hhmm_last){
      $hhmm_last=$hhmm;
      include "act_timed.php";
    }
  }
  
  // command analysis
  $conn=@stream_socket_accept($serv,0);
  if($conn!==false && pcntl_fork()==0){
    $aux=trim(fread($conn,2048));
    $mytext="<html><body style='background-color:#F9F4B7'><pre>";
    include "act_command.php";
    $mytext.="</pre></body></html>";
    $myout="HTTP/1.1 200 OK\r\n";
    $myout.="Cache-Control: no-cache\r\n";
    $myout.="Content-Type: text/html\r\n";
    $myout.="Content-Length: ".strlen($mytext)."\r\n";
    $myout.="Connection: Close\r\n";
    $myout.="\r\n$mytext";
    fwrite($conn,$myout,strlen($myout));
    fclose($conn);
    exit(0);
  }
  
  include "$fileshared";
  if($nkey)include "act_action.php";
  include "act_releout.php";
  
  // update last pression
  for($j=0;$j<$nkey;$j++){
    if($key_state[$j]==0)$key_last0[$key_number[$j]]=$key_time[$j];
    else $key_last1[$key_number[$j]]=$key_time[$j];
  }
}
?>
