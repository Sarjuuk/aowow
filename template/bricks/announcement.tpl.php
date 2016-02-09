<?php
foreach ($this->announcements as $id => $data):
?>
            <div id="announcement-<?=$id; ?>"></div>
            <script type="text/javascript">
                new Announcement(<?=Util::toJSON($data); ?>);
            </script>
<?php
endforeach;
?>