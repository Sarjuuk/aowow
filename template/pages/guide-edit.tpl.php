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
<?php
    $this->brick('article');
?>
            </div>
            <div class="pad"></div>
<!-- start insert -->
<div class="adjacent-preview">
    <div class="adjacent-preview-edit">
        <form id="guide-form" method="post" action="?guide=edit&id=<?=$this->typeId;?>" onsubmit="leavePage(1)">
            <table class="responsive-collapse guide-form-main">
                <tr class="guide-form-guide-link">
                    <td colspan="2"><h2 style="margin:0" class="heading-size-2"><a href="?guide=<?=$this->typeId;?>" target="_blank"><?=$this->editorFields('title');?></a></h2></td>
                </tr>

                <tr>
                    <th><label for="title"><dfn title="<?=Lang::guide('editor', 'fullTitleTip');?>"><?=Lang::guide('editor', 'fullTitle');?></dfn></label></th>
                    <td>
                        <input required="required" type="text" maxlength="100" name="title" id="title"
                               value="<?=$this->editorFields('title');?>"
                               placeholder="<?=Lang::guide('editor', 'fullTitleTip');?>"
                               data-charwarning="title-char-warning">
                        <small id="title-char-warning" class="char-warning"></small>
                    </td>
                </tr>


                <tr>
                    <th><label for="name"><dfn title="<?=Lang::guide('editor', 'nameTip');?>"><?=Lang::guide('editor', 'name');?></dfn></label></th>
                    <td>
                        <input required="required" type="text" maxlength="100" name="name" id="name"
                               value="<?=$this->editorFields('name');?>"
                               placeholder="<?=Lang::guide('editor', 'nameTip');?>"
                               data-charwarning="name-char-warning">
                        <small id="name-char-warning" class="char-warning"></small>
                    </td>
                </tr>


                <tr>
                    <th><label for="locale"><?=Lang::main('language');?></label></th>
                    <td><select name="locale" id="locale" required="required" size="1">
<?php
foreach (Lang::locales() as $i => $l):
    if (Cfg::get('LOCALES') & (1 << $i))
        echo '                    <option value="'.$i.'"'.($this->editorFields('locale', true) == $i ? ' selected="selected"' : '').'>'.$l."</option>\n";
endforeach;
?>
                    </select></td>
                </tr>


                <tr>
                    <th><label for="category"><?=Lang::guide('editor', 'category');?></label></th>
                    <td>
                        <select id="category" name="category" required="required"><option></option>
<?php
foreach (Lang::guide('category') as $i => $c):
    if ($c)
        echo '                            <option value="'.$i.'"'.($this->editorFields('category', true) == $i ? ' selected="selected"' : '').'>'.$c."</option>\n";
