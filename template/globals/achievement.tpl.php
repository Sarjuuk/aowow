var _ = g_achievements;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={' .
            'name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\',' .
            'icon:\''.Util::jsEscape($data['icon']).'\'' .
        '};';
endforeach;
?>
