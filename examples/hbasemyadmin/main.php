          <div class="hero-unit">
            <h1>Hello, HBaseMyAdmin!</h1>
            <p>phpMyAdmin是PHP界中相當知名且廣受大眾使用的自由軟體專案，實驗二實作一個能基於SQL語法指令操作HBase資料庫之網頁應用程式雛形：HBaseMyAdmin。</p>
            <p><a class="btn btn-primary btn-large" href="?table=<?php echo $try_it_tbl;?>">Try it now!</a></p>
          </div>
          <div class="row-fluid">
            <div class="span12">
              <h2>Attributes</h2>
              <?php 
              echo $hbase_attr_table;
              ?>
            </div><!--/span-->

          </div><!--/row-->
          <div class="row-fluid">
            <div class="span4">

            </div><!--/span-->
            <div class="span4">

            </div><!--/span-->
            <div class="span4">

            </div><!--/span-->
          </div><!--/row-->