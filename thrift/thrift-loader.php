<?php
$GLOBALS['THRIFT_ROOT'] = __DIR__."/src";

require_once( $GLOBALS['THRIFT_ROOT'].'/Thrift.php' );

require_once( $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php' );
require_once( $GLOBALS['THRIFT_ROOT'].'/transport/TBufferedTransport.php' );
require_once( $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php' );

# According to the thrift documentation, compiled PHP thrift libraries should
# reside under the THRIFT_ROOT/packages directory.  If these compiled libraries 
# are not present in this directory, move them there from gen-php/.  
require_once( $GLOBALS['THRIFT_ROOT'].'/packages/Hbase/Hbase.php' );