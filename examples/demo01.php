<?php
namespace MySQLMigrationBridge;
require_once __DIR__."/../bootstrap.php";


define("DB_HOST", "MySQL主機位置");
define("DB_USER", "資料庫使用者");
define("DB_PASSWORD", "MySQL資料庫密碼");
define("DB_NAME", "資料庫名稱");

$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
$db = mysql_select_db(DB_NAME);
mysql_query("SET NAMES 'utf8'");


$rs = mysql_query("SHOW TABLES");
while ($row = mysql_fetch_array($rs)) {
  print_r($row);
}