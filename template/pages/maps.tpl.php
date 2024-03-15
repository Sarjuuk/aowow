<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
                <div style="text-align: center">
                    <div class="text">
                        <div style="text-align: center">
                            <select id="maps-ek" onchange="ma_ChooseZone(this)" class="zone-picker" style="margin: 0">
                                <option value="0" style="color: #bbbbbb"><?=Lang::maps('EasternKingdoms'); ?></option>
                            </select>
                            <select id="maps-kalimdor" onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb"><?=Lang::maps('Kalimdor'); ?></option>
                            </select>
                            <select id="maps-outland" onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb"><?=Lang::maps('Outland'); ?></option>
                            </select>
                            <select id="maps-northrend" onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb"><?=Lang::maps('Northrend'); ?></option>
                            </select>
                            <div style="padding-bottom: 4px"></div>
                            <select onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb"><?=Lang::maps('Instances'); ?></option>
                                <optgroup label="<?=Lang::maps('Dungeons'); ?>" id="maps-dungeons"></optgroup>
                                <optgroup label="<?=Lang::maps('Raids'); ?>" id="maps-raids"></optgroup>
                            </select>
                            <select onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb"><?=Lang::maps('More'); ?></option>
                                <optgroup label="<?=Lang::maps('Battlegrounds'); ?>" id="maps-battlegrounds"></optgroup>
                                <optgroup label="<?=Lang::maps('Miscellaneous'); ?>">
                                    <option value="-1"><?=Lang::maps('Azeroth'); ?></option>
                                    <option value="-3"><?=Lang::maps('EasternKingdoms'); ?></option>
                                    <option value="-6"><?=Lang::maps('Kalimdor'); ?></option>
                                    <option value="-2"><?=Lang::maps('Outland'); ?></option>
                                    <option value="-5"><?=Lang::maps('Northrend'); ?></option>
                                    <option value="-4"><?=Lang::maps('CosmicMap'); ?></option>
                                </optgroup>
                            </select>
                        </div>
                        <div id="mapper" style="display: none; width: 778px; margin: 0 auto">
                            <div id="mapper-generic"></div>
                            <div class="pad"></div>
                            <div style="text-align: center; font-size: 13px">
                                <a href="javascript:;" style="margin-right: 2em" id="link-to-this-map"><?=Lang::maps('linkToThisMap'); ?></a>
                                <a href="javascript:;" onclick="myMapper.setCoords([])" onmousedown="return false"><?=Lang::maps('clear'); ?></a>
                            </div>
                        </div>
                        <script type="text/javascript">ma_Init();</script>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>

        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
