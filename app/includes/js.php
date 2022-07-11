<?php
HeaderContent('text/plain');
if($PARAMETROS[1] == 'menus'){
  echo renderView('js_menus',false);
}
