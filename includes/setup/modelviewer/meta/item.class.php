<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * uses:
 * 'Model' -> GameObjectDisplayInfo.db2/FileDataId // is path/string in 335a
 */

class Item extends MetaJSON
{
    protected string $path = 'item';

    private const MODELED_ARMOR = array(
        INVTYPE_HEAD,
        INVTYPE_SHOULDERS,
        INVTYPE_BODY,
        INVTYPE_CHEST,
        INVTYPE_WAIST,
        INVTYPE_LEGS,
        INVTYPE_FEET,
        INVTYPE_WRISTS,
        INVTYPE_HANDS,
        INVTYPE_CLOAK,
        INVTYPE_TABARD,
        INVTYPE_ROBE
    );


    public function __construct(int $displayId, int $invType = INVTYPE_NON_EQUIP)
    {
        return parent::__construct($displayId);

        if (in_array($invType, self::MODELED_ARMOR))
            $this->path = 'armor/'.$invType;
    }

    public function appendData(mixed $modelPath) : void
    {
        /*
         *  "Item": {
         *      "Flags":               ItemDisplayInfo.db2/Flags                0,
         *      "InventoryType":       Item.db2/InventoryType                   17,                     // unclear - one ItemDisplayId could be used by many items
         *      "ItemClass":           Item.db2/ClassID                         2,
         *      "ItemSubClass":        Item.db2/SubClassID                      10,
         *      "HideGeosetMale":     ?ItemDisplayInfo.db2/HelmetGeosetVis0     null,                   // from HelmetGeosetsData.dbc .. needs to be reformatted
         *      "HideGeosetFemale":   ?ItemDisplayInfo.db2/HelmetGeosetVis1     null,
         *      "GeosetGroup":         ItemDisplayInfo.db2/GeosetGroup          [0, 0, 0, 0, 0, 0],     // unsure - 0-2 similarish in ItemDisplayInfo.dbc; 3-5 not in 335a
         *      "AttachGeosetGroup":   ItemDisplayInfo.db2/AttachGeosetGroup    [0, 0, 0, 0, 0, 0],     // unsure - not in 335a
         *      "GeosetGroupOverride": ItemDisplayInfo.db2/GeosetGroupOverride  0,
         *      "ItemVisual":          ItemDisplayInfo.db2/ItemVisual           0,
         *      "ParticleColor":      ?ParticleColor.db2/*                      {Id:, Start:[0, 1, 2], Mid:[0, 1, 2], End[0, 1, 2]} ?? null // see displayId: 656445
         *  },
         */
        $this->Model = $modelPath;
    }
}

?>

https://wow.zamimg.com/modelviewer/live/meta/item/674650.json
{
  "Model": 0,
  "Textures": {
    "2": 147677
  },
  "Textures2": null,
  "TextureFiles": null,
  "ModelFiles": {
    "15929": [
      {
        "FileDataId": 147674,
        "Gender": 2,
        "Class": 0,
        "Race": 0,
        "ExtraData": -1
      }
    ]
  },
  "Item": {
    "Flags": 0,
    "InventoryType": 17,
    "ItemClass": 2,
    "ItemSubClass": 10,
    "HideGeosetMale": null,
    "HideGeosetFemale": null,
    "GeosetGroup": [0, 0, 0, 0, 0, 0],
    "AttachGeosetGroup": [0, 0, 0, 0, 0, 0],
    "GeosetGroupOverride": 0,
    "ItemVisual": 0,
    "ParticleColor": null
  },
  "Creature": null,
  "Character": null,
  "ComponentTextures": null,
  "ComponentModels": {
    "0": 15929
  },
}

