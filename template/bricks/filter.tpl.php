<?php namespace Aowow; ?>

            <script type="text/javascript">//<![CDATA[
<?php if (isset($this->region) && isset($this->realm)): ?>
                pr_setRegionRealm($WH.ge('fi').firstChild, '<?=$this->region; ?>', '<?=$this->realm; ?>');
<?php if ($this->filterObj->values['ra']): ?>
                pr_onChangeRace();
<?php
    endif;
endif;

if ($this->filterObj->fiInit):                                           // str: filter template (and init html form)
    echo "                fi_init('".$this->filterObj->fiInit."');\n";
elseif ($this->filterObj->fiType):                                       // str: filter template (set without init)
    echo "                var fi_type = '".$this->filterObj->fiType."'\n";
endif;

if ($this->filterObj->fiSetCriteria):
    echo '                fi_setCriteria('.mb_substr(Util::toJSON(array_values($this->filterObj->fiSetCriteria)), 1, -1).");\n";
endif;
if ($this->filterObj->fiSetWeights):
    /*
        nt: don't try to match provided weights on predefined weight sets (preselects preset from opt list and ..?)
        ids: weights are encoded as ids, not by their js name and need conversion before use
        stealth: the ub-selector (items filter) will not visually change (so what..?)
    */
    echo '                fi_setWeights('.Util::toJSON($this->filterObj->fiSetWeights).", 0, 1, 1);\n";
endif;
if ($this->filterObj->fiExtraCols):
    echo '                fi_extraCols = '.Util::toJSON(array_values(array_unique($this->filterObj->fiExtraCols))).";\n";
endif;
?>
            //]]></script>
