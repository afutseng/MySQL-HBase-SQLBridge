          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">資料表</li>
              <?php 
              
              foreach ($hbase_tables as $ht): 
                $li_class = "";
                if ($uri_param_table === $ht[0]) {
                  $li_class = ' class="active"';
                }
              ?>
              <li<?php echo $li_class;?>>
                <a href="?table=<?php echo $ht[0];?>"><i class="icon-list"></i> <?php echo $ht[0];?></a>
              </li>
              <?php endforeach; ?>
              <li class="nav-header">操作管理</li>
              <li>
                <a href="create-table.php"><i class="icon-plus"></i> 建立資料表</a>
              </li>
              <li>
                <a href="import-table.php"><i class="icon-retweet"></i> 匯入資料表</a>
              </li>
            </ul>
          </div><!--/.well -->