var _ = g_spells;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={' .
            'name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\',' .
            'icon:\''.$data['icon'].'\'' .
        '};';
endforeach;

if (isset($this->typeId) && !empty($extra[$this->typeId])):
    $x = $extra[$this->typeId];
    echo "\n_[".$x['id'].'].tooltip_'.User::$localeString.' = \''.$x['tooltip']."';";
    echo "\n_[".$x['id'].'].buff_'.User::$localeString.' = \''.$x['buff']."';";
    echo "\n_[".$x['id'].'].spells_'.User::$localeString.' = '.json_encode($x['spells'], JSON_NUMERIC_CHECK).';';
    echo "\n_[".$x['id'].'].buffspells_'.User::$localeString.' = '.json_encode($x['buffspells'], JSON_NUMERIC_CHECK).';';
endif;
?>
