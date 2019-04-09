<?php

include_once '0_config/config.php';


if(!function_exists("__autoload")) {
	function __autoload($class_name) {
		include_once 'source/' . $class_name . '.php';
	}
}
//	$conn = null;
//	try {
//			$conn = new PDO ( "sqlsrv:server = tcp:e46m6jeaqx.database.windows.net,1433; Database = vmbidder", "vmbidder", "i7P2w4s16G72u!S");
//			$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
//		}
//		catch ( PDOException $e ) {
//			print( "Error connecting to SQL Server." );
//			die(print_r($e));
//		}
//print_r($conn);	
		
//$database = new MDatabase($db['address'], $db['username'], $db['password'], $db['database'], $db['type']);
//
//print_r($database->getError());	
//
//$query = "SELECT * FROM dbo.instancies";
//
//$result = $database->runQuery($query);
//
//echo $database->getError();
//
//print_r($result);
//
//echo '<br>' . Helper::encryptStringArray('3');

echo Helper::encryptStringArray('2') ."</br>";
echo urlencode(Helper::encryptStringArray('2'));

?>
