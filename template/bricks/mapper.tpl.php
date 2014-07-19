<?php
if (!empty($this->map['data'])):
    if ($this->map['data']['zone'] < 0):
?>
            <div id="mapper" style="width: 778px; margin: 0 auto">
<?php if (isset($this->map['som'])): ?>
                <div id="som-generic"></div>
<?php endif; ?>
                <div id="mapper-generic"></div>
                <div class="pad clear"></div>
            </div>
<?php else: ?>
            <div class="pad"></div>
<?php if (isset($this->map['som'])): ?>
            <div id="som-generic"></div>
<?php
    endif;
    if (isset($this->map['mapperData'])):
?>
            <div><?php echo Lang::$map['foundIn']; ?> <span id="locations"><?php echo $this->map['mapSelector']; ?>.</span></div>
<?php endif; ?>
            <div id="mapper-generic"></div>
            <div style="clear: left"></div>
<?php endif; ?>

            <script type="text/javascript">//<![CDATA[
<?php if ($this->map['data']['zone'] < 0): ?>
                var g_pageInfo = {id:<?php echo $this->map['data']['zone']; ?>};
<?php elseif (!empty($this->map['mapperData'])): ?>
                var g_mapperData = <?php echo $this->map['mapperData']; ?>;
<?php else: ?>
                var g_mapperData = {<?php echo $this->map['data']['zone']; ?>: {}};
<?php endif; ?>
                var myMapper = new Mapper(<?php echo json_encode($this->map['data'], JSON_NUMERIC_CHECK); /* dont forget to set "'parent' => 'mapper-generic'" */ ?>);
<?php if (!empty($this->map['som'])): ?>
                new ShowOnMap(<?php echo json_encode($this->map['som'], JSON_NUMERIC_CHECK); ?>);
<?php endif; ?>
            //]]></script>
<?php endif; ?>
