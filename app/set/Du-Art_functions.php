<?php
include_once(__DIR__."/lib/default.php");
foreach(_SYS_['modules'] as $dep){
	if($dep <> "default"){
		include_once(__DIR__."/lib/$dep.php");
	}
}
