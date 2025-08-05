<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

if ($this->book): ?>
                <div class="clear"></div>
                <h3><?=Lang::item('content'); ?></h3>

                <div id="book-generic"></div>
                <script>//<![CDATA[
                    <?=$this->book; ?>
                //]]></script>
<?php endif; ?>
