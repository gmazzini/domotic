<?php
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
multiout(0x43,0x00);
multiout(0x46,0x00);
multiout(0x4a,0x00);
?>
