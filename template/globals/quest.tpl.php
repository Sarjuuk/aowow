var _ = g_quests;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\'};';
endforeach;
?>
