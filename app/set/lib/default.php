<?php
class Fingerprint{
    public static function searchFingerprint($finger,$user=null){
        $filter = array(
            "fingerprint"=>$finger
            );

        if(!is_null($user)){
            $filter['user'] = $user;
        }
        return getListDb('fingerprints',$filter);
    }
    public static function regFinger($finger,$user){
        $busca = self::searchFingerprint($finger,$user);
        if($busca){
            $DB = array("last_used"=>date_time_now());
            if(updateDB('fingerprints',$DB,$busca[0]['hash'])){
                return true;
            }else{
                return false;
            }
        }else{
            $DB = array(
                "fingerprint"=>$finger,
                "user"=>$user,
                "last_used"=>date_time_now()
            );
            if(insertDB('fingerprints',$DB,'hash')){
                return true;
            }else{
                return false;
            }
        }
    }
    public static function checkMeta($fingerprint){
        $busca = getListDb('metadados',array("fingerprint"=>$fingerprint));
        if($busca){
            return $busca[0];
        }else{
            return false;
        }
    }
    public static function setMetadata($fingerprint,$meta){
        $meta = is_array($meta) ? json_encode($meta,JSON_UNESCAPED_UNICODE) : $meta;
        $DB = array(
            "fingerprint"=>$fingerprint,
            "metadados"=>$meta
        );
        if(insertDB('metadados',$DB,'data,hash')){
            return true;
        }else{
            return false;
        }

    }
}

function smtpmailer($para, $de, $de_nome, $assunto, $corpo,$isHTML=false) {
	$mail = new PHPMailer();
	$mail->IsSMTP();		// Ativar SMTP
	$mail->SMTPDebug = 0;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
	$mail->SMTPAuth = true;		// Autenticação ativada
//	$mail->SMTPSecure = 'ssl';	// SSL REQUERIDO pelo GMail
	$mail->Host = SMTP_HOST;	// SMTP utilizado
	$mail->Port = SMTP_PORT;  		// A porta 587 deverá estar aberta em seu servidor
	$mail->Username = GUSER;
	$mail->Password = GPWD;
	$mail->SetFrom($de, $de_nome);
	$mail->IsHTML($isHTML);
	$mail->Subject = $assunto;
	$mail->CharSet = 'UTF-8';
	$mail->Body = $corpo;
	$mail->AddAddress($para);
	if(!$mail->Send()) {
		return false;
	} else {
		return true;
	}
}

function write_array(string $string,$array){
	preg_match_all('/({%)[\W\w]{1,}(%})/U',$string,$indexes);
	$indexes = $indexes[0];
	if($array){
		foreach($array as $item){
			$txt = $string;
			foreach($indexes as $x){
				$y = preg_replace('/({%)|(%})/','',$x);
				if(strstr($y,'->')){
					$blc = explode('->',$y);
					$func = false;
					if(count($blc)==2 || count($blc)==4){
						$func = $blc[count($blc)-1];
					}
					if(count($blc) > 2){
						$busca = verItemDb($blc[1],$item[$blc[0]]);
						$sub = $busca[$blc[2]];
					}else{
						$sub = $item[$blc[0]];
					}
					if($func){
						$sub = $func($sub);
					}
				}else{
					$sub = $item[$y];
				}
				$txt = str_replace($x,$sub,$txt);
			}
			return $txt;
		}
	}
}

function str_var(string $string,array $vars){
  $view = $string;
	$view = renderLang($view,_SYS_['LOCALE']);
	if($view && strstr($view,'{%') && strstr($view,'%}')){
		if(is_array($vars)){
			$txt = $view;
			preg_match_all('/({%)[\W\w]{1,}(%})/U',$view,$indexes);
			$indexes = $indexes[0];
			foreach($indexes as $x){
				$y = preg_replace('/({%)|(%})/','',$x);
				$init = 0;
				if(strstr($y,'>')){

					$A = explode('>',$y);

					foreach($A as $a1=>$a2){
						if($a1 == 0){
							$result = $vars;
						}

						$a3 = str_split($a2);
						if($a3[count($a3)-1] == '-'){
							$ind = substr($a2,0,strlen($a2)-1);
							if(in_array(substr($A[$a1+1],-1,1),array("-",":"))){
								$tbl = substr($A[$a1+1],0,strlen($A[$a1+1])-1);
							}else{
								$tbl = $A[$a1+1];
							}
							$busca = verItemDb($tbl,$result[$ind]);

							$result = $busca;
						}else if($a3[count($a3)-1] == ':'){
							$func = $A[$a1+1];
							$ind = substr($a2,0,strlen($a2)-1);
							if(in_array(substr($func,-1,1),array("-",":"))){
								$func = substr($func,0,strlen($func)-1);
							}
							if(isset($result[$ind])){
								$result = $func($result[$ind]);
							}
						}else if(isset($A[$a1+1])){
							if(isset($A[$a1-1]) && in_array(substr($A[$a1-1],-1,1),array("-",":"))){
								continue;
							}
							if(isset($result[$a2])){
								if($a2 == 'json'){
									$result = json_decode($result[$a2],true);
								}else{
									$result = $result[$a2];
								}
							}
						}else{
							if(isset($result[$a2])){
								if(!in_array(substr($A[$a1-1],-1,1),array("-",":"))){
									$result = $result[$a2];
								}
							}
						}
					}
					if(is_array($result) || is_null($result)){
						$result = '';
					}
					$txt = str_replace($x,$result,$txt);
				}else{
					if(isset($vars[$y])){
						$txt = str_replace($x,$vars[$y],$txt);
					}
				}

			}
			return $txt;

		}else if(is_string($vars)){
			return preg_replace('/({%)[\w\W]{1,}(%})/U',$vars,$view);
		}else{
			return $view;
		}
	}else{
		return $view;
	}
}

