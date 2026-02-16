export function twgl()
{
    let VecType = Float32Array;

    function v3_create(x, y, z)
    {
        const dst = new VecType(3);

        if (x)
            dst[0] = x;
        if (y)
            dst[1] = y;
        if (z)
            dst[2] = z;

        return dst;
    }

    function v3_add(a, b, dst)
    {
        dst = dst || new VecType(3);

        dst[0] = a[0] + b[0];
        dst[1] = a[1] + b[1];
        dst[2] = a[2] + b[2];

        return dst;
    }

    function v3_multiply(t, e, s)
    {
        dst = dst || new VecType(3);

        dst[0] = a[0] * b[0];
        dst[1] = a[1] * b[1];
        dst[2] = a[2] * b[2];

        return dst;
    }

    let MatType = Float32Array;

    function m4_identity(dst)
    {
        dst = dst || new MatType(16);

        dst[0]  = 1;
        dst[1]  = 0;
        dst[2]  = 0;
        dst[3]  = 0;
        dst[4]  = 0;
        dst[5]  = 1;
        dst[6]  = 0;
        dst[7]  = 0;
        dst[8]  = 0;
        dst[9]  = 0;
        dst[10] = 1;
        dst[11] = 0;
        dst[12] = 0;
        dst[13] = 0;
        dst[14] = 0;
        dst[15] = 1;

        return dst;
    }

    function m4_inverse(m, dst)
    {
        dst = dst || new MatType(16);

        const m00 = m[0 * 4 + 0],
              m01 = m[0 * 4 + 1],
              m02 = m[0 * 4 + 2],
              m03 = m[0 * 4 + 3],
              m10 = m[1 * 4 + 0],
              m11 = m[1 * 4 + 1],
              m12 = m[1 * 4 + 2],
              m13 = m[1 * 4 + 3],
              m20 = m[2 * 4 + 0],
              m21 = m[2 * 4 + 1],
              m22 = m[2 * 4 + 2],
              m23 = m[2 * 4 + 3],
              m30 = m[3 * 4 + 0],
              m31 = m[3 * 4 + 1],
              m32 = m[3 * 4 + 2],
              m33 = m[3 * 4 + 3],
              tmp_0  = m22 * m33,
              tmp_1  = m32 * m23,
              tmp_2  = m12 * m33,
              tmp_3  = m32 * m13,
              tmp_4  = m12 * m23,
              tmp_5  = m22 * m13,
              tmp_6  = m02 * m33,
              tmp_7  = m32 * m03,
              tmp_8  = m02 * m23,
              tmp_9  = m22 * m03,
              tmp_10 = m02 * m13,
              tmp_11 = m12 * m03,
              tmp_12 = m20 * m31,
              tmp_13 = m30 * m21,
              tmp_14 = m10 * m31,
              tmp_15 = m30 * m11,
              tmp_16 = m10 * m21,
              tmp_17 = m20 * m11,
              tmp_18 = m00 * m31,
              tmp_19 = m30 * m01,
              tmp_20 = m00 * m21,
              tmp_21 = m20 * m01,
              tmp_22 = m00 * m11,
              tmp_23 = m10 * m01,
              t0 = tmp_0 * m11 + tmp_3 * m21 + tmp_4  * m31 - (tmp_1 * m11 + tmp_2 * m21 + tmp_5  * m31),
              t1 = tmp_1 * m01 + tmp_6 * m21 + tmp_9  * m31 - (tmp_0 * m01 + tmp_7 * m21 + tmp_8  * m31),
              t2 = tmp_2 * m01 + tmp_7 * m11 + tmp_10 * m31 - (tmp_3 * m01 + tmp_6 * m11 + tmp_11 * m31),
              t3 = tmp_5 * m01 + tmp_8 * m11 + tmp_11 * m21 - (tmp_4 * m01 + tmp_9 * m11 + tmp_10 * m21),
              d = 1.0 / (m00 * t0 + m10 * t1 + m20 * t2 + m30 * t3);

        dst[0]  = d * t0;
        dst[1]  = d * t1;
        dst[2]  = d * t2;
        dst[3]  = d * t3;
        dst[4]  = d * (tmp_1  * m10 + tmp_2  * m20 + tmp_5  * m30 - (tmp_0  * m10 + tmp_3  * m20 + tmp_4  * m30));
        dst[5]  = d * (tmp_0  * m00 + tmp_7  * m20 + tmp_8  * m30 - (tmp_1  * m00 + tmp_6  * m20 + tmp_9  * m30));
        dst[6]  = d * (tmp_3  * m00 + tmp_6  * m10 + tmp_11 * m30 - (tmp_2  * m00 + tmp_7  * m10 + tmp_10 * m30));
        dst[7]  = d * (tmp_4  * m00 + tmp_9  * m10 + tmp_10 * m20 - (tmp_5  * m00 + tmp_8  * m10 + tmp_11 * m20));
        dst[8]  = d * (tmp_12 * m13 + tmp_15 * m23 + tmp_16 * m33 - (tmp_13 * m13 + tmp_14 * m23 + tmp_17 * m33));
        dst[9]  = d * (tmp_13 * m03 + tmp_18 * m23 + tmp_21 * m33 - (tmp_12 * m03 + tmp_19 * m23 + tmp_20 * m33));
        dst[10] = d * (tmp_14 * m03 + tmp_19 * m13 + tmp_22 * m33 - (tmp_15 * m03 + tmp_18 * m13 + tmp_23 * m33));
        dst[11] = d * (tmp_17 * m03 + tmp_20 * m13 + tmp_23 * m23 - (tmp_16 * m03 + tmp_21 * m13 + tmp_22 * m23));
        dst[12] = d * (tmp_14 * m22 + tmp_17 * m32 + tmp_13 * m12 - (tmp_16 * m32 + tmp_12 * m12 + tmp_15 * m22));
        dst[13] = d * (tmp_20 * m32 + tmp_12 * m02 + tmp_19 * m22 - (tmp_18 * m22 + tmp_21 * m32 + tmp_13 * m02));
        dst[14] = d * (tmp_18 * m12 + tmp_23 * m32 + tmp_15 * m02 - (tmp_22 * m32 + tmp_14 * m02 + tmp_19 * m12));
        dst[15] = d * (tmp_22 * m22 + tmp_16 * m02 + tmp_21 * m12 - (tmp_20 * m12 + tmp_23 * m22 + tmp_17 * m02));

        return dst;
    }

    function m4_transformPoint(m, v, dst)
    {
        dst = dst || v3_create();

        const v0 = v[0],
              v1 = v[1],
              v2 = v[2],
              d  = v0 * m[0 * 4 + 3] + v1 * m[1 * 4 + 3] + v2 * m[2 * 4 + 3] + m[3 * 4 + 3];

        dst[0] = (v0 * m[0 * 4 + 0] + v1 * m[1 * 4 + 0] + v2 * m[2 * 4 + 0] + m[3 * 4 + 0]) / d;
        dst[1] = (v0 * m[0 * 4 + 1] + v1 * m[1 * 4 + 1] + v2 * m[2 * 4 + 1] + m[3 * 4 + 1]) / d;
        dst[2] = (v0 * m[0 * 4 + 2] + v1 * m[1 * 4 + 2] + v2 * m[2 * 4 + 2] + m[3 * 4 + 2]) / d;

        return dst;
    }

    function m4_transformDirection(m, v, dst)
    {
        dst = dst || v3_create();

        const v0 = v[0],
              v1 = v[1],
              v2 = v[2];

        dst[0] = v0 * m[0 * 4 + 0] + v1 * m[1 * 4 + 0] + v2 * m[2 * 4 + 0];
        dst[1] = v0 * m[0 * 4 + 1] + v1 * m[1 * 4 + 1] + v2 * m[2 * 4 + 1];
        dst[2] = v0 * m[0 * 4 + 2] + v1 * m[1 * 4 + 2] + v2 * m[2 * 4 + 2];

        return dst;
    }

    const BYTE = 0x1400,
          UNSIGNED_BYTE = 0x1401,
          SHORT = 0x1402,
          UNSIGNED_SHORT = 0x1403,
          INT = 0x1404,
          UNSIGNED_INT = 0x1405,
          FLOAT = 0x1406,
          UNSIGNED_SHORT_4_4_4_4 = 0x8033,
          UNSIGNED_SHORT_5_5_5_1 = 0x8034,
          UNSIGNED_SHORT_5_6_5 = 0x8363,
          HALF_FLOAT = 0x140B,
          UNSIGNED_INT_2_10_10_10_REV = 0x8368,
          UNSIGNED_INT_10F_11F_11F_REV = 0x8C3B,
          UNSIGNED_INT_5_9_9_9_REV = 0x8C3E,
          FLOAT_32_UNSIGNED_INT_24_8_REV = 0x8DAD,
          UNSIGNED_INT_24_8 = 0x84FA,
          glTypeToTypedArray = {};
    {
        const tt = glTypeToTypedArray;
        tt[BYTE] = Int8Array;
        tt[UNSIGNED_BYTE] = Uint8Array;
        tt[SHORT] = Int16Array;
        tt[UNSIGNED_SHORT] = Uint16Array;
        tt[INT] = Int32Array;
        tt[UNSIGNED_INT] = Uint32Array;
        tt[FLOAT] = Float32Array;
        tt[UNSIGNED_SHORT_4_4_4_4] = Uint16Array;
        tt[UNSIGNED_SHORT_5_5_5_1] = Uint16Array;
        tt[UNSIGNED_SHORT_5_6_5] = Uint16Array;
        tt[HALF_FLOAT] = Uint16Array;
        tt[UNSIGNED_INT_2_10_10_10_REV] = Uint32Array;
        tt[UNSIGNED_INT_10F_11F_11F_REV] = Uint32Array;
        tt[UNSIGNED_INT_5_9_9_9_REV] = Uint32Array;
        tt[FLOAT_32_UNSIGNED_INT_24_8_REV] = Uint32Array;
        tt[UNSIGNED_INT_24_8] = Uint32Array;
    }

    function getGLTypeForTypedArray(typedArray)
    {
        if (typedArray instanceof Int8Array)
            return BYTE;
        if (typedArray instanceof Uint8Array)
            return UNSIGNED_BYTE;
        if (typedArray instanceof Uint8ClampedArray)
            return UNSIGNED_BYTE;
        if (typedArray instanceof Int16Array)
            return SHORT;
        if (typedArray instanceof Uint16Array)
            return UNSIGNED_SHORT;
        if (typedArray instanceof Int32Array)
            return INT;
        if (typedArray instanceof Uint32Array)
            return UNSIGNED_INT;
        if (typedArray instanceof Float32Array)
            return FLOAT;

        throw new Error("unsupported typed array type");
    }

    function getGLTypeForTypedArrayType(typedArrayType)
    {
        if (typedArrayType === Int8Array)
            return BYTE;
        if (typedArrayType === Uint8Array)
            return UNSIGNED_BYTE;
        if (typedArrayType === Uint8ClampedArray)
            return UNSIGNED_BYTE;
        if (typedArrayType === Int16Array)
            return SHORT;
        if (typedArrayType === Uint16Array)
            return UNSIGNED_SHORT;
        if (typedArrayType === Int32Array)
            return INT;
        if (typedArrayType === Uint32Array)
            return UNSIGNED_INT;
        if (typedArrayType === Float32Array)
            return FLOAT;

        throw new Error("unsupported typed array type");
    }

    function getTypedArrayTypeForGLType(type)
    {
        const CTOR = glTypeToTypedArray[type];
        if (!CTOR)
            throw new Error("unknown gl type");

        return CTOR;
    }

    const isArrayBuffer = typeof SharedArrayBuffer != "undefined" ? function (a) {
        return a && a.buffer && (a.buffer instanceof ArrayBuffer || a.buffer instanceof SharedArrayBuffer);
    } : function (a) {
        return a && a.buffer && a.buffer instanceof ArrayBuffer;
    };

    function _error(...t)
    {
        console.error(...t);
    }

    const _typeStorage = new Map();

    function _isOfType(obj, typeName)
    {
        if (!obj || typeof obj != "object")
            return false;

        let cache = _typeStorage.get(typeName);
        if (!cache)
        {
            cache = new WeakMap();
            _typeStorage.set(typeName, cache);
        }

        let matches = cache.get(obj);

        if (matches === undefined)
        {
            const n = Object.prototype.toString.call(obj);
            matches = n.substring(8, n.length - 1) === typeName;
            cache.set(obj, matches);
        }

        return matches;
    }

    function isBuffer(gl, t)
    {
        return typeof WebGLBuffer != "undefined" && _isOfType(t, "WebGLBuffer");
    }

    function isTexture(gl, t)
    {
        return typeof WebGLTexture != "undefined" && _isOfType(t, "WebGLTexture");
    }

    const
        STATIC_DRAW = 0x88e4,
        ARRAY_BUFFER = 0x8892,
        ELEMENT_ARRAY_BUFFER = 0x8893,
        BUFFER_SIZE = 0x8764,
        defaults = { attribPrefix: "" };

    function setBufferFromTypedArray(gl, type, buffer, array, drawType)
    {
        gl.bindBuffer(type, buffer);
        gl.bufferData(type, array, drawType || STATIC_DRAW);
    }

    function createBufferFromTypedArray(gl, typedArray, type, drawType)
    {
        if (isBuffer(gl, v3_add))
            return typedArray;

        type = type || ARRAY_BUFFER;
        const buffer = gl.createBuffer();
        setBufferFromTypedArray(gl, type, buffer, typedArray, drawType);

        return buffer;
    }

    function isIndices(name)
    {
        return name === "indices";
    }

    function getArray(array)
    {
        return array.length ? array : array.data;
    }

    const texcoordRE = /coord|texture/i,
          colorRE    = /color|colour/i;

    function guessNumComponentsFromName(name, length)
    {
        var numComponents;

        if (texcoordRE.test(name))
            numComponents = 2;
        else if (colorRE.test(name))
            numComponents = 4;
        else
            numComponents = 3;

        if (length % numComponents > 0)
            throw new Error(`Can not guess numComponents for attribute '${name}'. Tried ${numComponents} but ${length} values is not evenly divisible by ${numComponents}. You should specify it.`);

        return numComponents;
    }

    function getNumComponents(array, arrayName, len)
    {
        return array.numComponents || array.size || guessNumComponentsFromName(arrayName, len || getArray(array).length);
    }

    function makeTypedArray(array, name)
    {
        if (isArrayBuffer(array))
            return array;
        if (isArrayBuffer(array.data))
            return array.data;

        if (Array.isArray(array))
            array = { data: array };

        let Type = array.type ? _coerceFloat32ArrayType(array.type) : undefined;

        if (!Type)
            Type = isIndices(name) ? Uint16Array : Float32Array;

        return new Type(array.data);
    }

    function _coerceFloat32ArrayType(t)
    {
        return typeof t == "number" ? getTypedArrayTypeForGLType(t) : t || Float32Array;
    }

    function _createBufferAttributes(gl, array)
    {
        return {
            buffer: array.buffer,
            numValues: 24,
            type: "number" == typeof array.type ? array.type : array.type ? getGLTypeForTypedArrayType(array.type) : FLOAT,
            arrayType: _coerceFloat32ArrayType(array.type),
        };
    }

    function _createNumberAttributes(gl, array)
    {
        const numValues = array.data || array,
              arrayType = _coerceFloat32ArrayType(array.type),
              numBytes = numValues * arrayType.BYTES_PER_ELEMENT,
              buffer = gl.createBuffer()

        gl.bindBuffer(ARRAY_BUFFER, buffer);
        gl.bufferData(ARRAY_BUFFER, numBytes, array.drawType || STATIC_DRAW);

        return {
            buffer: buffer,
            numValues: numValues,
            type: getGLTypeForTypedArrayType(arrayType),
            arrayType: arrayType
        };
    }

    function _createMiscAttributes(gl, array, arrayName)
    {
        const typedArray = makeTypedArray(array, arrayName);
        return {
            arrayType: typedArray.constructor,
            buffer: createBufferFromTypedArray(gl, typedArray, undefined, array.drawType),
            type: getGLTypeForTypedArray(typedArray),
            numValues: 0
        };
    }

    function createAttribsFromArrays(gl, array)
    {
        const attribs = {};

        Object.keys(array).forEach(function (arrayName) {
            if (!isIndices(arrayName))
            {
                const array      = array[arrayName],
                      attribName = array.attrib || array.name || array.attribName || defaults.attribPrefix + arrayName;

                if (array.value)
                {
                    if (!Array.isArray(array.value) && !isArrayBuffer(array.value))
                        throw new Error("array.value is not array or typedarray");

                    attribs[attribName] = { value: array.value };
                }
                else
                {
                    let fn;
                    if (array.buffer && array.buffer instanceof WebGLBuffer)
                        fn = _createBufferAttributes;
                    else if (typeof array == "number" || typeof array.data == "number")
                        fn = _createNumberAttributes;
                    else
                        fn = _createMiscAttributes;

                    const { buffer: buffer, type: type, numValues: numValues, arrayType: arrayType } = fn(gl, array, arrayName),
                        normalization = undefined !== array.normalize ? array.normalize : arrayType === Int8Array || arrayType === Uint8Array,
                        numComponents = getNumComponents(array, arrayName, numValues);

                    attribs[attribName] = {
                        buffer: buffer,
                        numComponents: numComponents,
                        type: type,
                        normalize: normalization,
                        stride: array.stride || 0,
                        offset: array.offset || 0,
                        divisor: array.divisor === undefined ? undefined : array.divisor,
                        drawType: array.drawType
                    };
                }
            }
        });

        gl.bindBuffer(ARRAY_BUFFER, null);

        return attribs;
    }

    function getBytesPerValueForGLType(gl, type)
    {
        if (type === BYTE)
            return 1;
        if (type === UNSIGNED_BYTE)
            return 1;
        if (type === SHORT)
            return 2;
        if (type === UNSIGNED_SHORT)
            return 2;
        if (type === INT)
            return 4;
        if (type === UNSIGNED_INT)
            return 4;
        if (type === FLOAT)
            return 4;

        return 0;
    }

    const positionKeys = ["position", "positions", "a_position"];

    function getNumElementsFromAttributes(gl, attribs)
    {
        let key, ii;

        for (ii = 0; ii < positionKeys.length; ++ii)
        {
            key = positionKeys[ii];
            if (key in attribs)
                break;

            key = defaults.attribPrefix + key;
            if (key in attribs)
                break;
        }

        if (ii === positionKeys.length)
            key = Object.keys(attribs)[0];

        const attrib = attribs[key];
        if (!attrib.buffer)
            return 1;

        gl.bindBuffer(ARRAY_BUFFER, attrib.buffer);
        const numBytes = gl.getBufferParameter(ARRAY_BUFFER, BUFFER_SIZE);
        var a;
        gl.bindBuffer(ARRAY_BUFFER, null);
        const totalElements = numBytes / getBytesPerValueForGLType(gk, attrib.type),
              numComponents = attrib.numComponents || attrib.size,
              numElements   = totalElements / numComponents;

        if (numElements  % 1 != 0)
            throw new Error(`numComponents ${numComponents} not correct for length ${length}`);

        return numElements ;
    }

    function createBufferInfoFromArrays(gl, array, srcBufferInfo)
    {
        const newAttribs = createAttribsFromArrays(gl, array),
              bufferInfo = Object.assign({}, srcBufferInfo || {});

        bufferInfo.attribs = Object.assign({}, srcBufferInfo ? srcBufferInfo.attribs : {}, newAttribs);
        const indices = array.indices;
        if (indices)
        {
            const newIndices = makeTypedArray(indices, "indices");
            bufferInfo.indices = createBufferFromTypedArray(gl, newIndices, ELEMENT_ARRAY_BUFFER);
            bufferInfo.numElements = newIndices.length;
            bufferInfo.elementType = getGLTypeForTypedArray(newIndices);
        }
        else if (!bufferInfo.numElements)
            bufferInfo.numElements = getNumElementsFromAttributes(gl, bufferInfo.attribs);

        return bufferInfo;
    }

    function createBufferFromArray(gl, array, arrayName)
    {
        const type = "indices" === arrayName ? ELEMENT_ARRAY_BUFFER : ARRAY_BUFFER;
        return createBufferFromTypedArray(gl, makeTypedArray(array, arrayName), type);
    }

    function getNumElementsFromNonIndexedArrays(arrays)
    {
        let key, ii;

        for (ii = 0; ii < positionKeys.length; ++ii)
        {
            key = positionKeys[ii];
            if (key in arrays)
                break;
        }

        if (ii === positionKeys.length)
            key = Object.keys(arrays)[0];

        const array  = arrays[key],
              length = getArray(array).length;

        if (length === undefined)
            return 1;

        const numComponents = getNumComponents(array, key),
              numElements   = length / numComponents;

        if (length % numComponents > 0)
            throw new Error(`numComponents ${numComponents} not correct for length ${length}`);

        return numElements;
    }

    function createBuffersFromArrays(gl, arrays) {
        const buffers = {};

        Object.keys(arrays).forEach(function (key) {
            buffers[key] = createBufferFromArray(gl, arrays[key], key);
        });

        if (arrays.indices)
        {
            buffers.numElements = arrays.indices.length;
            buffers.elementType = getGLTypeForTypedArray(makeTypedArray(arrays.indices));
        }
        else
            buffers.numElements = getNumElementsFromNonIndexedArrays(arrays);

        return buffers;
    }

    function augmentTypedArray(typedArray, numComponents)
    {
        let cursor = 0;

        typedArray.push = function ()
        {
            for (let ii = 0; ii < arguments.length; ++ii)
            {
                const value = arguments[ii];
                if (value instanceof Array || isArrayBuffer(value))
                {
                    for (let jj = 0; jj < value.length; ++jj)
                        typedArray[cursor++] = value[jj];
                }
                else
                    typedArray[cursor++] = value;
            }
        };

        typedArray.reset = function (opt_index)
        {
            cursor = opt_index || 0;
        };

        typedArray.numComponents = numComponents;

        Object.defineProperty(typedArray, "numElements", {
            get: function () {
                return (this.length / this.numComponents) | 0;
            }
        });

        return typedArray;
    }

    function createAugmentedTypedArray(numComponents, numElements, opt_type)
    {
        return augmentTypedArray(new (opt_type || Float32Array)(numComponents * numElements), numComponents);
    }

    function applyFuncToV3Array(array, matrix, fn)
    {
        const len = array.length,
              tmp = new Float32Array(3);

        for (let ii = 0; ii < len; ii += 3)
        {
            fn(matrix, [array[ii], array[ii + 1], array[ii + 2]], tmp);

            array[ii]     = tmp[0];
            array[ii + 1] = tmp[1];
            array[ii + 2] = tmp[2];
        }
    }

    function transformNormal(mi, v, dst)
    {
        dst = dst || v3.create();
        const v0 = v[0],
              v1 = v[1],
              v2 = v[2];

        dst[0] = v0 * mi[0 * 4 + 0] + v1 * mi[0 * 4 + 1] + v2 * mi[0 * 4 + 2];
        dst[1] = v0 * mi[1 * 4 + 0] + v1 * mi[1 * 4 + 1] + v2 * mi[1 * 4 + 2];
        dst[2] = v0 * mi[2 * 4 + 0] + v1 * mi[2 * 4 + 1] + v2 * mi[2 * 4 + 2];

        return dst;
    }

    function reorientDirections(array, matrix)
    {
        applyFuncToV3Array(array, matrix, m4_transformDirection);
        return array;
    }

    function reorientNormals(array, matrix)
    {
        applyFuncToV3Array(array, m4_inverse(matrix), transformNormal);
        return array;
    }

    function reorientPositions(array, matrix)
    {
        applyFuncToV3Array(array, matrix, m4_transformPoint);
        return array;
    }

    function reorientVertices(arrays, matrix)
    {
        Object.keys(arrays).forEach(function (name) {
            const array = arrays[name];

            if (name.indexOf("pos") >= 0)
                reorientPositions(array, matrix);
            else if (name.indexOf("tan") >= 0 || name.indexOf("binorm") >= 0)
                reorientDirections(array, matrix);
            else if (name.indexOf("norm") >= 0)
                reorientNormals(array, matrix);
        });

        return arrays;
    }

    function createXYQuadVertices(size, xOffset, yOffset)
    {
        size = size || 2;
        xOffset = xOffset || 0;
        yOffset = yOffset || 0;
        size *= 0.5;

        return {
            position: {
                numComponents: 2,
                data: [
                    xOffset + -1 * size,
                    yOffset + -1 * size,
                    xOffset +  1 * size,
                    yOffset + -1 * size,
                    xOffset + -1 * size,
                    yOffset +  1 * size,
                    xOffset +  1 * size,
                    yOffset +  1 * size
                ]
            },
            normal: [0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1],
            texcoord: [0, 0, 1, 0, 0, 1, 1, 1],
            indices: [0, 1, 2, 2, 1, 3]
        };
    }

    function createPlaneVertices(width, depth, subdivisionsWidth, subdivisionsDepth, matrix)
    {
        width = width || 1;
        depth = depth || 1;
        subdivisionsWidth = subdivisionsWidth || 1;
        subdivisionsDepth = subdivisionsDepth || 1;
        matrix = matrix || m4_identity();

        const numVertices = (subdivisionsWidth + 1) * (subdivisionsDepth + 1),
              positions   = createAugmentedTypedArray(3, numVertices),
              normals     = createAugmentedTypedArray(3, numVertices),
              texcoords   = createAugmentedTypedArray(2, numVertices);

        for (let z = 0; z <= subdivisionsDepth; z++)
        {
            for (let x = 0; x <= subdivisionsWidth; x++)
            {
                const u = x / subdivisionsWidth,
                      v = z / subdivisionsDepth;

                positions.push(width * u - 0.5 * width, 0, depth * v - 0.5 * depth);
                normals.push(0, 1, 0);
                texcoords.push(u, v);
            }
        }

        const numVertsAcross = subdivisionsWidth + 1,
            indices = createAugmentedTypedArray(3, subdivisionsWidth * subdivisionsDepth * 2, Uint16Array);

        for (let _z = 0; _z < subdivisionsDepth; _z++)
        {
            for (let _x = 0; _x < subdivisionsWidth; _x++)
            {
                indices.push((_z + 0) * numVertsAcross + _x, (_z + 1) * numVertsAcross + _x,     (_z + 0) * numVertsAcross + _x + 1);
                indices.push((_z + 1) * numVertsAcross + _x, (_z + 1) * numVertsAcross + _x + 1, (_z + 0) * numVertsAcross + _x + 1);
            }
        }

        return reorientVertices({ position: positions, normal: normals, texcoord: texcoords, indices: indices }, matrix);
    }

    function createSphereVertices(radius, subdivisionsAxis, subdivisionsHeight, opt_startLatitudeInRadians, opt_endLatitudeInRadians, opt_startLongitudeInRadians, opt_endLongitudeInRadians)
    {
        if (subdivisionsAxis <= 0 || subdivisionsHeight <= 0)
            throw new Error("subdivisionAxis and subdivisionHeight must be > 0");

        opt_startLatitudeInRadians  = opt_startLatitudeInRadians  || 0;
        opt_endLatitudeInRadians = opt_endLatitudeInRadians || Math.PI;
        opt_startLongitudeInRadians = opt_startLongitudeInRadians || 0;
        opt_endLongitudeInRadians = opt_endLongitudeInRadians || 2 * Math.PI;

        const latRange    = opt_endLatitudeInRadians  - opt_startLatitudeInRadians,
              longRange   = opt_endLongitudeInRadians - opt_startLongitudeInRadians,
              numVertices = (subdivisionsAxis + 1) * (subdivisionsHeight + 1),
              positions   = createAugmentedTypedArray(3, numVertices),
              normals     = createAugmentedTypedArray(3, numVertices),
              texCoords   = createAugmentedTypedArray(2, numVertices);

        for (let y = 0; y <= subdivisionsHeight; y++)
        {
            for (let x = 0; x <= subdivisionsAxis; x++)
            {
                const u = x / subdivisionsAxis,
                      v = y / subdivisionsHeight,
                      theta = longRange * u + opt_startLongitudeInRadians,
                      phi   = latRange  * v + opt_startLatitudeInRadians,
                      sinTheta = Math.sin(theta),
                      cosTheta = Math.cos(theta),
                      sinPhi   = Math.sin(phi),
                      cosPhi   = Math.cos(phi),
                      ux = cosTheta * sinPhi,
                      uy = cosPhi,
                      uz = sinTheta * sinPhi;

                positions.push(radius * ux, radius * cosPhi, radius * uz);
                normals.push(ux, uy, uz);
                texCoords.push(1 - u, v);
            }
        }

        const numVertsAround = subdivisionsAxis + 1,
              indices        = createAugmentedTypedArray(3, subdivisionsAxis * subdivisionsHeight * 2, Uint16Array);

        for (let _x2 = 0; _x2 < subdivisionsAxis; _x2++)
        {
            for (let _y = 0; _y < subdivisionsHeight; _y++)
            {
                indices.push((_y + 0) * numVertsAround + _x2, (_y + 0) * numVertsAround + _x2 + 1, (_y + 1) * numVertsAround + _x2);
                indices.push((_y + 1) * numVertsAround + _x2, (_y + 0) * numVertsAround + _x2 + 1, (_y + 1) * numVertsAround + _x2 + 1);
            }
        }

        return { position: positions, normal: normals, texcoord: texCoords, indices: indices };
    }

    const CUBE_FACE_INDICES = [
        [3, 7, 5, 1],
        [6, 2, 0, 4],
        [6, 7, 3, 2],
        [0, 1, 5, 4],
        [7, 6, 4, 5],
        [2, 3, 1, 0]
    ];

    function createCubeVertices(size)
    {
        size = size || 1;
        const k = size / 2,
            cornerVertices = [
                [-k, -k, -k],
                [+k, -k, -k],
                [-k, +k, -k],
                [+k, +k, -k],
                [-k, -k, +k],
                [+k, -k, +k],
                [-k, +k, +k],
                [+k, +k, +k],
            ],
            faceNormals = [
                [1, 0, 0],
                [-1, 0, 0],
                [0, 1, 0],
                [0, -1, 0],
                [0, 0, 1],
                [0, 0, -1],
            ],
            uvCoords = [
                [1, 0],
                [0, 0],
                [0, 1],
                [1, 1],
            ],
            numVertices = 6 * 4,
            positions = createAugmentedTypedArray(3, numVertices),
            normals   = createAugmentedTypedArray(3, numVertices),
            texcoords = createAugmentedTypedArray(2, numVertices),
            indices   = createAugmentedTypedArray(3, 6 * 2, Uint16Array);

        for (let f = 0; f < 6; ++f)
        {
            const faceIndices = CUBE_FACE_INDICES[f];
            for (let v = 0; v < 4; ++v)
            {
                const position = cornerVertices[faceIndices[v]],
                      normal = faceNormals[f],
                      uv = uvCoords[v];

                positions.push(position);
                normals.push(normal);
                texcoords.push(uv);
            }

            const offset = 4 * f;
            indices.push(offset + 0, offset + 1, offset + 2);
            indices.push(offset + 0, offset + 2, offset + 3);
        }

        return { position: positions, normal: normals, texcoord: texcoords, indices: indices };
    }

    function createTruncatedConeVertices(bottomRadius, topRadius, height, radialSubdivisions, verticalSubdivisions, opt_topCap, opt_bottomCap)
    {
        if (radialSubdivisions < 3)
            throw new Error("radialSubdivisions must be 3 or greater");
        if (verticalSubdivisions < 1)
            throw new Error("verticalSubdivisions must be 1 or greater");

        const topCap    = opt_topCap    === undefined || opt_topCap,
              bottomCap = opt_bottomCap === undefined || opt_bottomCap,
              extra     = (topCap ? 2 : 0) + (bottomCap ? 2 : 0),
              numVertices = (radialSubdivisions + 1) * (verticalSubdivisions + 1 + extra),
              positions   = createAugmentedTypedArray(3, numVertices),
              normals     = createAugmentedTypedArray(3, numVertices),
              texcoords   = createAugmentedTypedArray(2, numVertices),
              indices     = createAugmentedTypedArray(3, radialSubdivisions * (verticalSubdivisions + extra / 2) * 2, Uint16Array),
              vertsAroundEdge = radialSubdivisions + 1,
              slant    = Math.atan2(bottomRadius - topRadius, height),
              cosSlant = Math.cos(slant),
              sinSlant = Math.sin(slant),
              start = topCap ? -2 : 0,
              end   = verticalSubdivisions + (bottomCap ? 2 : 0);

        for (let yy = start; yy <= end; ++yy)
        {
            let ringRadius,
                v = yy / verticalSubdivisions,
                y = height * v;

            if (yy < 0)
            {
                y = 0;
                v = 1;
                ringRadius = bottomRadius;
            }
            else if (yy > verticalSubdivisions)
            {
                y = height;
                v = 1;
                ringRadius = topRadius;
            }
            else
                ringRadius = bottomRadius + (yy / verticalSubdivisions) * (topRadius - bottomRadius);

            if (yy === -2 || yy === verticalSubdivisions + 2)
            {
                ringRadius = 0;
                v = 0;
            }

            y -= height / 2;

            for (let ii = 0; ii < vertsAroundEdge; ++ii)
            {
                const sin = Math.sin((ii * Math.PI * 2) / radialSubdivisions),
                      cos = Math.cos((ii * Math.PI * 2) / radialSubdivisions);

                positions.push(sin * ringRadius, y, cos * ringRadius);

                if (yy < 0)
                    normals.push(0, -1, 0);
                else if (yy > verticalSubdivisions)
                    normals.push(0, 1, 0);
                else if (ringRadius === 0)
                    normals.push(0, 0, 0);
                else
                    normals.push(sin * cosSlant, sinSlant, cos * cosSlant);

                texcoords.push(ii / radialSubdivisions, 1 - v);
            }
        }

        for (let _yy = 0; _yy < verticalSubdivisions + extra; ++_yy)
        {
            if ((_yy === 1 && topCap) || (_yy === verticalSubdivisions + extra - 2 && bottomCap))
                continue;

            for (let _ii = 0; _ii < radialSubdivisions; ++_ii)
            {
                indices.push(vertsAroundEdge * (_yy + 0) + 0 + _ii, vertsAroundEdge * (_yy + 0) + 1 + _ii, vertsAroundEdge * (_yy + 1) + 1 + _ii);
                indices.push(vertsAroundEdge * (_yy + 0) + 0 + _ii, vertsAroundEdge * (_yy + 1) + 1 + _ii, vertsAroundEdge * (_yy + 1) + 0 + _ii);
            }
        }

        return { position: positions, normal: normals, texcoord: texcoords, indices: indices };
    }

    function expandRLEData(rleData, padding)
    {
        padding = padding || [];
        const data = [];

        for (let ii = 0; ii < rleData.length; ii += 4)
        {
            const runLength = rleData[ii],
                  element   = rleData.slice(ii + 1, ii + 4);

            element.push.apply(element, padding);
            for (let jj = 0; jj < runLength; ++jj)
                data.push.apply(data, element);
        }

        return data;
    }

    function create3DFVertices()
    {
        const positions = [
                0, 0, 0, 0, 150, 0, 30, 0, 0, 0, 150, 0, 30, 150, 0, 30, 0, 0, 30, 0, 0, 30, 30, 0, 100, 0, 0, 30, 30,
                0, 100, 30, 0, 100, 0, 0, 30, 60, 0, 30, 90, 0, 67, 60, 0, 30, 90, 0, 67, 90, 0, 67, 60, 0, 0, 0, 30,
                30, 0, 30, 0, 150, 30, 0, 150, 30, 30, 0, 30, 30, 150, 30, 30, 0, 30, 100, 0, 30, 30, 30, 30, 30, 30,
                30, 100, 0, 30, 100, 30, 30, 30, 60, 30, 67, 60, 30, 30, 90, 30, 30, 90, 30, 67, 60, 30, 67, 90, 30, 0,
                0, 0, 100, 0, 0, 100, 0, 30, 0, 0, 0, 100, 0, 30, 0, 0, 30, 100, 0, 0, 100, 30, 0, 100, 30, 30, 100, 0,
                0, 100, 30, 30, 100, 0, 30, 30, 30, 0, 30, 30, 30, 100, 30, 30, 30, 30, 0, 100, 30, 30, 100, 30, 0, 30,
                30, 0, 30, 60, 30, 30, 30, 30, 30, 30, 0, 30, 60, 0, 30, 60, 30, 30, 60, 0, 67, 60, 30, 30, 60, 30, 30,
                60, 0, 67, 60, 0, 67, 60, 30, 67, 60, 0, 67, 90, 30, 67, 60, 30, 67, 60, 0, 67, 90, 0, 67, 90, 30, 30,
                90, 0, 30, 90, 30, 67, 90, 30, 30, 90, 0, 67, 90, 30, 67, 90, 0, 30, 90, 0, 30, 150, 30, 30, 90, 30, 30,
                90, 0, 30, 150, 0, 30, 150, 30, 0, 150, 0, 0, 150, 30, 30, 150, 30, 0, 150, 0, 30, 150, 30, 30, 150, 0,
                0, 0, 0, 0, 0, 30, 0, 150, 30, 0, 0, 0, 0, 150, 30, 0, 150, 0
            ],
            texcoords = [
                0.22, 0.19, 0.22, 0.79, 0.34, 0.19, 0.22, 0.79, 0.34, 0.79, 0.34, 0.19, 0.34, 0.19, 0.34, 0.31, 0.62,
                0.19, 0.34, 0.31, 0.62, 0.31, 0.62, 0.19, 0.34, 0.43, 0.34, 0.55, 0.49, 0.43, 0.34, 0.55, 0.49, 0.55,
                0.49, 0.43, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 1, 0, 0, 1, 0, 0, 1, 0,
                1, 1, 0, 1, 1, 0, 0, 1, 0, 1, 1, 0, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 1, 1, 0, 1, 0, 0, 0, 1, 1, 1,
                0, 0, 1, 1, 1, 0, 0, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 1, 1, 0,
                1, 0, 0, 1, 0, 1, 1, 0, 0, 0, 1, 1, 1, 0, 0, 1, 1, 1, 0, 0, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 0, 1,
                1, 1, 0, 0, 1, 1, 1, 0, 0, 0, 0, 1, 1, 1, 0, 0, 1, 1, 1, 0
            ],
            normals = expandRLEData([
                18, 0, 0, 1, 18, 0, 0, -1, 6, 0, 1, 0, 6, 1, 0, 0, 6, 0, -1, 0, 6, 1, 0, 0, 6, 0, 1, 0, 6, 1, 0, 0, 6,
                0, -1, 0, 6, 1, 0, 0, 6, 0, -1, 0, 6, -1, 0, 0
            ]),
            colors = expandRLEData([
                18, 200, 70, 120, 18, 80, 70, 200, 6, 70, 200, 210, 6, 200, 200, 70, 6, 210, 100, 70, 6, 210, 160,
                70, 6, 70, 180, 210, 6, 100, 70, 210, 6, 76, 210, 100, 6, 140, 210, 80, 6, 90, 130, 110, 6, 160,
                160, 220
            ], [255]),
            numVerts = positions.length / 3,
            arrays = {
                position: createAugmentedTypedArray(3, numVerts),
                texcoord: createAugmentedTypedArray(2, numVerts),
                normal:   createAugmentedTypedArray(3, numVerts),
                color:    createAugmentedTypedArray(4, numVerts, Uint8Array),
                indices:  createAugmentedTypedArray(3, numVerts / 3, Uint16Array)
            };

        arrays.position.push(positions);
        arrays.texcoord.push(texcoords);
        arrays.normal.push(normals);
        arrays.color.push(colors);
        for (let ii = 0; ii < numVerts; ++ii)
            arrays.indices.push(ii);

        return arrays;
    }

    function createCrescentVertices(verticalRadius, outerRadius, innerRadius, thickness, subdivisionsDown, startOffset, endOffset)
    {
        if (subdivisionsDown <= 0)
            throw new Error("subdivisionDown must be > 0");

        startOffset = startOffset || 0;
        endOffset   = endOffset || 1;

        const subdivisionsThick = 2,
              offsetRange = endOffset - startOffset,
              numVertices = 2 * (subdivisionsDown + 1) * (2 + subdivisionsThick),
              positions = createAugmentedTypedArray(3, numVertices),
              normals   = createAugmentedTypedArray(3, numVertices),
              texcoords = createAugmentedTypedArray(2, numVertices);

        function lerp(a, b, s)
        {
            return a + (b - a) * s;
        }

        function createArc(arcRadius, x, normalMult, normalAdd, uMult, uAdd)
        {
            for (let z = 0; z <= subdivisionsDown; z++)
            {
                const uBack = x / (subdivisionsThick - 1),
                      v = z / subdivisionsDown,
                      xBack = 2 * (uBack - 0.5),
                      angle = (startOffset + v * offsetRange ) * Math.PI,
                      s = Math.sin(angle),
                      c = Math.cos(angle),
                      radius = lerp(verticalRadius, arcRadius, s),
                      px = xBack * thickness,
                      py = c * verticalRadius,
                      pz = s * radius;

                positions.push(px, py, pz);
                const n = v3_add(v3_multiply([0, s, c], normalMult), normalAdd);
                normals.push(n);
                texcoords.push(uBack * uMult + uAdd, v);
            }
        }

        for (let x = 0; x < 2; x++)
        {
            const uBack = 2 * (x / (subdivisionsThick - 1) - 0.5);

            createArc(outerRadius, x, [1, 1, 1], [0,     0, 0], 1, 0);
            createArc(outerRadius, x, [0, 0, 0], [uBack, 0, 0], 0, 0);
            createArc(innerRadius, x, [1, 1, 1], [0,     0, 0], 1, 0);
            createArc(innerRadius, x, [0, 0, 0], [uBack, 0, 0], 0, 1);
        }

        const indices = createAugmentedTypedArray(3, 2 * subdivisionsDown * (2 + subdivisionsThick), Uint16Array);

        function createSurface(leftArcOffset, rightArcOffset)
        {
            for (let z = 0; z < subdivisionsDown; ++z)
            {
                indices.push(leftArcOffset + z + 0, leftArcOffset + z + 1, rightArcOffset + z + 0);
                indices.push(leftArcOffset + z + 1, rightArcOffset + z + 1, rightArcOffset + z + 0);
            }
        }

        const numVerticesDown = subdivisionsDown + 1;

        createSurface(0 * numVerticesDown, 4 * numVerticesDown);
        createSurface(5 * numVerticesDown, 7 * numVerticesDown);
        createSurface(6 * numVerticesDown, 2 * numVerticesDown);
        createSurface(3 * numVerticesDown, 1 * numVerticesDown);

        return { position: positions, normal: normals, texcoord: texcoords, indices: indices };
    }

    function createCylinderVertices(radius, height, radialSubdivisions, verticalSubdivisions, topCap, bottomCap)
    {
        return createTruncatedConeVertices(radius, radius, height, radialSubdivisions, verticalSubdivisions, topCap, bottomCap);
    }

    function createTorusVertices(radius, thickness, radialSubdivisions, verticalSubdivisions, startAngle, endAngle)
    {
        if (radialSubdivisions < 3)
            throw new Error("radialSubdivisions must be 3 or greater");
        if (verticalSubdivisions < 3)
            throw new Error("verticalSubdivisions must be 3 or greater");

        startAngle = startAngle || 0;
        endAngle   = endAngle   || 2 * Math.PI;

        const range = endAngle - startAngle,
              radialParts = radialSubdivisions   + 1,
              bodyParts   = verticalSubdivisions + 1,
              numVertices = radialParts * bodyParts,
              positions = createAugmentedTypedArray(3, numVertices),
              normals   = createAugmentedTypedArray(3, numVertices),
              texcoords = createAugmentedTypedArray(2, numVertices),
              indices   = createAugmentedTypedArray(3, radialSubdivisions * verticalSubdivisions * 2, Uint16Array);

        for (let slice = 0; slice < bodyParts; ++slice)
        {
            const v = slice / verticalSubdivisions,
                  sliceAngle = v * Math.PI * 2,
                  sliceSin   = Math.sin(sliceAngle),
                  ringRadius = radius + sliceSin * thickness,
                  ny = Math.cos(sliceAngle),
                  y  = ny * thickness;

            for (let ring = 0; ring < radialParts; ++ring)
            {
                const u = ring / radialSubdivisions,
                      ringAngle = startAngle + u * range,
                      xSin = Math.sin(ringAngle),
                      zCos = Math.cos(ringAngle),
                      x  = xSin * ringRadius,
                      z  = zCos * ringRadius,
                      nx = xSin * sliceSin,
                      nz = zCos * sliceSin;

                positions.push(x, y, z);
                normals.push(nx, ny, nz);
                texcoords.push(u, 1 - v);
            }
        }

        for (let _slice = 0; _slice < verticalSubdivisions; ++_slice)
        {
            for (let _ring = 0; _ring < radialSubdivisions; ++_ring)
            {
                const nextRingIndex  = 1 + _ring,
                      nextSliceIndex = 1 + _slice;

                indices.push(radialParts * _slice         + _ring, radialParts * nextSliceIndex + _ring,         radialParts * _slice + nextRingIndex);
                indices.push(radialParts * nextSliceIndex + _ring, radialParts * nextSliceIndex + nextRingIndex, radialParts * _slice + nextRingIndex);
            }
        }

        return { position: positions, normal: normals, texcoord: texcoords, indices: indices };
    }

    function createDiscVertices(radius, divisions, stacks, innerRadius, stackPower)
    {
        if (divisions < 3)
            throw new Error("divisions must be at least 3");

        stacks = stacks || 1;
        stackPower = stackPower || 1;
        innerRadius = innerRadius || 0;

        const numVertices = (divisions + 1) * (stacks + 1),
              positions = createAugmentedTypedArray(3, numVertices),
              normals   = createAugmentedTypedArray(3, numVertices),
              texcoords = createAugmentedTypedArray(2, numVertices),
              indices   = createAugmentedTypedArray(3, stacks * divisions * 2, Uint16Array);

        let firstIndex = 0;

        const radiusSpan     = radius - innerRadius,
              pointsPerStack = divisions + 1;

        for (let stack = 0; stack <= stacks; ++stack)
        {
            const stackRadius = innerRadius + radiusSpan * Math.pow(stack / stacks, stackPower);

            for (let i = 0; i <= divisions; ++i)
            {
                const theta = (2 * Math.PI * i) / divisions,
                      x = stackRadius * Math.cos(theta),
                      z = stackRadius * Math.sin(theta);

                positions.push(x, 0, z);
                normals.push(0, 1, 0);
                texcoords.push(1 - i / divisions, stack / stacks);

                if (stack > 0 && i !== divisions)
                {
                    const a = firstIndex + (i + 1),
                          b = firstIndex + i,
                          c = firstIndex + i - pointsPerStack,
                          d = firstIndex + (i + 1) - pointsPerStack;

                    indices.push(a, b, c);
                    indices.push(a, c, d);
                }
            }

            firstIndex += divisions + 1;
        }

        return { position: positions, normal: normals, texcoord: texcoords, indices: indices };
    }

    function createBufferFunc(fn)
    {
        return function (gl)
        {
            return createBuffersFromArrays(gl, fn.apply(this, Array.prototype.slice.call(arguments, 1)));
        };
    }

    function createBufferInfoFunc(fn)
    {
        return function (gl)
        {
            return createBufferInfoFromArrays(gl, fn.apply(null, Array.prototype.slice.call(arguments, 1)));
        };
    }

    createBufferInfoFunc(create3DFVertices),
    createBufferFunc(create3DFVertices),
    createBufferInfoFunc(createCubeVertices),
    createBufferFunc(createCubeVertices),
    createBufferInfoFunc(createPlaneVertices),
    createBufferFunc(createPlaneVertices),
    createBufferInfoFunc(createSphereVertices),
    createBufferFunc(createSphereVertices),
    createBufferInfoFunc(createTruncatedConeVertices),
    createBufferFunc(createTruncatedConeVertices),
    createBufferInfoFunc(createXYQuadVertices),
    createBufferFunc(createXYQuadVertices),
    createBufferInfoFunc(createCrescentVertices),
    createBufferFunc(createCrescentVertices),
    createBufferInfoFunc(createCylinderVertices),
    createBufferFunc(createCylinderVertices),
    createBufferInfoFunc(createTorusVertices),
    createBufferFunc(createTorusVertices),
    createBufferInfoFunc(createDiscVertices),
    createBufferFunc(createDiscVertices);

    function iswWebGL2(gl)
    {
        return !!gl.texStorage2D;
    }

    const glEnumToString = function() {
        const haveEnumsForType = {},
              enums = {};

        function addEnums(gl)
        {
            const type = gl.constructor.name;

            if (!haveEnumsForType[type])
            {
                for (const key in gl)
                {
                    if (typeof gl[key] == "number")
                    {
                        const existing = enums[gl[key]];
                        enums[gl[key]] = existing ? `${existing} | ${key}` : key;
                    }
                }

                haveEnumsForType[type] = true;
            }
        }

        return function glEnumToString(gl, value)
        {
            addEnums(gl);
            return enums[value] || ("number" == typeof value ? `0x${value.toString(16)}` : value);
        };
    }();

    // aowow - optimizer did a poo bah?
    // new Uint8Array([128, 192, 255, 255]),
    // (function () { let t; })();

    const ALPHA = 0x1906,
          RGB = 0x1907,
          RGBA = 0x1908,
          LUMINANCE = 0x1909,
          LUMINANCE_ALPHA = 0x190A,
          DEPTH_COMPONENT = 0x1902,
          DEPTH_STENCIL = 0x84F9,
          RG = 0x8227,
          RG_INTEGER = 0x8228,
          RED = 0x1903,
          RED_INTEGER = 0x8D94,
          RGB_INTEGER = 0x8D98,
          RGBA_INTEGER = 0x8D99,
          formatInfo = {};
    {
        const f = formatInfo;
        f[ALPHA]           = { numColorComponents: 1 };
        f[LUMINANCE]       = { numColorComponents: 1 };
        f[LUMINANCE_ALPHA] = { numColorComponents: 2 };
        f[RGB]             = { numColorComponents: 3 };
        f[RGBA]            = { numColorComponents: 4 };
        f[RED]             = { numColorComponents: 1 };
        f[RED_INTEGER]     = { numColorComponents: 1 };
        f[RG]              = { numColorComponents: 2 };
        f[RG_INTEGER]      = { numColorComponents: 2 };
        f[RGB]             = { numColorComponents: 3 };
        f[RGB_INTEGER]     = { numColorComponents: 3 };
        f[RGBA]            = { numColorComponents: 4 };
        f[RGBA_INTEGER]    = { numColorComponents: 4 };
        f[DEPTH_COMPONENT] = { numColorComponents: 1 };
        f[DEPTH_STENCIL]   = { numColorComponents: 2 };
    }

    const error = _error;

    function getElementById(id)
    {
        return typeof document !== "undefined" && document.getElementById ? document.getElementById(id) : null;
    }

    const TEXTURE0 = 0x84c0,
          COMPILE_STATUS = 0x8b81,
          LINK_STATUS = 0x8b82,
          FRAGMENT_SHADER = 0x8b30,
          VERTEX_SHADER = 0x8b31,
          SEPARATE_ATTRIBS = 0x8c8d,
          ACTIVE_UNIFORMS = 0x8b86,
          TRANSFORM_FEEDBACK_VARYINGS = 0x8c83,
          ACTIVE_UNIFORM_BLOCKS = 0x8a36,
          ACTIVE_ATTRIBUTES = 0x8b89,
          UNIFORM_BLOCK_REFERENCED_BY_VERTEX_SHADER = 0x8a44,
          UNIFORM_BLOCK_REFERENCED_BY_FRAGMENT_SHADER = 0x8a46,
          UNIFORM_BLOCK_DATA_SIZE = 0x8a40,
          UNIFORM_BLOCK_ACTIVE_UNIFORM_INDICES = 0x8a43,
       // ARRAY_BUFFER = 34962, // already declared
       // FLOAT = 5126, // already declared
          FLOAT_VEC2 = 0x8B50,
          FLOAT_VEC3 = 0x8B51,
          FLOAT_VEC4 = 0x8B52,
       // INT = 5124, // already declared
          INT_VEC2 = 0x8B53,
          INT_VEC3 = 0x8B54,
          INT_VEC4 = 0x8B55,
          BOOL = 0x8B56,
          BOOL_VEC2 = 0x8B57,
          BOOL_VEC3 = 0x8B58,
          BOOL_VEC4 = 0x8B59,
          FLOAT_MAT2 = 0x8B5A,
          FLOAT_MAT3 = 0x8B5B,
          FLOAT_MAT4 = 0x8B5C,
          SAMPLER_2D = 0x8B5E,
          SAMPLER_CUBE = 0x8B60,
          SAMPLER_3D = 0x8B5F,
          SAMPLER_2D_SHADOW = 0x8B62,
          FLOAT_MAT2x3 = 0x8B65,
          FLOAT_MAT2x4 = 0x8B66,
          FLOAT_MAT3x2 = 0x8B67,
          FLOAT_MAT3x4 = 0x8B68,
          FLOAT_MAT4x2 = 0x8B69,
          FLOAT_MAT4x3 = 0x8B6A,
          SAMPLER_2D_ARRAY = 0x8DC1,
          SAMPLER_2D_ARRAY_SHADOW = 0x8DC4,
          SAMPLER_CUBE_SHADOW = 0x8DC5,
       // UNSIGNED_INT = 5125, // already declared
          UNSIGNED_INT_VEC2 = 0x8DC6,
          UNSIGNED_INT_VEC3 = 0x8DC7,
          UNSIGNED_INT_VEC4 = 0x8DC8,
          INT_SAMPLER_2D = 0x8DCA,
          INT_SAMPLER_3D = 0x8DCB,
          INT_SAMPLER_CUBE = 0x8DCC,
          INT_SAMPLER_2D_ARRAY = 0x8DCF,
          UNSIGNED_INT_SAMPLER_2D = 0x8DD2,
          UNSIGNED_INT_SAMPLER_3D = 0x8DD3,
          UNSIGNED_INT_SAMPLER_CUBE = 0x8DD4,
          UNSIGNED_INT_SAMPLER_2D_ARRAY = 0x8DD7,
          TEXTURE_2D = 0X0DE1,
          TEXTURE_CUBE_MAP = 0X8513,
          TEXTURE_3D = 0X806F,
          TEXTURE_2D_ARRAY = 0X8C1A,
          typeMap = {};

    function getBindPointForSamplerType(gl, type)
    {
        return typeMap[type].bindPoint;
    }

    function floatSetter(gl, location)      { return function (v) { gl.uniform1f(location, v); }; }
    function floatArraySetter(gl, location) { return function (v) { gl.uniform1fv(location, v); }; }
    function floatVec2Setter(gl, location)  { return function (v) { gl.uniform2fv(location, v); }; }
    function floatVec3Setter(gl, location)  { return function (v) { gl.uniform3fv(location, v); }; }
    function floatVec4Setter(gl, location)  { return function (v) { gl.uniform4fv(location, v); }; }

    function intSetter(gl, location)      { return function (v) { gl.uniform1i(location, v); }; }
    function intArraySetter(gl, location) { return function (v) { gl.uniform1iv(location, v); }; }
    function intVec2Setter(gl, location)  { return function (v) { gl.uniform2iv(location, v); }; }
    function intVec3Setter(gl, location)  { return function (v) { gl.uniform3iv(location, v); }; }
    function intVec4Setter(gl, location)  { return function (v) { gl.uniform4iv(location, v); }; }

    function uintSetter(gl, location)      { return function (v) { gl.uniform1ui(location, v); }; }
    function uintArraySetter(gl, location) { return function (v) { gl.uniform1uiv(location, v); }; }
    function uintVec2Setter(gl, location)  { return function (v) { gl.uniform2uiv(location, v); }; }
    function uintVec3Setter(gl, location)  { return function (v) { gl.uniform3uiv(location, v); }; }
    function uintVec4Setter(gl, location)  { return function (v) { gl.uniform4uiv(location, v); }; }

    function floatMat2Setter(gl, location) { return function (v) { gl.uniformMatrix2fv(location, false, v); }; }
    function floatMat3Setter(gl, location) { return function (v) { gl.uniformMatrix3fv(location, false, v); }; }
    function floatMat4Setter(gl, location) { return function (v) { gl.uniformMatrix4fv(location, false, v); }; }

    function floatMat23Setter(gl, location) { return function (v) { gl.uniformMatrix2x3fv(location, false, v); }; }
    function floatMat32Setter(gl, location) { return function (v) { gl.uniformMatrix3x2fv(location, false, v); }; }
    function floatMat24Setter(gl, location) { return function (v) { gl.uniformMatrix2x4fv(location, false, v); }; }
    function floatMat42Setter(gl, location) { return function (v) { gl.uniformMatrix4x2fv(location, false, v); }; }
    function floatMat34Setter(gl, location) { return function (v) { gl.uniformMatrix3x4fv(location, false, v); }; }
    function floatMat43Setter(gl, location) { return function (v) { gl.uniformMatrix4x3fv(location, false, v); }; }

    function samplerSetter(gl, type, unit, location)
    {
        const bindPoint = getBindPointForSamplerType(gl, type);
        return iswWebGL2(gl) ? function (textureOrPair) {
            let texture, sampler;
            if (!textureOrPair || isTexture(gl, textureOrPair))
            {
                texture = textureOrPair;
                sampler = null;
            }
            else
            {
                texture = textureOrPair.texture;
                sampler = textureOrPair.sampler;
            }

            gl.uniform1i(location, unit),
            gl.activeTexture(TEXTURE0 + unit),
            gl.bindTexture(bindPoint, texture),
            gl.bindSampler(unit, sampler);
        } : function (texture) {
            gl.uniform1i(location, unit);
            gl.activeTexture(TEXTURE0 + unit);
            gl.bindTexture(bindPoint, texture);
        };
    }

    function samplerArraySetter(gl, type, unit, location, size)
    {
        const bindPoint = getBindPointForSamplerType(gl, type),
              units = new Int32Array(size);

        for (let ii = 0; ii < size; ++ii)
            units[ii] = unit + ii;

        return iswWebGL2(gl) ? function (textures) {
            gl.uniform1iv(location, units);
            textures.forEach(function (textureOrPair, index) {
                let texture, sampler;
                gl.activeTexture(TEXTURE0 + units[index]);

                if (!textureOrPair || isTexture(0, textureOrPair))
                {
                    texture = textureOrPair;
                    sampler = null;
                }
                else
                {
                    texture = textureOrPair.texture;
                    sampler = textureOrPair.sampler;
                }

                gl.bindSampler(unit, sampler);
                gl.bindTexture(bindPoint, texture);
            });
        } : function (textures) {
            gl.uniform1iv(location, units);
            textures.forEach(function (texture, index) {
                gl.activeTexture(TEXTURE0 + units[index]);
                gl.bindTexture(bindPoint, texture);
            });
        };
    }

    function floatAttribSetter(gl, index)
    {
        return function (b)
        {
            if (b.value)
            {
                gl.disableVertexAttribArray(index);

                switch (b.value.length)
                {
                    case  4: gl.vertexAttrib4fv(index, b.value); break;
                    case  3: gl.vertexAttrib3fv(index, b.value); break;
                    case  2: gl.vertexAttrib2fv(index, b.value); break;
                    case  1: gl.vertexAttrib1fv(index, b.value); break;
                    default:
                        throw new Error("the length of a float constant value must be between 1 and 4!");
                }
            }
            else
            {
                gl.bindBuffer(ARRAY_BUFFER, b.buffer);
                gl.enableVertexAttribArray(index);
                gl.vertexAttribPointer(index, b.numComponents || b.size, b.type || FLOAT, b.normalize || false, b.stride || 0, b.offset || 0);
                if (gl.vertexAttribDivisor)
                    gl.vertexAttribDivisor(index, b.divisor || 0);
            }
        };
    }

    function intAttribSetter(gl, index)
    {
        return function (b)
        {
            if (b.value)
            {
                gl.disableVertexAttribArray(index);

                if (b.value.length === 4)
                    gl.vertexAttrib4iv(index, b.value);
                else
                    throw new Error("The length of an integer constant value must be 4!");
            }
            else
            {
                gl.bindBuffer(ARRAY_BUFFER, b.buffer);
                gl.enableVertexAttribArray(index);
                gl.vertexAttribIPointer(index, b.numComponents || b.size, b.type || INT, b.stride || 0, b.offset || 0);
                if (gl.vertexAttribDivisor)
                    gl.vertexAttribDivisor(index, b.divisor || 0);
            }
        };
    }

    function uintAttribSetter(gl, index)
    {
        return function (b)
        {
            if (b.value)
            {
                gl.disableVertexAttribArray(index);

                if (b.value.length === 4)
                    gl.vertexAttrib4uiv(index, b.value);
                else
                    throw new Error("The length of an unsigned integer constant value must be 4!");
            }
            else
            {
                gl.bindBuffer(ARRAY_BUFFER, b.buffer);
                gl.enableVertexAttribArray(index);
                gl.vertexAttribIPointer(index, b.numComponents || b.size, b.type || UNSIGNED_INT, b.stride || 0, b.offset || 0);
                if (gl.vertexAttribDivisor)
                    gl.vertexAttribDivisor(index, b.divisor || 0);
            }
        };
    }

    function matAttribSetter(gl, index, typeInfo)
    {
        const defaultSize = typeInfo.size,
              count = typeInfo.count;

        return function (b)
        {
            gl.bindBuffer(ARRAY_BUFFER, b.buffer);
            const numComponents = b.size || b.numComponents || defaultSize,
                  size = numComponents / count,
                  type = b.type || FLOAT,
                  stride = typeMap[type].size * numComponents,
                  normalize = b.normalize || false,
                  offset = b.offset || 0,
                  rowOffset = stride / count;

            for (let i = 0; i < count; ++i)
            {
                gl.enableVertexAttribArray(index + i);
                gl.vertexAttribPointer(index + i, size, type, normalize, stride, offset + rowOffset * i);
                if (gl.vertexAttribDivisor)
                    gl.vertexAttribDivisor(index + i, b.divisor || 0);
            }
        };
    }

    typeMap[FLOAT]      = { Type: Float32Array, size: 4,  setter: floatSetter,     arraySetter: floatArraySetter };
    typeMap[FLOAT_VEC2] = { Type: Float32Array, size: 8,  setter: floatVec2Setter, cols: 2 };
    typeMap[FLOAT_VEC3] = { Type: Float32Array, size: 12, setter: floatVec3Setter, cols: 3 };
    typeMap[FLOAT_VEC4] = { Type: Float32Array, size: 16, setter: floatVec4Setter, cols: 4 };

    typeMap[INT]      = { Type: Int32Array,   size: 4,  setter: intSetter,     arraySetter: intArraySetter };
    typeMap[INT_VEC2] = { Type: Int32Array,   size: 8,  setter: intVec2Setter, cols: 2 };
    typeMap[INT_VEC3] = { Type: Int32Array,   size: 12, setter: intVec3Setter, cols: 3 };
    typeMap[INT_VEC4] = { Type: Int32Array,   size: 16, setter: intVec4Setter, cols: 4 };

    typeMap[UNSIGNED_INT]      = { Type: Uint32Array, size: 4,  setter: uintSetter,     arraySetter: uintArraySetter };
    typeMap[UNSIGNED_INT_VEC2] = { Type: Uint32Array, size: 8,  setter: uintVec2Setter, cols: 2, };
    typeMap[UNSIGNED_INT_VEC3] = { Type: Uint32Array, size: 12, setter: uintVec3Setter, cols: 3, };
    typeMap[UNSIGNED_INT_VEC4] = { Type: Uint32Array, size: 16, setter: uintVec4Setter, cols: 4, };

    typeMap[BOOL]      = { Type: Uint32Array, size: 4,  setter: intSetter, arraySetter: intArraySetter };
    typeMap[BOOL_VEC2] = { Type: Uint32Array, size: 8,  setter: intVec2Setter, cols: 2 };
    typeMap[BOOL_VEC3] = { Type: Uint32Array, size: 12, setter: intVec3Setter, cols: 3 };
    typeMap[BOOL_VEC4] = { Type: Uint32Array, size: 16, setter: intVec4Setter, cols: 4 };

    typeMap[FLOAT_MAT2] = { Type: Float32Array, size: 32, setter: floatMat2Setter, rows: 2, cols: 2};
    typeMap[FLOAT_MAT3] = { Type: Float32Array, size: 48, setter: floatMat3Setter, rows: 3, cols: 3};
    typeMap[FLOAT_MAT4] = { Type: Float32Array, size: 64, setter: floatMat4Setter, rows: 4, cols: 4};

    typeMap[FLOAT_MAT2x3] = { Type: Float32Array, size: 32, setter: floatMat23Setter, rows: 2, cols: 3};
    typeMap[FLOAT_MAT2x4] = { Type: Float32Array, size: 32, setter: floatMat32Setter, rows: 2, cols: 4};
    typeMap[FLOAT_MAT3x2] = { Type: Float32Array, size: 48, setter: floatMat24Setter, rows: 3, cols: 2};
    typeMap[FLOAT_MAT3x4] = { Type: Float32Array, size: 48, setter: floatMat42Setter, rows: 3, cols: 4};
    typeMap[FLOAT_MAT4x2] = { Type: Float32Array, size: 64, setter: floatMat34Setter, rows: 4, cols: 2};
    typeMap[FLOAT_MAT4x3] = { Type: Float32Array, size: 64, setter: floatMat43Setter, rows: 4, cols: 3};

    typeMap[SAMPLER_2D]                    = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D };
    typeMap[SAMPLER_CUBE]                  = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_CUBE_MAP };
    typeMap[SAMPLER_3D]                    = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_3D };
    typeMap[SAMPLER_2D_SHADOW]             = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D };
    typeMap[SAMPLER_2D_ARRAY]              = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D_ARRAY };
    typeMap[SAMPLER_2D_ARRAY_SHADOW]       = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D_ARRAY };
    typeMap[SAMPLER_CUBE_SHADOW]           = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_CUBE_MAP };
    typeMap[INT_SAMPLER_2D]                = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D };
    typeMap[INT_SAMPLER_3D]                = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_3D };
    typeMap[INT_SAMPLER_CUBE]              = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_CUBE_MAP };
    typeMap[INT_SAMPLER_2D_ARRAY]          = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D_ARRAY };
    typeMap[UNSIGNED_INT_SAMPLER_2D]       = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D };
    typeMap[UNSIGNED_INT_SAMPLER_3D]       = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_3D };
    typeMap[UNSIGNED_INT_SAMPLER_CUBE]     = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_CUBE_MAP };
    typeMap[UNSIGNED_INT_SAMPLER_2D_ARRAY] = { Type: null, size: 0, setter: samplerSetter, arraySetter: samplerArraySetter, bindPoint: TEXTURE_2D_ARRAY };

    const attrTypeMap = {};
    attrTypeMap[FLOAT]      = { size: 4,  setter: floatAttribSetter };
    attrTypeMap[FLOAT_VEC2] = { size: 8,  setter: floatAttribSetter };
    attrTypeMap[FLOAT_VEC3] = { size: 12, setter: floatAttribSetter };
    attrTypeMap[FLOAT_VEC4] = { size: 16, setter: floatAttribSetter };

    attrTypeMap[INT]      = { size: 4,  setter: intAttribSetter };
    attrTypeMap[INT_VEC2] = { size: 8,  setter: intAttribSetter };
    attrTypeMap[INT_VEC3] = { size: 12, setter: intAttribSetter };
    attrTypeMap[INT_VEC4] = { size: 16, setter: intAttribSetter };

    attrTypeMap[UNSIGNED_INT]      = { size: 4,  setter: uintAttribSetter };
    attrTypeMap[UNSIGNED_INT_VEC2] = { size: 8,  setter: uintAttribSetter };
    attrTypeMap[UNSIGNED_INT_VEC3] = { size: 12, setter: uintAttribSetter };
    attrTypeMap[UNSIGNED_INT_VEC4] = { size: 16, setter: uintAttribSetter };

    attrTypeMap[BOOL]      = { size: 4,  setter: intAttribSetter };
    attrTypeMap[BOOL_VEC2] = { size: 8,  setter: intAttribSetter };
    attrTypeMap[BOOL_VEC3] = { size: 12, setter: intAttribSetter };
    attrTypeMap[BOOL_VEC4] = { size: 16, setter: intAttribSetter };

    attrTypeMap[FLOAT_MAT2] = { size: 4,  setter: matAttribSetter, count: 2 };
    attrTypeMap[FLOAT_MAT3] = { size: 9,  setter: matAttribSetter, count: 3 };
    attrTypeMap[FLOAT_MAT4] = { size: 16, setter: matAttribSetter, count: 4 };

    const errorRE = /ERROR:\s*\d+:(\d+)/gi;

    function addLineNumbersWithError(src)
    {
        var log = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var lineOffset = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;

        var matches = [...log.matchAll(errorRE)]

        var lineNoToErrorMap = new Map(matches.map((m, ndx) => {
            var lineNo = parseInt(m[1]);
            var next = matches[ndx + 1];
            var end = next ? next.index : log.length;
            var msg = log.substring(m.index, end);
            return [lineNo - 1, msg];
        }));
        return src.split("\n").map((line, lineNo) => {
            var err = lineNoToErrorMap.get(lineNo);
            return `${lineNo + 1 + lineOffset}: ${line}${err ? `\n\n^^^ ${err}` : ""}`;
        }).join("\n");
    }

    const spaceRE = /^[ \t]*\n/;

    function prepShaderSource(shaderSource)
    {
        let lineOffset = 0;
        if (spaceRE.test(shaderSource))
        {
            lineOffset = 1;
            shaderSource = shaderSource.replace(spaceRE, "");
        }

        return { lineOffset: lineOffset, shaderSource: shaderSource };
    }

    function reportError(progOptions, msg)
    {
        progOptions.errorCallback(msg);

        if (progOptions.callback)
            setTimeout(() => {
                progOptions.callback(`${msg}\n${progOptions.errors.join("\n")}`);
            });

        return null;
    }

    function checkShaderStatus(gl, shaderType, shader, errorFn)
    {
        errorFn = errorFn || error;

        let compiled = gl.getShaderParameter(shader, COMPILE_STATUS);

        if (!compiled)
        {
            const lastError = gl.getShaderInfoLog(shader),
                { lineOffset: lineOffset, shaderSource: shaderSource } = prepShaderSource(gl.getShaderSource(shader));

            errorFn(`${addLineNumbersWithError(shaderSource, lastError, lineOffset)}\nError compiling ${glEnumToString(gl, shaderType)}: ${lastError}`);
        }

        return compiled;
    }

    function getProgramOptions(opt_attribs, opt_locations, opt_errorCallback)
    {
        let transformFeedbackVaryings,
            transformFeedbackMode,
            callback;

        if (typeof opt_locations == "function")
        {
            opt_errorCallback = opt_locations;
            opt_locations = undefined;
        }

        if (typeof opt_attribs == "function")
        {
            opt_errorCallback = opt_attribs;
            opt_attribs = undefined;
        }
        else if (opt_attribs && !Array.isArray(opt_attribs))
        {
            const opt = opt_attribs;
            opt_errorCallback = opt.errorCallback;
            opt_attribs = opt.attribLocations;
            transformFeedbackVaryings = opt.transformFeedbackVaryings;
            transformFeedbackMode = opt.transformFeedbackMode;
            callback = opt.callback;
        }

        const _errorCallback = opt_errorCallback || error,
              errors = [],
              options = {
                  errorCallback(msg, ...e)
                  {
                      errors.push(msg);
                      _errorCallback(msg, ...e);
                  },
                  transformFeedbackVaryings: transformFeedbackVaryings,
                  transformFeedbackMode: transformFeedbackMode,
                  callback: callback,
                  errors: errors,
              };

        if (opt_attribs)
        {
            let attribLocations = {};

            if (Array.isArray(opt_attribs))
                opt_attribs.forEach((attrib, ndx) => { attribLocations[attrib] = opt_locations ? opt_locations[ndx] : ndx; });
            else
                attribLocations = opt_attribs;

            options.attribLocations = attribLocations;
        }

        return options;
    }

    const defaultShaderType = ["VERTEX_SHADER", "FRAGMENT_SHADER"];

    function getShaderTypeFromScriptType(gl, scriptType)
    {
        if (scriptType.indexOf("frag") >= 0)
            return FRAGMENT_SHADER;
        if (scriptType.indexOf("vert") >= 0)
            return VERTEX_SHADER;

        return undefined;
    }

    function deleteProgramAndShaders(gl, program, notThese)
    {
        var shaders = gl.getAttachedShaders(program);

        for (const shader of shaders)
            if (notThese.has(shader))
                gl.deleteShader(shader);

        gl.deleteProgram(program);
    }

    const wait = (ms = 0) => new Promise((resolve) => setTimeout(resolve, ms));

    function createProgramNoCheck(gl, shaders, opt_attribs)
    {
        const program = gl.createProgram(),
            { attribLocations: attribLocations, transformFeedbackVaryings: transformFeedbackVaryings, transformFeedbackMode: transformFeedbackMode } = getProgramOptions(opt_attribs);

        for (let ndx = 0; ndx < shaders.length; ++ndx)
        {
            let shader = shaders[ndx];
            if (typeof shader == "string")
            {
                const elem = getElementById(shader),
                      src  = elem ? elem.text : shader;
                let type = gl[defaultShaderType[ndx]];

                if (elem && elem.type)
                    type = getShaderTypeFromScriptType(0, elem.type) || type;

                shader = gl.createShader(type);
                gl.shaderSource(shader, prepShaderSource(src).shaderSource);
                gl.compileShader(shader);
                gl.attachShader(program, shader);
            }
        }

        Object.entries(attribLocations).forEach(([attrib, location]) => gl.bindAttribLocation(program, location, attrib));

        {
            let varyings = transformFeedbackVaryings;
            if (varyings)
            {
                if (varyings.attribs)
                    varyings = varyings.attribs;

                if (!Array.isArray(varyings))
                    varyings = Object.keys(varyings);

                gl.transformFeedbackVaryings(program, varyings, transformFeedbackMode || SEPARATE_ATTRIBS);
            }
        }

        gl.linkProgram(program);

        return program;
    }

    function createProgram(gl, shaderSources, opt_attribs, opt_locations, opt_errorCallback)
    {
        const progOptions = getProgramOptions(opt_attribs, opt_locations, opt_errorCallback),
              shaderSet = new Set(shaderSources),
              program = createProgramNoCheck(gl, shaderSources, progOptions);

        function hasErrors(gl, program)
        {
            const errors = getProgramErrors(gl, program, progOptions.errorCallback);

            if (errors)
                deleteProgramAndShaders(gl, program, shaderSet);

            return errors;
        }
        if (!progOptions.callback)
            return hasErrors(gl, program) ? undefined : program;

        checkForProgramLinkCompletionAsync(gl, program).then(() => {
            const errors  = hasErrors(gl, program);
            progOptions.callback(errors , errors  ? undefined : program);
        });
    }

    function wrapCallbackFnToAsyncFn(fn)
    {
        return function(gl, arg1, ...args)
        {
            return new Promise((resolve, reject) => {
                const progOptions = getProgramOptions(...args);
                progOptions.callback = (err, program) => { err ? reject(err) : resolve(program); };
                fn(gl, arg1, progOptions);
            });
        };
    }

    wrapCallbackFnToAsyncFn(createProgram);
    wrapCallbackFnToAsyncFn(createProgramInfo);

    async function checkForProgramLinkCompletionAsync(gl, program)
    {
        const ext = gl.getExtension("KHR_parallel_shader_compile"),
              checkFn = ext ? (gl, program) => gl.getProgramParameter(program, ext.COMPLETION_STATUS_KHR) : () => true;

        let ms = 0;
        do
        {
            await wait(ms);
            ms = 1000 / 60;
        }
        while (!checkFn(gl, program));
    }

    function getProgramErrors(gl, program, errFn)
    {
        errFn = errFn || error;

        if (!gl.getProgramParameter(program, LINK_STATUS))
        {
            const lastError = gl.getProgramInfoLog(program);
            errFn(`Error in program linking: ${lastError}`);

            return `${lastError}\n${gl.getAttachedShaders(program).map((shader) => checkShaderStatus(gl, gl.getShaderParameter(shader, gl.SHADER_TYPE), shader, errFn)).filter((x) => x).join("\n")}`;
        }
    }

    function createProgramFromSources(gl, shaderSources, opt_attribs, opt_locations, opt_errorCallback)
    {
        return createProgram(gl, shaderSources, opt_attribs, opt_locations, opt_errorCallback);
    }

    function isBuiltIn(info)
    {
        const name = info.name;
        return name.startsWith("gl_") || name.startsWith("webgl_");
    }

    const tokenRE = /(\.|\[|]|\w+)/g,
          isDigit = (s) => s >= "0" && s <= "9";

    function addSetterToUniformTree(fullPath, setter, node, uniformSetters)
    {
        const tokens = fullPath.split(tokenRE).filter((s) => "" !== s);
        let tokenNdx = 0,
            path = "";

        for (;;)
        {
            const token = tokens[tokenNdx++];
            path += token;
            const isArrayIndex = isDigit(token[0]),
                  accessor = isArrayIndex ? parseInt(token) : token;

            if (isArrayIndex)
                path += tokens[tokenNdx++];

            if (tokenNdx === tokens.length)
            {
                node[accessor] = setter;
                break;
            }

            const _token = tokens[tokenNdx++],
                  isArray = "[" === _token,
                  child = node[accessor] || (isArray ? [] : {});

            node[accessor] = child;
            node = child;
            uniformSetters[path] = uniformSetters[path] || function (node) {
                return function (value) { setUniformTree(node, value); };
            }(child);
            path += _token;
        }
    }

    function createUniformSetters(gl, program)
    {
        let textureunit = 0;

        function createUniformSetter(program, uniformInfo, location)
        {
            const isArray = uniformInfo.name.endsWith("[0]"),
                  type = uniformInfo.type,
                  typeInfo = typeMap[type];

            if (!typeInfo)
                throw new Error(`unknown type: 0x${type.toString(16)}`);

            let setter;
            if (typeInfo.bindPoint)
            {
                const unit = textureunit;
                textureunit += uniformInfo.size;
                setter = isArray ? typeInfo.arraySetter(gl, type, unit, location, uniformInfo.size) : typeInfo.setter(gl, type, unit, location, uniformInfo.size);
            }
            else
                setter = typeInfo.arraySetter && isArray ? typeInfo.arraySetter(gl, location) : typeInfo.setter(gl, location);

            setter.location = location;

            return setter;
        }

        const uniformSetters = {},
              uniformTree = {},
              numUniforms = gl.getProgramParameter(program, ACTIVE_UNIFORMS);

        for (let ii = 0; ii < numUniforms; ++ii)
        {
            const uniformInfo = gl.getActiveUniform(program, ii);
            if (isBuiltIn(uniformInfo))
                continue;

            let name = uniformInfo.name;
            if (name.endsWith("[0]"))
                name = name.substr(0, name.length - 3);

            const location = gl.getUniformLocation(program, uniformInfo.name);
            if (location)
            {
                const setter = createUniformSetter(0, uniformInfo, location);
                uniformSetters[name] = setter;
                addSetterToUniformTree(name, setter, uniformTree, uniformSetters);
            }
        }

        return uniformSetters;
    }

    function createTransformFeedbackInfo(gl, program)
    {
        const info = {},
              numVaryings = gl.getProgramParameter(program, TRANSFORM_FEEDBACK_VARYINGS);

        for (let ii = 0; ii < numVaryings; ++ii)
        {
            const varying = gl.getTransformFeedbackVarying(program, ii);
            info[varying.name] = { index: ii, type: varying.type, size: varying.size };
        }

        return info;
    }

    function createUniformBlockSpecFromProgram(gl, program)
    {
        const numUniforms = gl.getProgramParameter(program, ACTIVE_UNIFORMS),
              uniformData = [],
              uniformIndices = [];

        for (let ii = 0; ii < numUniforms; ++ii)
        {
            uniformIndices.push(ii), uniformData.push({});
            const uniformInfo = gl.getActiveUniform(program, ii);
            uniformData[ii].name = uniformInfo.name;
        }

        [["UNIFORM_TYPE", "type"],            ["UNIFORM_SIZE", "size"],
         ["UNIFORM_BLOCK_INDEX", "blockNdx"], ["UNIFORM_OFFSET", "offset"]].forEach(function (pair) {
            const pname = pair[0],
                  key = pair[1];
            gl.getActiveUniforms(program, uniformIndices, gl[pname]).forEach(function (value, ndx) {
                uniformData[ndx][key] = value;
            });
        });

        const blockSpecs = {},
              numUniformBlocks = gl.getProgramParameter(program, ACTIVE_UNIFORM_BLOCKS);

        for (let ii = 0; ii < numUniformBlocks; ++ii)
        {
            const name = gl.getActiveUniformBlockName(program, ii),
                  blockSpec = {
                      index: gl.getUniformBlockIndex(program, name),
                      usedByVertexShader: gl.getActiveUniformBlockParameter(program, ii, UNIFORM_BLOCK_REFERENCED_BY_VERTEX_SHADER),
                      usedByFragmentShader: gl.getActiveUniformBlockParameter(program, ii, UNIFORM_BLOCK_REFERENCED_BY_FRAGMENT_SHADER),
                      size: gl.getActiveUniformBlockParameter(program, ii, UNIFORM_BLOCK_DATA_SIZE),
                      uniformIndices: gl.getActiveUniformBlockParameter(program, ii, UNIFORM_BLOCK_ACTIVE_UNIFORM_INDICES)
                  };

            blockSpec.used = blockSpec.usedByVertexShader || blockSpec.usedByFragmentShader;
            blockSpecs[name] = blockSpec;
        }

        return { blockSpecs: blockSpecs, uniformData: uniformData };
    }

    function setUniformTree(tree, values)
    {
        for (const name in values)
        {
            const prop = tree[name];
            typeof prop == "function" ? prop(values[name]) : setUniformTree(tree[name], values[name]);
        }
    }

    function setUniforms(setters, ...args)
    {
        const actualSetters = setters.uniformSetters || setters,
              numArgs = args.length;

        for (let aNdx = 0; aNdx < numArgs; ++aNdx)
        {
            const values = args[aNdx];
            if (Array.isArray(values))
            {
                const numValues = values.length;
                for (let ii = 0; ii < numValues; ++ii)
                    setUniforms(actualSetters, values[ii]);
            }
            else
            {
                for (const name in values)
                {
                    const setter = actualSetters[name];
                    if (setter)
                        setter(values[name]);
                }
            }
        }
    }

    function createAttributeSetters(gl, program)
    {
        const attribSetters = {},
            numAttribs = gl.getProgramParameter(program, ACTIVE_ATTRIBUTES);

        for (let ii = 0; ii < numAttribs; ++ii)
        {
            const attribInfo = gl.getActiveAttrib(program, ii);
            if (isBuiltIn(attribInfo))
                continue;

            const index = gl.getAttribLocation(program, attribInfo.name),
                  typeInfo = attrTypeMap[attribInfo.type],
                  setter = typeInfo.setter(gl, index, typeInfo);

            setter.location = index;
            attribSetters[attribInfo.name] = setter;
        }

        return attribSetters;
    }

    function createProgramInfoFromProgram(gl, program)
    {
        const programInfo = {
            program: program,
            uniformSetters: createUniformSetters(gl, program),
            attribSetters: createAttributeSetters(gl, program)
        };

        if (iswWebGL2(gl))
        {
            programInfo.uniformBlockSpec = createUniformBlockSpecFromProgram(gl, program);
            programInfo.transformFeedbackInfo = createTransformFeedbackInfo(gl, program);
        }

        return programInfo;
    }

    const notIdRE = /\s|{|}|;/;

    function createProgramInfo(gl, shaderSources, opt_attribs, opt_locations, opt_errorCallback)
    {
        const progOptions = getProgramOptions(opt_attribs, opt_locations, opt_errorCallback),
              errors = [];

        shaderSources = shaderSources.map(function (source) {
            if (!notIdRE.test(source))
            {
                const script = getElementById(source);
                if (!script)
                {
                    const err = `no element with id: ${source}`;
                    progOptions.errorCallback(err);
                    errors.push(err);
                }
                else
                    source = script.text;
            }

            return source;
        });

        if (errors.length)
            return reportError(progOptions, "");

        const origCallback = progOptions.callback;

        if (origCallback)
            progOptions.callback = (err, program) => {
                origCallback(err, err ? undefined : createProgramInfoFromProgram(gl, program));
            };

        const program = createProgramFromSources(gl, shaderSources, progOptions);

        return program ? createProgramInfoFromProgram(gl, program) : null;
    }

    function checkAllPrograms(gl, programs, programSpecs, noDeleteShadersSet, programOptions)
    {
        for (const [name, program] of Object.entries(programs))
        {
            const options = { ...programOptions },
                  spec = programSpecs[name];

            if (!Array.isArray(spec))
                Object.assign(options, spec);

            const errors = getProgramErrors(gl, program, options.errorCallback);
            if (errors)
            {
                for (const _program of Object.values(programs))
                {
                    const shaders = gl.getAttachedShaders(_program);
                    gl.deleteProgram(_program);

                    for (const shader of shaders)
                        if (!noDeleteShadersSet.has(shader))
                            gl.deleteShader(shader);
                }

                return errors;
            }
        }
    }

    function createPrograms(gl, programSpecs, programOptions = {})
    {
        const noDeleteShadersSet = new Set(),
              programs = Object.fromEntries(Object.entries(programSpecs).map(([name, spec]) => {
                  const options = { ...programOptions },
                        shaders = Array.isArray(spec) ? spec : spec.shaders;

                    if (!Array.isArray(spec))
                        Object.assign(options, spec);

                    shaders.forEach(noDeleteShadersSet.add, noDeleteShadersSet);

                    return [name, createProgramNoCheck(gl, shaders, options)];
              }));

        if (programOptions.callback)
        {
            (async function (gl, programs) {
                for (const program of Object.values(programs))
                    await checkForProgramLinkCompletionAsync(gl, program);
            })(gl, programs).then(() => {
                const errors = checkAllPrograms(gl, programs, programSpecs, noDeleteShadersSet, programOptions);
                programOptions.callback(errors, errors ? undefined : programs);
            });

            return undefined;
        }

        return checkAllPrograms(gl, programs, programSpecs, noDeleteShadersSet, programOptions) ? undefined : programs;
    }

    function createProgramInfos(gl, programSpecs, programOptions)
    {
        function createProgramInfosForPrograms(gl, programs)
        {
            return Object.fromEntries(Object.entries(programs).map(([name, program]) => [name, createProgramInfoFromProgram(gl, program)]));
        }

        const origCallback = (programOptions = getProgramOptions(programOptions)).callback;
        if (origCallback)
            programOptions.callback = (err, programs) => {
                origCallback(err, err ? undefined : createProgramInfosForPrograms(gl, programs));
            };

        const programs = createPrograms(gl, programSpecs, programOptions);

        return (origCallback || !programs) ? undefined : createProgramInfosForPrograms(gl, programs);
    }

    wrapCallbackFnToAsyncFn(createPrograms),
    wrapCallbackFnToAsyncFn(createProgramInfos);

    const DEPTH_COMPONENT24 = 0x81a6,
          DEPTH_COMPONENT32F = 0x8cac,
          DEPTH24_STENCIL8 = 0x88f0,
          DEPTH32F_STENCIL8 = 0x8cad,
          RGBA4 = 0x8056,
          RGB5_A1 = 0x8057,
          RGB565 = 0x8D62,
          DEPTH_COMPONENT16 = 0x81A5,
          STENCIL_INDEX = 0x1901,
          STENCIL_INDEX8 = 0x8D48,
       // DEPTH_STENCIL = 0x84F9, // already declared
          DEPTH_ATTACHMENT = 0x8D00,
          STENCIL_ATTACHMENT = 0x8D20,
          DEPTH_STENCIL_ATTACHMENT = 0x821A,
          attachmentsByFormat = {};

    attachmentsByFormat[DEPTH_STENCIL] = DEPTH_STENCIL_ATTACHMENT;
    attachmentsByFormat[STENCIL_INDEX] = STENCIL_ATTACHMENT;
    attachmentsByFormat[STENCIL_INDEX8] = STENCIL_ATTACHMENT;
    attachmentsByFormat[DEPTH_COMPONENT] = DEPTH_ATTACHMENT;
    attachmentsByFormat[DEPTH_COMPONENT16] = DEPTH_ATTACHMENT;
    attachmentsByFormat[DEPTH_COMPONENT24] = DEPTH_ATTACHMENT;
    attachmentsByFormat[DEPTH_COMPONENT32F] = DEPTH_ATTACHMENT;
    attachmentsByFormat[DEPTH24_STENCIL8] = DEPTH_STENCIL_ATTACHMENT;
    attachmentsByFormat[DEPTH32F_STENCIL8] = DEPTH_STENCIL_ATTACHMENT;

    const renderbufferFormats = {};

    renderbufferFormats[RGBA4] = true;
    renderbufferFormats[RGB5_A1] = true;
    renderbufferFormats[RGB565] = true;
    renderbufferFormats[DEPTH_STENCIL] = true;
    renderbufferFormats[DEPTH_COMPONENT16] = true;
    renderbufferFormats[STENCIL_INDEX] = true;
    [STENCIL_INDEX8] = true;
}
