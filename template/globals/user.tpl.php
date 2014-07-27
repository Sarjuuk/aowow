        var _ = g_users;
<?php
foreach ($vars as $name => $data):
    echo "        _['".$name."']=".json_encode($data, JSON_NUMERIC_CHECK).';';
endforeach;
?>

