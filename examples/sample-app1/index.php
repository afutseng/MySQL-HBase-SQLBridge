<?php
//namespace MySQLMigrationBridge;
//namespace MyApp;
namespace MySQLMigrationBridge;
require dirname(__DIR__)."/mmb/bootstrap.php";

// Application
define("DB_HOST", "localhost");
define("DB_USER", "testmysqlapp");
define("DB_PASSWORD", "UwLTyPTjy8bqemX9");
define("DB_NAME", "ecc");
$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
$db = mysql_select_db(DB_NAME);
mysql_query("SET NAMES 'utf8'");

$sql = "SELECT * FROM `student` WHERE gender = 'male'";
$sql = "SELECT age, gender, name FROM `student` WHERE age > 25 LIMIT 1";
$sql = "SELECT * FROM `event` WHERE registration_start = '2011-09-06 15:57:41' LIMIT 10 ";
$sql = "SELECT * FROM `event` WHERE `title` LIKE '%教師%' ORDER BY date_close DESC  ";
/*
$sql = "
INSERT INTO `testmysqlapp`.`event` (`action_id`, `title`, `intro`, `date`, `registration_start`, `date_close`, `guide_unit`, `sponsor_unit`, `assist_unit`, `place`, `object`, `contractor`, `contractor_ext`, `people_limit`, `supply_meal`, `owner`) VALUES (NULL, 'Test event 測試活動', 'This is a test event. 
這是一則測試活動', NOW(), NOW(), NOW(), 'guide_unit', 'sponsor_unit', 'assist_unit', 'place', 'object', 'contractor', '2401', '0', 'y', '1');
";

$sql = "
INSERT INTO `student` (`stu_id`, `age`, `name`, `gender`) 
VALUES (NULL, '26', 'I am chph tseng.', 'male');
";*/
$sql = "SELECT * FROM `student` WHERE gender = 'male'";
$sql = "UPDATE `student` SET name = 'I am chph tseng!', age = 25 WHERE name LIKE '%chph%'";

$rs = mysql_query($sql) or die(mysql_error());
$result = array();
while ($row = mysql_fetch_array($rs)) {
  $result[] = $row;
}
$students = $result;




?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Bootstrap, from Twitter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">Sample PHP Application</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
              <li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">
<pre class="prettyprint linenums">
<?php 
if (class_exists('MMB')) {
  $debugprint = MMB::getDebugMessages();
  isset($debugprint) ? print_r($debugprint) : "";
}



/*
usort($students, function ($a, $b){
      if ($a == $b) {
          return 0;
      }
      return ($a["date_close"] > $b["date_close"]) ? -1 : 1;
});*/

?>
</pre>

      <h1>Sample PHP Application</h1>
      <p>Just a demo php application.</p>

    <table class="table table-striped">
    <thead>
      <tr>
        <?php for ($i = 0; $i < mysql_num_fields($students); ++$i): ?>
        <th>#<?php echo mysql_field_name($students, $i);?></th>
        <?php endfor; ?>
      </tr>
    </thead>
    <?php foreach ($students as $stu): ?>
    <tr>

      <?php for ($i = 0; $i < mysql_num_fields($students); ++$i): ?>
      <td><?php echo $stu[mysql_field_name($students, $i)]; ?></td>
      <?php endfor; ?>
<!--
        <td><?php print_r($stu);//echo $stu["stu_code"]; ?></td>
      <td><?php echo $stu["title"];//$stu["name"]; ?></td>
      <td><?php echo $stu["date"];//$stu["gender"]; ?></td>
      <td><?php echo $stu["place"];//$stu["age"]; ?></td>
-->
    </tr>
    <?php endforeach; ?>
    </table>


    </div> <!-- /container -->

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