function getView($view){
	if(file_exists(__DIR__."/../../views/$view.html")){
		return file_get_contents(__DIR__."/../../views/$view.html");
	}else{
		return false;
	}
}

function renderView(string $view,$vars){
	$view = getView($view);
	$view = renderLang($view,_SYS_['LOCALE']);
	if($view && strstr($view,'{%') && strstr($view,'%}')){
		if(is_array($vars)){
			$txt = $view;
			preg_match_all('/({%)[\W\w]{1,}(%})/U',$view,$indexes);
			$indexes = $indexes[0];
			foreach($indexes as $x){
				$y = preg_replace('/({%)|(%})/','',$x);
				$init = 0;
				if(strstr($y,'>')){

					$A = explode('>',$y);

					foreach($A as $a1=>$a2){
						if($a1 == 0){
							$result = $vars;
						}

						$a3 = str_split($a2);
						if($a3[count($a3)-1] == '-'){
							$ind = substr($a2,0,strlen($a2)-1);
							if(in_array(substr($A[$a1+1],-1,1),array("-",":"))){
								$tbl = substr($A[$a1+1],0,strlen($A[$a1+1])-1);
							}else{
								$tbl = $A[$a1+1];
							}
							$busca = verItemDb($tbl,$result[$ind]);

							$result = $busca;
						}else if($a3[count($a3)-1] == ':'){
							$func = $A[$a1+1];
							$ind = substr($a2,0,strlen($a2)-1);
							if(in_array(substr($func,-1,1),array("-",":"))){
								$func = substr($func,0,strlen($func)-1);
							}
							if(isset($result[$ind])){
								$result = $func($result[$ind]);
							}
						}else if(isset($A[$a1+1])){
							if(isset($A[$a1-1]) && in_array(substr($A[$a1-1],-1,1),array("-",":"))){
								continue;
							}
							if(isset($result[$a2])){
								if($a2 == 'json'){
									$result = json_decode($result[$a2],true);
								}else{
									$result = $result[$a2];
								}
							}
						}else{
							if(isset($result[$a2])){
								if(!in_array(substr($A[$a1-1],-1,1),array("-",":"))){
									$result = $result[$a2];
								}
							}
						}
					}
					if(is_array($result) || is_null($result)){
						$result = '';
					}
					$txt = str_replace($x,$result,$txt);
				}else{
					if(isset($vars[$y])){
						$txt = str_replace($x,$vars[$y],$txt);
					}
				}

			}
			return $txt;

		}else if(is_string($vars)){
			return preg_replace('/({%)[\w\W]{1,}(%})/U',$vars,$view);
		}else{
			return $view;
		}
	}else{
		return $view;
	}
}

function renderLang($view,$locale){
	preg_match_all('/({{)[\W\w]{1,}(}})/U',$view,$indexes);
	$indexes = $indexes[0];
	$path = __DIR__."/../lang/".$locale;
	if(is_dir($path)){
		foreach($indexes as $x){
			$x = preg_replace('/({{)|(}})/','',$x);
			$exX = strstr($x,".") ? $x : 'main.'.$x;
			$e = explode('.',$exX);
			$file = "$path/$e[0].json";
			if(!file_exists($file)){
				$file = "$path/main.json";
			}
			$json = json(file_get_contents($file));
			for($k = 1;$k < count($e); $k++){
        if(isset($json[$e[$k]])){
          $json = $json[$e[$k]];
        }
			}
			if(!is_array($json)){
				$view = str_replace("{{".$x."}}",$json,$view);
			}
		}
	}
	return $view;
}

