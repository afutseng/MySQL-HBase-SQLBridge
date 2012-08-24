<?php




namespace MySQLMigrationBridge;
require "mmb.php";

require "thrift/thrift-loader.php";
/*
$socket = new \TSocket( 'carglecloud2', 9090 );
$socket->setSendTimeout( 10000 ); // Ten seconds (too long for production, but this is just a demo ;)
$socket->setRecvTimeout( 20000 ); // Twenty seconds
$transport = new \TBufferedTransport( $socket );
$protocol = new \TBinaryProtocol( $transport );
$client = new \HbaseClient( $protocol );

$transport->open();


$tables = $client->getTableNames();
sort( $tables );
var_dump($tables);*/

header("Content-Type:text/html;charset=UTF-8");
echo "<pre>";

$query = "
SELECT * FROM    `school_status`   WHERE id = 1  
";
echo "query=". $query;
preg_match_all("/SELECT ([\w,\*`\s]+) FROM\s+([\w,`]+)\s+(WHERE ([\w,`\s=']+)*)/i", $query, $matches);
//preg_match_all("/SELECT ([\w,\*`\s]+) FROM\s+([\w,`]+) /i", $query, $matches);
print_r($matches);



/*function mysql_connect($server = "localhost", $username = NULL, 
                       $password = NULL, $new_link = false, $client_flags = 0) {
echo "x";                     
return \mysql_connect($server, $username, $password, $new_link, $client_flags);                    
}*/


function mysql_connect22($server = "localhost", $username = NULL, $password = NULL, $new_link = false, $client_flags = 0) {
  return MMB::connect($server, $username, $password, $new_link, $client_flags);                       
  //return \mysql_connect($server, $username, $password, $new_link, $client_flags);

}

//echo sha1('0', true) ;exit;

/*
$data = "½½½";
echo strlen($data);*/
/*
echo '1'.(print '2') ;*/
error_reporting(E_ALL);
ini_set('display_errors','On');
require "wp-config.php";
$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
$db = mysql_select_db(DB_NAME);
mysql_query("SET NAMES 'utf8'");
//echo "<pre>";
$rs = mysql_query("SELECT * FROM `student`");
//$rs = mysql_query("    SHOW TABLES");
//var_dump($rs);
//print_r($rs);

echo "==============================";
while ($row = mysql_fetch_array($rs)) {
  //echo "inner WHILE".LN;
  print_r($row);
 // echo "inner WHILE".LN;

}