https://wow.zamimg.com/modelviewer/wrath/meta/armor/1/34367.json
{
  "Model": 0,
  "Textures": {
    "2": 139644
  },
  "Textures2": null,
  "TextureFiles": null,
  "ModelFiles": {
    "16634": [
      {
        "FileDataId": 139645,
        "Gender": 1,
        "Class": 0,
        "Race": 10,
        "ExtraData": -1
      },
      {
        "FileDataId": 139646,
        "Gender": 0,
        "Class": 0,
        "Race": 10,
        "ExtraData": -1
      },
      {
        "FileDataId": 139647,
        "Gender": 1,
        "Class": 0,
        "Race": 11,
        "ExtraData": -1
      },
      {
        "FileDataId": 139648,
        "Gender": 0,
        "Class": 0,
        "Race": 11,
        "ExtraData": -1
      },
      {
        "FileDataId": 139649,
        "Gender": 1,
        "Class": 0,
        "Race": 3,
        "ExtraData": -1
      },
      {
        "FileDataId": 139650,
        "Gender": 0,
        "Class": 0,
        "Race": 3,
        "ExtraData": -1
      },
      {
        "FileDataId": 139651,
        "Gender": 1,
        "Class": 0,
        "Race": 7,
        "ExtraData": -1
      },
      {
        "FileDataId": 139652,
        "Gender": 0,
        "Class": 0,
        "Race": 7,
        "ExtraData": -1
      },
      {
        "FileDataId": 139653,
        "Gender": 1,
        "Class": 0,
        "Race": 9,
        "ExtraData": -1
      },
      {
        "FileDataId": 139654,
        "Gender": 0,
        "Class": 0,
        "Race": 9,
        "ExtraData": -1
      },
      {
        "FileDataId": 139655,
        "Gender": 1,
        "Class": 0,
        "Race": 1,
        "ExtraData": -1
      },
      {
        "FileDataId": 139656,
        "Gender": 0,
        "Class": 0,
        "Race": 1,
        "ExtraData": -1
      },
      {
        "FileDataId": 139657,
        "Gender": 1,
        "Class": 0,
        "Race": 4,
        "ExtraData": -1
      },
      {
        "FileDataId": 139658,
        "Gender": 0,
        "Class": 0,
        "Race": 4,
        "ExtraData": -1
      },
      {
        "FileDataId": 139659,
        "Gender": 1,
        "Class": 0,
        "Race": 2,
        "ExtraData": -1
      },
      {
        "FileDataId": 139660,
        "Gender": 0,
        "Class": 0,
        "Race": 2,
        "ExtraData": -1
      },
      {
        "FileDataId": 139661,
        "Gender": 1,
        "Class": 0,
        "Race": 5,
        "ExtraData": -1
      },
      {
        "FileDataId": 139662,
        "Gender": 0,
        "Class": 0,
        "Race": 5,
        "ExtraData": -1
      },
      {
        "FileDataId": 139663,
        "Gender": 1,
        "Class": 0,
        "Race": 6,
        "ExtraData": -1
      },
      {
        "FileDataId": 139664,
        "Gender": 0,
        "Class": 0,
        "Race": 6,
        "ExtraData": -1
      },
      {
        "FileDataId": 139665,
        "Gender": 1,
        "Class": 0,
        "Race": 8,
        "ExtraData": -1
      },
      {
        "FileDataId": 139666,
        "Gender": 0,
        "Class": 0,
        "Race": 8,
        "ExtraData": -1
      }
    ]
  },
  "Item": {
    "Flags": 0,
    "InventoryType": 1,
    "ItemClass": 4,
    "ItemSubClass": 3,
    "HideGeosetMale": [
      {
        "RaceId": 1,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 2,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 3,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 4,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 5,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 7,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 8,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 9,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 10,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 11,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 12,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 13,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 14,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 15,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 18,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 7,
        "GeosetGroup": 2,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 6,
        "GeosetGroup": 7,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 11,
        "GeosetGroup": 7,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 16,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 17,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 19,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 20,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 21,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      }
    ],
    "HideGeosetFemale": [
      {
        "RaceId": 1,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 2,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 3,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 4,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 5,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 7,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 8,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 10,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 11,
        "GeosetGroup": 0,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 2,
        "GeosetGroup": 3,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 3,
        "GeosetGroup": 3,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 4,
        "GeosetGroup": 3,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 7,
        "GeosetGroup": 3,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 11,
        "GeosetGroup": 3,
        "RaceBitSelection": 0
      },
      {
        "RaceId": 6,
        "GeosetGroup": 7,
        "RaceBitSelection": 0
      }
    ],
    "GeosetGroup": [
      0,
      0,
      0,
      0,
      0,
      0
    ],
    "AttachGeosetGroup": [
      0,
      0,
      0,
      0,
      0,
      0
    ],
    "GeosetGroupOverride": 0,
    "ItemVisual": 0,
    "ParticleColor": null
  },
  "Creature": null,
  "Character": null,
  "ItemEffects": null,
  "Equipment": null,
  "ComponentTextures": null,
  "ComponentModels": {
    "0": 16634
  },
  "StateKit": null,
  "StateKits": null,
  "Scale": 1
}