function delTree($dir) {
 $files = array_diff(scandir($dir), array('.','..'));
  foreach ($files as $file) {
	(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
  }
  return rmdir($dir);
}

function replace(array $ArrayReplaces,string $string){
	foreach($ArrayReplaces as $search=>$replace){
		$string = str_replace($search,$replace,$string);
	}
	return $string;
}

function read($file){
	if(file_exists($file)){
		$fp = fopen($file,"r");
		$read = fread($fp,filesize($file));
		fclose($fp);
		return $read;
	}else{
		return false;
	}
}

function escrever_arquivo(string $file,string $string,bool $init=false){
	$fp = fopen($file,($init ? "w+" : "a"));
	fwrite($fp,$string);
	fclose($fp);
}

function listarDiretorio($dir,int $nivel = 1,array $result=array()){
	$str = '';
	for($x=0;$x<$nivel;$x++){
		$str .= '/*';
	}
	$HaveNext = false;
	if(is_dir($dir) || strstr($dir,'*')){
		foreach(glob($dir.$str,GLOB_MARK) as $file){
			if(substr($file,-1,1) <> '\\'){
				$result[] = $file;
			}else{
				$HaveNext = true;
			}
		}
	}
	if($HaveNext){
		return listarDiretorio($dir,$nivel+1,$result);
	}else{
		return $result;
	}
}

function normalize ($string) {
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
    );

    return strtr($string, $table);
}

function FILTRO_CODE($dado){
	$dado = normalize($dado);
	$dado = strtolower($dado);
	return $dado;
}

function FILTRO_FILENAME($dado){
	$dado = normalize($dado);
	$dado = str_replace(' ', '-', $dado);
	$dado = strtolower($dado);
	return $dado;
}

function javascript($script='/* script */'){
	$GLOBALS['BODY']['bottom-script'] =  "\n".$script."\n";
}

function recarregar_pagina($link='/'){
	$GLOBALS['BODY']['bottom-script'] =  "\n".'window.location.href = "'.$link.'";'."\n";
}

function mensagem_de_erro($mensagem=false){
	$LANG = getLanguage('main');
	$mensagem = $mensagem ? $mensagem : $LANG['messages']['msg_error'];
	$GLOBALS['BODY']['bottom-script'] = "\n".'modal("'.$LANG['messages']['error'].'","'.$mensagem.'");'."\n";
}

function mensagem_de_sucesso($mensagem=false){
	$LANG = getLanguage('main');
	$mensagem = $mensagem ? $mensagem : $LANG['messages']['msg_success'];
	$GLOBALS['BODY']['bottom-script'] = "\n".'modal("'.$LANG['messages']['success'].'","'.$mensagem.'");'."\n";
}

function location_time($link='/'){
	$GLOBALS['BODY']['bottom-script'] = "\n".'location_time("'.$link.'");'."\n";
}

function mensagem_com_location($mensagem,$link){
	$LANG = getLanguage('main');
	$mensagem = $mensagem ? $mensagem : $LANG['messages']['msg_success'];
	$GLOBALS['BODY']['bottom-script'] = "\n".'modal("'.$LANG['messages']['success'].'","'.$mensagem.'");location_time("'.$link.'");'."\n";
}

