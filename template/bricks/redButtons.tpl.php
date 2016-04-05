<?php
// link to wowhead
if (isset($this->redButtons[BUTTON_WOWHEAD])):
    if ($this->redButtons[BUTTON_WOWHEAD]):
        echo '<a href="'.Util::$wowheadLink.'" rel="np" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>';
    else:
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>';
    endif;
endif;

// ingame-links/markdown/ect
if (isset($this->redButtons[BUTTON_LINKS])):
    if ($b = $this->redButtons[BUTTON_LINKS]):
        echo '<a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({' .
        (isset($b['color']) ? "linkColor: '".$b['color']."', " : null) . (isset($b['linkId']) ? "linkId: '".$b['linkId']."', " : null) .
        "linkName: '".Util::jsEscape(isset($b['name']) ? $b['name'] : $this->name)."', type: ".$this->type.', typeId: '.$this->typeId.' });"><em><b><i>'.Lang::main('links').'</i></b><span>'.Lang::main('links').'</span></em></a>';
    else:
        echo '<a href="javascript:;" id="open-links-button" class="button-red button-red-disabled"><em><b><i>'.Lang::main('links').'</i></b><span>'.Lang::main('links').'</span></em></a>';
    endif;
endif;

// view in 3D
if (isset($this->redButtons[BUTTON_VIEW3D])):
    if ($b = $this->redButtons[BUTTON_VIEW3D]):             // json_encode puts property names in brackets wich is not cool with inline javascript
        echo '<a href="javascript:;" id="view3D-button" class="button-red" onclick="this.blur(); ModelViewer.show('.strtr(Util::toJSON($b, JSON_NUMERIC_CHECK), ['"' => '']).')"><em><b><i>'.Lang::main('view3D').'</i></b><span>'.Lang::main('view3D').'</span></em></a>';
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
        echo '<a href="javascript:;" class="button-red button-red-disabled"><em><b><i>{if $b.pet}'.Lang::main('petCalc').'else:'.Lang::main('talentCalc').'endif;</i></b><span>{if $b.pet}'.Lang::main('petCalc').'else:'.Lang::main('talentCalc').'endif;</span></em></a>';
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
