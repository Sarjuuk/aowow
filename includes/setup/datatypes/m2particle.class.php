<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// described as "wrong as hell" .. oh dear D:
class M2Particle                                            // Mo3: ParticleEmitter
{
    public readonly Int32    $particleId;                   // Always (as I have seen): -1.
    public readonly UInt32   $flags;                        // See Below
    public readonly C3Vector $position;                     // The position. Relative to the following bone.
    public readonly UInt16   $bone;                         // The bone its attached to.
    public readonly UInt16   $texture;                      // And the textures that are used.
    public readonly M2Array  $geometryModelFilename;        // if given, this emitter spawns models
    public readonly M2Array  $recursionModelFilename;       // if given, this emitter is an alias for the (maximum 4) emitters of the given model
    public readonly UInt8    $blendingType;                 // A blending type for the particle. See Below
    public readonly UInt8    $emitterType;                  // 1 - Plane (rectangle), 2 - Sphere, 3 - Spline, 4 - Bone
    public readonly UInt16   $particleColorIndex;           // This one is used for ParticleColor.dbc. See below.
    public readonly UInt8    $particleType;                 // Found below.
    public readonly UInt8    $headOrTail;                   // 0 - Head, 1 - Tail, 2 - Both - Head - The particle is a billboarded square quad; Tail – A tail particle is billboarded along the axis of motion and stretches in length based on speed;
    public readonly UInt16   $textureTileRotation;          // Rotation for the texture tile. (Values: -1,0,1) -- priorityPlane
    public readonly UInt16   $textureDimensionsRows;        // for tiled textures
    public readonly UInt16   $textureDimensionsColumns;
    public readonly M2Track  $emissionSpeed;                // Base velocity at which particles are emitted.
    public readonly M2Track  $speedVariation;               // Random variation in particle emission speed. (range: 0 to 1)
    public readonly M2Track  $verticalRange;                // longitude; Drifting away vertically. (range: 0 to pi) For plane generators, this is the maximum polar angle of the initial velocity;
    public readonly M2Track  $horizontalRange;              // latitude; They can do it horizontally too! (range: 0 to 2*pi) For plane generators, this is the maximum azimuth angle of the initial velocity;
    public readonly M2Track  $gravity;                      // Not necessarily a float; see below.
    public readonly M2Track  $lifespan;                     // Number of seconds each particle continues to be drawn after its creation.[1]
    public readonly Double   $lifespanVary;                 // An individual particle's lifespan is added to by lifespanVary * random(-1, 1)
    public readonly M2Track  $emissionRate;
    public readonly Double   $emissionRateVary;             // This adds to the base emissionRate value the same way as lifespanVary. The random value is different every update.
    public readonly M2Track  $emissionAreaLength;           // For plane generators, this is the width of the plane in the x-axis. - For sphere generators, this is the minimum radius.
    public readonly M2Track  $emissionAreaWidth;            // For plane generators, this is the width of the plane in the y-axis. - For sphere generators, this is the maximum radius.
    public readonly M2Track  $zSource;                      // When greater than 0, the initial velocity of the particle is (particle.position - C3Vector(0, 0, zSource)).Normalize()
    public readonly FBlock   $colorTrack;                   // Most likely they all have 3 timestamps for {start, middle, end}.
    public readonly FBlock   $alphaTrack;
    public readonly FBlock   $scaleTrack;
    public readonly C2Vector $scaleVary;                    // A percentage amount to randomly vary the scale of each particle
    public readonly FBlock   $headCellTrack;                // Some kind of intensity values seen: 0,16,17,32 (if set to different it will have high intensity)
    public readonly FBlock   $tailCellTrack;
    public readonly Double   $tailLength;                   // A multiplier to the calculated tail particle length.[1]
    public readonly Double   $twinkleSpeed;                 // twinkleFPS; has something to do with the spread
    public readonly Double   $twinklePercent;               // same mechanic as MDL twinkleOnOff but non-binary in 0.11.0
    public readonly CRange   $twinkleScale;                 // min, max
    public readonly Double   $burstMultiplier;              // ivelScale; requires (flags & 0x40)
    public readonly Double   $drag;                         // For a non-zero values, instead of travelling linearly the particles seem to slow down sooner. Speed is multiplied by exp( -drag * t ).
    public readonly Double   $baseSpin;                     // Initial rotation of the particle quad
    public readonly Double   $baseSpinVary;
    public readonly Double   $spin;                         // Rotation of the particle quad per second
    public readonly Double   $spinVary;
    public readonly M2Box    $tumble;
    public readonly C3Vector $windVector;
    public readonly Double   $windTime;
    public readonly Double   $followSpeed1;
    public readonly Double   $followScale1;
    public readonly Double   $followSpeed2;
    public readonly Double   $followScale2;
    public readonly M2Array  $splinePoints;                 // Set only for spline praticle emitter. Contains array of points for spline
    public readonly M2Track  $enabledIn;                    // (boolean) Appears to be used sparely now, probably there's a flag that links particles to animation sets where they are enabled.

