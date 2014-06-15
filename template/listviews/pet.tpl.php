var _ = function(family)
{
	family.foodCount = 0;
	for(var food in g_pet_foods)
	{
		if(family.diet & food)
			family.foodCount++;
	}

	family.spellCount = 0;

	for(var i = 0, len = family.spells.length; i < len; ++i)
	{
		if(family.spells[i])
			family.spellCount++;
	}
};

new Listview({
        template:'pet',
        computeDataFunc: _,
<?php
    echo !isset($params['id'])     ? "id:'hunter-pets',"    : null;
    echo !isset($params['name'])   ? "name:LANG.tab_pets,"  : null;
    echo !isset($params['parent']) ? "parent:'lv-generic'," : null;

    foreach ($params as $k => $v):
        if ($v[0] == '$'):
            echo $k.':'.substr($v, 1).',';
        elseif ($v):
            echo $k.":'".$v."',";
        endif;
    endforeach;
?>
    data:<?php echo json_encode(array_values($data), JSON_NUMERIC_CHECK); ?>
});
