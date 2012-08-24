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


$uri_param_table = isset($_GET["table"]) ? $_GET["table"] : null;
$uri_param_func = isset($_GET["func"]) ? $_GET["func"] : null;

$uri_param_query_post = isset($_GET["query_post"]) ? $_GET["query_post"] : null;
$colnames_post = isset($_POST["colname"]) ? $_POST["colname"] : null;
$tablenames_post = isset($_POST["tablename"]) ? $_POST["tablename"] : null; 

$act = isset($_GET["act"]) ? $_GET["act"] : null;
if (! empty($act) && $act == "submit") {
echo ";;;;;;;;;;;;;;;;;;;";
  print_r($colnames_post);
  print_r($tablenames_post);

// put 'tbl1', 'row1', 'cf1:column1', 'v1'

$cmd = "create '$tablenames_post', 'mysql'\n";
foreach ($colnames_post as $col) {
  $cmd .= "put '$tablenames_post', 'row1', 'mysql:$col', ''";
}

//file_put_contents("tmp/create-table.txt", $cmd);
//exec("hbase shell < tmp/create-table.txt");

MMB::createTable($tablenames_post, "mysql");
$cols = implode(",", $colnames_post);
$sql = "INSERT INTO $tablenames_post ($cols) VALUES ($cols)";
mysql_query($sql);

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
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">資料表</li>
              <?php 
              foreach ($hbase_tables as $ht): 
              ?>
              <li>
                <a href="index.php?table=<?php echo $ht[0];?>"><i class="icon-list"></i> <?php echo $ht[0];?></a>
              </li>
              <?php endforeach; ?>
              <li class="nav-header">操作管理</li>
              <li class="active">
                <a href="create-table.php"><i class="icon-plus"></i> 建立資料表</a>
              </li>
              <li>
                <a href="import-table.php"><i class="icon-retweet"></i> 匯入資料表</a>
              </li>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->

        <div class="span10">




          <div class="row-fluid">
            <div class="span12">



<form action="?act=submit" method="post">
  <legend>建立資料表</legend>
  <div class="row-fluid">
    <div class="span9">
      <div class="control-group">
      <label>資料表名稱</label>
      <input type="text" name="tablename" placeholder="Type table name">
      </div>


      <div class="control-group">
      <label>欄位組</label>

      <div id="colnames-container">
        <input type="text" name="colname[]" placeholder="Column name here">
      </div>

      </div>

      <div class="form-actions ">
        <button type="submit" class="btn btn-primary">執行</button>
        <button type="button" class="btn btn-info" id="add-more-column">更多欄位</button>
      </div>
    </div> <!-- /span -->

    <div class="span3">


    </div> <!-- /span -->



  </div>
</form>





            </div><!--/span-->

          </div><!--/row-->


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

    <script>
    $("#add-more-column").click(function(){
      $("#colnames-container input[type='text']:last").clone().appendTo('#colnames-container');;
    });
    </script>
  </body>
</html>
