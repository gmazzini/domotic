<?php

$aux=trim(fread($conn,2048));

$mytext="<html><body style='background-color:#F9F4B7'><pre>";
$instart=strpos($aux,"GET")+4;
$inlen=strpos($aux,"HTTP")-$instart-1;
$mycmd=substr($aux,$instart,$inlen);
$in=explode("/",$mycmd);
if(!isset($in[1]) || $in[1]!=$passwd)$mytext.="Wrong Parameters\n".$aux."\n";
else {
  $mytext.="<i>Casa_v:$casa_version Rule_v:$config_version #rules:$nact Keyoff:$keyoff</i>\n";
  $mytext.="<i>hh:".sprintf("%02d",$hhmm[0])." mm:".sprintf("%02d",$hhmm[1]);
  $mytext.=" time_loop:$time_loop lastrefresh:".mytime_print($time_loop_lastrefresh)."</i>\n";
  $mysharedtext="";
  switch($in[2]){
    
    case "status":
    $mytext.="<b>Relay Status</b>\n";
    $count=0;
    for($r=0;$r<$totrele;$r++){
      if($rele[$r]){
        $mytext.=sprintf("%02d:<b style='color:red;'>1</b> ",$r);
        $count++;
      }
      else $mytext.=sprintf("%02d:0 ",$r);
      if($r%8==7)$mytext.="\n";
    }
    $mytext.="Total On: <b>$count</b>\n";
    break;
          
    case "keystatus":
    $mytext.="<b>Key Status</b>\n";
    $count=0;
    for($nn=0;$nn<64;$nn++){
      if(key_checkstatus($nn)){
        $mytext.=sprintf("%02d:<b style='color:red;'>1</b> ",$nn);
        $count++;
      }
      else $mytext.=sprintf("%02d:0 ",$nn);
      if($nn%8==7)$mytext.="\n";
    }
    $mytext.="Total Pressed: <b>$count</b>\n";
    break;
    
    case "keyoff":
    $mysharedtext.="\$keyoff=1;\n";
    $mytext.="Key set to off\n";
    break;
    
    case "keyon":
    $mysharedtext.="\$keyoff=0;\n";
    $mytext.="Keyset to on\n";
    break;
          
    case "inject":
    $puls=(int)$in[3];
    $mysharedtext.="\$key_number[".$nkey."]=".$puls.";\n";
    $mysharedtext.="\$key_time[".$nkey."]=".mytime_up().";\n";
    $mysharedtext.="\$key_state[".$nkey."]=1;\n";
    $mysharedtext.="\$nkey++;\n";
    $mysharedtext.="\$inject_key[".$inject_last."]=".$puls.";\n";
    $mysharedtext.="\$inject_last++;\n";
    $mytext.="Inject key <b>$puls</b>\n";
    break;
          
    case "set":
    $mysharedtext.="myreleset(".(int)$in[3].",1);\n";
    $mytext.="Set rele <b>$in[3]</b>\n";
    break;
          
    case "reset":
    $mysharedtext.="myreleset(".(int)$in[3].",0);\n";
    $mytext.="Reset rele <b>$in[3]</b>\n";
          break;
          
        case "delete":
          $mysharedtext.="\$act[".(int)$in[3]."][0]=-1;\n";
          $mytext.="Delete rule <b>$in[3]</b>\n";
          break;
          
        case "switchoff":
          for($r=0;$r<$totrele;$r++)$mysharedtext.="myreleset(".$r.",0);\n";
          $mytext.="Reset rele all\n";
          break;
          
        case "rule":
          $mytext.=sprintf("<b>Rule list</b>\n");
          for($n=0;$n<$nact;$n++){
            switch($act[$n][0]){
              
              case -1:
                $mytext.=sprintf("Rule: %02d Type: <i>deleted</i>\n\n",$n);
                break;
              
              case 0:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $qq=$act[$n][5+$mm+$nn];
                $mytext.=sprintf("Rule: %02d Type: <i>3level</i> Name: <b>%s</b>\n",$n,$act[$n][6+$nn+$mm+$qq]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n";
                $mytext.=sprintf("Relay_A #:%02d",$mm);
                for($cm=0;$cm<$mm;$cm++){
                  if($rele[$act[$n][$cm+5+$nn]])$mytext.=sprintf(" %02d:<b style='color:red;'>%02d</b>",$cm,$act[$n][$cm+5+$nn]);
                  else $mytext.=sprintf(" %02d:%02d",$cm,$act[$n][$cm+5+$nn]);
                }
                $mytext.="\n";
                $mytext.=sprintf("Relay_B #:%02d",$qq);
                for($cq=0;$cq<$qq;$cq++){
                  if($rele[$act[$n][$cq+6+$nn+$mm]])$mytext.=sprintf(" %02d:<b style='color:red;'>%02d</b>",$cq,$act[$n][$cq+6+$nn+$mm]);
                  else $mytext.=sprintf(" %02d:%02d",$cq,$act[$n][$cq+6+$nn+$mm]);
                }
                $mytext.="\n\n";
                break;
                
              case 11:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $qq=$act[$n][5+$mm+$nn];
                $vv=$act[$n][6+$mm+$nn+$qq];
                $mytext.=sprintf("Rule: %02d Type: <i>3lights</i> Name: <b>%s</b>\n",$n,$act[$n][7+$nn+$mm+$qq+$vv]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n";
                $mytext.=sprintf("Relay_A #:%02d",$mm);
                for($cm=0;$cm<$mm;$cm++){
                  if($rele[$act[$n][$cm+5+$nn]])$mytext.=sprintf(" %02d:<b style='color:red;'>%02d</b>",$cm,$act[$n][$cm+5+$nn]);
                  else $mytext.=sprintf(" %02d:%02d",$cm,$act[$n][$cm+5+$nn]);
                }
                $mytext.="\n";
                $mytext.=sprintf("Relay_B #:%02d",$qq);
                for($cq=0;$cq<$qq;$cq++){
                  if($rele[$act[$n][$cq+6+$nn+$mm]])$mytext.=sprintf(" %02d:<b style='color:red;'>%02d</b>",$cq,$act[$n][$cq+6+$nn+$mm]);
                  else $mytext.=sprintf(" %02d:%02d",$cq,$act[$n][$cq+6+$nn+$mm]);
                }
                $mytext.="\n";
                $mytext.=sprintf("Relay_C #:%02d",$vv);
                for($cv=0;$cv<$vv;$cv++){
                  if($rele[$act[$n][$cv+7+$nn+$mm+$qq]])$mytext.=sprintf(" %02d:<b style='color:red;'>%02d</b>",$cv,$act[$n][$cv+7+$nn+$mm+$qq]);
                  else $mytext.=sprintf(" %02d:%02d",$cv,$act[$n][$cv+7+$nn+$mm+$qq]);
                }
                $mytext.="\n\n";
                break;  
               
              case 1:
              case 2:
              case 3:
              case 8:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $mytext.=sprintf("Rule: %02d Type: <i>",$n);
                if($act[$n][0]==1)$mytext.=sprintf("onoff ");
                else if($act[$n][0]==2)$mytext.=sprintf("on ");
                else if($act[$n][0]==3)$mytext.=sprintf("off ");
                else if($act[$n][0]==8)$mytext.=sprintf("push ");
                $mytext.=sprintf("</i>Name: <b>%s</b>\n",$act[$n][5+$nn+$mm]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n";
                $mytext.=sprintf("Relay #:%02d",$mm);
                for($cm=0;$cm<$mm;$cm++){
                  if($rele[$act[$n][$cm+5+$nn]])$mytext.=sprintf(" %02d:<b style='color:red;'>%02d</b>",$cm,$act[$n][$cm+5+$nn]);
                  else $mytext.=sprintf(" %02d:%02d",$cm,$act[$n][$cm+5+$nn]);
                }
                $mytext.="\n\n";
                break;
                
              case 4:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $mytext.=sprintf("Rule: %02d Type: <i>alloff</i> Name: <b>%s</b>\n",$n,$act[$n][5+$nn+$mm]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n";
                $mytext.=sprintf("Relay #:%02d",$mm);
                for($cm=0;$cm<$mm;$cm++){
                  if($rele[$act[$n][$cm+5+$nn]])$mytext.=sprintf(" %02d:<b style='color:red;'>%02d</b>",$cm,$act[$n][$cm+5+$nn]);
                  else $mytext.=sprintf(" %02d:%02d",$cm,$act[$n][$cm+5+$nn]);
                }
                $mytext.="\n\n";
                break;
                
              case 6:
                $mytext.=sprintf("Rule: %02d Type: <i>injectifoff</i> Name: <b>%s</b>\n",$n,$act[$n][5]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key: %02d Relayifoff %02d\n\n",$act[$n][3],$act[$n][4]);
                break;
                
              case 7:
                $mytext.=sprintf("Rule: %02d Type: <i>injectifon</i> Name: <b>%s</b>\n",$n,$act[$n][5]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Key: %02d Relayifon %02d\n\n",$act[$n][3],$act[$n][4]);
                break;
                
              case 9:
                $mytext.=sprintf("Rule: %02d Type: <i>offtimed</i> Name: <b>%s</b>\n",$n,$act[$n][5]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Relayoff: %02d After(min): %02d\n\n",$act[$n][3],$act[$n][4]);
                break;
                
              case 10:
                $nn=$act[$n][3];
                $mytext.=sprintf("Rule: %02d Type: <i>offtimed_keysup</i> Name: <b>%s</b>\n",$n,$act[$n][6+$nn]);
                $mytext.=sprintf("HH_start: %02d, HH_end: %02d\n",$act[$n][1],$act[$n][2]);
                $mytext.=sprintf("Relayoff: %02d After(min): %02d\n",$act[$n][4+$nn],$act[$n][5+$nn]);
                $mytext.=sprintf("Key #:%02d",$nn);
                for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d",$cn,$act[$n][$cn+4]);
                $mytext.="\n\n";
                break;
            }
          }
          break;
          
        case "key":
          $mytext.=sprintf("<b>Keys associations</b>\n");
          for($n=0;$n<72;$n++)$ww[$n]=0;
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
          for($n=0;$n<72;$n++){
            $mytext.=sprintf("Key #:%02d",$n);
            $nn=$ww[$n];
            for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d(%s)",$cn,$www[$n][$cn],end($act[$www[$n][$cn]]));
            $mytext.="\n";
          }
          break;
        
        case "relay":
          $mytext.=sprintf("<b>Relays associations</b>\n");
          for($n=0;$n<64;$n++)$ww[$n]=0;
          for($n=0;$n<$nact;$n++){
            $myaa=$act[$n][0];
            if($myaa==-1||$myaa==6||$myaa==7||$myaa==9)continue;
            switch($myaa){
              case 0:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $qq=$act[$n][5+$mm+$nn];
                for($cm=0;$cm<$mm;$cm++){
                   $kk=$act[$n][$cm+5+$nn];
                   $www[$kk][$ww[$kk]]=$n; $ww[$kk]++;
                }
                for($cq=0;$cq<$qq;$cq++){
                  $kk=$act[$n][$cq+6+$nn+$mm];
                  $www[$kk][$ww[$kk]]=$n; $ww[$kk]++;
                }
                break;
              case 11:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                $qq=$act[$n][5+$mm+$nn];
                $vv=$act[$n][6+$mm+$nn+$qq];
                for($cm=0;$cm<$mm;$cm++){
                  $kk=$act[$n][$cm+5+$nn];
                  $www[$kk][$ww[$kk]]=$n; $ww[$kk]++;
                }
                for($cq=0;$cq<$qq;$cq++){
                  $kk=$act[$n][$cq+6+$nn+$mm];
                  $www[$kk][$ww[$kk]]=$n; $ww[$kk]++;
                } 
                for($cv=0;$cv<$vv;$cv++){
                  $kk=$act[$n][$cv+7+$nn+$mm+$qq];
                  $www[$kk][$ww[$kk]]=$n; $ww[$kk]++;
                }
                break;
              case 1:
              case 2:
              case 3:
              case 8:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                for($cm=0;$cm<$mm;$cm++){
                  $kk=$act[$n][$cm+5+$nn];
                  $www[$kk][$ww[$kk]]=$n; $ww[$kk]++;
                }
                break;
              case 4:
                $nn=$act[$n][3];
                $mm=$act[$n][4+$nn];
                for($cm=0;$cm<$mm;$cm++){
                  $kk=$act[$n][$cm+5+$nn];
                  $www[$kk][$ww[$kk]]=$n; $ww[$kk]++;
                }
                break;
                
            }
          }
          for($n=0;$n<64;$n++){
            if($rele[$n])$mytext.=sprintf("Relay #:<b style='color:red;'>%02d</b>",$n);
            else $mytext.=sprintf("Relay #:%02d",$n);
            $nn=$ww[$n];
            for($cn=0;$cn<$nn;$cn++)$mytext.=sprintf(" %02d:%02d(%s)",$cn,$www[$n][$cn],end($act[$www[$n][$cn]]));
            $mytext.="\n";
          }
          break;
          
        case "log":
          $mytext.=sprintf("<b>Log of last $in[3] actions</b>\n");
          $fpout=popen("tail -r -n $in[3] $mylog", 'r');
          while(!feof($fpout))$mytext.=fgets($fpout);
          pclose($fpout);
          break;
          
        case "reload":
          $mysharedtext.="myconfig();\n";
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
          $mytext.="/passwd/key display keys mapping on rules\n";
          $mytext.="/passwd/relay display relays mapping on rules\n";
          $mytext.="/passwd/log/n log last n lines\n";
          $mytext.="/passwd/reload reload configuration\n";
          $mytext.="/passwd/delete/n delete rule n\n";
          $mytext.="/passwd/keyoff key deactivation\n";
          $mytext.="/passwd/keyon key activation\n";
          $mytext.="/passwd/help this help\n";
          break;
          
        default:
          $mytext.="<i>Wrong Command</i>\n";
          break;
      }
      $mysharedtext.="unlink('".$fileshared."');\n";
      $mysharedtext.="touch('".$fileshared."');\n";
      file_put_contents($fileshared,"<?php $mysharedtext ?>");
    }
?>
