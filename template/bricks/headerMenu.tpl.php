<?php
if (User::$id):
    echo '<a id="toplinks-user">'.User::$displayName.'</a>';
else:
    echo '<a href="?account=signin">'.Lang::$main['signIn'].'</a>';
endif;
?>
|<a href="#" id="toplinks-feedback" class="icon-email"><?php echo Lang::$main['feedback']; ?></a>
|<a href="javascript:;" id="toplinks-language"><?php echo Lang::$main['language']; ?></a>
