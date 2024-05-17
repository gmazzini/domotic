<?php
$casa_version="67";
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
$mytime_ref=time()-(int)(mytime_up()/100);
$hhmm_last=0;
$totrele=64;
$totkeydev=6;
$keyoff=0;
$fileshared="q3.php";
include $mydir."password.php";
$keybasefordev=[0,12,24,36,48,56,64];
unlink("$fileshared");
touch("$fileshared");

myconfig();

// Welcome
fprintf($fplog,"Casa:$casa_version, Config:$config_version, #Rules:$nact, Creator GM\n");
fprintf($fplog,"Starting on %s\n",mytime_print(mytime_up()));

// open receive socket
$serv=stream_socket_server("tcp://10.0.0.4:3333");

// open communications to 4 devices
for($dev=0;$dev<4;$dev++){
  $ip=sprintf("10.0.0.%d",21+$dev);
  $fp[$dev]=fsockopen($ip,10001);
  if($fp[$dev]==NULL){
    exit(-1);
  }
  sleep(1);
}

// open communications with BEM106 6 and 8
$myso9=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_connect($myso9,"10.0.0.33",5000);
$myso10=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_connect($myso10,"10.0.0.35",5000);

// data initialization
for($r=0;$r<$totrele;$r++){
  $rele_old[$r]=0;
  $rele[$r]=0;
  $rete_time[$r]=mytime_up();
}
for($j=0;$j<12;$j++)$maskin[$j]=pow(2,$j);
for($dev=0;$dev<$totkeydev;$dev++)$oldin[$dev]=65535;
for($i=0;$i<72;$i++){
  $key_last0[$i]=0.0;
  $key_last1[$i]=0.0;
  for($j=$totkeydev;$j>0;$j--)if($i>=$keybasefordev[$j])break;
  $keyassigneddev[$i]=$j;
}

// card initialization
multiout(0x42,0x00);
multiout(0x45,0x0f);
multiout(0x48,0xff);
multiout(0x43,0x00);
multiout(0x46,0x00);
multiout(0x4a,0x00);
$myso1=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_connect($myso1,"10.0.0.32",5000);
$myso2=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_connect($myso2,"10.0.0.34",5000);
for($j=48;$j<56;$j++){
  $mymsg1="k0".chr($j+1)."=0;";
  socket_write($myso1,$mymsg1,strlen($mymsg1));
  socket_write($myso2,$mymsg1,strlen($mymsg1));
}
socket_close($myso1);
socket_close($myso2);

// actual time
$hhmm=mytime_hhmm();

