<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * uses:
 * 'ItemEffects' -> ItemVisualsXEffects.db2
 *  {
 *      "Slot": 1,      // inherited from ItemVisuals.dbc colIdx
 *      "SubClass": -1, // DisplayWeaponSubclassID not in 335a
 *      "Model": 12345, // AttachmentModelFileID is string/path in 335a
 *      "kit": null,    // SpellVisualKitID not in 335a
 *      "Scale": 1      // Scale not in 335a
 *  },
 *  here Slot: 1 - 5
 *
 * 'Equipment' -> ItemVisuals.db2
 *      Slot => AttachmentModelFileID // which is a string/path in 335a
 *  here Slot: 0 - 4
 */

class ItemVisual extends MetaJSON
{
    protected string $path = 'itemvisual';

    private $slotIdx = 0;

    public function appendData(mixed $modelPath = '') : void
    {
        $this->slotIdx++;

        if (!$modelPath)
            return;

        $this->ItemEffects[] = array(
            "Slot"     => $this->slotIdx,
            "SubClass" => -1,
            "Model"    => $modelPath,
            "kit"      => null,
            "Scale"    => 1
        );

        $this->Equipment[$this->slotIdx - 1] = $modelPath;
    }
}

?>
