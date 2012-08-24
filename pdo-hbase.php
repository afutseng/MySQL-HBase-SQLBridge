<?php
namespace MySQLMigrationBridge;


class PDO extends \PDO {
  private $_query_result = array();
  private $_mmb_connect;

  function __construct ($dsn, $username = null, $password = null, $driver_options = array() ) {
    parent::__construct($dsn, $username, $password, $driver_options);

    echo preg_match("/host=([\w\.-]+);/", $dsn, $matches);
    $server = $matches[1];

    $params = array(
      "server"       => $server,
      "username"     => $username,
      "password"     => $password,
      "new_link"     => false,
      "client_flags" => 0
    );
    $this->_mmb_connect = MMB::connect($params); 
    return $this->_mmb_connect;
  }

  function query( $statement ) {
    echo $statement."####################";
    $this->_query_result = MMB::query($statement, array("link_identifier" => false));
    return new PDOStatement($this->_mmb_connect, $this->_query_result);
  }


  /*
bool beginTransaction ( void )
bool commit ( void )
mixed errorCode ( void )
array errorInfo ( void )
int exec ( string $statement )
mixed getAttribute ( int $attribute )
static array getAvailableDrivers ( void )
bool inTransaction ( void )
string lastInsertId ([ string $name = NULL ] )
PDOStatement prepare ( string $statement [, array $driver_options = array() ] )
PDOStatement query ( string $statement )
string quote ( string $string [, int $parameter_type = PDO::PARAM_STR ] )
bool rollBack ( void )
bool setAttribute ( int $attribute , mixed $value )*/
}


class PDOStatement extends \PDOStatement {
  private $_mmb_connect;
  private $_query_result = array();

  function __construct ($dbh, $query_result = array()) {
    $this->_mmb_connect = $dbh;
    $this->_query_result = $query_result;
  }

  function rowCount() {
    if (is_array($this->_query_result)) {
      return count($this->_query_result);
    }
    return FALSE;
  }

  function fetch($fetch_style = \PDO::FETCH_BOTH, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = 0 ) {
    return MMB::fetchArray($this->_query_result, MYSQL_BOTH);

  }
}