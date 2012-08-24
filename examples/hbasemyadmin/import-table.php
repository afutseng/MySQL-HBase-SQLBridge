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


$params = array();
foreach ($_POST as $k => $v) {
  $params[$k] = isset($_POST[$k]) ? $_POST[$k] : null;
}
extract($params);


$act = isset($_GET["act"]) ? $_GET["act"] : null;
if (! empty($act) && $act == "submit") {


  $uri = SQOOP_HOST_URI . "?";
  $uri .= http_build_query($params);

  $ret = file_get_contents($uri . http_build_query($params));

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
              <li>
                <a href="create-table.php"><i class="icon-plus"></i> 建立資料表</a>
              </li>
              <li class="active">
                <a href="import-table.php"><i class="icon-retweet"></i> 匯入資料表</a>
              </li>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->

        <div class="span10">

          <?php if (isset($ret) && !empty($ret)): ?>
          <div class="alert alert-success">
            <p>匯入資料表中，請稍後..</p>
          </div>
          <?php endif; ?>

          <div class="alert alert-info">
            <p>使用前請確認環境有 Sqoop 1.4.1+ 版本，以及MySQL JDBC driver jar 檔於/usr/lib/sqoop/lib</p>
          </div>

          <ul class="nav nav-pills">
            <li>
              <a href="?table=<?php echo $uri_param_table;?>">欄位</a>
            </li>
            <li><a href="?table=<?php echo $uri_param_table;?>&amp;func=browse">瀏覽</a></li>
            <li class="active"><a href="?table=<?php echo $uri_param_table;?>&amp;func=sql">SQL</a></li>
          </ul>

          <div class="row-fluid">
            <div class="span12">



<form action="?act=submit" method="post" class="form-horizontal">
  <legend>匯入資料表至 HBase</legend>
  <div class="row-fluid">
    <div class="span9">



      <div class="control-group">
      <label class="control-label">MySQL 主機位置</label>
      <div class="controls"><input type="text" name="db_host" placeholder="hostname/IP"></div>
      </div>

      <div class="control-group">
      <label class="control-label">MySQL 資料庫名稱</label>
      <div class="controls"><input type="text" name="db_name" placeholder="database name" value="test"></div>
      </div>

      <div class="control-group">
      <label class="control-label">MySQL 資料表名稱</label>
      <div class="controls"><input type="text" name="table_name" placeholder="table name"></div>
      </div>

      <div class="control-group">
      <label class="control-label">HBase 資料表名稱</label>
      <div class="controls"><input type="text" name="hbase_table_name" placeholder="HBase table name"></div>
      </div>

      <div class="control-group">
      <label class="control-label">HBase Column Family</label>
      <div class="controls"><input type="text" name="column_family" placeholder="mysql" value="mysql"></div>
      </div>

      <div class="control-group">
      <label class="control-label">MySQL 使用者帳號</label>
      <div class="controls"><input type="text" name="username" placeholder="user" value="root"></div>
      </div>

      <div class="control-group">
      <label class="control-label">MySQL 使用者密碼</label>
      <div class="controls"><input type="password" name="password" placeholder="password"></div>
      </div>

      <div class="form-actions ">
        <button type="submit" class="btn btn-primary">匯入</button>

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
