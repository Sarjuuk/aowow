<?php
    namespace Aowow\Template;

foreach ($this->announcements as $a): ?>
            <div id="announcement-<?=$a->id;?>"></div>
            <script type="text/javascript">
                <?=$a;?>
            </script>
<?php endforeach; ?>
