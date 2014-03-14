<?php
/*
$clMasks = [];
$raMasks = [];
$tbl = DB::Aowow()->Select('SELECT * FROM dbc.CharBaseInfo');

foreach ($tbl as $data)
{
    $cl = $data['class'];
    $ra = $data['race'];

    @$clMasks[$ra] |= (1 << ($cl - 1));
    @$raMasks[$cl] |= (1 << ($ra - 1));
}

foreach ($clMasks as $ra => $msk)
    DB::Aowow()->Query('UPDATE ?_races SET classmask = ?d WHERE Id = ?d', $msk, $ra);

foreach ($raMasks as $cl => $msk)
    DB::Aowow()->Query('UPDATE ?_classes SET racemask = ?d WHERE Id = ?d', $msk, $cl);

    DB::Aowow()->Query('UPDATE ?_races SET side = side + 1');
    DB::Aowow()->Query('UPDATE ?_races SET side = 0 WHERE side = 3');
    
    */
// manually add infos about races
// leader, faction, startArea
$info = array(
    null,
    [29611,  72,   12],
    [39605,  76,   14],
    [2784,   47,    1],
    [7999,   96,  141],
    [10181,  68,   85],
    [36648,  81,  215],
    [7937,   54,    1],
    [10540, 530,   14],
    null,
    [16802, 911, 3430],
    [17468, 930, 3524]
);

foreach ($info as $id => $data)
    if ($data)
        DB::Aowow()->query(
            'UPDATE ?_races SET leader = ?d, factionId = ?d, startAreaId = ?d WHERE Id = ?d',
            $data[0],
            $data[1],
            $data[2],
            $id
            )
    
?>