    private BinaryFile $ref;

    public function __construct(BinaryFile $byteBuffer)
    {
        $this->particleId               = $byteBuffer->readInt32(); // described as unsigned and always -1? o_0
        $this->flags                    = $byteBuffer->readUInt32();
        $this->position                 = new C3Vector($byteBuffer);
        $this->bone                     = $byteBuffer->readUInt16();
        $this->texture                  = $byteBuffer->readUInt16();
        $this->geometryModelFilename    = new M2Array($byteBuffer);
        $this->recursionModelFilename   = new M2Array($byteBuffer);
        $this->blendingType             = $byteBuffer->readUInt8();
        $this->emitterType              = $byteBuffer->readUInt8();
        $this->particleColorIndex       = $byteBuffer->readUInt16();
        $this->particleType             = $byteBuffer->readUInt8();
        $this->headOrTail               = $byteBuffer->readUInt8();
        $this->textureTileRotation      = $byteBuffer->readUInt16();
        $this->textureDimensionsRows    = $byteBuffer->readUInt16();
        $this->textureDimensionsColumns = $byteBuffer->readUInt16();
        $this->emissionSpeed            = new M2Track($byteBuffer, Double::class);
        $this->speedVariation           = new M2Track($byteBuffer, Double::class);
        $this->verticalRange            = new M2Track($byteBuffer, Double::class);
        $this->horizontalRange          = new M2Track($byteBuffer, Double::class);
        $this->gravity                  = new M2Track($byteBuffer, Double::class);
        $this->lifespan                 = new M2Track($byteBuffer, Double::class);
        $this->lifespanVary             = $byteBuffer->readFloat();
        $this->emissionRate             = new M2Track($byteBuffer, Double::class);
        $this->emissionRateVary         = $byteBuffer->readFloat();
        $this->emissionAreaLength       = new M2Track($byteBuffer, Double::class);
        $this->emissionAreaWidth        = new M2Track($byteBuffer, Double::class);
        $this->zSource                  = new M2Track($byteBuffer, Double::class);
        $this->colorTrack               = new FBlock($byteBuffer, C3Vector::class);
        $this->alphaTrack               = new FBlock($byteBuffer, Fixed16::class);
        $this->scaleTrack               = new FBlock($byteBuffer, C2Vector::class);
        $this->scaleVary                = new C2Vector($byteBuffer);
        $this->headCellTrack            = new FBlock($byteBuffer, UInt16::class);
        $this->tailCellTrack            = new FBlock($byteBuffer, UInt16::class);
        $this->tailLength               = $byteBuffer->readFloat();
        $this->twinkleSpeed             = $byteBuffer->readFloat();
        $this->twinklePercent           = $byteBuffer->readFloat();
        $this->twinkleScale             = new CRange($byteBuffer);
        $this->burstMultiplier          = $byteBuffer->readFloat();
        $this->drag                     = $byteBuffer->readFloat();
        $this->baseSpin                 = $byteBuffer->readFloat();
        $this->baseSpinVary             = $byteBuffer->readFloat();
        $this->spin                     = $byteBuffer->readFloat();
        $this->spinVary                 = $byteBuffer->readFloat();
        $this->tumble                   = new M2Box($byteBuffer);
        $this->windVector               = new C3Vector($byteBuffer);
        $this->windTime                 = $byteBuffer->readFloat();
        $this->followSpeed1             = $byteBuffer->readFloat();
        $this->followScale1             = $byteBuffer->readFloat();
        $this->followSpeed2             = $byteBuffer->readFloat();
        $this->followScale2             = $byteBuffer->readFloat();
        $this->splinePoints             = new M2Array($byteBuffer);
        $this->enabledIn                = new M2Track($byteBuffer, Char::class);

        $this->ref = $byteBuffer;
    }

