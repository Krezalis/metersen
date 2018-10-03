<?php

/*
################################
# Yuriy Rudyy info@uaid.net.ua #
#     https://blog.uaid.net.ua #
################################
*/

function loe_webchat($persid,$count){
  //webchat send meters in one click  http://loe.lviv.ua/
  $post=array(
    'action'=>'save_ecount',
    'persid'=>$persid,
    'ecount1'=>$count,
    'ecount2'=>'undefined',
    'ecount3'=>'undefined',
    'user'=>'',
    'login'=>''
  );
  if (strpos($count, '/')){
    list($post['ecount1'],$post['ecount2'],$post['ecount3'])=explode('/', $count);
  };
  $post_txt=''; foreach ($post as $k=>$v) {$post_txt.=(empty($post_txt)?'':'&').urlencode($k).'='.urlencode($v);};
  $ch = curl_init('http://loe.lviv.ua/content/site/webchat.php');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_txt);    
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                                                                                                          
      'Content-Length: ' .strlen($post_txt)
    )                                                                       
  );                           
  $info=curl_getinfo($ch);                                                                                                                                                                                           
  $result = curl_exec($ch); 
  curl_close($ch);
  if ((int)$result===1){
    return true;
  }else{
    return array($info,$result);
  };
};

function loe_info($meter,$count,$email,$pass){
  //send meters https://info.loe.lviv.ua/
  //TBD
};

var_dump(loe_webchat(1800123456,49000));
var_dump(loe_webchat(1800123456,'49000/1455/4445'));

?>
