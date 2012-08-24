<?php
namespace MySQLMigrationBridge;

require_once "config-inc.php";

function fetch_all($rs) {
  $result = array();
  while ($row = mysql_fetch_array($rs)) {
    $result[] = $row;
  }
  return $result;
}
function fetch_all_assoc($rs) {
  $result = array();
  while ($row = mysql_fetch_assoc($rs)) {
    $result[] = $row;
  }
  return $result;
}

function get_column_names_by_table_name($table) {
  $result_one_row = fetch_all(mysql_query("SELECT * FROM `{$table}` LIMIT 1"));
  $table_column_names = array();
  for ($i = 0; $i < mysql_num_fields($result_one_row); ++$i) {
    $table_column_names[] = mysql_field_name($result_one_row, $i);
  }
  return $table_column_names;
}





$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
$db = mysql_select_db(DB_NAME);
mysql_query("SET NAMES 'utf8'");