for(;;){
 
  $nkey=0;
  
  // key scan
  $inlow=multiin(0x47);
  $inhigh=multiin(0x44);
  for($dev=0;$dev<4;$dev++)$inkey[$dev]=($inlow[$dev] & 0xff) | (($inhigh[$dev] & 0x0f) << 8);
  $mymsg1="getpara[189]=1;getpara[190]=1;getpara[191]=1;getpara[192]=1;getpara[193]=1;getpara[194]=1;getpara[195]=1;getpara[196]=1;";
  $mymsg2="getpara[196]=1;getpara[195]=1;getpara[194]=1;getpara[193]=1;getpara[192]=1;getpara[191]=1;getpara[190]=1;getpara[189]=1;";
  socket_write($myso9,$mymsg1,strlen($mymsg1));
  socket_write($myso10,$mymsg2,strlen($mymsg2));
  $aux=socket_read($myso9,1000);
  $zs=0;for($ii=0;$ii<8;$ii++)$zs=($zs << 1)+1-((int)substr($aux,15+17*$ii,1));
  $inkey[4]=$zs;
  $aux=socket_read($myso10,1000);
  $zs=0;for($ii=0;$ii<8;$ii++)$zs=($zs << 1)+1-((int)substr($aux,15+17*$ii,1));
  $inkey[5]=$zs;
  
  if($keyoff==1)$inkey=$oldin;

  // key analysis
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
  $conn=@stream_socket_accept($serv,0);
  if($conn!==false && pcntl_fork()==0){

    include "act_command.php";
    
    $mytext.="</pre></body></html>";
    $myout="HTTP/1.1 200 OK\r\n";
    $myout.="Cache-Control: no-cache\r\n";
    $myout.="Content-Type: text/html\r\n";
    $myout.="Content-Length: ".strlen($mytext)."\r\n";
    $myout.="Connection: Close\r\n";
    $myout.="\r\n$mytext";
    fwrite($conn,$myout);
    fclose($conn);
    exit(0);
  }
  
  include "$fileshared";
  if($nkey){
    for($i=0;$i<$nkey;$i++)fprintf($fplog,"key: %02d %01d %s\n",$key_number[$i],$key_state[$i],mytime_print($key_time[$i]));
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
          
        // 3light
        case 11:
          if($hhmm[0]<$act[$n][1] || $hhmm[0]>$act[$n][2])break;
          $nn=$act[$n][3];
          $mm=$act[$n][4+$nn];
          $qq=$act[$n][5+$mm+$nn];
          $vv=$act[$n][6+$mm+$nn+$qq];
          for($i=4;$i<4+$nn;$i++){
            for($j=0;$j<$nkey;$j++){
              if($act[$n][$i]==$key_number[$j] && !$key_state[$j]){
                $aux=$key_time[$j]-$key_last0[$key_number[$j]];
                $actm=0;
                for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)if($rele[$act[$n][$cm]])$actm++;
                $actq=0;
                for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)if($rele[$act[$n][$cq]])$actq++;
                $actv=0;
                for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)if($rele[$act[$n][$cv]])$actv++;
                if($aux>$threelevels_time){
                  if($actm || $actq || $actv){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
                    for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
                  }
                  else {
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
                    for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],1);
                  }
                }
                else {
                  if($actm && $actq && $actv){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
                    for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
                  }
                  else if($actm && !$actq && !$actv){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
                    for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
                  }
                  else if(!$actm && $actq && !$actv){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
                    for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],1);
                  }
                  else if(!$actm && !$actq && $actv){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
                    for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
                  }
                  else if(!$actm && !$actq && !$actv){
                    for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
                    for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
                    for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],1);
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
  $mymsg1="";
  $myqr=0;
  for($j=48;$j<56;$j++){
    if($rele[$j]!=$rele_old[$j]){
      $mymsg1.="k0".chr($j+1)."=".chr(48+$rele[$j]).";";
      fprintf($fplog,"out: %02d %01d %s\n",$j,$rele[$j],mytime_print($rele_time[$j]));
      $rele_old[$j]=$rele[$j];
      $myqr++;
    }
  }
  if($myqr){
    $myso1=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
    socket_connect($myso1,"10.0.0.32",5000);
    socket_write($myso1,$mymsg1,strlen($mymsg1));
    socket_close($myso1);
  }

  // rele out on device 7
  $mymsg1="";
  $myqr=0;
  for($j=56;$j<64;$j++){
    if($rele[$j]!=$rele_old[$j]){
      $mymsg1.="k0".chr($j-7)."=".chr(48+$rele[$j]).";";
      fprintf($fplog,"out: %02d %01d %s\n",$j,$rele[$j],mytime_print($rele_time[$j]));
      $rele_old[$j]=$rele[$j];
      $myqr++;
    }
  }
  if($myqr){
    $myso1=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
    socket_connect($myso1,"10.0.0.34",5000);
    socket_write($myso1,$mymsg1,strlen($mymsg1));
    socket_close($myso1);
  }
    
  // update last pression
  for($j=0;$j<$nkey;$j++){
    if($key_state[$j]==0)$key_last0[$key_number[$j]]=$key_time[$j];
    else $key_last1[$key_number[$j]]=$key_time[$j];
  }
}

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
