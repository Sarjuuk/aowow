var _ = g_titles;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\'';
    if (isset($data['namefemale'])):
        echo ', namefemale_'.User::$localeString.':\''.Util::jsEscape($data['namefemale']).'\'';
    endif;
    echo '};';
endforeach;
?>
