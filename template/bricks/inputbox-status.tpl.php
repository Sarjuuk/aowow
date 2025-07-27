<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>
            <div class="pad3"></div>

            <div class="inputbox">
                <h1><?=$head ?? ''; ?></h1>
                <div id="inputbox-error"><?=$error ?? ''; ?></div>
    <?php if ($message ?? ''): ?>
                <div style="text-align: center; font-size: 110%"><?=$message; ?></div>
    <?php else: ?>
                <div class="clear"></div>
    <?php endif; ?>
            </div>
