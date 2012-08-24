<?php
namespace MySQLMigrationBridge;

define("LN", "<br>\n");
define("DS", DIRECTORY_SEPARATOR);
function debugln($str) {
  echo $str;
}
function debugdump($str) {
  var_dump($str);
}
require "SQLBridgeException.php";
require "ThriftHBaseClientWrapper.php";
require "thrift/thrift-loader.php";
require 'php-sql-parser.php';
require 'sql-spec.php';

class MMB {
  protected static $config;
  protected static $hbase;
  protected static $parser;
  protected static $select_db;

  private static $_debug_msgs = array();
  private static $fetch_conunt = 0;
  private static $_last_filter = null;

  public function __construct() {}
  public static function connect($params = array()) {
    self::$config = $params;

    // Initialize Thrift connection
    $socket = new \TSocket( $params["server"], 9090 );
    $socket->setSendTimeout( 3000 ); 
    $socket->setRecvTimeout( 10000 );
    $transport = new \TBufferedTransport( $socket );
    $protocol = new \TBinaryProtocol( $transport );
    $client = new \HbaseClient( $protocol );
    $transport->open();

    self::$hbase = new ThriftHBaseClientWrapper($client);
    
  }

  public static function query($query, $params = array()) {
    $default_comparator = "binary";

    if (preg_match("/^\s*SHOW TABLES$/i", $query)) {
      return MMB::showTables();
    }

    $parser = new \PHPSQLParser();
    $parsed = $parser->parse($query);
    $parsed["SQL"] = $query;

    self::debug(print_r($parsed, true));

    if (isset($parsed["SELECT"])) {
        return self::processSelectStatement($parsed);
    }
    else if (isset($parsed["INSERT"], $parsed["VALUES"])) {
        return self::processInsertStatement($parsed);
    }
    else if (isset($parsed["UPDATE"])) {
      print_r($parsed);
      return self::processUpdateStatement($parsed);
    }
    else if (isset($parsed["DELETE"])) {
      return self::processDeleteStatement($parsed);
    }



    if ($params["link_identifier"]) {
      return \mysql_query($query, $params["link_identifier"]);  
    }
    return array("1");
  }

  protected static function processSelectStatement($parsed = array()) {
    $query = $parsed["SQL"];

    // SELECT <select list> FROM <table reference>
    preg_match_all("/SELECT ([\w,\*`\s]+) FROM\s+([\w,`]+)/i", $query, $matches);
    if (! empty($matches[1][0]) && ! empty($matches[2][0])) {
      $column = ($matches[1][0] == "*") ? null : $matches[1][0];
      $column = str_replace("`", "", $column);
      $column = str_replace(" ", "", $column);

      $table = str_replace("`", "", $matches[2][0]);
      $rowkey = "*";

      $filter = "";
      // SQL 有挑選特定欄位就加入 MultipleColumnPrefixFilter
      if ($column != "*" && ! empty($column)) {

        // Convert Col1,Col2 => 'Col1','Col2'
        $column = "'" . str_replace(",", "','", $column) . "'";
        $filter .= "MultipleColumnPrefixFilter ($column) AND ";
      }
    }

    // SELECT <select list> FROM <table reference> WHERE <search condition>
    preg_match_all("/SELECT ([\w,\*`\s]+) FROM\s+([\w,`]+)\s+(WHERE ([\w\W,`\s<=>'\"\-:]+)*)/i", $query, $matches);
    if (! empty($matches[3])) {
      self::debug(print_r($matches, true));


      $condition_str = trim($matches[4][0]);
      //debugdump($condition_str);
      Predicate::parseMultiPredicate($condition_str);
      
      $predicates = Predicate::getPredicates();
      $operators = Predicate::getOperators();

      foreach ($predicates as $predicate_str) {
        $predicate = Predicate::getMappingPredicate($predicate_str);//new \Predicate($condition_str);
        if (! $predicate) {
          throw new SQLBridgeException('查詢子句語意不明! (WHERE clause is ambiguity.)');
        }
        $args = $predicate->toFilterArguments();
        

        $qualifier = $args->qualifier;
        $op = $args->compareOperator;
        $comparator = $args->comparatorType;
        $value = $args->comparatorValue;

        $comparator = (! empty($comparator)) ? $comparator : $default_comparator ;
        $cf = self::$hbase->getFirstColumnFamilyName($table);

        if (! preg_match("/^rowkey|id$/i", $qualifier)) {
          // SQL WHERE 條件子句以 SingleColumnValueFilter 處理
          $filter .= "SingleColumnValueFilter ('$cf', '{$qualifier}', {$op}, '{$comparator}:{$value}', true, false)";
        } else {
          $filter .= "RowFilter ($op, '{$comparator}:{$value}')";
        }

        $filter .= " " . current($operators) . " ";
        next($operators);
      }
    }

    // LIMIT 
    preg_match_all("/LIMIT\s*([\d]+)/i", $query, $matches);
    if (! empty($matches[1][0])) {
      $limit = $matches[1][0];
      if (! empty($filter)) {
        $filter .= " AND ";
      }
      $filter .= "PageFilter ($limit)";
    }
    if (preg_match("/^\s*SELECT /i", $query)) {
      return MMB::select($table, $rowkey, $column, $filter);
    }
  }

