<?php
namespace MHBridge;

/**
 * mysql_connect
 * 
 * @param resource $result
 * @param int $field_offset
 * @return string
 */
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

