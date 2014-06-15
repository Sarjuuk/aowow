var _ = g_pets;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={icon:\''.Util::jsEscape($data['icon']).'\'};';
endforeach;
?>
