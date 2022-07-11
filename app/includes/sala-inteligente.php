<?php
if(!isset($_SESSION['tipo'])){
  HeaderLocation("/login");
}

$BODY['content'] = renderView('pages/sala-inteligente',md5(time()));
echo renderView('body',$BODY);