endforeach;
?>
                        </select>
                        <script>
                            (function() {
                                var specCategoryIds = [1];
                                var setCategoryId = function() {
                                    var $this = $(this);
                                    $this.closest('form').attr('data-category', $this.val())
                                         .attr('data-spec-category', $WH.in_array(specCategoryIds, $this.val()) >= 0 ? 'true' : 'false');
                                };
                                var $categorySelect = $('#category');
                                $categorySelect.change(setCategoryId);
                                setCategoryId.call($categorySelect.get(0));
                                setTimeout(setCategoryId.bind($categorySelect.get(0)), 500);
                            })();
                        </script>
                    </td>
                </tr>


                <tr id="class-guide-specialization-options">
                    <th><label for="specId"><?=Lang::guide('editor', 'class-spec');?></label></th>
                    <td>
                        <input name="specId" id="specId" type="hidden" value="<?=$this->editorFields('specId');?>">
                        <input name="classId" id="classId" type="hidden" value="<?=$this->editorFields('classId');?>">
                        <script>
                            setTimeout(function() {
                                // const PC = WH.Wow.PlayerClass;
                                // const Spec = WH.Wow.PlayerClass.Specialization;

                                let classes = [];
                                $.each(g_chr_classes, function (classId, className) {
                                    classes.push({id: classId, name: className});
                                });

                                let specOptionsMenu = [
                                    [0, LANG.finone, setClassSpec.bind(null, 0, -1), {
                                        isChecked: function() {
                                            return $WH.ge('specId').value == -1;
                                        }
                                    }]
                                ];

                                /**
                                 * Set the guide as being for the given class and spec.
                                 *
                                 * @param {number}  classId
                                 * @param {number}  [specId]
                                 * @param {boolean} [initialSetup]
                                 */
                                function setClassSpec(classId, specId, initialSetup) {
                                    if (typeof specId !== 'number') {
                                        specId = -1;
                                    }

                                    $WH.ge('specId').value = specId;
                                    $WH.ge('classId').value = classId;

                                    // Update the widget text.
                                    let widget = $WH.ge('options-menu-widget-spec-id');
                                    $WH.ee(widget);
                                    $WH.st(
                                        widget,
                                        (
                                            specId > -1 &&  g_chr_specs[classId][specId] ||
                                            classId && g_chr_classes[classId] ||
                                            LANG.finone
                                        ) + ' '
                                    );
                                    $WH.ae(widget, $WH.ce('i', {className: 'q0'}));
                                    widget.className = 'options-menu-widget options-menu-widget-spec-id c' + classId;

                                    // Add the spec/class icon.
                                    let iconName = specId > -1 ? g_file_specs[classId][specId] : (classId ? 'class_' + g_file_classes[classId] : 'inv_misc_questionmark');
                                    $WH.aef(widget, $WH.ce('span', { style: { display: 'inline-block', marginRight: '3px', verticalAlign: 'middle' }
                                    }, Icon.create(iconName, 0, null, null, null, null, null, null, true)));
                                }

                                for (var y = 0, classs; classs = classes[y]; y++) {
                                    var specMenu = [];

                                    var specIds = [];
                                    $.each(g_chr_specs[classs.id], function (classSpecId, classSpecName) {
                                        specIds.push(classSpecId);
                                        specMenu.push([classSpecId, classSpecName, setClassSpec.bind(null, classs.id, classSpecId), null, {
                                            tinyIcon: g_file_specs[classs.id][classSpecId],
                                            checkedFunc: (function(specId) {
                                                return $WH.ge('specId').value == specId;
                                            }).bind(null, classSpecId)
                                        }]);
                                    });

                                    specOptionsMenu.push([classs.id, classs.name, setClassSpec.bind(null, classs.id), {
                                        tinyIcon: 'class_' + g_file_classes[classs.id],
                                        className: 'c' + classs.id,
                                        menu: specMenu,
                                        isChecked: (function(specIds) {
                                            return specIds.indexOf($WH.ge('specId').value) > -1;
                                        }).bind(null, specIds)
                                    }]);
                                }

                                $WH.createOptionsMenuWidget('spec-id', "None", {
                                    target: $('#class-guide-specialization-options td:last-child'),
                                    options: specOptionsMenu,
                                    noChevron: true
                                });

                                setTimeout(function() {
                                    let specId = parseInt($WH.ge('specId').value) || 0;
                                    let classId = parseInt($WH.ge('classId').value) || 0;
                                    if (specId) {
                                        // classId = PC.getBySpec(specId);
                                    }
                                    if (classId) {
                                        setClassSpec(classId, specId, true);
                                    }
                                }, 500);
                            }, 5);
                        </script>
                    </td>
                </tr>


                <tr>
                    <th><label for="description">
                        <dfn title="<?=Lang::guide('editor', 'descriptionTip');?>"><?=Lang::guide('editor', 'description');?></dfn></label></th>
                    <td colspan="3">
                        <textarea rows="1" name="description" cols="100" id="description" style="height:69px"
                                  placeholder="<?=Lang::guide('editor', 'descriptionTip');?>"
                                  ><?=$this->editorFields('description');?></textarea>
                        <script>g_enhanceTextarea('#description')</script>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3"><span id="desc-info"></span></td>
                </tr>

