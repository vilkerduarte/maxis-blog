<?php
if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] <> 'admin'){
  erro(403);
}
function stringToArrayRecursive($string,$valor){
  $arr = explode(".",$string);
  $newArray = $valor;
  for($x = (count($arr)-1);$x >= 0;$x--){
    $newArray = array($arr[$x]=>$newArray);
  }
  return $newArray;
}
$locale = $PARAMETROS[1];
$file = $PARAMETROS[2]??false;
$path = __DIR__."/../set/lang/".$locale;
if($locale && is_dir($path)){
  if(isset($_REQUEST['key'],$_REQUEST['value'])){
    $key = strstr($_REQUEST['key'],'.') ? $_REQUEST['key'] : 'main.'.$_REQUEST['key'];
    $key = explode('.',$key);
    $file = $key[0];
    $jsonPath = "$path/$file.json";
    if(file_exists($jsonPath)){
      $json = json(file_get_contents($jsonPath));
    }else{
      $json = array();
    }
    HeaderContent("text/plain");
    if($json !== false){
      if(count($key) > 2){
        $newKey = $key;
        unset($newKey[0],$newKey[1]);
        $array = stringToArrayRecursive(implode(".",$newKey),$_REQUEST['value']);
        if(!isset($json[$key[1]])){
          $json[$key[1]] = array();
        }
        $json[$key[1]] = array_replace_recursive($json[$key[1]],$array);
      }else{
        $json[$key[1]] = $_REQUEST['value'];
      }
      if(file_put_contents($jsonPath,json($json))){

      }else{
        erro(501);
      }
    }else{
      erro(500);
    }
    exit;
  }
  if($file){
    $path = "$path/$file.json";
    if(file_exists($path)){
      HeaderContent("application/json");
      echo file_get_contents($path);
      exit;
    }else{
      erro(404);
    }
  }else{
    $list = [];
    foreach (glob($path."/*.json") as $file) {
      $list[] = str_replace($path.'/','',str_replace('.json','',$file));
    }
    HeaderContent("application/json");
    echo json($list);
    exit;
  }
}else{
  erro(401);
}
