{if $user.id}<a id="toptabs-menu-profiles">{$lang.userProfiles}</a>|<a id="toptabs-menu-user">{$user.name}</a></a>{else}<a href="?account=signin">{$lang.signIn}</a>{/if}
|<a href="#" id="toplinks-feedback" class="icon-email">{$lang.feedback}</a>
|<a href="javascript:;" id="toptabs-menu-language">{$lang.language} <small>&#9660;</small></a>
<script type="text/javascript">g_initHeaderMenus()</script>
