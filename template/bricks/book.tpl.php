<?php
if (!empty($this->pageText)):
?>
                <div class="clear"></div>
                <h3><?=Lang::item('content'); ?></h3>

                <div id="book-generic"></div>
                <script>//<![CDATA[
                    new Book({ parent: 'book-generic', pages: <?=Util::toJSON($this->pageText); ?>})
                //]]></script>
<?php
endif;
?>
