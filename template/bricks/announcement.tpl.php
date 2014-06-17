<?php
foreach ($this->announcements as $_):
?>
                <div id="announcement-<?php echo abs($_['id']); ?>"></div>
                <script type="text/javascript">
                    new Announcement({
                        parent: 'announcement-<?php echo abs($_['id']); ?>',
                            id: <?php echo $_['id']; ?>,
                          text: <?php echo $_['text'][0] == '$' ? substr($_['text'], 1) : "'".Util::jsEscape($_['text'])."'" ?>,
                          name: '<?php echo $_['name']; ?>',
                         style: '<?php echo $_['style']; ?>',
                        status: <?php echo $_['status']; ?>,
                          mode: <?php echo $_['mode']; ?>

                    });
                </script>
<?php
endforeach;
?>