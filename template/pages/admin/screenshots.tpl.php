<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');
?>
            <div class="text">
                <h1><?=$this->name; ?></h1>

                  <table>
                    <tr>
                        <td>User: </td>
                        <td colspan="2"><input type="text" id="usermanage" size="23"></td>
                        <td>&raquo;&nbsp;<a href="#" onClick="ss_ManageUser()">Search by User</a></td>
                    </tr>
                    <tr>
                        <td>Page: </td>
                        <td>
                            <select id="pagetype">
<?php
foreach (Type::getFileStringsFor() as $i => $str):
    if (Lang::game($str) && Type::checkClassAttrib($i, 'contribute', CONTRIBUTE_SS)):
        echo "                                <option value=\"".$i."\">".Util::ucFirst(Lang::game($str))."</option>\n";
    endif;
endforeach;
?>
                            </select>
                        </td>
                        <td>#<input type="number" size="6" id="pagetypeid"></td>
                        <td>&raquo;&nbsp;<a href="#" onClick="ss_Manage(null, $('#pagetype').val(), parseInt($('#pagetypeid').val()) || 0)">Search by Page</a></td>
                    </tr>
                </table>
                <hr />
                <table style="width:100%;">
                <thead><tr><th style="width:135px;"><div>Menu</div></th><th style="width:400px;">Pages</th><th>Screenshots: <span id="screenshotTotal"></span></th></tr></thead>
                <tbody><tr>
                    <td id="menu-container" style="vertical-align: top;">
                        <div id="show-all-pages"><?=($this->ssNFound ? ' &ndash; <a href="?admin=screenshots&all">Show All</a> ('.$this->ssNFound.')' : null); ?></div>
                        <h4>Mass Select</h4>
                             &ndash; <a href="#" onClick="ssm_MassSelect(1);">Select All</a><br>
                             &ndash; <a href="#" onClick="ssm_MassSelect(0);">Deselect All</a><br>
                             &ndash; <a href="#" onClick="ssm_MassSelect(-1);">Toggle Selection</a><br>
                             &ndash; <a href="#" onClick="ssm_MassSelect(2);">Select All Pending</a><br>
                             &ndash; <a href="#" onClick="ssm_MassSelect(5);">Select All Unique</a><br>
                             &ndash; <a href="#" onClick="ssm_MassSelect(3);">Select All Approved</a><br>
                             &ndash; <a href="#" onClick="ssm_MassSelect(4);">Select All Sticky</a><br>
                        <div id="withselected" style="display:none;">
                            <h4>Mass Action <b>(0)</b></h4>
                             &ndash; <a href="#" id="massapprove">Approve All</a><br>
                             &ndash; <a href="#" id="massdelete">Delete All</a><br>
                             &ndash; <a href="#" id="masssticky">Sticky All</a><br>
                        </div>
                    </td>
                    <td id="pages-container" style="vertical-align: top;"></td>
                    <td id="data-container" style="vertical-align: top;"><table class="grid" id="theScreenshotsList"><thead><tr>
                        <th>Screenshot</th>
                        <th>Id</th>
                        <th>Caption</th>
                        <th>Date</th>
                        <th>Uploader</th>
                        <th>Status</th>
                        <th>Options</th>
                    </tr></thead></table></td>
                </tr></tbody>
                </table>

                <script type="text/javascript">
                    var hasLoader = false;
                    function ajaxAnchor(el)
                    {
                        if (!el.href || hasLoader)
                            return;

                        $('#withselected').find('h4').append("&nbsp;").append(CreateAjaxLoader());
                        hasLoader = true;

                        new Ajax(el.href, {
                            method: 'get',
                            onSuccess: function(xhr) {
                                hasLoader = false;
                                $('#withselected img').remove();

                                var g = $WH.g_getGets();
                                if (g.type && g.typeid)
                                    ss_Manage(null, g.type, g.typeid);
                                else if (g.user)
                                    ss_ManageUser();
                                else
                                    ss_Refresh();
                            }
                        });
                    }

                    $WH.ge('usermanage').onkeydown = function(e)
                    {
                        e = $WH.$E(e);
                        if (e.keyCode != 13)
                            return;

                        ss_ManageUser();
                    }

                    $WH.ge('pagetypeid').onkeydown = function(e)
                    {
                        e = $WH.$E(e);
                        var validKeys = [8, 9, 13, 35, 36, 37, 38, 39, 40, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 173];
                        if (!e.ctrlKey && $WH.in_array(validKeys, e.keyCode) == -1)
                            return false;

                        if (e.keyCode == 13 && this.value != '')
                            ss_Manage(null, $('#pagetype').val(), parseInt($('#pagetypeid').val()) || 0);

                        return true;
                    }
<?php
if ($this->getAll):
    echo "                    var ss_getAll = true;\n";
endif;
if ($this->ssPages):
    echo "                    var ssm_screenshotPages = ".Util::toJSON($this->ssPages).";\n";
    echo "                    ssm_UpdatePages();\n";
elseif ($this->ssData):
    echo "                    var ssm_screenshotData = ".Util::toJSON($this->ssData).";\n";
    echo "                    ssm_UpdateList();\n";
endif;
?>
                    ss_OnResize();
                </script>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
