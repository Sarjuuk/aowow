{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">
{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}
            <div class="text">
                <div style="text-align: center">
                    <script type="text/javascript">g_initPath([1,1])</script>
                    <div class="text">
                        <div style="text-align: center">
                            <select id="maps-ek" onchange="ma_ChooseZone(this)" class="zone-picker" style="margin: 0">
                                <option value="0" style="color: #bbbbbb">{$lang.EasternKingdoms}</option>
                            </select>
                            <select id="maps-kalimdor" onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb">{$lang.Kalimdor}</option>
                            </select>
                            <select id="maps-outland" onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb">{$lang.Outland}</option>
                            </select>
                            <select id="maps-northrend" onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb">{$lang.Northrend}</option>
                            </select>
                            <div style="padding-bottom: 4px"></div>
                            <select onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb">{$lang.Instances}</option>
                                <optgroup label="{$lang.Dungeons}" id="maps-dungeons"></optgroup>
                                <optgroup label="{$lang.Raids}" id="maps-raids"></optgroup>
                            </select>
                            <select onchange="ma_ChooseZone(this)" class="zone-picker">
                                <option value="0" style="color: #bbbbbb">{$lang.More}</option>
                                <optgroup label="{$lang.Battlegrounds}" id="maps-battlegrounds"></optgroup>
                                <optgroup label="{$lang.Miscellaneous}">
                                    <option value="-1">{$lang.Azeroth}</option>
                                    <option value="-3">{$lang.EasternKingdoms}</option>
                                    <option value="-6">{$lang.Kalimdor}</option>
                                    <option value="-2">{$lang.Outland}</option>
                                    <option value="-5">{$lang.Northrend}</option>
                                    <option value="-4">{$lang.CosmicMap}</option>
                                </optgroup>
                            </select>
                        </div>
                        <div id="mapper" style="display: none; width: 778px; margin: 0 auto">
                            <div id="mapper-generic"></div>
                            <div class="pad"></div>
                            <div style="text-align: center; font-size: 13px">
                                <a href="javascript:;" style="margin-right: 2em" id="link-to-this-map">{$lang.linkToThisMap}</a>
                                <a href="javascript:;" onclick="myMapper.setCoords([])" onmousedown="return false">{$lang.clear}</a>
                            </div>
                        </div>
                        <script type="text/javascript">ma_Init();</script>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>

{include file='footer.tpl'}
