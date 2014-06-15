var _ = g_gatheredcurrencies;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={' .
            'name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\',' .
            'icon:'.json_encode($data['icon'], JSON_NUMERIC_CHECK) .
        '};';
endforeach;
?>
