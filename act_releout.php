<?php
// rele out on device 1234
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
    fwrite($mysock[$dev],chr(0x43).chr($v[0]),2);
    usleep($mysleep);
  }
  if($q[1]){
    fwrite($mysock[$dev],chr(0x46).chr($v[1]),2);
    usleep($mysleep);
  }
}

// rele out on device 6
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
  $mysock[6]=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
  socket_connect($mysock[6],"10.0.0.32",5000);
  socket_write($mysock[6],$mymsg1,strlen($mymsg1));
  socket_close($mysock[6]);
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
  $mysock[7]=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
  socket_connect($mysock[7],"10.0.0.34",5000);
  socket_write($mysock[7],$mymsg1,strlen($mymsg1));
  socket_close($mysock[7]);
}
?>
