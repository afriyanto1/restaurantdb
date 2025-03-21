<?php // Rememeber to change the username,password and database name to acutal values
$db_host = getenv('DB_HOST');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$db_name = getenv('DB_NAME');

//Create Connection
$link = new mysqli($db_host,$db_user,$db_pass,$db_name);

//Check COnnection
if($link->connect_error){ //if not Connection
die('Connection Failed'.$link->connect_error);//kills the Connection OR terminate execution
}
?>
