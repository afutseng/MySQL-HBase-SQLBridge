<?php
namespace MySQLMigrationBridge;


class ThriftHBaseClientWrapper {
  protected $_hbaseclient;

  public function __construct(\HbaseClient $hc) {
    $this->_hbaseclient = $hc;
  }

  public function query($query, $params = array()) {

  }

  /**
   * 
   * @param resource $result
   * @return array
   */
  public function fetchArray(&$result) {

  }

  public function select($table, $row, $column = null, $filter = "") {
    $tscan_params = array();
    if (! empty($filter)) {
      MMB::debug( " filter = $filter ## <br>");
      $tscan_params["filterString"] = $filter;
    }
    $scan = new \TScan($tscan_params);
    $scanner = $this->_hbaseclient->scannerOpenWithScan("$table", $scan);

    $res = array();
    $idx = 0;
    while ($row = $this->_hbaseclient->scannerGet($scanner)) {
      $row = $row[0];
      $res[$idx]["rowkey"] = $row->row;

      foreach ($row->columns as $qualifier => $cell) {
        $column_name = preg_replace("/^[\w]+:/", "", $qualifier);
        $res[$idx][$column_name] = $cell->value;
      }

      $idx++;
    }

    return $res;
  }

  public function insert($table, $rowkey, $columns = array(), $values = array()) {
    $cf = $this->getFirstColumnFamilyName($table);
    $size = count($columns);
    $mutations = array();
    for ($i = 0; $i < $size; ++$i) {
      $column = $columns[$i];
      $value  = $values[$i];
      $row = new \Mutation( array(
          "column" => "$cf:$column",
          "value" => "$value"
      ));

      $mutations[] = $row;
    }

    try {
      $this->_hbaseclient->mutateRow($table, $rowkey, $mutations);
    } catch (Exception $e) {
      return 0;
    }
    return array(1);
  }

  public function showTables($mysql_db_name) {
    $result = array();
    foreach($this->_hbaseclient->getTableNames() as $name) {
      array_push($result, array(0 => $name, "Tables_in_{$mysql_db_name}" => $name));
    }

    return $result;
  }

  public function getFirstColumnFamilyName($tableName) {
    $columnfamilies = $this->_hbaseclient->getColumnDescriptors($tableName);
    foreach ( $columnfamilies as $cf ) {
      return str_replace(":", "", $cf->name);
    }
  }

  public function getColumnDescriptors($tableName) {
    return $this->_hbaseclient->getColumnDescriptors($tableName);
  }

  public function deleteAllRow($tableName, $rowkey) {
    return $this->_hbaseclient->deleteAllRow($tableName, $rowkey);
  }

  public function createTable($tableName, $columnfamily_names) {
    $columnfamilies = array();
    if (! is_array($columnfamily_names) && is_string($columnfamily_names)) {
      $columnfamily_names = array($columnfamily_names);
    }
    foreach ($columnfamily_names as $cfname) {
      $columnfamilies[] = new \ColumnDescriptor(array(
        "name" => "$cfname:",
        "maxVersions" => 3,
        "compression" => "NONE",
        "inMemory" => 0,
        "bloomFilterType" => "NONE",
        "bloomFilterVectorSize" => 0,
        "bloomFilterNbHashes" => 0,
        "blockCacheEnabled" => 0,
        "timeToLive" => -1
      ));
    }

    return $this->_hbaseclient->createTable($tableName, $columnfamilies);
  }
} // end of class