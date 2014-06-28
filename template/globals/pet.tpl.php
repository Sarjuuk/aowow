var _ = g_pets;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={icon:\''.$data['icon'].'\'};';
endforeach;
?>
