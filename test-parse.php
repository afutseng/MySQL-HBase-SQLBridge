<?php
require 'php-sql-parser.php';

    $parser = new \PHPSQLParser();

$query = "
INSERT INTO test (
`c1` ,
`c2`
)
VALUES (
1, 11
), (
2 , 22
);
";

    $parsed = $parser->parse($query);
    $parsed["SQL"] = $query;

  print_r($parsed);


  echo microtime();