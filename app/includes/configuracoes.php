<?php
if(isset($PARAMETROS[1],$PARAMETROS[2])){
	if($PARAMETROS[1] == 'multiLanguageConfiguredDirs'){

		$locale = urldecode($PARAMETROS[2]);
		HeaderContent("application/json");
		$fileDir = __DIR__."/../set/lang/".$locale."/control.json";
		if(file_exists($fileDir)){
			$json = json_decode(file_get_contents($fileDir),true);
			if($json){
				echo json($json['DIR']);
				exit;
			}
		}
	}
	if($PARAMETROS[1] == 'unlinkDirMultiLanguage'){
		$locale = urldecode($PARAMETROS[2]);
		$dir = urldecode($PARAMETROS[3] ?? '');
		if(!empty($dir)){
			$fileDir = __DIR__."/../set/lang/".$locale."/control.json";
			if(file_exists($fileDir)){
				$json = json_decode(file_get_contents($fileDir),true);
				if($json){
					if(isset($json['DIR'][$dir])){
						unset($json['DIR'][$dir]);
					}
					file_put_contents($fileDir,json($json));
					echo 'ok';
					exit;
				}
			}
		}
	}
	if($PARAMETROS[1] == 'add' && isset($_POST['link'],$_POST['include']) ){
		$locale = urldecode($PARAMETROS[2]);
		$fileDir = __DIR__."/../set/lang/".$locale."/control.json";
		if(file_exists($fileDir)){
			$json = json_decode(file_get_contents($fileDir),true);
			if($json){
				$link = str_replace("/",'',$_POST['link']);
				$include = str_replace("/",'',$_POST['include']);
				$include = str_replace(" ",'-',$include);
				$json["DIR"][$include] = $link;
				file_put_contents($fileDir,json($json));
				echo 'ok';
				exit;
			}
		}

	}
	erro(500);
}

$C = new Config;

$html = array();
$html['regs-controle-de-acesso'] = '';

/* === restricao de acesso ===
  Dependência: -modulo users;
==============================
*/

if(class_exists('Usuarios')){

	if(isset($_POST['set'],$_POST['token']) and !check_post($_POST['token'])){
		receive_post($_POST['token']);
		if(isset($_POST['set'])){
			$key = $_POST['set'][0];
			$search = array_search($key,$sys['CONTROL_LANG']['DIR']);
			$key = $search ? $search : $key;
			if($C->set_restricao_de_acesso($key,$_POST['set'][1])){
				recarregar_pagina('/'.$PARAMETROS[0]);
			}else{
				mensagem_de_erro();
			}
		}
	}


	if(isset($GET['excluir'])){
		if($C->delete_restricao_de_acesso($GET['excluir'])){
			recarregar_pagina('/'.$PARAMETROS[0]);
		}else{
			mensagem_de_erro();
		}
	}

	$arr = $C->get_restricao_de_acesso();

	if(empty($arr)){
		$html['regs-controle-de-acesso'] = renderView('cards/nenhum-registro-encontrado','');
	}else{
		foreach($arr as $u=>$i){
			$i['ID'] = $u;
			if(isset($sys['CONTROL_LANG']['DIR'][$i['DIR']])){
				$i['DIR'] = $sys['CONTROL_LANG']['DIR'][$i['DIR']];
			}
			$i['acesso'] = $LANG['MAIN'][$i['acesso']];
			$html['regs-controle-de-acesso'] .= renderView('cards/regs-controle-de-acesso',$i);
		}
	}

}





$html['token'] = md5(time());

$LANGS = glob(__DIR__."/../set/lang/*-*");
$html['items-lang'] = '';
foreach($LANGS as $item){
	$item = explode("/",$item);
	$item = $item[count($item)-1];
	$active = $item == $sys['LOCALE'] ? "active" : "";
	$html['items-lang'] .= '<a href="#" onClick="return setLang(this.innerText)" class="item-lang-select it-lang_'.$item.' '.$active.'">'.$item."</a>\n";
}
$html['LOCALE'] = $sys['LOCALE'];

$BODY['content'] = renderView('pages/configuracoes',$html);
echo renderView('body',$BODY);
