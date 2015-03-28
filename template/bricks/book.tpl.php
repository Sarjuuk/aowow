<?php
if (!empty($this->pageText)):
?>
                <div class="clear"></div>
                <h3><?php echo Lang::item('content'); ?></h3>

                <div id="book-generic"></div>
                <script>//<![CDATA[
                    new Book({ parent: 'book-generic', pages: <?php echo json_encode($this->pageText); ?>})
                //]]></script>
<?php
endif;
?>
