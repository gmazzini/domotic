<pre>
# domotic
  
device 0 1 2 3 on 10.0.0.X with X=21 22 23 24 port 10001
  IN 09 10 11 12 17 18 19 20 21 22 23 24
  OUT 01 02 03 04 05 06 07 08 13 14 15 16
  A(01-08) B(09-16) C(17-24)
  A_Read=41H A_Conf=42H A_Write=43H
  B_Read=44H B_Conf=45H B_Write=46H
  C_Read=47H C_Conf=48H C_Write=4AH
  Conf 1=Input 0=Output 

device 4 BEM108 in on 10.0.0.33 48-55 
  http://10.0.0.33/getpara[189]=1&getpara[190]=1&getpara[191]=1&getpara[192]=1&getpara[193]=1&getpara[194]=1&getpara[195]=1&getpara[196]=1

device 5 BEM108 in on 10.0.0.35 56-63 
  http://10.0.0.35/getpara[196]=1;getpara[195]=1;getpara[194]=1;getpara[193]=1;getpara[192]=1;getpara[191]=1;getpara[190]=1;getpara[189]=1

device 6 BEM106 out on 10.0.0.32 48-55 
  set x=R-47 http://10.0.0.32/k0x=1 on http://10.0.0.32/k0x=0 off

device 7 BEM106 out on 10.0.0.34 56-63 
  set x=R-55 http://10.0.0.34/k0x=1 on http://10.0.0.34/k0x=0 off

virtualkey 64-71
