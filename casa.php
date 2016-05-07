#!/usr/bin/php -q
<?php

// device 1 2 3 4 on 10.0.0.X with X=21 22 23 24 port 10001
// IN 09 10 11 12 17 18 19 20 21 22 23 24
// OUT 01 02 03 04 05 06 07 08 13 14 15 16
// A(01-08) B(09-16) C(17-24)
// A_Read=41H A_Conf=42H A_Write=43H
// B_Read=44H B_Conf=45H B_Write=46H
// C_Read=47H C_Conf=48H C_Write=4AH
// Conf 1=Input 0=Output 
// device 5 on 10.0.0.30 UDP 6723: on 48 11:0, off 48 21:0, on 49 12:0, off 49 22:0
// virtualkey 48-63

$casa_version="35";

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
  global $inkey,$maskin;
  if($inkey[(int)($mykey/12)] & $maskin[$mykey%12])return 0;
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
  $x1=file("/proc/uptime");
  $x2=strtok($x1[0],".");
  $x3=strtok(" ");
  return 100*(int)$x2+(int)$x3;
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
  global $nact,$passwd,$config_version,$act;
  include "/tmp/mnt/sda1/config.php";
  $nact=sizeof($act);
}

// initialization
date_default_timezone_set("Europe/Rome");
$mylog="/tmp/mnt/sda1/mylog";
$fplog=fopen($mylog,"a+");
$mysleep=20000;
$myloop=100;
$rele_loop=0;
$time_loop=0;
$time_loop_lastrefresh=0;
$inject_last=0;
$threelevels_time=500;
$mytime_ref=time()-(int)(mytime_up()/100);
$hhmm_last=0;
$totrele=50;
$commblock=0;
$commlast=mytime_up();
$commdelta_time=200;
include "/tmp/mnt/sda1/password.php";

myconfig();

// Welcome
fprintf($fplog,"Casa:$casa_version, Config:$config_version, #Rules:$nact, Creator GM\n");
fprintf($fplog,"Starting on %s\n",mytime_print(mytime_up()));

// open socket
$sock=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_set_option($sock,SOL_SOCKET,SO_REUSEADDR,1);
socket_set_nonblock($sock);
socket_bind($sock,"10.0.0.2",3333);
socket_listen($sock);

// open communications to 4 devices
for($dev=0;$dev<4;$dev++){
  $ip=sprintf("10.0.0.%d",21+$dev);
  $fp[$dev]=fsockopen($ip,10001);
  if($fp[$dev]==NULL){
    exit(-1);
  }
  sleep(1);
}

// data initialization
for($r=0;$r<$totrele;$r++){
  $rele_old[$r]=0;
  $rele[$r]=0;
  $rete_time[$r]=mytime_up();
}
for($j=0;$j<12;$j++)$maskin[$j]=pow(2,$j);
for($dev=0;$dev<4;$dev++)$oldin[$dev]=65535;
for($i=0;$i<64;$i++){
  $key_last0[$i]=0.0;
  $key_last1[$i]=0.0;
}

// card initialization
multiout(0x42,0x00);
multiout(0x45,0x0f);
multiout(0x48,0xff);
multiout(0x43,0x00);
multiout(0x46,0x00);
multiout(0x4a,0x00);

// actual time
$hhmm=mytime_hhmm();

