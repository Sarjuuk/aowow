<?php
    namespace Aowow\Template;

    /** @var PageTemplate $this */

    $offset ??= 0;                                          // in case we have multiple icons on the page (prominently quest-rewards)
?>
                <h3><?=$this->mail->renderHeader(); ?></h3>
<?php
    if (!$this->mail->subject->isEmpty()):
?>
                <div class="book"><div class="page"><?=$this->mail->subject; ?></div></div>
<?php
    endif;
?>
                <div class="book" style="float:left; margin-bottom:26px;"><div class="page"><?=$this->mail->body; ?></div></div>
<?php
    echo $this->mail->renderAttachments(16, $offset);
?>