function upload($dir,$arquivo,$name=false){
	if(is_array($arquivo)){

		$pasta = $dir;
		$nome_imagem = $arquivo['name'];
		// pega a extensão do arquivo
		$ext = strtolower(strrchr($nome_imagem,"."));
		if($name){
			$nome_atual = $name.$ext;
		}else{
			$nome_atual = $nome_imagem;
		}
		$tmp = $arquivo['tmp_name']; //caminho temporário da imagem
		@unlink($pasta.$nome_atual);
		 // ============= Envio da imagem ============
		if(move_uploaded_file($tmp,$pasta.$nome_atual)){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

function check_post($token){
	if(isset($_SESSION['requests'][$token])){
		return true;
	}else{
		return false;
	}
}

function receive_post($token){
	if(isset($_SESSION['requests'])){
		unset($_SESSION['requests']);
	}
	$_SESSION['requests'][$token] = true;
}

function reset_post_session(){
	if(isset($_SESSION['requests'])){
		unset($_SESSION['requests']);
	}
}

function set_token(){
	$time = time().date('YmdHis');
	$number = rand(1,1000000);
	return md5(md5(md5(md5(md5(md5(md5(md5(md5(md5($number.$time))))))))));
}

function date_time_now(){
	return date('Y-m-d H:i:s');
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
// ============ Filtros de Dados ==============

function FILTRO_nome($dado){
	$dado = preg_replace("/[0-9%'\"><;?|\/]/", '',$dado);
	$dado = mb_strtolower($dado, 'UTF-8');
	$dado = mb_convert_case($dado, MB_CASE_TITLE, "UTF-8");;
	return $dado;
}

function FILTRO_idade($data_nasc){
	$time__ = strtotime($data_nasc);
	$dt[0] = intval(date('d',$time__));
	$dt[1] = intval(date('m',$time__));
	$dt[2] = intval(date('Y',$time__));
	if($dt[1] < intval(date('m'))){
		$calc = true;
	}else if($dt[1] == intval(date('m'))){
		if($dt[0] <= intval(date('d'))){
			$calc = true;
		}else{
			$calc = false;
		}
	}else{
		$calc = false;
	}
	$idade = intval(date('Y')) - $dt[2];
	return intval($calc ? $idade : $idade - 1);
}

function FILTRO_data($dado,int $mode=1,$format=false){
	$date = new DateTime($dado);
	$zone = new DateTimeZone('America/Cuiaba');
	$date->setTimezone($zone);
	$resultado = false;
	switch($mode){
		case 1:
		  	$resultado = $date->format('d/m/Y');
			break;
		case 2:
		  	$resultado = strftime("%d / %B / %G &nbsp;&nbsp; | &nbsp;&nbsp;  %A", strtotime($date->format('Y-m-d')));
			$resultado = ucwords(FILTRO_utf8($resultado));
			break;
		case 3:
			if($format){
				$resultado = $date->format($format);
			}
			break;
		case 4:
			if($format){
				$resultado = FILTRO_utf8(strftime($format, strtotime($date->format('Y-m-d H:i:s'))));
			}
			break;

	}
	return $resultado;
}

function FILTRO_utf8($dado){
	return utf8_encode($dado);
}

function FILTRO_text_funcional($dado,$NoSpace_NoUppercase=false){
	$dado = FILTRO($dado);
	$trocas = array("a"=>array("à","á","â","ã"),"e"=>array("è","é","ê"),"i"=>array("ì","í","î"),"o"=>array("ò","ó","ô","õ"),"u"=>array("ú","ù","û"),"A"=>array("À","Á","Â","Ã"),"E"=>array("È","É","Ê"),"I"=>array("Ì","Í","Î"),"O"=>array("Ò","Ó","Ô","Õ"),"U"=>array("Ú","Ù","Û"),"c"=>array("ç"),"C"=>array("Ç"));
	$new_dado = '';
	foreach($trocas as $k => $i){
		foreach($i as $o){
			$dado = str_replace($o,$k,$dado);
		}
	}
	if($NoSpace_NoUppercase){
		if(strstr($dado,' ')){
			$dado = str_replace(' ','-',$dado);
		}
		$dado = strtolower($dado);
	}
	return $dado;
}

function FILTRO_data_inv($dado){
	$dado = preg_replace('/[^[:digit:]_]/', '/',$dado);
	$dado1 = substr($dado,0,2);
	$dado2 = substr($dado,3,2);
	$dado3 = substr($dado,6,4);
	$dado = "$dado3-$dado2-$dado1";
	return $dado;
}

function FILTRO_number($dado){
	return preg_replace('/[^[:digit:]\-\.\,_]/','',$dado);
}

function FILTRO_moneyBRL($valor){
	$valor = doubleval($valor);
	return "R$ ".number_format($valor,2,',','.');
}

function FILTRO_crypt($valor){
	$valor = doubleval($valor);
	return number_format($valor,8,'.',',');
}

function FILTRO($dado){
	$dado = preg_replace("/[%'\"><;?|]/", '',$dado);
	return $dado;
}

function FILTRO123($dado){
	$dado = preg_replace("/[0-9]/", '',$dado);
	return $dado;
}

function FILTRO_DATA_FORMAT($dado){
	if(!is_string($dado) || strlen($dado)< 5){
		return '-----';
	}
	$dia = substr($dado,8,2);
	$mes = substr($dado,5,2);
	$ano = substr($dado,0,4);
	$hora = substr($dado, 10);
	return $dia.'/'.$mes.'/'.$ano.$hora;
}

function FILTRO_HORA($dado){
	$hora = substr($dado, 10);
	$hora = substr($hora, 1,5);
	return $hora;
}

function FILTRO_senha($senha){
	$senha = hash('sha256', md5($senha));
	return $senha;
}

function is_hash($string){
	if(ctype_xdigit($string) && (strlen($string) >= 16)){
		return true;
	}else{
		return false;
	}
}

function br2nl($html){
	return preg_replace('/\<br \/\>/', "\t", $html);
}

function _GET($string){
	if(is_string($string) and (strlen($string) > 2)){
		if(strstr($string,'?')){
			$string = str_replace('?','',$string);
		}
		if(strstr($string,'&')){
			$filtro1 = explode('&',$string);
			foreach($filtro1 as $item){
				$index = strstr($item,'=',true);
				$value = strstr($item,'=');
				$value = str_replace('=','',$value);
				$result[$index] = $value;
			}
		}else{
			$index = strstr($string,'=',true);
			$value = strstr($string,'=');
			$value = str_replace('=','',$value);
			$result[$index] = $value;
		}
		return $result;
	}else{
		return false;
	}
}

function FILTRO_tirar_acentos($string){
    return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
}

function FLAGS($string){
	switch($string){
		case 'json':
			return JSON_UNESCAPED_UNICODE;
			break;
		default:
			return false;
			break;
	}
}

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('Bytes', 'KB', 'MB', 'GB', 'TB');

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}

function formatTime(int $segundos){
	if($segundos < 15){
		$segundos = 1;
	}
	$a = array("minuto","hora","dia");
	switch($segundos){
		case $segundos >= 86400:
			$E = $a[2];
			$V = floor($segundos/86400);
			break;
		case $segundos >= 3600:
			$E = $a[1];
			$V = floor($segundos/3600);
			break;
		case $segundos >= 60:
			$E = $a[0];
			$V = floor($segundos/60);
			break;
		default:
			$E = $a[0];
			$V = 'Menos de 1';
	}
	$p = !is_string($V) && $V > 1 ? 's' : '';
	return "$V $E$p";
}

function erro(int $erro){
	http_response_code($erro);
	exit;
}

function HeaderContent($content){
	header("Content-Type: $content");
}

function HeaderLocation($location){
	header("Location: $location");
}

function show_errors(){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

function json($dados){
	if(is_string($dados)){
		return json_decode($dados,true);
	}else if(is_array($dados)){
		return json_encode($dados,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	}else{
		return false;
	}
}

function getLanguage($package){
	$locale = _SYS_['LOCALE'];
	if(file_exists(__DIR__."/../lang/$locale/$package.json")){
		$json = json(file_get_contents(__DIR__."/../lang/$locale/$package.json"));
		return $json ? $json : array();
	}else{
		return array();
	}
}

function createEncFile($path=__DIR__.'/../server.key'){
  $ref = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/+=';
  $ref = str_split($ref);
  $x = array();
  for($z=0;$z<32;$z++){
    shuffle($ref);
    $x[] = implode('',$ref);
  }
  file_put_contents($path,implode("\n",$x));
}
function encrypt($dado,$pathEncFile=__DIR__.'/../server.key'){
  $dado = base64_encode($dado);
  if(!file_exists($pathEncFile)){
    createEncFile($pathEncFile);
  }
  $chave = file_get_contents($pathEncFile);
  $chave = explode("\n",$chave);
  $k = array();
  while(count($k) < 8){
    $n = rand(1,31);
    if(!in_array($n,$k)){
      $k[] = $n;
    }
  }
  $id = '';
  $pair1 = str_split($chave[0]);
  $dado = str_split($dado);
  foreach($k as $x){
    $id .= str_pad($x,2,'0',STR_PAD_LEFT);
    $pair2 = str_split($chave[$x]);

    foreach($dado as $w=>$c){
      $i = array_search($c,$pair1);
      $dado[$w] = $pair2[$i];
    }
    $pair1 = $pair2;
  }
  $dado = implode('',$dado);

  $dado = $id.$dado;
  return $dado;
}
function decrypt($dado,$pathDecFile=__DIR__.'/../server.key'){
  $chave = file_get_contents($pathDecFile);
  $chave = explode("\n",$chave);
  $k = substr($dado,0,16);
  $dado = substr($dado,16,-1);
  $dado = str_split($dado);
  $k = str_split($k,2);
  foreach ($k as $a => $b) {
    $k[$a] = intval($b);
  }
  for($x=6;$x>-2;$x--){
    $y = $x+1;
    $pair1 = str_split($chave[$k[$y]]);
    if($y == 0){
      $pair2 = str_split($chave[$y]);
    }else{
      $pair2 = str_split($chave[$k[$x]]);
    }
    foreach($dado as $a=>$b){
      $i = array_search($b,$pair1);
      $dado[$a] = $pair2[$i];
    }
  }
  $dado = implode('',$dado);
  return base64_decode($dado);
}
