<?php
set_time_limit(120);

$db_host= $_GET["db_host"];
$db_name = $_GET["db_name"];
$table_name = $_GET["table_name"];
$hbase_table_name = $_GET["hbase_table_name"];
$column_family = $_GET["column_family"];
$username = $_GET["username"];

$import_cmd_format = "sqoop import --connect jdbc:mysql://%s/%s --table %s --hbase-table %s --hbase-create-table --column-family %s --username %s --password %s";

$import_cmd = sprintf($import_cmd_format, $db_host, $db_name, $table_name, $hbase_table_name, $column_family, $username, $password);

$last_line = system($import_cmd, $ret);


