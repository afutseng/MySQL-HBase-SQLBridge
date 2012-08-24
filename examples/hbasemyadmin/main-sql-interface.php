<?php
namespace MySQLMigrationBridge;

function transform_img_url($relative_uri) {
  return str_replace("/feature-news/", "http://www.stu.edu.tw/feature-news/", $relative_uri);
}
?>

          <ul class="nav nav-pills">
            <li>
              <a href="?table=<?php echo $uri_param_table;?>">欄位</a>
            </li>
            <li><a href="?table=<?php echo $uri_param_table;?>&amp;func=browse">瀏覽</a></li>
            <li class="active"><a href="?table=<?php echo $uri_param_table;?>&amp;func=sql">SQL</a></li>
          </ul>

          <div class="row-fluid">
            <div class="span12">



<form action="?table=<?php echo $uri_param_table;?>&amp;func=sql&amp;query_post=submit" method="post">
  <legend>使用 SQL 查詢 HBase</legend>
  <div class="row-fluid">
    <div class="span9">
      <?php if (! empty($sql_error)): ?>
      <div class="alert alert-error">
        <p><?php echo $sql_error;?></p>
      </div>
      <?php endif; ?>

      <?php if (isset($user_sql_query_results)): ?>
      <div class="alert alert-success">
        <p>查詢結果筆數：<?php echo count($user_sql_query_results);?> 總計, 查詢花費 <?php echo $query_took_time;?> 秒</p>
      </div>
      <div class="alert alert-info">
        <p>Filter String: <?php echo $user_sql_query_filter;?></p>
      </div>
      <?php endif; ?>

      <?php 
      $textarea_content = "SELECT * FROM $uri_param_table LIMIT 20";
      if (! empty($sql_query)) {
        $textarea_content = $sql_query;
      }
      ?>
      <textarea name="sql_query" rows="10" style="width:98.5%;"><?php echo $textarea_content;?></textarea>

      <div class="form-actions ">
        <button type="submit" class="btn btn-primary">執行</button>

      </div>
    </div> <!-- /span -->

    <div class="span3">
      <p>欄位</p>
      <ul>
      <?php foreach ($table_column_names as $col_name): ?>
      <li><?php echo $col_name;?></li>
      <?php endforeach; ?>
      </ul>

    </div> <!-- /span -->



  </div>
</form>


<?php if (! empty($user_sql_query_results) ): ?>
<table id="attributes_table" class="table table-striped table-hover">
    <thead>
      <tr>
        <?php foreach ($table_column_names as $col_name): ?>
        <th><?php echo $col_name;?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($user_sql_query_results as $row): ?>
      <tr>

        <?php for ($i = 0; $i < mysql_num_fields($user_sql_query_results); ++$i): ?>
        <td><?php $fname = mysql_field_name($user_sql_query_results, $i);
                  echo isset($row[$fname]) ? transform_img_url($row[$fname]) : ""; ?></td>
        <?php endfor; ?>

      </tr>
      <?php endforeach; ?>
    </tbody>

</table>
<?php endif; ?>


            </div><!--/span-->

          </div><!--/row-->
