<?php
function get_db($i=0){
	return _SYS_["DB"][$i];
}
function get_connect(){
	$db_conf = get_db();
	if(@$con = mysqli_connect($db_conf['host'],$db_conf['user'],$db_conf['pass'],$db_conf['db'])){
		mysqli_query($con,"SET NAMES 'utf8'");
		mysqli_query($con,'SET character_set_connection=utf8');
		mysqli_query($con,'SET character_set_client=utf8');
		mysqli_query($con,'SET character_set_results=utf8');
		return $con;
	}else{
		return false;
	}
}

function check_connect(){
	if(!get_connect()){
		echo '<script>alert("Erro ao conectar ao Banco de Dados")</script>';
    	exit;
	}
}
function close_connect(){
	if(mysqli_close(get_connect())){
		return true;
	}else{
		return false;
	}
}
function query($SQL){
	return mysqli_query(get_connect(),$SQL);
}
function get_table_db($data){
	return md5(hash('SHA256',$data.'_c'));
}
function get_ip(){
  $y = array(
    $_SERVER['HTTP_CLIENT_IP'] ?? false,
	  $_SERVER['HTTP_X_FORWARDED_FOR'] ?? false,
	  $_SERVER['HTTP_X_FORWARDED'] ?? false,
	  $_SERVER['HTTP_FORWARDED_FOR'] ?? false,
	  $_SERVER['HTTP_FORWARDED'] ?? false,
	  $_SERVER['REMOTE_ADDR'] ?? false
  );
  $ipaddress = 'UNKNOWN';
  foreach($y as $x){ if($x){ $ipaddress = $x; break;} }
  return $ipaddress;
}




