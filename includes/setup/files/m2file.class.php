<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class M2File extends BinaryFile
{
    private const /* string */ MAGIC       = 'MD20';
    private const /* int    */ VERSION     = 264;
    private const /* int    */ HEADER_SIZE = 126;           // +8 if textureCombinerCombos is used

    public readonly string $name;

    public readonly M2Array $ofsGlobalLoops;
    public readonly M2Array $ofsSequences;
    public readonly M2Array $ofsSequenceIdxHashById;
    public readonly M2Array $ofsBones;
    public readonly M2Array $ofsBoneIndicesById;
    public readonly M2Array $ofsVertices;
    public readonly UInt32  $nSkinProfiles;
    public readonly M2Array $ofsColors;
    public readonly M2Array $ofsTextures;
    public readonly M2Array $ofsTextureWeights;
    public readonly M2Array $ofsTextureTransforms;
    public readonly M2Array $ofsTextureIndicesById;
    public readonly M2Array $ofsMaterials;
    public readonly M2Array $ofsBoneCombos;
    public readonly M2Array $ofsTextureCombos;
    public readonly M2Array $ofsTextureCoordCombos;
    public readonly M2Array $ofsTextureWeightCombos;
    public readonly M2Array $ofsTextureTransformCombos;
    public readonly CAxisAlignedBox $boundingBox;
    public readonly Double  $boundingSphereRadius;
    public readonly CAxisAlignedBox $collisionBox;
    public readonly Double  $collisionSphereRadius;
    public readonly M2Array $ofsCollisionIndices;
    public readonly M2Array $ofsCollisionPositions;
    public readonly M2Array $ofsCollisionFaceNormals;
    public readonly M2Array $ofsAttachments;
    public readonly M2Array $ofsAttachmentIndicesById;
    public readonly M2Array $ofsEvents;
    public readonly M2Array $ofsLights;
    public readonly M2Array $ofsCameras;
    public readonly M2Array $ofsCameraIndicesById;
    public readonly M2Array $ofsRibbonEmitters;
    public readonly M2Array $ofsParticleEmitters;
    public readonly M2Array $ofsTextureCombinerCombos;

    private array $skins       = [];
    private array $extTextures = [];

    public function __construct(string $file)
    {
        parent::__construct($file);

        if ($this->read(4) != self::MAGIC)
        {
            $this->error = 'file '.$file.' has incorrect magic bytes';
            $this->close();
            $this->__destruct();
            return;
        }

        if ($this->readUInt32()->unpack() != self::VERSION)
        {
            $this->error = 'file '.$file.' has incorrect version';
            $this->close();
            return;
        }

        if ($this->filesize < strlen(self::MAGIC) + self::HEADER_SIZE)
        {
            $this->error = 'file '.$file.' too small for a m2';
            $this->close();
            return;
        }

        $this->name = $this->getStringFromOffset(...array_values(unpack('V2', $this->read(8))));

        $this->ffwd(4);                                     // skip global flags

        $this->ofsGlobalLoops            = new M2Array($this);
        $this->ofsSequences              = new M2Array($this);
        $this->ofsSequenceIdxHashById    = new M2Array($this);
        $this->ofsBones                  = new M2Array($this);
        $this->ofsBoneIndicesById        = new M2Array($this);
        $this->ofsVertices               = new M2Array($this);
        $this->nSkinProfiles             = $this->readUInt32();
        $this->ofsColors                 = new M2Array($this);
        $this->ofsTextures               = new M2Array($this);
        $this->ofsTextureWeights         = new M2Array($this);
        $this->ofsTextureTransforms      = new M2Array($this);
        $this->ofsTextureIndicesById     = new M2Array($this);
        $this->ofsMaterials              = new M2Array($this);
        $this->ofsBoneCombos             = new M2Array($this);
        $this->ofsTextureCombos          = new M2Array($this);
        $this->ofsTextureCoordCombos     = new M2Array($this);
        $this->ofsTextureWeightCombos    = new M2Array($this);
        $this->ofsTextureTransformCombos = new M2Array($this);
        $this->boundingBox               = new CAxisAlignedBox($this);
        $this->boundingSphereRadius      = $this->readFloat();
        $this->collisionBox              = new CAxisAlignedBox($this);
        $this->collisionSphereRadius     = $this->readFloat();
        $this->ofsCollisionIndices       = new M2Array($this);
        $this->ofsCollisionPositions     = new M2Array($this);
        $this->ofsCollisionFaceNormals   = new M2Array($this);
        $this->ofsAttachments            = new M2Array($this);
        $this->ofsAttachmentIndicesById  = new M2Array($this);
        $this->ofsEvents                 = new M2Array($this);
        $this->ofsLights                 = new M2Array($this);
        $this->ofsCameras                = new M2Array($this);
        $this->ofsCameraIndicesById      = new M2Array($this);
        $this->ofsRibbonEmitters         = new M2Array($this);
        $this->ofsParticleEmitters       = new M2Array($this);
        $this->ofsTextureCombinerCombos  = new M2Array($this);

        for ($i = 0; $i < $this->nSkinProfiles->unpack(); $i++)
        {
            // Modelname0$i.skin
            $this->skins[] = new SkinFile(strtok($file, '.') . '0' . $i . '.skin');
            break;                                          // only using one level of detail
        }
    }

    public function getStringFromOffset(int $length, int $offset) : ?string
    {
        return $this->readOffset($length, $offset);
    }

    public function writeMO3(string $destPath) : bool
    {
        /*
         *  all values in head + body are packed; body is additionally gzcompressed
         *      head
         *          UI32:MAGIC
         *          UI32:VERSION
         *          UI32:SUB_VERSION ?
         *          <array:35> UI32:dataOffsets
         *          UI32:uncompressedBodySize
         *      body
         *
         *  $head = MAGIC . VERSION . UNK_UI32
         *  $data = ''
         *
         *  foreach dataItems as item
         *      $head .= strlen($data)
         *      $data .= count(item)
         *      foreach item as i
         *          $data .= i
         *      endforeach
         *  endforeach
         *
         *  $data .= strlen($data)
         *
         *  return $head . gzcompress($data)
         */

        $head = [];
        $data = '';

        $head += array(
            604210112,                                      // magic mo3 bytes
            2001,                                           // version (must be larger than 2k)
            128                                             // UNK HEADER VALUE
         );


        # 1 Vertices #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsVertices, M2Vertex::class);


        # 2 Indices #
        $head[] = strlen($data);
        $data  .= $this->mo3Indices();


        # 3 Sequences  #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsGlobalLoops, UInt32::class);


        # 4 Animations #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsSequences, M2Sequence::class);


        # 5 AnimationLookups #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsSequenceIdxHashById, UInt16::class);


        # 6 Bones #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsBones, M2CompBone::class);


        # 7 BoneLookups #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsBoneCombos, UInt16::class);


        # 8 KeyBoneLookups #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsBoneIndicesById, UInt16::class);


        # 9 Meshes #
        $head[] = strlen($data);
        $data  .= $this->mo3Meshes();


        # 10 TexUnits #
        $head[] = strlen($data);
        $data  .= $this->mo3TexUnits();


        # 11 TexUnitLookup #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsTextureCoordCombos, UInt16::class);


        # 12 RenderFlags #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsMaterials, M2Material::class);


        # 13 Materials #
        $head[] = strlen($data);
        $data  .= $this->mo3Materials();


        # 14 MaterialLookups #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsTextureCombos, UInt16::class);


        # 15 TextureAnims #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsTextureTransforms, M2TextureTransform::class);


        # 16 TexAnimLookups #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsTextureTransformCombos, UInt16::class);


        # 17 BoundingBox + CollisionBox #
        $head[] = strlen($data);
        $data  .= $this->boundingBox->min->pack();
        $data  .= $this->boundingBox->max->pack();
        $data  .= $this->boundingSphereRadius->pack();
        $data  .= $this->collisionBox->min->pack();
        $data  .= $this->collisionBox->max->pack();
        $data  .= $this->collisionSphereRadius->pack();


        # 18 CollisionTriangles #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsCollisionIndices, UInt16::class);


        # 19 UNK CollisionVertices #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsCollisionPositions, C3Vector::class);


        # 20 UNK CollisionNormals #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsCollisionFaceNormals, C3Vector::class);


        # 21 TexReplacements #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsTextureIndicesById, UInt16::class);


        # 22 Attachments #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsAttachments, M2Attachment::class);


        # 23 AttachmentLookup #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsAttachmentIndicesById, UInt16::class);


        # 24 Colors #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsColors, M2Color::class);


        # 25 Alphas #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsTextureWeights, M2TextureWeight::class);


        # 26 AlphaLookups #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsTextureWeightCombos, UInt16::class);


        # 27 Lights #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsLights, M2Light::class);


        # 28 ParticleEmitters #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsParticleEmitters, M2Particle::class);


        # 35 RibbonEmitters #
        $head[] = strlen($data);
        $data  .= $this->mo3ArrayFromM2Array($this->ofsRibbonEmitters, M2Ribbon::class);


        # 29 UNK array<float, float, float, SAnimatedUint16> #
        $head[] = strlen($data);
        $data  .= pack(PACK_U32, 0);                        // numItems = 0 and no data


        # 30 UNK array<int16> #
        $head[] = strlen($data);
        $data  .= pack(PACK_U32, 0);                        // numItems = 0 and no data


        # 31 UNK array<int16, float, float, uint16, uint32> #
        $head[] = strlen($data);
        $data  .= pack(PACK_U32, 0);                        // numItems = 0 and no data


        # 33 unk data chunk (PCOL?) #
        $head[] = strlen($data);
        $data  .= pack(PACK_U32, 0);                        // numItems = 0 and no data
        // uint32 enabledTest (TWW enabled model?)
        // 4x pair<int32:numItems, uint32:offset>
        // 2x array<C3Vector>
        // 2x array<uint16>


        # 34 unk data chunk (DPIV?) #
        $head[] = strlen($data);
        $data  .= pack(PACK_U32, 0);                        // numItems = 0 and no data
        // uint32 enabledTest (TWW enabled model?)
        // C3Vector
        // 5x int32


        # 32 UNK array<float, float, uint32, uint32> #
        $head[] = strlen($data);
        $data  .= pack(PACK_U32, 0);                        // numItems = 0 and no data


        $head[] = strlen($data);                            // total data size


        if ($handle = fopen(CLI::nicePath(trim($this->name).'.mo3', $destPath), 'wb'))
        {
            fwrite($handle, pack(PACK_U32.'*', ...$head) . gzcompress($data));
            fclose($handle);

            return true;
        }

        trigger_error('WTF!?');

        return false;
    }

    public function exportComponents() : bool
    {
        // export additional textures etc here
        // $this->extTextures

        return false;
    }


    private function mo3Indices() : string
    {
        $indices = $this->skins[0]->getIndices();

        $data  = pack(PACK_U32, count($indices));
        $data .= implode('', $indices);

        return $data;
    }


    private function mo3Meshes() : string
    {
        $meshes = $this->skins[0]->getMeshes();

        $data  = pack(PACK_U32, count($meshes));
        $data .= implode('', $meshes);

        return $data;
    }

    private function mo3TexUnits() : string
    {
        $texUnits = $this->skins[0]->getTexUnits();

        $data  = pack(PACK_U32, count($texUnits));
        $data .= implode('', $texUnits);

        return $data;
    }


    private function mo3Materials() : string
    {
        $data = pack(PACK_U32, $this->ofsMaterials->size);

        $this->seek($this->ofsTextures->offset);
        for ($i = 0; $i < $this->ofsTextures->size; $i++)
        {
            $material = new M2Texture($this);
            if ($_ = $material->filename);
                $this->extTextures[] = $_;

            $data .= $material->pack();
        }

        return $data;
    }

    private function mo3ArrayFromM2Array(M2Array &$data, string $srcType, ?string $destType = null) : string
    {
        assert(class_exists($srcType), 'WTF!?');

        $this->seek($data->offset);

        $numItems = pack(PACK_U32, $data->size);

        if (get_parent_class($srcType) == __NAMESPACE__.'\Primitive')
        {
            if (!$destType || $srcType == $destType)
                return $numItems . $this->read($srcType::SIZE * $data->size);

            $buf = unpack($srcType::PACK_FMT . $data->size, $this->read($srcType::SIZE * $data->size));
            return $numItems . pack($destType::PACK_FMT . $destType::SIZE, $buf);
        }

        $buf = '';
        for ($i = 0; $i < $data->size; $i++)
            $buf .= (new $srcType($this))->pack();

        return $numItems . $buf;
    }
}

?>
