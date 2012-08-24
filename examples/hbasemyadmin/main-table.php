<?php
namespace MySQLMigrationBridge;
?>

          <ul class="nav nav-pills">
            <li class="active">
              <a href="?table=<?php echo $uri_param_table;?>">欄位</a>
            </li>
            <li><a href="?table=<?php echo $uri_param_table;?>&amp;func=browse">瀏覽</a></li>
            <li><a href="?table=<?php echo $uri_param_table;?>&amp;func=sql">SQL</a></li>
          </ul>

          <div class="row-fluid">
            <div class="span12">
              <h2>Attributes</h2>


<table id="attributes_table" class="table table-striped table-hover">

<?php foreach ($table_column_names as $col_name): ?>
<tr>
<th><?php echo $col_name;?></th>
</tr>
<?php endforeach; ?>

</table>
            </div><!--/span-->

          </div><!--/row-->
          <div class="row-fluid">

          </div><!--/row-->