class Config{
	public static function get_config($ID){
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."settings` WHERE `config` = '$ID'");
		return $busca ? $busca['valor'] : false;;
	}
	public static function set_config(string $ID,$Config,bool $json=false){
		if($json && is_array($Config)){
			$Config = json_encode($Config,JSON_UNESCAPED_UNICODE);
		}else{
			$Config = $Config;
		}
		if(query("UPDATE `"._SYS_['prefix_db']."settings` SET `valor` = '$Config' WHERE `config` = '$ID'")){
			return true;
		}else{
			return false;
		}
	}
	public static function set_restricao_de_acesso($DIR,$nivel){
		$array['DIR'] = $DIR;
		$array['acesso'] = $nivel;
		$x = self::get_restricao_de_acesso();
		$x[] = $array;
		$json = json_encode($x);
		if(query("UPDATE `"._SYS_['prefix_db']."settings` SET `valor` = '$json' WHERE `config` = 'acesso_restrito'")){
			return true;
		}else{
			return false;
		}
	}
	public static function get_restricao_de_acesso(){
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."settings` WHERE `config` = 'acesso_restrito'");
		if($busca and !empty($busca['valor'])){
			return json_decode($busca['valor'],true);
		}else{
			return false;
		}
	}
	public static function delete_restricao_de_acesso($index){
		$arr = self::get_restricao_de_acesso();
		if(isset($arr[$index])){
			unset($arr[$index]);
			$json = json_encode($arr);
			if(query("UPDATE `"._SYS_['prefix_db']."settings` SET `valor` = '$json' WHERE `config` = 'acesso_restrito'")){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public static function email_system(){
		return _SYS_['email_system'];
	}
	public static function alt_email_system(string $email){
		if(query("UPDATE `"._SYS_['prefix_db']."settings` SET `valor` = '$email' WHERE `config` = 'email_system'")){
			return true;
		}else{
			return false;
		}
	}
	public static function get_menu($tipo=false){
		$busca = get_data_single("SELECT * FROM `"._SYS_['prefix_db']."settings` WHERE `config` = 'itens_menu'");
		if($busca and !empty($busca['valor'])){
			$array = json_decode($busca['valor'],true);
			foreach($array as $index=>$item){
				if($tipo){
					if($item['tipo'] == $tipo){
						unset($item['tipo']);
						$result[$index] = $item;
					}
				}else{
					$result[$index] = $item;
				}
			}
			return isset($result) ? $result : false;
		}else{
			return false;
		}
	}
	public static function new_item_menu($text,$link,$tipo){
		$busca = self::get_menu();
		if($busca){
			$array['texto'] = FILTRO_nome($text);
			$array['link'] = $link;
			$array['tipo'] = $tipo;
			$busca[count($busca)] = $array;
			if(query("UPDATE `"._SYS_['prefix_db']."settings` SET `valor` = '".json_encode($busca, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."' WHERE `config` = 'itens_menu'")){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public static function excluir_item_menu($texto,$link,$tipo){
		$busca = self::get_menu();
		if($busca){

			foreach($busca as $index=>$item){
				if($item['texto'] == $texto && $item['link'] == $link && $item['tipo'] == $tipo){
					unset($busca[$index]);
				}else{
					$array[] = $item;
				}
			}

			if(query("UPDATE `"._SYS_['prefix_db']."settings` SET `valor` = '".json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."' WHERE `config` = 'itens_menu'")){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public static function InstallExternalSMTP(array $DADOS){
		$SMTPfields = array("user","pass","host","port");
		foreach($DADOS as $a=>$b){
			if(!in_array($a,$SMTPfields)){
				unset($DADOS[$a]);
			}
		}
		if(count($DADOS) <> count($SMTPfields)){
			return false;
		}
		$arq = json($GLOBALS['ConfigFile']);
		if($arq){
			$arq['SMTP'] = $DADOS;
			$arq = json($arq);
			escrever_arquivo(__DIR__."/../local.config.json",$arq,true);
			return true;
		}else{
			return false;
		}
	}
	public static function setLocalConfig($index,$value){
		$a = json($GLOBALS['ConfigFile']);
		if($a){
			$a[$index] = $value;
			if(file_put_contents(__DIR__.'/../local.config.json',json($a))){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public static function set_email_system($email){
		return self::setLocalConfig('email_system',$email);
	}
}

class Log{
	static function novo($text){
		$DB = array();
	  $DB['log'] = $text;
		if(isset($_SESSION['fingerprint'])){
			$DB['fingerprint'] = $_SESSION['fingerprint'];
		}
		insertDB('logs',$DB,'data,ip,hash');
	}
	static function carregarLogs($fromDate=null,$toDate=null){
		$queryParts = array();
		if(!is_null($fromDate)){
			$queryParts[] = "`data` > '$fromDate'";
		}
		if(!is_null($toDate)){
			$queryParts[] = "`data` < '$toDate'";
		}
		if(!empty($queryParts)){
			$SQL = "SELECT * FROM `"._SYS_['prefix_db']."logs` WHERE ".implode(' AND ',$queryParts).' ORDER BY `data` DESC LIMIT 1500';
		}else{
			$SQL = "SELECT * FROM `"._SYS_['prefix_db']."logs` ORDER BY `data` DESC LIMIT 1500";
		}
		return get_data($SQL);
	}
}

class Backup{
	private $db;
	private $tables;
	private $file = array();

	function __construct($db=false){
		if($db){
			$this->db = $db;
		}else{
			$db = get_db();
			$this->db = $db['db'];
		}
		$this->tables = $this->set_tables($this->db);
		$this->set_drop();
		$this->set_creates();
		$this->set_inserts();
	}
	function set_tables($db){
		$tables = get_data("SHOW TABLES FROM $db");
		foreach($tables as $IN => $KY){
			foreach($KY as $AA => $BB){
				$array[$IN]= $BB;
			}
		}
		return $array;
	}
	function get_tables(){
		return $this->tables;
	}
	private function set_creates(){
		$array = array();
		foreach($this->tables as $table){
			$create = get_data_single("SHOW CREATE TABLE `$table`");
			$array[] = $create['Create Table'].";\n";
		}
		$array[]= "\n\n";
		$this->file = array_merge($this->file,$array);
	}
	private function set_inserts(){
		foreach($this->tables as $table){
			$this->make_insert($table);
		}
	}
	private function make_insert($table){
		$busca = get_data("SELECT * FROM `$table`");
		$array = array();
		if($busca){
			foreach($busca as $i){
				$vls = '';
				foreach($i as $f=>$v){
					if(is_string($v)){
						$vls .= "'$v',";
					}else if(is_null($v)){
						$vls .= "NULL,";
					}else{
						$vls .= "$v,";
					}
				}
				$vls = $this->filtro(trim($vls,','));
				$array[] = "INSERT INTO `$table` VALUES ($vls);\n";
			}
			$array[]= "\n\n";
			$this->file = array_merge($this->file,$array);
		}
	}
	private function set_drop(){
		$array = array();
		foreach($this->tables as $table){
			$array[] = "DROP TABLE IF EXISTS `$table`;\n";
		}
		$array[]= "\n\n";
		$this->file = array_merge($this->file,$array);
	}
	function output($path=__DIR__.'/../../DB.sql'){
		$fp = fopen($path,"w+");
		foreach($this->file as $line){
			fwrite($fp,$line);
		}
		fclose($fp);
	}
	private function filtro($str){
		return str_replace('"','\"',$str);
	}
}

class SyncDB{

	private $file;
	private $commands = array();

	function __construct($path=__DIR__.'/../../DB.sql'){
		if(file_exists($path)){
			$this->file = file_get_contents($path);
		}else{
			echo 'Arquivo DB Inexistente!';
			return;
		}
		$this->set_commands();
	}

	private function set_commands(){

		preg_match_all('/(INSERT|DROP|CREATE)[\w\W]{1,}(\;)/U',$this->file,$array);
		foreach($array[0] as $command){
			array_push($this->commands,str_replace(';','',$command));
		}
	}

	function exe(){
		@header("Content-Type: text/plain");
		foreach($this->commands as $SQL){
			if(query($SQL)){
				echo 'ok'."\n";
			}else{
				echo "Erro: ".$SQL."\n";
			}
		}
	}
}


function getListDb(string $table,$filtrosArray=false,$mod='=',$orderBy=false,$pagination=false,int $itens_p_pagina=35,int $pagina_atual=1){
	if($filtrosArray){
		$str = 'WHERE ';
		foreach($filtrosArray as $a=>$b){
			if($mod == 'LIKE'){
				$str .= '`'.$a.'` LIKE \'%'.$b.'%\'***@@***';
			}else{
				$str .= '`'.$a.'` '.$mod.' \''.$b.'\'***@@***';
			}
		}
		$str = trim($str,'***@@***');
		$str = str_replace('***@@***',' AND ',$str);
	}else{
		$str = '';
	}

	$order = '';
	if($orderBy){
		if(strstr($orderBy,' ')){
			$oT = explode(' ',$orderBy)[0];
			$oP = explode(' ',$orderBy)[1];
		}else{
			$oT = $orderBy;
			$oP = '';
		}
		$order = "ORDER BY `$oT` $oP";
	}
	$SQL = "SELECT * FROM `"._SYS_['prefix_db']."$table` $str $order";
	return get_data($SQL,$pagination,$itens_p_pagina,$pagina_atual);
}


function verItemDb($table,$key){
	$WHERE = strlen($key) == strlen(set_token()) ? "WHERE `hash` = '$key'" : "WHERE `ID` = '".intval($key)."'";
	$SQL = "SELECT * FROM `"._SYS_['prefix_db']."$table` $WHERE";
	return get_data_single($SQL);
}
function updateDB(string $table,array $ArrayDados,$key){
	$WHERE = strlen($key) == strlen(set_token()) ? "WHERE `hash` = '$key'" : "WHERE `ID` = ".intval($key);
	$log = array();
	$DESCRIBE = checkFieldsDB($table);

	if(!$DESCRIBE){ return false;}
	foreach($ArrayDados as $field => $valor){
		if(isset($DESCRIBE[$field])){
			$SQL = "UPDATE `"._SYS_['prefix_db']."$table` SET `$field`='$valor' $WHERE";
			// file_put_contents("log.txt",$SQL);
			if(query("UPDATE `"._SYS_['prefix_db']."$table` SET `$field`='$valor' $WHERE")){
				$log[$field] = true;
			}else{
				$log[$field] = false;
			}
		}else{
			$log[$field] = true;
		}
	}

	$false = 0;
	foreach($log as $i){
		if(!$i){ $false++;}
	}
	if($false == count($ArrayDados)){
		$result = false;
	}else if($false == 0){
		$result = true;
	}else{
		$result = $log;
	}
	return $result;
}

function AutoInsert($data){
	switch($data){
		case 'data':
			return date_time_now();
		case 'hash':
			return set_token();
		case 'ip':
			return get_ip();
		default:
			return false;

	}
}
function insertDB(string $table,array $ArrayDados,$Auto=false){
	$AutoHash = false;
	if($Auto){
		$Auto = str_replace(' ','',$Auto);
		if(!empty($Auto)){
			if(strstr($Auto,',')){
				$Autos = explode(',',$Auto);
			}else{
				$Autos = array($Auto);
			}
			if(in_array('hash',$Autos)){
				$AutoHash = true;
			}
			foreach($Autos as $i){
				if(AutoInsert($i)){
					$ArrayDados[$i] = AutoInsert($i);
				}
			}
		}
	}

	$values = '(';
	$campos = '(';
	foreach($ArrayDados as $field => $valor){
		$campos .= "`$field`,";
		$values .= "'$valor',";
	}
	$values = trim($values,',').')';
	$campos = trim($campos,',').')';
	$SQL = "INSERT INTO `"._SYS_['prefix_db']."$table` $campos VALUES $values";

	if(query($SQL)){
		return $AutoHash ? $ArrayDados['hash'] : true;
	}else{
		return false;
	}
}
function checkFieldsDB($table){
	$busca = get_data("DESCRIBE `"._SYS_['prefix_db']."$table`");
	if($busca){
		$dados = array();
		foreach($busca as $i){
			$dados[$i['Field']] = $i['Null'] == 'NO' ? false : true;
		}
		return $dados;
	}else{
		return false;
	}
}
function get_data(string $SQL, bool $Pagination=false, int $Itens_per_page=30, int $CurrentPage=1){
	$query1 = query($SQL);
	if(mysqli_num_rows($query1)){
		while($row_reg = mysqli_fetch_assoc($query1)){
			$fetch1[] = $row_reg;
		}
		if($Pagination){
			$ini = ($CurrentPage - 1) * $Itens_per_page;
			$SQL2 = $SQL." LIMIT $ini,$Itens_per_page";
			$query2 = query($SQL2);
			while($row_reg = mysqli_fetch_assoc($query2)){
				$fetch2[] = $row_reg;
			}
			$result['full'] = $fetch1;
			$result['page'] = $fetch2;

			return $result;
		}else{
			return $fetch1;
		}
	}else{
		return false;
	}
}

function get_data_single(string $SQL){
	$query = query($SQL);
	if(mysqli_num_rows($query)){
		return mysqli_fetch_assoc($query);
	}else{
		return false;
	}
}
function check_pagination(array $array_regs_amount, int $current_page, string $current_GET, int $amount_p_page=30){
	$current_GET = str_replace('?','',$current_GET);
	$current_GET = str_replace('page='.$current_page,'',$current_GET);
	$current_GET = trim($current_GET,'&');
	$current_GET = strstr($current_GET,'=')&&strlen($current_GET) > 1?$current_GET.'&' : '';

	$amount = count($array_regs_amount);
	if($amount > $amount_p_page){
		$pages = $amount / $amount_p_page;
		$total_pages = ceil($pages);
		$content = '<div class="col-12 text-center p-3 mt-2 formAlt">';
		if($current_page > 1){
			$content .= '<a href="'.strstr($_SERVER['REQUEST_URI'],'?',true).'?'.$current_GET.'page='.($current_page-1).'"><button class="float-left">Anterior</button></a>';
		}
		if($current_page < $total_pages){
			$content .='<a href="'.strstr($_SERVER['REQUEST_URI'],'?',true).'?'.$current_GET.'page='.($current_page+1).'"><button class="float-right">Próximo</button></a>';
		}
		$content .= '</div>';

		return $content;

	}else{
		return '';
	}
}

function check_session_pages($DIRETORIO){
	if(get_connect()){
		$conf = new Config;
		$array = $conf->get_restricao_de_acesso();
		$result = '';
		if($array){
			foreach($array as $k => $v){
				if($DIRETORIO == $v['DIR']){
					$result = $v['acesso'];
				}
			}
			if(!empty($result)){
				@include_once(__DIR__."/../session_".$result.".php");
			}
		}else{
			return false;
		}
	}else{
		return false;
	}
}
function check_session_pages_modal($DIRETORIO){
	$conf = new Config;
	$array = $conf->get_restricao_de_acesso();
	$result = '';
	if($array){
		foreach($array as $k => $v){
			if($DIRETORIO == $v['DIR']){
				$result = $v['acesso'];
			}
		}

		if(!empty($result) && !strstr($result,$_SESSION['tipo']) && $_SESSION['tipo']<>'admin'){
			include_once("../includes/acesso-negado.php");
			exit;
		}

	}else{
		return false;
	}
}