    public function pack() : string
    {
        $data  = '';

        $data .= $this->particleId->pack();               // id
        $data .= $this->flags->pack();                    // flags
        $data .= $this->position->pack();                 // position
        $data .= $this->bone->pack();                     // boneId
        $data .= $this->texture->pack();                  // textureId
        $data .= $this->blendingType->pack();             // blendMode
        $data .= $this->emitterType->pack();              // emitterType
        $data .= $this->particleColorIndex->pack();       // particleColorIndex
        $data .= $this->textureTileRotation->pack();      // tileRotation
        $data .= $this->textureDimensionsRows->pack();    // tileRows
        $data .= $this->textureDimensionsColumns->pack(); // tileColumns
        $data .= $this->emissionSpeed->pack();            // emissionSpeed
        $data .= $this->speedVariation->pack();           // speedVariation
        $data .= $this->verticalRange->pack();            // verticalRange
        $data .= $this->horizontalRange->pack();          // horizontalRange
        $data .= $this->gravity->pack();                  // gravity
        $data .= $this->lifespan->pack();                 // lifespan
        $data .= $this->lifespanVary->pack();             // lifespanVary
        $data .= $this->emissionRate->pack();             // emissionRate
        $data .= $this->emissionRateVary->pack();         // emissionRateVary
        $data .= $this->emissionAreaLength->pack();       // areaLength
        $data .= $this->emissionAreaWidth->pack();        // areaWidth
        $data .= $this->zSource->pack();                  // gravity2 - unsure, but fits in terms of data structure
        $data .= $this->colorTrack->pack();               // color
        $data .= $this->alphaTrack->pack();               // alpha
        $data .= $this->scaleTrack->pack();               // size
        $data .= $this->scaleVary->pack();                // scaleVary
        $data .= $this->headCellTrack->pack();            // intensity
        $data .= $this->tailCellTrack->pack();            // tailCellTrack
        $data .= $this->tailLength->pack();               // tailLength
        $data .= $this->twinkleSpeed->pack();             // twinkleSpeed
        $data .= $this->twinklePercent->pack();           // twinklePercent
        $data .= $this->twinkleScale->pack();             // twinkleScale
        $data .= $this->burstMultiplier->pack();          // burstMultiplier
        $data .= $this->drag->pack();                     // slowdown
        $data .= $this->baseSpin->pack();                 // baseSpin
        $data .= $this->baseSpinVary->pack();             // baseSpinVary
        $data .= $this->spin->pack();                     // spin
        $data .= $this->spinVary->pack();                 // spinVary
        $data .= $this->tumble->min->pack();              // modelRot1 - C3Vector: unsure, but fits in terms of data structure
        $data .= $this->tumble->max->pack();              // modelRot2 - C3Vector: unsure, but fits in terms of data structure
        $data .= $this->windVector->pack();               // modelTranslation - C3Vector: unsure, but fits in terms of data structure
        $data .= $this->windTime->pack();                 // windTime
        $data .= $this->followSpeed1->pack();             // followSpeed1
        $data .= $this->followScale1->pack();             // followScale1
        $data .= $this->followSpeed2->pack();             // followSpeed2
        $data .= $this->followScale2->pack();             // followScale2

        $cPos = $this->ref->tell();
        $this->ref->seek($this->splinePoints->offset);

        $data .= pack(PACK_I32, $this->splinePoints->size); // numSplinePoints
        for ($i = 0; $i < $this->splinePoints->size; $i++)
            $data .= (new C3Vector($this->ref))->pack();    // splinePoints

        $this->ref->seek($cPos);

        $data .= $this->enabledIn->pack();                  // enabled
        $data .= pack('f2', 0, 0);                          // multiTexScale - 2 floats; don't see corresponding 335a data; maybe Cata+ multiTextureParamX[2]
        $data .= pack('f4', 0, 0, 0, 0);                    // multiTexBaseCoords - 4 floats; don't see corresponding 335a data
        $data .= pack('f4', 0, 0, 0, 0);                    // multiTexVaryCoords - 4 floats; don't see corresponding 335a data

        return $data;
    }
}
