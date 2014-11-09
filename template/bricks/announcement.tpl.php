<?php
foreach ($this->announcements as $id => $data):
    $data = json_encode($data, JSON_NUMERIC_CHECK);
    // json_encode does not recognize variables
    $data = preg_replace('/"\$((.+?)([^\\\\]))"/i', '$1', $data);
?>
            <div id="announcement-<?php echo $id; ?>"></div>
            <script type="text/javascript">
                new Announcement(<?php echo $data; ?>);
            </script>
<?php
endforeach;
?>