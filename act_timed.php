<?php
for($n=0;$n<$nact;$n++){
  switch($act[$n][0]){
    
    // offtimed
    case 9:
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2]||$rele[$act[$n][3]]==0)break;
    if(mytime_up()-$rele_time[$act[$n][3]]>((int)$act[$n][4])*6000)myreleset($act[$n][3],0);
    break;
    
    // offtimed_keysup
    case 10:
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
    $nn=$act[$n][3];
    if($rele[$act[$n][4+$nn]]==0)break;
    $actp=0;
    for($i=4;$i<4+$nn;$i++)if(key_checkstatus($act[$n][$i]))$actp=1;
    if($actp)break;
    if(mytime_up()-$rele_time[$act[$n][4+$nn]]>((int)$act[$n][5+$nn])*6000)myreleset($act[$n][4+$nn],0);
    break;
    
    // injectifoff
    case 6:
    if($hhmm[0]!=$act[$n][1]||$hhmm[1]!=$act[$n][2]||$rele[$act[$n][4]]==1)break;
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
    if($hhmm[0]!=$act[$n][1]||$hhmm[1]!=$act[$n][2]||$rele[$act[$n][4]]==0)break;
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
?>
