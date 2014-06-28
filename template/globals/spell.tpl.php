var _ = g_spells;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={' .
            'name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\',' .
            'icon:\''.$data['icon'].'\'' .
        '};';
endforeach;

if ($extra):
    echo '_['.$extra['id'].'].tooltip_'.User::$localeString.' = \''.$extra['tooltip'].'\'';
    echo '_['.$extra['id'].'].buff_'.User::$localeString.' = \''.$extra['buff'].'\'';
    echo '_['.$extra['id'].'].spells_'.User::$localeString.' = {'.json_encode($extra['spells'], JSON_NUMERIC_CHECK).'};';
    echo '_['.$extra['id'].'].buffspells_'.User::$localeString.' = {'.json_encode($extra['buffspells'], JSON_NUMERIC_CHECK).'};';
endif;
?>
