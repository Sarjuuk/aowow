<?php
// link to wowhead
if (isset($this->redButtons[BUTTON_WOWHEAD])):
    if ($this->redButtons[BUTTON_WOWHEAD]):
        echo '<a href="'.$this->wowheadLink.'" rel="np" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>';
    endif;
endif;

// go to my playlist
if (isset($this->redButtons[BUTTON_PLAYLIST])):
    echo '<a href="?sound&playlist" class="button-red"><em><b><i>'.Lang::sound('goToPlaylist').'</i></b><span>'.Lang::sound('goToPlaylist').'</span></em></a>';
endif;

// ingame-links/markdown/ect
if (isset($this->redButtons[BUTTON_LINKS])):
    if ($b = $this->redButtons[BUTTON_LINKS]):
        echo '<a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show('.strtr(Util::toJSON($b, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS), ['"' => "'"]).');"><em><b><i>'.Lang::main('links').'</i></b><span>'.Lang::main('links').'</span></em></a>';
    else:
        echo '<a href="javascript:;" id="open-links-button" class="button-red button-red-disabled"><em><b><i>'.Lang::main('links').'</i></b><span>'.Lang::main('links').'</span></em></a>';
    endif;
endif;

// view in 3D
if (isset($this->redButtons[BUTTON_VIEW3D])):
    if ($b = $this->redButtons[BUTTON_VIEW3D]):             // json_encode puts property names in brackets wich is not cool with inline javascript
        echo '<a href="javascript:;" id="view3D-button" class="button-red" onclick="this.blur(); ModelViewer.show('.strtr(Util::toJSON($b, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS), ['"' => "'"]).')"><em><b><i>'.Lang::main('view3D').'</i></b><span>'.Lang::main('view3D').'</span></em></a>';
    else:
        echo '<a href="javascript:;" id="view3D-button" class="button-red button-red-disabled"><em><b><i>'.Lang::main('view3D').'</i></b><span>'.Lang::main('view3D').'</span></em></a>';
    endif;
endif;

// item comparison tool
if (isset($this->redButtons[BUTTON_COMPARE])):
    if ($b = $this->redButtons[BUTTON_COMPARE]):
        echo '<a href="javascript:;" class="button-red" onclick="this.blur(); su_addToSaved(\''.(isset($b['eqList']) ? $b['eqList'] : $this->typeId).'\', '.(isset($b['qty']) ? $b['qty'] : 1).')"><em><b><i>'.Lang::main('compare').'</i></b><span>'.Lang::main('compare').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('compare').'</i></b><span>'.Lang::main('compare').'</span></em></a>';
    endif;
endif;

// item upgrade search
if (isset($this->redButtons[BUTTON_UPGRADE])):
    if ($b = $this->redButtons[BUTTON_UPGRADE]):
        echo '<a href="javascript:;" class="button-red" onclick="this.blur(); pr_showClassPresetMenu(this, '.$this->typeId.', ',$b['class'].', '.$b['slot'].', event);"><em><b><i>'.Lang::main('findUpgrades').'</i></b><span>'.Lang::main('findUpgrades').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('findUpgrades').'</i></b><span>'.Lang::main('findUpgrades').'</span></em></a>';
    endif;
endif;

// goto: talent calculator
if (isset($this->redButtons[BUTTON_TALENT])):
    if ($b = $this->redButtons[BUTTON_TALENT]):
        echo '<a href="'.$b['href'].'" class="button-red"><em><b><i>'.($b['pet'] ? Lang::main('petCalc') : Lang::main('talentCalc')).'</i></b><span>'.($b['pet'] ? Lang::main('petCalc') : Lang::main('talentCalc')).'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('talentCalc').'</i></b><span>'.Lang::main('talentCalc').'</span></em></a>';
    endif;
endif;

// goto: (sub-)forum
if (isset($this->redButtons[BUTTON_FORUM])):
    if ($b = $this->redButtons[BUTTON_FORUM]):
        echo '<a href="'.$b['href'].'" class="button-red"><em><b><i>'.Lang::main('forum').'</i></b><span>'.Lang::main('forum').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('forum').'</i></b><span>'.Lang::main('forum').'</span></em></a>';
    endif;
endif;

// equip item on pinned character
if (isset($this->redButtons[BUTTON_EQUIP])):
    echo '<div id="equip-pinned-button"></div>'; /* content is added by jScript */
endif;

// resync profiler content
if (isset($this->redButtons[BUTTON_RESYNC])):
    if ($b = $this->redButtons[BUTTON_RESYNC]):
        echo '<a href="javascript:;" class="button-red" onclick="pr_resyncRoster('.$b[0].',\''.$b[1].'\')"><em><b><i>'.Lang::profiler('resync').'</i></b><span>'.Lang::profiler('resync').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::profiler('resync').'</i></b><span>'.Lang::profiler('resync').'</span></em></a>';
    endif;
endif;

// report guide
if (isset($this->redButtons[BUTTON_GUIDE_REPORT])):
    if ($this->redButtons[BUTTON_GUIDE_REPORT]):
        echo '<a href="javascript:;" class="button-red" onclick="a = ContactTool.show.bind(ContactTool, {mode: 6, guide: '.$this->typeId.'}); a();"><em><b><i>'.Lang::main('report').'</i></b><span>'.Lang::main('report').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('report').'</i></b><span>'.Lang::main('report').'</span></em></a>';
    endif;
endif;

// show guide changelog
if (isset($this->redButtons[BUTTON_GUIDE_LOG])):
    if ($this->redButtons[BUTTON_GUIDE_LOG]):
        echo '<a href="?guide=changelog&id='.$this->typeId.'" class="button-red"><em><b><i>'.Lang::main('changelog').'</i></b><span>'.Lang::main('changelog').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('changelog').'</i></b><span>'.Lang::main('changelog').'</span></em></a>';
    endif;
endif;

// edit existing guide
if (isset($this->redButtons[BUTTON_GUIDE_EDIT])):
    if ($this->redButtons[BUTTON_GUIDE_EDIT]):
        echo '<a href="?guide=edit&id='.$this->typeId.'" class="button-red"><em><b><i>'.Lang::main('edit').'</i></b><span>'.Lang::main('edit').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('edit').'</i></b><span>'.Lang::main('edit').'</span></em></a>';
    endif;
endif;

// create new guide
if (isset($this->redButtons[BUTTON_GUIDE_NEW])):
    if ($this->redButtons[BUTTON_GUIDE_NEW]):
        echo '<a href="?guide=new" class="button-red"><em><b><i>'.Lang::main('writeGuide').'</i></b><span>'.Lang::main('writeGuide').'</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>'.Lang::main('writeGuide').'</i></b><span>'.Lang::main('writeGuide').'</span></em></a>';
    endif;
endif;
