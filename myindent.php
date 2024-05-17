<?php
$lastc="";
$lq=0;
$lap2=0;
$lap=0;
for(;;){
  $c=fgetc(STDIN);
  if($c==NULL)break;
  if($c=="\r")continue;
  if($c=="{"&&$lap==0&&$lap2==0)$lq++;
  if($c=="}"&&$lap==0&&$lap2==0)$lq--;
  if($c=='"'&&$lap2==0&&$lap==0)$lap2=1;
  elseif($c=='"'&&$lap2==1&&$lap==0)$lap2=0;
  if($c=="'"&&$lap==0&&$lap2==0)$lap=1;
  elseif($c=="'"&&$lap==1&&$lap2==0)$lap=0;
  if($lq<0){echo"------Error {}";exit(-1);}
  if($c==" "&&$lap==0&&$lap2==0)continue;
  if($lastc=="\n")for($j=0;$j<$lq;$j++)echo"  ";
  echo$c;
  $lastc=$c;
}

?>