for(;;){
  
  // key scan
  $inlow=multiin(0x47);
  $inhigh=multiin(0x44);
  for($dev=0;$dev<4;$dev++)$inkey[$dev]=($inlow[$dev] & 0xff) | (($inhigh[$dev] & 0x0f) << 8);

  // key analysis
  $nkey=0;
  for($dev=0;$dev<4;$dev++){
    $diff=$inkey[$dev] ^ $oldin[$dev];
    if($diff){
      for($key=0;$key<12;$key++){
        if($diff & $maskin[$key]){
          $key_number[$nkey]=$key+$dev*12;
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
      for($n=0;$n<$nact;$n++){
        switch($act[$n][0]){
          
          // offtimed
          case 9:
            if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2] || $rele[$act[$n][3]]==0)break;
            if(mytime_up()-$rele_time[$act[$n][3]]>((int)$act[$n][4])*6000)myreleset($act[$n][3],0);
            break;
            
          // offtimed_keysup
          case 10:
            if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
            $nn=$act[$n][3];
            if($rele[$act[$n][4+$nn]]==0)break;
            $actp=0;
            for($i=4;$i<4+$nn;$i++)if(key_checkstatus($act[$n][$i]))$actp=1;
            if($actp)break;
            if(mytime_up()-$rele_time[$act[$n][4+$nn]]>((int)$act[$n][5+$nn])*6000)myreleset($act[$n][4+$nn],0);
            break;
            
          // injectifoff
          case 6:
            if($hhmm[0]!=$act[$n][1] || $hhmm[1]!=$act[$n][2] || $rele[$act[$n][4]]==1)break;
            $puls=(int)$act[$n][3];
            $key_number[$nkey]=$puls;
            $key_time[$nkey]=mytime_up();
            $key_state[$nkey]=1;
            $nkey++;
            $inject_key[$inject_last]=$puls;
            $inject_last++;
            break;
            
          // injectifon
          case 7:
            if($hhmm[0]!=$act[$n][1] || $hhmm[1]!=$act[$n][2] || $rele[$act[$n][4]]==0)break;
            $puls=(int)$act[$n][3];
            $key_number[$nkey]=$puls;
            $key_time[$nkey]=mytime_up();
            $key_state[$nkey]=1;
            $nkey++;
            $inject_key[$inject_last]=$puls;
            $inject_last++;
            break;
        }
      }
    }
  }
  
  // command analysis
  $client=@socket_accept($sock);
  $ext_ip="";
  if($client!==false && $commblock==0 && mytime_up()-$commlast>$commdelta_time){
    $commblock=1;
    $commlast=mytime_up();
    socket_getpeername($client,$ext_ip);
    $mytext="<html><body><pre>";
    $aux=trim(socket_read($client,2048));
    $instart=strpos($aux,"GET")+4;
    $inlen=strpos($aux,"HTTP")-$instart-1;
    $mycmd=substr($aux,$instart,$inlen);
    $in=explode("/",$mycmd);
    if($in[1]!=$passwd)$mytext.="Wrong Password\n";
    else {
      switch($in[2]){
        
        case "status":
          $mytext.="Casa_v:$casa_version Rule_v:$config_version #rules:$nact\n";
          $mytext.="hh:".sprintf("%02d",$hhmm[0])." mm:".sprintf("%02d",$hhmm[1]);
          $mytext.=" time_loop:$time_loop lastrefresh:".mytime_print($time_loop_lastrefresh)."\n";
          $count=0;
          for($r=0;$r<$totrele;$r++){
            $mytext.=sprintf("%02d:%d ",$r,$rele[$r]);
            if($rele[$r])$count++;
            if($r%6==5)$mytext.="\n";
          }
          $mytext.="\nTotal On: $count\n";
          break;
          
        case "keystatus":
          $count=0;
          for($nn=0;$nn<48;$nn++){
            $mm=key_checkstatus($nn);
            $mytext.=sprintf("%02d:%d ",$nn,$mm);
            if($mm)$count++;
            if($nn%6==5)$mytext.="\n";
          }
          $mytext.="Total ups: $count\n";
          break;
          
        case "inject":
          $puls=(int)$in[3];
          $key_number[$nkey]=$puls;
          $key_time[$nkey]=mytime_up();
          $key_state[$nkey]=1;
          $nkey++;
          $inject_key[$inject_last]=$puls;
          $inject_last++;
          $mytext.="Inject key $puls\n";
          break;
          
        case "set":
          myreleset((int)$in[3],1);
          $mytext.="Set rele $in[3]\n";
          break;
          
        case "reset":
          myreleset((int)$in[3],0);
          $mytext.="Reset rele $in[3]\n";
          break;
          
        case "delete":
          $act[(int)$in[3]][0]=-1;
          $mytext.="Delete rule $in[3]\n";
          break;
          
        case "switchoff":
          for($r=0;$r<$totrele;$r++)myreleset($r,0);
          $mytext.="Reset rele all\n";
          break;
          
        case "rule":
          for($n=0;$n<$nact;$n++){
            switch($act[$n][0]){
              
              case -1:
                $mytext.=sprintf("Rule: %02d Type: delete\n\n",$n);
                break;
              
              case 0:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $qq=$act[$n][5+$mm+$nn];
                $mytext.=sprintf("Rule: %02d Type: 3level Name: %s\n",$n,$act[$n][6+$nn+$mm+$qq]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n";
                $mytext.=sprintf("Rele_A #:%02d",$mm);
                for($cm=0;$cm<$mm;$cm++)$mytext.=sprintf(" %02d:%02d",$cm,$act[$n][$cm+5+$nn]);
                $mytext.="\n";
                $mytext.=sprintf("Rele_B #:%02d",$qq);
                for($cq=0;$cq<$qq;$cq++)$mytext.=sprintf(" %02d:%02d",$cq,$act[$n][$cq+6+$nn+$mm]);
                $mytext.="\n\n";
                break;
               
              case 1:
              case 2:
              case 3:
              case 8:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $mytext.=sprintf("Rule: %02d Type: ",$n);
                if($act[$n][0]==1)$mytext.=sprintf("onoff ");
                else if($act[$n][0]==2)$mytext.=sprintf("on ");
                else if($act[$n][0]==3)$mytext.=sprintf("off ");
                else if($act[$n][0]==8)$mytext.=sprintf("push ");
                $mytext.=sprintf("Name: %s\n",$act[$n][5+$nn+$mm]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n";
                $mytext.=sprintf("Rele #:%02d",$mm);
                for($cm=0;$cm<$mm;$cm++)$mytext.=sprintf(" %02d:%02d",$cm,$act[$n][$cm+5+$nn]);
                $mytext.="\n\n";
                break;
                
              case 4:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $mytext.=sprintf("Rule: %02d Type: alloff Name: %s\n",$n,$act[$n][5+$nn+$mm]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n";
                $mytext.=sprintf("Rele #:%02d",$mm);
                for($cm=0;$cm<$mm;$cm++)$mytext.=sprintf(" %02d:%02d",$cm,$act[$n][$cm+5+$nn]);
                $mytext.="\n\n";
                break;
                
              case 6:
                $mytext.=sprintf("Rule: %02d Type: injectifoff Name: %s\n",$n,$act[$n][5]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key: %02d Releifoff %02d\n\n",$act[$n][3],$act[$n][4]);
                break;
                
              case 7:
                $mytext.=sprintf("Rule: %02d Type: injectifon Name: %s\n",$n,$act[$n][5]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key: %02d Releifon %02d\n\n",$act[$n][3],$act[$n][4]);
                break;
                
              case 9:
                $mytext.=sprintf("Rule: %02d Type: offtimed Name: %s\n",$n,$act[$n][5]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Releoff: %02d After(min): %02d\n\n",$act[$n][3],$act[$n][4]);
                break;
                
              case 10:
                $nn=$act[$n][3];
                $mytext.=sprintf("Rule: %02d Type: offtimed_keysup Name: %s\n",$n,$act[$n][6+$nn]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Releoff: %02d After(min): %02d\n",$act[$n][4+$nn],$act[$n][5+$nn]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n\n";
                break;
            }
          }
          break;
          
        case "key":
          for($n=0;$n<64;$n++)$ww[$n]=0;
          for($n=0;$n<$nact;$n++){
            $myaa=$act[$n][0];
            if($myaa==-1||$myaa==6||$myaa==7||$myaa==9)continue;
            $nn=$act[$n][3];
            for($qq=0;$qq<$nn;$qq++){
              $kk=$act[$n][4+$qq];
              $www[$kk][$ww[$kk]]=$n;
              $ww[$kk]++;
            }
          }
          for($n=0;$n<64;$n++){
            $mytext.=sprintf("Key #:%02d",$n);
            $nn=$ww[$n];
            for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$www[$n][$cn]);
            $mytext.="\n";
          }
          break;
          
        case "log":
          $fpout=popen("tail -n $in[3] $mylog", 'r');
          while(!feof($fpout))$mytext.=fgets($fpout);
          pclose($fpout);
          break;
          
        case "reload":
          myconfig();
          $mytext.="Load Rule_v:$config_version #rules:$nact\n";
          break;
          
        case "help":
          $mytext.="/passwd/status show the status\n";
          $mytext.="/passwd/keystatus show the keys status\n";
          $mytext.="/passwd/inject/n inject key n\n";
          $mytext.="/passwd/set/n set rele n\n";
          $mytext.="/passwd/reset/n reset rele n\n";
          $mytext.="/passwd/switchoff reset all the rele\n";
          $mytext.="/passwd/rule display actual rules\n";
          $mytext.="/passwd/key display key mapping on rules\n";
          $mytext.="/passwd/log/n log last n lines\n";
          $mytext.="/passwd/reload reload configuration\n";
          $mytext.="/passwd/delete/n delete rule n\n";
          $mytext.="/passwd/help this help\n";
          break;
          
        default:
          $mytext.="Wrong command\n";
          break;
      }
    }
    $mytext.="</pre></body></html>";
    $mytextlen=strlen($mytext);
    socket_write($client,"HTTP/1.1 200 OK\r\n");
    socket_write($client,"Cache-Control: no-cache\r\n");
    socket_write($client,"Content-Type: text/html\r\n");
    socket_write($client,"Content-Length: $mytextlen\r\n");
    socket_write($client,"Connection: Close\r\n");
    socket_write($client,"\r\n");
    socket_write($client,$mytext);
    socket_close($client);
    $commblock=0;
  }
  
  if($nkey){
    for($i=0;$i<$nkey;$i++)fprintf($fplog,"key: %02d %01d %s %s\n",$key_number[$i],$key_state[$i],mytime_print($key_time[$i]),$ext_ip);
    // action analysis
    for($n=0;$n<$nact;$n++){
      switch($act[$n][0]){
        
        // 3level
        case 0:
          if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
          $nn=$act[$n][3];
          $mm=$act[$n][4+$nn];
          $qq=$act[$n][5+$mm+$nn];
          for($i=4;$i<4+$nn;$i++){
            for($j=0;$j<$nkey;$j++){
              if($act[$n][$i]==$key_number[$j] && !$key_state[$j]){
                $aux=$key_time[$j]-$key_last0[$key_number[$j]];
                $actm=0;
                for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)if($rele[$act[$n][$cm]])$actm++;
                $actq=0;
                for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)if($rele[$act[$n][$cq]])$actq++;
                if($aux>$threelevels_time){
                  if($actm || $actq){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
                  }
                  else {
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
                  }
                }
                else {
                  if($actm && $actq){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
                  }
                  else if($actm && !$actq){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
                  }
                  else if(!$actm && $actq){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
                  }
                  else if(!$actm && !$actq){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
                  }
                }
              }
            }
          }
          break;
          
        // onoff
        case 1:
          if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
          $nn=$act[$n][3];
          $mm=$act[$n][4+$nn];
          for($i=4;$i<4+$nn;$i++){
            for($j=0;$j<$nkey;$j++){
              if($act[$n][$i]==$key_number[$j] && !$key_state[$j]){
                $actk=0;
                for($k=5+$nn;$k<5+$nn+$mm;$k++)$actk+=$rele[$act[$n][$k]];
                if($actk!=$mm)for($k=5+$nn;$k<5+$nn+$mm;$k++)myreleset($act[$n][$k],1);
                else for($k=5+$nn;$k<5+$nn+$mm;$k++)myreleset($act[$n][$k],0);
              }
            }
          }
          break;
          
        // on
        case 2:
          if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
          $nn=$act[$n][3];
          $mm=$act[$n][4+$nn];
          for($i=4;$i<4+$nn;$i++){
            for($j=0;$j<$nkey;$j++){
              if($act[$n][$i]==$key_number[$j] && !$key_state[$j]){
                for($k=5+$nn;$k<5+$nn+$mm;$k++)myreleset($act[$n][$k],1);
              }
            }
          }
          break;
          
        // off
        case 3:
          if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
          $nn=$act[$n][3];
          $mm=$act[$n][4+$nn];
          for($i=4;$i<4+$nn;$i++){
            for($j=0;$j<$nkey;$j++){
              if($act[$n][$i]==$key_number[$j] && !$key_state[$j]){
                for($k=5+$nn;$k<5+$nn+$mm;$k++)myreleset($act[$n][$k],0);
              }
            }
          }
          break;
          
        // alloff
        case 4:
          if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
          $nn=$act[$n][3];
          $mm=$act[$n][4+$nn];
          for($i=4;$i<4+$nn;$i++){
            for($j=0;$j<$nkey;$j++){
              if($act[$n][$i]==$key_number[$j] && !$key_state[$j]){
                for($r=0;$r<$totrele;$r++){
                  for($k=5+$nn;$k<5+$nn+$mm;$k++)if($r==$act[$n][$k])continue 2;
                  myreleset($r,0);
                }
              }
            }
          }
          break;
          
        // push
        case 8:
          if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
          $nn=$act[$n][3];
          $mm=$act[$n][4+$nn];
          $actk=0;
          $actp=0;
          for($i=4;$i<4+$nn;$i++){
            for($j=0;$j<$nkey;$j++){
              if($act[$n][$i]==$key_number[$j]){
                $actk++;
                if($key_state[$j])$actp=1;
              }
            }
          }
          if($actk>0){
            for($k=5+$nn;$k<5+$nn+$mm;$k++)myreleset($act[$n][$k],$actp);
          }
          break;
      }
    }
  }
  
  // rele out on device 1 2 3 4
  for($dev=0;$dev<4;$dev++){
    $q[0]=$q[1]=0;
    $v[0]=$v[1]=0;
    for($i=0;$i<12;$i++){
      if($i<8)$gr=0;
      else $gr=1;
      $j=$i+$dev*12;
      if($rele[$j]!=$rele_old[$j]){
        fprintf($fplog,"out: %02d %01d %s\n",$j,$rele[$j],mytime_print($rele_time[$j]));
        $q[$gr]++;
      }
      if($rele[$j])$v[$gr]+=$maskin[$i-4*$gr];
      $rele_old[$j]=$rele[$j];
    }
    if($q[0]){
      fwrite($fp[$dev],chr(0x43).chr($v[0]),2);
      usleep($mysleep);
    }
    if($q[1]){
      fwrite($fp[$dev],chr(0x46).chr($v[1]),2);
      usleep($mysleep);
    }
  }
  // rele out on device 5
  for($j=48;$j<50;$j++){
    if($rele[$j]!=$rele_old[$j]){
      $devstr=chr(50-$rele[$j]).chr($j+1).":0";
      $mysock=socket_create(AF_INET,SOCK_DGRAM,SOL_UDP);
      socket_sendto($mysock,$devstr,strlen($devstr),0,"10.0.0.30",6723);
      socket_close($mysock);
      usleep($mysleep);
      fprintf($fplog,"out: %02d %01d %s\n",$j,$rele[$j],mytime_print($rele_time[$j]));
      $rele_old[$j]=$rele[$j];
    }
  }
  
  // update last pression
  for($j=0;$j<$nkey;$j++){
    if($key_state[$j]==0)$key_last0[$key_number[$j]]=$key_time[$j];
    else $key_last1[$key_number[$j]]=$key_time[$j];
  }
}

?>
