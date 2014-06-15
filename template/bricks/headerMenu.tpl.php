<?php
if (User::$id):
    echo '<a id="toptabs-menu-profiles">'.Lang::$main['userProfiles'].'</a>|<a id="toptabs-menu-user">'.User::$displayName.'</a></a>';
else:
    echo '<a href="?account=signin">'.Lang::$main['signIn'].'</a>';
endif;
?>
|<a href="#" id="toplinks-feedback" class="icon-email"><?php echo Lang::$main['feedback']; ?></a>
|<a href="javascript:;" id="toptabs-menu-language"><?php echo Lang::$main['language']; ?><small>&#9660;</small></a>
<script type="text/javascript">g_initHeaderMenus()</script>