<?php
/*
                <tr>
                    <th>
                        <label for="comment-emails">
                            <dfn title="<?=Lang::guide('editor', 'commentEmailTip');?>"><?=Lang::guide('editor', 'commentEmail');?></dfn>
                        </label>
                    </th>
                    <td colspan="3">
                        <input type="radio" name="comment-emails" value="0" checked> <?=Lang::main('no');?><input type="radio" name="comment-emails" value="1"> <?=Lang::main('yes');?></td>
                </tr>
*/
?>

                <tr>
                    <th><?=Lang::main('status');?></th>
                    <td colspan="3"><dfn title="<?=Lang::guide('editor', 'statusTip', $this->editorFields('status'));?>" style="color:<?=Guidelist::STATUS_COLORS[$this->editorFields('status')];?>"><?=Lang::guide('status', $this->editorFields('status'));?></dfn>
<?php
if ($this->editorFields('status') == GUIDE_STATUS_DRAFT):
    echo '<small>(<a href="?guide='.$this->typeId.'&rev='.$this->editorFields('rev').'" target="_blank" class="q1">'.Lang::guide('editor', 'testGuide')."</a>)</small>\n";
endif;
?>
                    </td>
                </tr>


                <tr>
                    <th><?=Lang::guide('editor', 'images');?></th>
                    <td colspan="3"><div id="image-upload"></div><div id="upload-progress" style="width:110px"></div><div id="upload-result"></div></td>
                </tr>

            </table>

            <div class="adjacent-preview-controls">
                <label><input class="adjacent-preview-checkbox" type="checkbox"> <?=Lang::guide('editor', 'showAdjPrev');?></label>
            </div>

            <div class="guide-edit-section">

                <textarea
                    name="body"
                    id="editBox"
                    class="guide-edit-box"
                    onclick="leavePage()"
                    onkeydown="updatePreview(false, this)"
                    onkeyup="updatePreview(false, this)"
                    onchange="updatePreview(false, this)"
                    rows="8"
                    cols="40"
                    style="width:95%"><?=$this->editorFields('text');?></textarea>
                <script>
                    g_enhanceTextarea('#editBox', {
                        markup: true,
                        scrollingMarkup: true,
                        dynamicSizing: false,
                        dynamicResizeOption: true
                    });
                </script>
            </div>

            <div class="guide-submission">
                <div class="guide-submission-options">
                    <button type="button" class="btn btn-site" data-type="save" onclick="$('.guide-submission').attr('data-type', 'save'); $('#changelog').focus();"><?=Lang::guide('editor', 'save');?></button>
                    <button type="button" class="btn btn-site" data-type="submit" onclick="$('.guide-submission').attr('data-type', 'submit'); $('#changelog').focus();"><?=Lang::guide('editor', 'submit');?></button>
                </div>
                <div class="guide-submission-changelog">
                    <h2 class="heading-size-4"><?=Lang::guide('editor', 'changelog');?></h2>
                    <textarea name="changelog" id="changelog" onclick="leavePage()" onkeydown="updatePreview(false, this)" onkeyup="updatePreview(false, this)" onchange="updatePreview(false, this)" rows="6" cols="40" style="min-height:52px; width:95%" placeholder="<?=Lang::guide('editor', 'changelogTip');?>" required></textarea>
                    <script>g_enhanceTextarea('#changelog');</script>
                    <button type="submit" name="save" class="guide-submission-changelog-save"><?=Lang::guide('editor', 'save');?></button>
                    <button type="submit" name="submit" class="guide-submission-changelog-submit"><?=Lang::guide('editor', 'submit');?></button>
                </div>
            </div>

            <img src="<?=Cfg::get('STATIC_URL');?>/images/icons/ajax.gif" style="display:none" class="spinning-circle">
            <span id="save-status"></span>
        </form>
    </div>

    <div class="adjacent-preview-preview">
        <h2 class="heading-size-2"><?=Lang::guide('editor', 'preview');?> &nbsp; <label style="font-size:75%"><input id="previewupdate" type="checkbox" checked="checked" onchange="setTimeout((function() {
                if (this.checked) {
                    updatePreview(true);
                }
                updateQfPreview();
            }).bind(this), 50)"><?=Lang::guide('editor', 'autoupdate');?></label></h2>
        <div id="guide-body"><div id="livePreview" style="margin-right:10px"></div></div>
        <script>updatePreview(true);</script>
    </div>
</div>
<script>$WH.AdjacentPreview.init()</script>
<!-- end insert -->
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
