<?php

$config_version="113";

// 0> 3level: 0 h_start h_end n_key key_1 ... key_n m_releA releA_1 ... releA_m q_releB releB_1 ... releB_q name
// 1> onoff: 1 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// 2> on: 2 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// 3> off: 3 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// 4> alloff: 4 h_start h_end n_key key_1 ... key_n skip_rele rele_1 ... rele_skip name
// 6> injectifoff: 6 hh mm key releifoff name
// 7> injectifon: 7 hh mm key releifon name
// 8> push: 8 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// 9> offtimed: 9 h_start h_end rele minutes name
// 10> offtimed_keysup: 10 h_start h_end n_key key_1 ... key_n rele minutes name
// 11> 3light: 11 h_start h_end n_key key_1 ... key_n m_releA releA_1 ... releA_m q_releB releB_1 ... releB_q v_releC releC_1 ... releC_v name

// virtualkey 56-63

$act=array(
// array(6,5,20,4,12,"accendi acqua mattina"),
// array(7,5,40,4,12,"spegni acqua mattina"),
// array(6,22,20,4,12,"accendi acqua sera"),
// array(7,22,40,4,12,"spegni acqua sera"),
// array(1,0,23,1,4,1,12,"acqua"),
// array(1,0,23,3,12,18,30,11,1,12,13,19,20,21,22,37,43,48,56,"natale"),
array(8,7,22,2,17,54,1,25,"campanello"),
array(1,0,23,1,19,1,16,"cucina"),
array(1,0,23,1,20,1,18,"barracucina"),
array(1,0,23,1,22,2,8,10,"bagno_terra"),
array(1,0,23,2,15,58,5,4,30,38,53,54,"esterne"),
array(0,0,23,3,3,36,32,1,45,2,31,47,"scala"),
array(1,0,23,1,28,2,35,32,"bagno_sopra"),
array(11,0,23,2,1,10,2,6,3,1,2,2,5,3,"barradinner"),
array(1,0,23,1,9,2,5,3,"barracena"),
array(0,0,23,3,0,11,27,1,7,1,29,"led scala e diner"),
array(1,0,23,1,47,1,40,"doccia"),
array(0,0,23,2,46,34,1,39,1,36,"luna"),
array(1,0,23,1,33,1,41,"luna_studio"),
array(1,0,23,1,35,1,46,"luna_letto"),
array(0,0,23,4,40,25,31,45,1,42,1,24,"gm&mts"),
array(1,0,23,2,24,64,1,27,"gm_comodino"),
array(1,0,23,1,41,1,28,"mts_comodino"),
array(11,0,23,1,7,1,15,1,17,1,14,"barraliving"),
array(1,0,23,1,13,1,9,"soffuse"),
array(1,0,23,1,8,1,14,"barraTV"),
array(1,0,23,1,23,2,13,11,"living piccola"),
array(1,0,23,1,14,1,23,"ripostiglio"),
array(1,0,23,2,38,43,1,0,"neve"),
array(1,0,23,1,44,1,44,"neve_letto"),
array(1,0,23,1,42,1,34,"neve_studio"),
array(1,0,23,1,55,1,33,"neve_wood"),
array(1,0,23,2,37,48,1,26,"sala_radio"),
array(1,0,23,1,49,1,61,"radio_left"),
array(1,0,23,1,50,1,62,"radio_middle"),
array(1,0,23,1,51,1,63,"radio_right"),  
array(4,0,23,3,2,39,26,0,"spegnitutto"),
array(3,0,23,2,5,52,22,0,24,26,27,28,31,32,33,34,35,36,39,40,41,42,44,45,46,47,61,62,63,"spegnisopra"),
array(3,0,23,2,29,53,14,2,3,5,6,8,10,11,14,15,16,17,18,21,23,"spegnisotto"),
array(1,0,23,1,56,4,52,51,50,49,"atelier_luci"),
array(1,0,23,1,57,1,55,"atelier_ap"),
array(1,0,23,1,60,1,52,"atelier_luce2"),
array(1,0,23,1,61,1,51,"atelier_luce3"),
array(1,0,23,1,62,1,50,"atelier_luce4"),
array(1,0,23,1,63,1,49,"atelier_luce5"),
array(1,0,23,1,16,1,48,"prese muro")
);

?>
