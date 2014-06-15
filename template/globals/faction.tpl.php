var _ = g_factions;
{strip}
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\'};';
endforeach;
?>
