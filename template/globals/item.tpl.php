var _ = g_items;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={' .
            'name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\',' .
            'icon:\''.$data['icon'].'\'' .
            (isset($data['quality']) ? ', quality:\''.$data['quality'].'\'' : null) .
        '};';
endforeach;

if (isset($this->typeId) && !empty($extra[$this->typeId])):
    $x = $extra[$this->typeId];
    echo "\n_[".$x['id'].'].tooltip_'.User::$localeString.' = \''.Util::jsEscape($x['tooltip'])."';";
    echo "\n_[".$x['id'].'].spells_'.User::$localeString.' = '.json_encode($x['spells'], JSON_NUMERIC_CHECK).';';
endif;
?>
