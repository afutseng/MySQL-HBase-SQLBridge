<?php
namespace MySQLMigrationBridge;

?>

          <ul class="nav nav-pills">
            <li>
              <a href="?table=<?php echo $uri_param_table;?>">欄位</a>
            </li>
            <li class="active"><a href="?table=<?php echo $uri_param_table;?>&amp;func=browse">瀏覽</a></li>
            <li><a href="?table=<?php echo $uri_param_table;?>&amp;func=sql">SQL</a></li>
          </ul>

          <div class="row-fluid">
            <div class="span12">




<table id="attributes_table" class="table table-striped table-hover">
    <thead>
      <tr>
        <?php foreach ($table_column_names as $col_name): ?>
        <th><?php echo $col_name;?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($query_result as $row): ?>
      <tr>
        <?php for ($i = 0; $i < mysql_num_fields($query_result); ++$i): ?>
        <td><?php $fname = mysql_field_name($query_result, $i);
                  echo isset($row[$fname]) ? $row[$fname] : ""; ?></td>
        <?php endfor; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>

</table>



            </div><!--/span-->

          </div><!--/row-->
