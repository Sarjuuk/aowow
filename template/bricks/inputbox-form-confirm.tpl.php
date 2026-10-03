<?php
    namespace Aowow\Template;
    use \Aowow\Lang;
    /** @var PageTemplate $this */
?>
            <div class="text">
                <h1><?=$this->escHTML($head);?></h1>
                <form method="post" action="<?=$this->escHTML($action);?>">
                    <input type="hidden" name="key" value="<?=$this->escHTML($key);?>">
                    <button type="submit"><?=Lang::main('submit');?></button>
                    <?=$this->csrfField();?>
                </form>
            </div>