  protected static function processInsertStatement($parsed = array()) {
    $record_count = count($parsed["VALUES"]);

    foreach ($parsed["VALUES"] as $parse_values) {
      $columns = array();
      $values = array();

      $size = count($parsed["INSERT"]["columns"]);
      for ($i = 0; $i < $size; ++$i) {
        $column = $parsed["INSERT"]["columns"][$i];
        $col_name = str_replace("`", "", $column["base_expr"]);
        $columns[] = $col_name;

        $value = $parse_values["data"][$i]["base_expr"];
        $value = str_replace("'", "", $value);
        $value = str_replace("NOW", date("Y-m-d H:i:s"), $value);
        $values[] = $value;
      }

      $table = explode(".", $parsed["INSERT"]["table"]);
      $table = str_replace("`", "", $table[count($table) - 1]);
      $rowkey = time();
      self::$hbase->insert($table, $rowkey, $columns, $values);

      // 0.2s
      usleep(200000);
    }
    return $record_count;
  }

  /**
   * Process update sql statement
   * @param array $parsed
   * @return array
   */
  protected static function processUpdateStatement($parsed = array()) {
    // Extract columns & values array
    $columns = array();
    $values = array();
    $size = count($parsed["SET"]);
    for ($i = 0; $i < $size; ++$i) {
      $expression = $parsed["SET"][$i]["sub_tree"];
      $col_name = str_replace("`", "", $expression[0]["base_expr"]);
      $columns[] = $col_name;

      $value = $expression[2]["base_expr"];
      $value = str_replace("'", "", $value);
      $value = str_replace("NOW", date("Y-m-d H:i:s"), $value);
      $values[] = $value;
    }

    // Covert UPDATE statement to SELECT statement
    $sql = preg_replace("/UPDATE /i", "SELECT * FROM ", $parsed["SQL"]);
    $sql = preg_replace("/SET ([\w\W,`\s<=>'\"\-:]*) WHERE/i", "WHERE", $sql);
    $result = self::processSelectStatement(array("SQL" => $sql));

    $table = explode(".", $parsed["UPDATE"][0]["table"]);
    $table = str_replace("`", "", $table[count($table) - 1]);
    foreach ($result as $record) {
      self::$hbase->insert($table, $record["rowkey"], $columns, $values);
    }

    return array(true);
  }

  /**
   * Process delete sql statement
   * @param array $parsed
   * @return array
   */
  protected static function processDeleteStatement($parsed = array()) {
    // Covert to SELECT statement
    $sql = preg_replace("/DELETE /i", "SELECT * ", $parsed["SQL"]);
    $result = self::processSelectStatement(array("SQL" => $sql));

    $table = str_replace("`", "", $parsed["DELETE"]["TABLES"][0]);

    //print_r($result);
    // Deleting every match condition's record
    foreach ($result as $record) {
      //echo "==Deleting=== $table,   " . $record["rowkey"] . "============" . LN;
      self::$hbase->deleteAllRow($table, $record["rowkey"]);
    }
    return array(true);
  }

  /**
   * 
   * @param resource $result
   * @return array
   */
  public static function fetchArray(&$result) {
    if (self::$fetch_conunt < count($result)) {
      self::$fetch_conunt++;
      $ret = current($result);
      $key = key($result);

      next($result);
      return $ret;
    }
    return false;
  }

  public static function fetchAssoc(&$result) {
    if (self::$fetch_conunt < count($result)) {
      self::$fetch_conunt++;
      $ret = current($result);
      $key = key($result);

      next($result);

      $ret_assoc = array();
      foreach ($ret as $k => $v) {
        if (intval($k) === $k) {
          $ret_assoc[] = $v;
        }
      }

      return $ret_assoc;
    }

    return false;
  }

  public static function select($table, $row, $column = null, $filter = null) {
    self::$fetch_conunt = 0;
    self::$_last_filter = "$filter";
    return self::$hbase->select($table, $row, $column, $filter);
  }

  public static function showTables() {
    self::$fetch_conunt = 0;
    return self::$hbase->showTables(self::$select_db);
  }

  public static function createTable($tableName, $columnfamily_names) {
    return self::$hbase->createTable($tableName, $columnfamily_names);  
  }

  public static function debug($msg) {
    array_push(self::$_debug_msgs, $msg);

  }
  public static function getDebugMessages() {
    return self::$_debug_msgs;
  }

  public static function select_db($database) {
    self::$select_db = $database;
    return $database;
  }

  public static function getLastFilter() {
    return self::$_last_filter;
  }
} // end of class


// === mysql_* functions overloading ===
function mysql_connect($server = "localhost", $username = NULL, $password = NULL, $new_link = false, $client_flags = 0) {
  $params = array(
    "server"       => $server,
    "username"     => $username,
    "password"     => $password,
    "new_link"     => $new_link,
    "client_flags" => $client_flags
  );
  return MMB::connect($params);                       
}

function mysql_fetch_array(&$result, $result_type = MYSQL_BOTH) {
  return MMB::fetchArray($result, $result_type);
}

function mysql_fetch_assoc(&$result) {

  return MMB::fetchAssoc($result);
}

function mysql_query($query, $link_identifier = false) {
  try {
    return MMB::query($query, array("link_identifier" => $link_identifier));
  } catch (SQLBridgeException $e) {
    throw new SQLBridgeException($e->getMessage());
  }
}

function mysql_field_name(&$result, $field_offset) {
  $row = $result[0];
  $offset = 0;
  foreach ($row as $col_name => $v) {
    if ($field_offset == $offset) {
      return $col_name;
    }
    $offset++;
  }

}

function mysql_num_fields(&$result) {
  if (is_array($result) && array_key_exists(0, $result)) {
    return count(array_keys($result[0]));
  }
  return FALSE;
}

function mysql_num_rows(&$result) {
  if (is_array($result)) {
    return count($result);
  }
  return FALSE;
}

function mysql_select_db($database_name, $link_identifier = NULL) {
  MMB::select_db($database_name);
  return TRUE;
}