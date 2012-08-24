<?php 
namespace MySQLMigrationBridge;

require_once __DIR__."/../../bootstrap.php";


require "common.php";

require "vendor/phpQuery/phpQuery-onefile.php";
$hbase_panel = \phpQuery::newDocumentHTML(file_get_contents("http://" . DB_HOST . ":60010/master-status"));
//print_r($hbase_panel);

$hbase_attr_table = null;
$hbase_panel["table"]
  ->filter(":first")
  ->addClass("table table-striped table-hover")
  ->toReference($hbase_attr_table);

$hbase_tables = fetch_all_assoc(mysql_query("SHOW TABLES"));
$try_it_tbl = $hbase_tables[rand(0, count($hbase_tables) - 1)][0];

$uri_param_table = isset($_GET["table"]) ? $_GET["table"] : null;
$uri_param_func = isset($_GET["func"]) ? $_GET["func"] : null;

$uri_param_query_post = isset($_GET["query_post"]) ? $_GET["query_post"] : null;
$sql_query = isset($_POST["sql_query"]) ? $_POST["sql_query"] : null;
if (! empty($sql_query) && $uri_param_query_post == "submit") {
  try {


    if (preg_match("/\s*SELECT/i", $sql_query) && ! preg_match("/\s+LIMIT\s*([\d]+)/i", $sql_query)) {
      $sql_query .= " LIMIT 500";
    }

    $time_start = microtime(true);

    $rs = mysql_query($sql_query);

    $time_end = microtime(true);
    $query_took_time = $time_end - $time_start;

    $user_sql_query_results = fetch_all($rs);
    $user_sql_query_filter = MMB::getLastFilter();
  } catch (Exception $e) {
    $sql_error = $e->getMessage();
  }
}


?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>HBaseMyAdmin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="index.php">HBaseMyAdmin</a>
          <div class="nav-collapse collapse">
            <p class="navbar-text pull-right">
              Logged in as <a href="#" class="navbar-link">Username</a>
            </p>
            <ul class="nav">
              <li class="active"><a href="index.php">Home</a></li>
              <li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span2">
          <?php include "sidebar.php"; ?>
        </div><!--/span-->

        <div class="span10">
          <?php 
          if (empty($uri_param_table)) {
            include "main.php";  
          } else {
            if (! empty($uri_param_func)) {
              switch ($uri_param_func) {
                case "browse":
                  $query_result = fetch_all(mysql_query("SELECT * FROM `{$uri_param_table}` LIMIT 100"));
                  $table_column_names = array();
                  for ($i = 0; $i < mysql_num_fields($query_result); ++$i) {
                    $table_column_names[] = mysql_field_name($query_result, $i);
                  }

                  include "main-browser-table.php";
                  break;

                case "sql":
                  $table_column_names = get_column_names_by_table_name($uri_param_table);

                  include "main-sql-interface.php";
                  break;
              }



            } else {
              $result_one_row = fetch_all(mysql_query("SELECT * FROM `{$uri_param_table}` LIMIT 1"));
              $table_column_names = array();
              for ($i = 0; $i < mysql_num_fields($result_one_row); ++$i) {
                $table_column_names[] = mysql_field_name($result_one_row, $i);
              }
              include "main-table.php";
            }
          }

           ?>
        </div><!--/span-->
      </div><!--/row-->

      <hr>

<?php include "footer.tpl.php" ?>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap-transition.js"></script>
    <script src="assets/js/bootstrap-alert.js"></script>
    <script src="assets/js/bootstrap-modal.js"></script>
    <script src="assets/js/bootstrap-dropdown.js"></script>
    <script src="assets/js/bootstrap-scrollspy.js"></script>
    <script src="assets/js/bootstrap-tab.js"></script>
    <script src="assets/js/bootstrap-tooltip.js"></script>
    <script src="assets/js/bootstrap-popover.js"></script>
    <script src="assets/js/bootstrap-button.js"></script>
    <script src="assets/js/bootstrap-collapse.js"></script>
    <script src="assets/js/bootstrap-carousel.js"></script>
    <script src="assets/js/bootstrap-typeahead.js"></script>

  </body>
</html>
