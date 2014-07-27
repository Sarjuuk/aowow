        var _ = g_itemsets;
<?php
foreach ($vars as $id => $data):
    echo '        _['.$id.']={name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\'};';
endforeach;
?>

