<?php
HeaderContent("text/plain");


$file = $PARAMETROS[1];
$files = array(
  "vars.css"
);

$vars = $sys;

if(in_array($file,$files)){
  $file = __DIR__."/../css/model.".$file;
  echo str_var(file_get_contents($file),$vars);
}else{
  erro(404);
}
