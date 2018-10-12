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

function txt_p($post){
 $post_txt='';
 foreach ($post as $k=>$v) {$post_txt.=(empty($post_txt)?'':'&').urlencode($k).'='.urlencode($v);}; 
 return $post_txt;  
}; 
function txt_c($cookie){
  $cookie_txt='';
  foreach ($cookie as $k=>$v) {$cookie_txt.=urlencode($k).'='.urlencode($v).'; ';};
  return $cookie_txt;
};  

function EnergySuite($ConsumerId,$CurrentValue,$Email,$Password,$domain='info.loe.lviv.ua',$debug=false){
  //send meters to EnergySuite.Online by https://www.extracode.com.ua/
  /* 
     https://info.loe.lviv.ua/ - Львівобленерго
     https://my.oe.if.ua/ - Прикарпаттяобленерго
     https://esozoe.azurewebsites.net/ - ПАТ Запоріжжяобленерго
     https://my.toe.com.ua/ - ВАТ Тернопільобленерго 
  */ 
  
  $success=true;
  $log=array();
  
  $Consumer=array();
  $FRVT='';
  $cookie=array();
  
  $log[]='Використано домен: '.$domain;
  
  //1. отримати форму авторизації і CRF токен
  $ch = curl_init('https://'.$domain.'/Account/Login');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $result = curl_exec($ch); 
  curl_close($ch);
  preg_match('/Set-Cookie: ASP.NET_SessionId=([a-z0-9]*); path=\/; HttpOnly/', $result, $m, PREG_OFFSET_CAPTURE);
  if (isset($m[1][0])){$cookie['ASP.NET_SessionId']=$m[1][0];}else{$success=false; $log[]='Не знайдено ASP.NET_SessionId';};
  preg_match('/Set-Cookie: ARRAffinity=([a-z0-9]*);/', $result, $m, PREG_OFFSET_CAPTURE);
  if (isset($m[1][0])){$cookie['ARRAffinity']=$m[1][0];}else{$success=false; $log[]='Не знайдено Cookie ARRAffinity';};
  preg_match('/Set-Cookie: __RequestVerificationToken=([a-zA-Z0-9_-]*); path=\/; HttpOnly/', $result, $m, PREG_OFFSET_CAPTURE);
  if (isset($m[1][0])){$cookie['__RequestVerificationToken']=$m[1][0];}else{$success=false; $log[]='Не знайдено Cookie __RequestVerificationToken';};
  preg_match('/__RequestVerificationToken" type="hidden" value="([a-zA-Z0-9_-]*)" \/>/', $result, $m, PREG_OFFSET_CAPTURE);
  if (isset($m[1][0])){$FRVT=$m[1][0];}else{$success=false; $log[]='Не знайдено у формі __RequestVerificationToken';}; 
    
  //2. авторизуватися і отримати токен сесії
  if ($success){
    $post=array(
      '__RequestVerificationToken'=>$FRVT,
      'Email'=>$Email,
      'Password'=>$Password,
      'RememberMe'=>'false'
    );  
    $ch = curl_init('https://'.$domain.'/Account/Login');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, txt_p($post)); 
    curl_setopt($ch, CURLOPT_COOKIE, txt_c($cookie));  
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $result = curl_exec($ch); 
    curl_close($ch);
    preg_match('/.AspNet.ApplicationCookie=([a-zA-Z0-9_-]*);/', $result, $m, PREG_OFFSET_CAPTURE);
    if (isset($m[1][0])){$cookie['.AspNet.ApplicationCookie']=$m[1][0];}else{$success=false; $log[]='Не знайдено Cookie .AspNet.ApplicationCookie';};
    preg_match('/.AspNet.TwoFactorCookie=([a-zA-Z0-9_-]*);/', $result, $m, PREG_OFFSET_CAPTURE);
    if (isset($m[1][0])){$cookie['.AspNet.TwoFactorCookie']=$m[1][0];}else{$log[]='Не знайдено Cookie .AspNet.TwoFactorCookie';};
    preg_match('/..AspNet.ExternalCookie=([a-zA-Z0-9_-]*);/', $result, $m, PREG_OFFSET_CAPTURE);
    if (isset($m[1][0])){$cookie['.AspNet.ExternalCookie']=$m[1][0];}else{$log[]='Не знайдено Cookie .AspNet.ExternalCookie';};
    if (strpos($result, 'Location: /')===false){
      $log[]='Авторизація невдала';
      $success=false; 
    };
  }; 
   
  //3. отримати перелік лічильників
  if ($success){  
    $ch = curl_init('https://'.$domain.'/consumers');
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_COOKIE, txt_c($cookie));  
    $result = curl_exec($ch);
    curl_close($ch);
    preg_match_all('/\/consumers\/([0-9]*)\/info/', $result, $m, PREG_OFFSET_CAPTURE);
    foreach ($m[1] as $k=>$v) {
      if (!isset($Consumers[$v[0]])){
        $Consumers[$v[0]]=array(
          'cname'=>'',
          'rr'=>'',
          'name'=>'',
          'meters'=>array()
        );   
        preg_match('/'.$v[0].'\/info"><strong>(.*)<\/strong>/', $result, $m2, PREG_OFFSET_CAPTURE);
        if (isset($m2[1][0])){$Consumers[$v[0]]['cname']=$m2[1][0];};
        preg_match('/'.$v[0].'(.*)<dd>(\d{10}), ([\S ]*)<\/dd>(.*)'.$v[0].'\/forpay/s', $result, $m2, PREG_OFFSET_CAPTURE);
        if (isset($m2[2][0])){$Consumers[$v[0]]['rr']=$m2[2][0];};
        if (isset($m2[3][0])){$Consumers[$v[0]]['name']=$m2[3][0];};   
        $log[]='Знайдено акаунт ID '.$v[0].': '.$Consumers[$v[0]]['rr'].', '.$Consumers[$v[0]]['name'];    
      };
    };
    $success=false;
    if (isset($Consumers[$ConsumerId])){
      $log[]='Знайдено ID користувача: '.$ConsumerId;
      $success=true;   
    }; 
    foreach ($Consumers as $k=>$v) {
      if ($v['rr']==$ConsumerId) {
        $success=true;
        $log[]='Знайдено особовий рахунок: '.$ConsumerId.', замінено на ID '.$k;
        $ConsumerId=$k;
      };   
    };
    if (!$success)  $log[]='Не знайдено: '.$ConsumerId; 
  };
  
  //4. отримати CRF токен для подачі показника  
  if ($success){ 
    $ch = curl_init('https://'.$domain.'/consumers/'.$ConsumerId.'/indexes/add');
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_COOKIE,txt_c($cookie));  
    $result = curl_exec($ch);
    curl_close($ch);
    preg_match('/id="reportForm" method="post"><input name="__RequestVerificationToken" type="hidden" value="([a-zA-Z0-9_-]*)" \/>/', $result, $m, PREG_OFFSET_CAPTURE);
    if (isset($m[1][0])){$FRVT=$m[1][0];}else{$success=false; $log[]='Не знайдено у формі __RequestVerificationToken';}; 
    preg_match('/<th class="text-center" colspan="5">(.*)<\/th>/', $result, $m, PREG_OFFSET_CAPTURE);
    if (isset($m[1][0])){$log[]='Лічильник: '.$m[1][0];};
    preg_match('/<td class="text-center">([\d.]*)<\/td>/', $result, $m, PREG_OFFSET_CAPTURE);
    if (isset($m[1][0])){$log[]='Дата показника: '.$m[1][0];};
    preg_match('/<td class="text-right">([\d.]*)<\/td>/', $result, $m, PREG_OFFSET_CAPTURE);
    if (isset($m[1][0])){
      $log[]='Попередній показник: '.$m[1][0];
      if (($CurrentValue-$m[1][0])>500){
        $success=false;
        $log[]='Показник новий більше 500 кВт, вихід';
      }; 
    };
  };
  
  //5. відпраивти показник
  if ($success){ 
    $post=array(
      '__RequestVerificationToken'=>$FRVT,
      'ConsumerId'=>$ConsumerId,
      'ConsumerType'=>'Physical',
      'CounterMeterages[0].Id'=>'0',
      'CounterMeterages[0].TimeZoneId'=>'1',
      'CounterMeterages[0].Quadrant'=>'WithoutQuadrant',
      'CounterMeterages[0].Counter.UsageCalculationMethodId'=>'57257',
      'CounterMeterages[0].CurrentValue'=>$CurrentValue
    ); 
    $ch = curl_init('https://'.$domain.'/consumers/'.$ConsumerId.'/indexes/add');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, txt_p($post));  
    curl_setopt($ch, CURLOPT_COOKIE, txt_c($cookie));  
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); ;                                                                                                                                                                                                                                                                                
    $result = curl_exec($ch); 
    curl_close($ch);
    $log[]='Новий показник: '.$CurrentValue;
    if (strpos($result, 'Location: /consumers/')){
      $log[]='Дані відправлено';
    };
    if (strpos($result, '500 Internal Server Error')){
      $log[]='Fail add: 500 error';
      $success= false; 
    };
  };

  //6. Отримати результат внесення
  if ($success){ 
    $ch = curl_init('https://'.$domain.'/consumers/'.$ConsumerId.'/indexes');
    curl_setopt($ch, CURLOPT_COOKIE, txt_c($cookie)); 
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $result = curl_exec($ch); 
    $info=curl_getinfo($ch);
    curl_close($ch);  
    if (strpos($result, 'Покази відправлено в дата центр.')===false){
      $log[]='Не відправлено показник';
      $success= false; 
    }else{
      $log[]='Покази відправлено в дата центр';        
    };
  };

  if ($success & !$debug){
    return true;
  }else{
    return $log;
  };  
};

var_dump(loe_webchat(1800123456,49000));
var_dump(loe_webchat(1800123456,'49000/1455/4445'));

var_dump(EnergySuite(1800123456,49000,'user@gmail.com','12345678'));

?>
