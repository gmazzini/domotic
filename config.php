<?php

$config_version="51";

// 3level: 0 h_start h_end n_key key_1 ... key_n m_releA releA_1 ... releA_m q_releB releB_1 ... releB_q name
// onoff: 1 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// on: 2 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// off: 3 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// alloff: 4 h_start h_end n_key key_1 ... key_n v_rele rele_1 ... rele_v name
// injectifoff: 6 hh mm key releifoff name
// injectifon: 7 hh mm key releifon name
// push: 8 h_start h_end n_key key_1 ... key_n m_rele rele_1 ... rele_m name
// offtimed: 9 h_start h_end rele minutes name
// offtimed_keysup: 10 h_start h_end n_key key_1 ... key_n rele minutes name

$act=array(
// array(6,5,20,4,12,"accendi acqua mattina"),
// array(7,5,30,4,12,"spegni acqua mattina"),
// array(6,8,20,4,12,"accendi acqua mezzogiorno"),
// array(7,8,30,4,12,"spegni acqua mezzogiorno"),
// array(6,18,20,4,12,"accendi acqua pomeriggio"),
// array(7,18,30,4,12,"spegni acqua pomeriggio"),
// array(6,22,20,4,12,"accendi acqua sera"),
// array(7,22,30,4,12,"spegni acqua sera"),
// array(1,0,23,1,4,1,12,"acqua"),
array(8,0,23,1,14,1,19,"caldaia_automatica"),
array(1,0,23,1,60,1,19,"caldaia"),
array(10,0,23,1,14,19,60,"caldaia_autoff"),
array(1,0,23,3,30,6,16,7,43,37,9,12,16,26,13,"natale"),
array(8,7,22,1,17,1,25,"campanello"),
array(1,0,23,1,19,1,20,"cucina"),
array(1,0,23,1,18,1,16,"pianale_cucina"),
array(1,0,23,1,20,1,18,"cappa"),
array(1,0,23,1,22,2,10,8,"bagno_terra"),
array(1,0,23,1,15,4,4,30,38,49,"esterne"),
array(0,0,23,3,3,36,32,1,45,2,31,47,"scala"),
array(1,0,23,1,28,2,35,32,"bagno_sopra"),
array(0,0,23,2,1,10,2,5,6,2,2,3,"diner"),
array(0,0,23,4,0,8,11,27,1,7,1,29,"led scala e diner"),
array(1,0,23,1,47,1,40,"doccia"),
array(0,0,23,2,46,34,1,39,1,36,"luna"),
array(1,0,23,1,33,1,41,"luna_studio"),
array(1,0,23,1,35,1,46,"luna_letto"),
array(0,0,23,4,40,25,31,45,1,42,1,24,"gm&mts"),
array(1,0,23,2,24,63,1,27,"gm_comodino"),
array(1,0,23,1,41,1,28,"mts_comodino"),
// array(0,0,23,1,13,2,21,17,2,22,13,"living da terra"),
array(0,0,23,1,13,2,21,17,1,22,"living da terra (a natale)"),
array(0,0,23,1,7,2,15,11,1,14,"living a parete"),
array(1,0,23,1,23,2,13,11,"living piccola"),
array(1,0,23,1,12,1,23,"ripostiglio"),
array(0,0,23,2,43,37,1,0,1,1,"neve"),
array(1,0,23,1,44,2,33,50"neve_studio"),
array(1,0,23,1,42,1,34,"neve_letto"),
array(1,0,23,1,38,1,26,"neve_hood"),
array(4,0,23,3,2,39,26,1,19,"spegnitutto"),
array(3,0,23,1,5,20,0,1,24,25,27,28,31,32,33,34,35,36,39,40,41,42,44,45,46,47,"spegnisopra"),
array(1,0,23,1,16,1,48,"cantina")
);

?>
