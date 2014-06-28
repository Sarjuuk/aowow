var _ = g_items;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={' .
            'name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\',' .
            'icon:\''.$data['icon'].'\'' .
            (isset($data['quality']) ? ', quality:\''.$data['quality'].'\'' : null) .
        '};';
endforeach;

if (isset($extra[$this->typeId])):
    echo '_['.$extra[$this->typeId]['id'].'].tooltip_'.User::$localeString.' = \''.Util::jsEscape($extra[$this->typeId]['tooltip']).'\';';
    echo '_['.$extra[$this->typeId]['id'].'].spells_'.User::$localeString.' = '.json_encode($extra[$this->typeId]['spells'], JSON_NUMERIC_CHECK).';';
endif;
?>
