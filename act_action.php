<?php
for($i=0;$i<$nkey;$i++)fprintf($fplog,"key: %02d %01d %s\n",$key_number[$i],$key_state[$i],mytime_print($key_time[$i]));
//actionanalysis
for($n=0;$n<$nact;$n++){
  switch($act[$n][0]){
    
    // 3level
    case 0:
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
    $nn=$act[$n][3];
    $mm=$act[$n][4+$nn];
    $qq=$act[$n][5+$mm+$nn];
    for($i=4;$i<4+$nn;$i++){
      for($j=0;$j<$nkey;$j++){
        if($act[$n][$i]==$key_number[$j]&&!$key_state[$j]){
          $aux=$key_time[$j]-$key_last0[$key_number[$j]];
          $actm=0;
          for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)if($rele[$act[$n][$cm]])$actm++;
          $actq=0;
          for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)if($rele[$act[$n][$cq]])$actq++;
          if($aux>$threelevels_time){
            if($actm||$actq){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
            }
            else{
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
            }
          }
          else{
            if($actm&&$actq){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
            }
            elseif($actm&&!$actq){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
            }
            elseif(!$actm&&$actq){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
            }
            elseif(!$actm&&!$actq){
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
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
    $nn=$act[$n][3];
    $mm=$act[$n][4+$nn];
    $qq=$act[$n][5+$mm+$nn];
    $vv=$act[$n][6+$mm+$nn+$qq];
    for($i=4;$i<4+$nn;$i++){
      for($j=0;$j<$nkey;$j++){
        if($act[$n][$i]==$key_number[$j]&&!$key_state[$j]){
          $aux=$key_time[$j]-$key_last0[$key_number[$j]];
          $actm=0;
          for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)if($rele[$act[$n][$cm]])$actm++;
          $actq=0;
          for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)if($rele[$act[$n][$cq]])$actq++;
          $actv=0;
          for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)if($rele[$act[$n][$cv]])$actv++;
          if($aux>$threelevels_time){
            if($actm||$actq||$actv){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
              for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
            }
            else{
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
              for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],1);
            }
          }
          else{
            if($actm&&$actq&&$actv){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],1);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
              for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
            }
            elseif($actm&&!$actq&&!$actv){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],1);
              for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
            }
            elseif(!$actm&&$actq&&!$actv){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
              for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],1);
            }
            elseif(!$actm&&!$actq&&$actv){
              for($cm=5+$nn;$cm<5+$nn+$mm;$cm++)myreleset($act[$n][$cm],0);
              for($cq=6+$nn+$mm;$cq<6+$nn+$mm+$qq;$cq++)myreleset($act[$n][$cq],0);
              for($cv=7+$nn+$mm+$qq;$cv<7+$nn+$mm+$qq+$vv;$cv++)myreleset($act[$n][$cv],0);
            }
            elseif(!$actm&&!$actq&&!$actv){
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
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
    $nn=$act[$n][3];
    $mm=$act[$n][4+$nn];
    for($i=4;$i<4+$nn;$i++){
      for($j=0;$j<$nkey;$j++){
        if($act[$n][$i]==$key_number[$j]&&!$key_state[$j]){
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
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
    $nn=$act[$n][3];
    $mm=$act[$n][4+$nn];
    for($i=4;$i<4+$nn;$i++){
      for($j=0;$j<$nkey;$j++){
        if($act[$n][$i]==$key_number[$j]&&!$key_state[$j]){
          for($k=5+$nn;$k<5+$nn+$mm;$k++)myreleset($act[$n][$k],1);
        }
      }
    }
    break;
    
    // off
    case 3:
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
    $nn=$act[$n][3];
    $mm=$act[$n][4+$nn];
    for($i=4;$i<4+$nn;$i++){
      for($j=0;$j<$nkey;$j++){
        if($act[$n][$i]==$key_number[$j]&&!$key_state[$j]){
          for($k=5+$nn;$k<5+$nn+$mm;$k++)myreleset($act[$n][$k],0);
        }
      }
    }
    break;
    
    // alloff
    case 4:
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
    $nn=$act[$n][3];
    $mm=$act[$n][4+$nn];
    for($i=4;$i<4+$nn;$i++){
      for($j=0;$j<$nkey;$j++){
        if($act[$n][$i]==$key_number[$j]&&!$key_state[$j]){
          for($r=0;$r<$totrele;$r++){
            for($k=5+$nn;$k<5+$nn+$mm;$k++)if($r==$act[$n][$k])continue2;
            myreleset($r,0);
          }
        }
      }
    }
    break;
    
    // push
    case 8:
    if($hhmm[0]<$act[$n][1]||$hhmm[0]>$act[$n][2])break;
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
?>
