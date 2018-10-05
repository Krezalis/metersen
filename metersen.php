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

function EnergySuite($ConsumerId,$CurrentValue,$Email,$Password,$domain='info.loe.lviv.ua'){
  //send meters to EnergySuite.Online by https://www.extracode.com.ua/
  /* 
     https://info.loe.lviv.ua/ - Львівобленерго
     https://my.oe.if.ua/ - Прикарпаттяобленерго
     https://esozoe.azurewebsites.net/ - ПАТ Запоріжжяобленерго
     https://my.toe.com.ua/ - ВАТ Тернопільобленерго 
  */ 
  
  $S=array(
    'ASP.NET_SessionId'=>'',
    '__RequestVerificationToken-HEADER'=>'',
    'ARRAffinity'=>'',
    '__RequestVerificationToken-FORM'=>'',
    '.AspNet.ApplicationCookie'=>'',
    '.AspNet.TwoFactorCookie'=>'',
    '.AspNet.ExternalCookie'=>'',
    'Consumer'=>array()
  );
  
  //1. отримати форму авторизації і CRF токен
  $ch = curl_init('https://'.$domain.'/Account/Login');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $result = curl_exec($ch); 
  $info=curl_getinfo($ch);
  curl_close($ch);
  preg_match('/Set-Cookie: ASP.NET_SessionId=([a-z0-9]*); path=\/; HttpOnly/', $result, $matches, PREG_OFFSET_CAPTURE);
  if (isset($matches[1][0])){$S['ASP.NET_SessionId']=$matches[1][0];};
  preg_match('/Set-Cookie: ARRAffinity=([a-z0-9]*);/', $result, $matches, PREG_OFFSET_CAPTURE);
  if (isset($matches[1][0])){$S['ARRAffinity']=$matches[1][0];};
  preg_match('/Set-Cookie: __RequestVerificationToken=([a-zA-Z0-9_-]*); path=\/; HttpOnly/', $result, $matches, PREG_OFFSET_CAPTURE);
  if (isset($matches[1][0])){$S['__RequestVerificationToken-HEADER']=$matches[1][0];};
  preg_match('/__RequestVerificationToken" type="hidden" value="([a-zA-Z0-9_-]*)" \/>/', $result, $matches, PREG_OFFSET_CAPTURE);
  if (isset($matches[1][0])){$S['__RequestVerificationToken-FORM']=$matches[1][0];}; 
  
  //2. авторизуватися і отримати токен сесії
  $post=array(
    '__RequestVerificationToken'=>$S['__RequestVerificationToken-FORM'],
    'Email'=>$Email,
    'Password'=>$Password,
    'RememberMe'=>'false'
  );
  $post_txt='';foreach ($post as $k=>$v) {$post_txt.=(empty($post_txt)?'':'&').urlencode($k).'='.urlencode($v);};
  $cookie=array(
    '__RequestVerificationToken'=>$S['__RequestVerificationToken-HEADER'],
    'ARRAffinity'=>$S['ARRAffinity'],
    'ASP.NET_SessionId'=>$S['ASP.NET_SessionId']
  ); 
  $cookie_txt='';foreach ($cookie as $k=>$v) {$cookie_txt.=urlencode($k).'='.urlencode($v).'; ';};  
  $ch = curl_init('https://'.$domain.'/Account/Login');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_txt); 
  curl_setopt($ch, CURLOPT_COOKIE,$cookie_txt);  
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $result = curl_exec($ch); 
  $info=curl_getinfo($ch);
  curl_close($ch);
  preg_match('/.AspNet.ApplicationCookie=([a-zA-Z0-9_-]*);/', $result, $matches, PREG_OFFSET_CAPTURE);
  if (isset($matches[1][0])){$S['.AspNet.ApplicationCookie']=$matches[1][0];};
  
  //3. отримати перелік лічильників
  $cookie=array(
    '__RequestVerificationToken'=>$S['__RequestVerificationToken-HEADER'],
    'ARRAffinity'=>$S['ARRAffinity'],
    'ASP.NET_SessionId'=>$S['ASP.NET_SessionId'],
    '.AspNet.ApplicationCookie'=>$S['.AspNet.ApplicationCookie'],
    '.AspNet.TwoFactorCookie'=>$S['.AspNet.TwoFactorCookie'],
    '.AspNet.ExternalCookie'=>$S['.AspNet.ExternalCookie'],
  ); 
  $cookie_txt='';foreach ($cookie as $k=>$v) {$cookie_txt.=urlencode($k).'='.urlencode($v).'; ';};  
  $ch = curl_init('https://'.$domain.'/consumers');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_COOKIE,$cookie_txt);  
  $result = curl_exec($ch); 
  $info=curl_getinfo($ch);
  curl_close($ch);
  preg_match_all('/\/consumers\/([0-9]*)\/info/', $result, $matches, PREG_OFFSET_CAPTURE);
  foreach ($matches[1] as $k=>$v) {
    if (!isset($S['Consumer'][$v[0]])){
      $S['Consumer'][$v[0]]='';  
    };
  };
  
  //4. отримати CRF токен для подачі показника 
  $cookie=array(
    '__RequestVerificationToken'=>$S['__RequestVerificationToken-HEADER'],
    'ARRAffinity'=>$S['ARRAffinity'],
    'ASP.NET_SessionId'=>$S['ASP.NET_SessionId'],
    '.AspNet.ApplicationCookie'=>$S['.AspNet.ApplicationCookie'],
    '.AspNet.TwoFactorCookie'=>$S['.AspNet.TwoFactorCookie'],
    '.AspNet.ExternalCookie'=>$S['.AspNet.ExternalCookie'],
  ); 
  $cookie_txt='';foreach ($cookie as $k=>$v) {$cookie_txt.=urlencode($k).'='.urlencode($v).'; ';};  
  $ch = curl_init('https://'.$domain.'/consumers/'.$ConsumerId.'/indexes/add');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_COOKIE,$cookie_txt);  
  $result = curl_exec($ch); 
  $info=curl_getinfo($ch);
  curl_close($ch);
  preg_match('/__RequestVerificationToken" type="hidden" value="([a-zA-Z0-9_-]*)" \/>/', $result, $matches, PREG_OFFSET_CAPTURE);
  if (isset($matches[1][0])){$S['__RequestVerificationToken-FORM']=$matches[1][0];}; 
  
  //5. відпраивти показник
  $post=array(
    '__RequestVerificationToken'=>$S['__RequestVerificationToken-FORM'],
    'ConsumerId'=>$ConsumerId,
    'ConsumerType'=>'Physical',
    'CounterMeterages[0].Id'=>'0',
    'CounterMeterages[0].TimeZoneId'=>'1',
    'CounterMeterages[0].Quadrant'=>'WithoutQuadrant',
    'CounterMeterages[0].Counter.UsageCalculationMethodId'=>'57257',
    'CounterMeterages[0].CurrentValue'=>$CurrentValue
  );
  $post_txt='';foreach ($post as $k=>$v) {$post_txt.=(empty($post_txt)?'':'&').urlencode($k).'='.urlencode($v);};
  $cookie=array(
    '__RequestVerificationToken'=>$S['__RequestVerificationToken-HEADER'],
    'ARRAffinity'=>$S['ARRAffinity'],
    'ASP.NET_SessionId'=>$S['ASP.NET_SessionId'],
    '.AspNet.ApplicationCookie'=>$S['.AspNet.ApplicationCookie'],
    '.AspNet.TwoFactorCookie'=>$S['.AspNet.TwoFactorCookie'],
    '.AspNet.ExternalCookie'=>$S['.AspNet.ExternalCookie'],
  );  
  $cookie_txt='';foreach ($cookie as $k=>$v) {$cookie_txt.=urlencode($k).'='.urlencode($v).'; ';};
  $ch = curl_init('https://'.$domain.'/consumers/'.$ConsumerId.'/indexes/add');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_txt);  
  curl_setopt($ch, CURLOPT_COOKIE,$cookie_txt);  
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); ;                                                                      
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($post_txt)));                                                                                                                                                                                                                
  $result = curl_exec($ch); 
  $info=curl_getinfo($ch);
  curl_close($ch);
  //Location: /consumers/*****/indexes
  
  //var_dump($S); 
};

var_dump(loe_webchat(1800123456,49000));
var_dump(loe_webchat(1800123456,'49000/1455/4445'));

EnergySuite(12345,11111,'user@gmail.com','12345678');

?>
