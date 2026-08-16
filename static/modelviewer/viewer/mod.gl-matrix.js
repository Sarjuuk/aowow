/* gl-matrix - High performance matrix and vector operations
 * @author Brandon Jones
 * @author Colin MacKenzie IV
 * Copyright (c) 2015-2025, Brandon Jones, Colin MacKenzie IV.
 */

export function gl_matrix()
{

    var GLMAT_EPSILON = 0.000001,
        GLMAT_ARRAY_TYPE = "undefined" != typeof Float32Array ? Float32Array : Array;

    function vec3Create()
    {
        var vec = new GLMAT_ARRAY_TYPE(3);

        if (GLMAT_ARRAY_TYPE != Float32Array)
        {
            vec[0] = 0;
            vec[1] = 0;
            vec[2] = 0;
        }

        return vec;
    }

    function vec3Len(vec)
    {
        var x = vec[0],
            y = vec[1],
            z = vec[2];

        return Math.hypot(x, y, z);
    }

    function vec3FromValues(x, y, z)
    {
        var vec = new GLMAT_ARRAY_TYPE(3);
        vec[0] = x;
        vec[1] = y;
        vec[2] = z;

        return vec;
    }

    function vec3Copy(vec, a)
    {
        vec[0] = a[0];
        vec[1] = a[1];
        vec[2] = a[2];

        return vec;
    }

    function vec3Set(vec, x, y, z)
    {
        vec[0] = x;
        vec[1] = y;
        vec[2] = z;

        return vec;
    }

    function vec3Add(vec, a, b)
    {
        vec[0] = a[0] + b[0];
        vec[1] = a[1] + b[1];
        vec[2] = a[2] + b[2];

        return vec;
    }

    function vec3Sub(vec, a, b)
    {
        vec[0] = a[0] - b[0];
        vec[1] = a[1] - b[1];
        vec[2] = a[2] - b[2];

        return vec;
    }

    function vec3Mult(vec, a, b)
    {
        vec[0] = a[0] * b[0];
        vec[1] = a[1] * b[1];
        vec[2] = a[2] * b[2];

        return vec;
    }

    function vec3Min(vec, a, b)
    {
        vec[0] = Math.min(a[0], b[0]);
        vec[1] = Math.min(a[1], b[1]);
        vec[2] = Math.min(a[2], b[2]);

        return vec;
    }

    function vec3Max(vec, a, b)
    {
        vec[0] = Math.max(a[0], b[0]);
        vec[1] = Math.max(a[1], b[1]);
        vec[2] = Math.max(a[2], b[2]);

        return vec;
    }

    function vec3Scale(vec, a, scale)
    {
        vec[0] = a[0] * scale;
        vec[1] = a[1] * scale;
        vec[2] = a[2] * scale;

        return vec;
    }

    function vec3ScaleAdd(vec, a, b, scale)
    {
        vec[0] = a[0] + b[0] * scale;
        vec[1] = a[1] + b[1] * scale;
        vec[2] = a[2] + b[2] * scale;

        return vec;
    }

    function vec3SquareLen(a)
    {
        var x = a[0],
            y = a[1],
            z = a[2];

        return x * x + y * y + z * z;
    }

    function vec3Negate(vec, a)
    {
        vec[0] = -a[0];
        vec[1] = -a[1];
        vec[2] = -a[2];

        return vec;
    }

    function vec3Normalize(vec, a)
    {
        var x = a[0],
            y = a[1],
            z = a[2],
            len = x * x + y * y + z * z;

        if (len > 0)
        {
            len = 1 / Math.sqrt(len);
            vec[0] = a[0] * len;
            vec[1] = a[1] * len;
            vec[2] = a[2] * len;
        }

        return vec;
    }

    function vec3Dot(a, b)
    {
        return a[0] * b[0] + a[1] * b[1] + a[2] * b[2];
    }

    function vec3Cross(vec, a, b)
    {
        var ax = a[0],
            ay = a[1],
            az = a[2],
            bx = b[0],
            by = b[1],
            bz = b[2];

        vec[0] = ay * bz - az * by;
        vec[1] = az * bx - ax * bz;
        vec[2] = ax * by - ay * bx;

        return vec;
    }

    function vec3Lerp(vec, a, b, t)
    {
        var ax = a[0],
            ay = a[1],
            az = a[2];

        vec[0] = ax + t * (b[0] - ax);
        vec[1] = ay + t * (b[1] - ay);
        vec[2] = az + t * (b[2] - az);

        return vec;
    }

    function vec3TransformMat4(vec, a, mat)
    {
        var x = a[0],
            y = a[1],
            z = a[2],
            norm = mat[3] * x + mat[7] * y + mat[11] * z + mat[15];

            norm = norm || 1;
            vec[0] = (mat[0] * x + mat[4] * y + mat[8]  * z + mat[12]) / norm;
            vec[1] = (mat[1] * x + mat[5] * y + mat[9]  * z + mat[13]) / norm;
            vec[2] = (mat[2] * x + mat[6] * y + mat[10] * z + mat[14]) / norm;

        return vec;
    }

    function vec3TransformMat3(vec, a, mat) {
        var x = a[0],
            y = a[1],
            z = a[2];

        vec[0] = x * mat[0] + y * mat[3] + z * mat[6];
        vec[1] = x * mat[1] + y * mat[4] + z * mat[7];
        vec[2] = x * mat[2] + y * mat[5] + z * mat[8];

        return vec;
    }

    Math.hypot || (Math.hypot = function ()
    {
        for (var t = 0, len = arguments.length; len--; )
            t += arguments[len] * arguments[len];
        return Math.sqrt(t);
    });

    var C3Vector,
        vec3Subtract = vec3Sub,
        vec3Length   = vec3Len;

    C3Vector = vec3Create();

    function mat4Create()
    {
        var mat = new GLMAT_ARRAY_TYPE(16);

        if (GLMAT_ARRAY_TYPE != Float32Array)
        {
            mat[0]  = 1;
            mat[1]  = 0;
            mat[2]  = 0;
            mat[3]  = 0;
            mat[4]  = 0;
            mat[5]  = 1;
            mat[6]  = 0;
            mat[7]  = 0;
            mat[8]  = 0;
            mat[9]  = 0;
            mat[10] = 1;
            mat[11] = 0;
            mat[12] = 0;
            mat[13] = 0;
            mat[14] = 0;
            mat[15] = 1;
        }

        return mat;

    }

    function mat4Copy(mat, a)
    {
        mat[0]  = a[0];
        mat[1]  = a[1];
        mat[2]  = a[2];
        mat[3]  = a[3];
        mat[4]  = a[4];
        mat[5]  = a[5];
        mat[6]  = a[6];
        mat[7]  = a[7];
        mat[8]  = a[8];
        mat[9]  = a[9];
        mat[10] = a[10];
        mat[11] = a[11];
        mat[12] = a[12];
        mat[13] = a[13];
        mat[14] = a[14];
        mat[15] = a[15];

        return mat;
    }

    function mat4FromValues(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p)
    {
        var mat = new GLMAT_ARRAY_TYPE(16);

        mat[0]  = a;
        mat[1]  = b;
        mat[2]  = c;
        mat[3]  = d;
        mat[4]  = e;
        mat[5]  = f;
        mat[6]  = g;
        mat[7]  = h;
        mat[8]  = i;
        mat[9]  = j;
        mat[10] = k;
        mat[11] = l;
        mat[12] = m;
        mat[13] = n;
        mat[14] = o;
        mat[15] = p;

        return mat;
    }

    function mat4Identity(mat)
    {
        mat[0]  = 1;
        mat[1]  = 0;
        mat[2]  = 0;
        mat[3]  = 0;
        mat[4]  = 0;
        mat[5]  = 1;
        mat[6]  = 0;
        mat[7]  = 0;
        mat[8]  = 0;
        mat[9]  = 0;
        mat[10] = 1;
        mat[11] = 0;
        mat[12] = 0;
        mat[13] = 0;
        mat[14] = 0;
        mat[15] = 1;

        return mat;
    }

    function mat4Transpose(mat, a)
    {
        if (mat === a)
        {
            var a01 = a[1],
                a02 = a[2],
                a03 = a[3],
                a12 = a[6],
                a13 = a[7],
                a23 = a[11];

            mat[1]  = a[4];
            mat[2]  = a[8];
            mat[3]  = a[12];
            mat[4]  = a01;
            mat[6]  = a[9];
            mat[7]  = a[13];
            mat[8]  = a02;
            mat[9]  = a12;
            mat[11] = a[14];
            mat[12] = a03;
            mat[13] = a13;
            mat[14] = a23;
        }
        else
        {
            mat[0]  = a[0];
            mat[1]  = a[4];
            mat[2]  = a[8];
            mat[3]  = a[12];
            mat[4]  = a[1];
            mat[5]  = a[5];
            mat[6]  = a[9];
            mat[7]  = a[13];
            mat[8]  = a[2];
            mat[9]  = a[6];
            mat[10] = a[10];
            mat[11] = a[14];
            mat[12] = a[3];
            mat[13] = a[7];
            mat[14] = a[11];
            mat[15] = a[15];
        }

        return mat;
    }

    function mat4Invert(mat, a)
    {
        var a00 = a[0],
            a01 = a[1],
            a02 = a[2],
            a03 = a[3],
            a10 = a[4],
            a11 = a[5],
            a12 = a[6],
            a13 = a[7],
            a20 = a[8],
            a21 = a[9],
            a22 = a[10],
            a23 = a[11],
            a30 = a[12],
            a31 = a[13],
            a32 = a[14],
            a33 = a[15],
            b00 = a00 * a11 - a01 * a10,
            b01 = a00 * a12 - a02 * a10,
            b02 = a00 * a13 - a03 * a10,
            b03 = a01 * a12 - a02 * a11,
            b04 = a01 * a13 - a03 * a11,
            b05 = a02 * a13 - a03 * a12,
            b06 = a20 * a31 - a21 * a30,
            b07 = a20 * a32 - a22 * a30,
            b08 = a20 * a33 - a23 * a30,
            b09 = a21 * a32 - a22 * a31,
            b10 = a21 * a33 - a23 * a31,
            b11 = a22 * a33 - a23 * a32,
            det = b00 * b11 - b01 * b10 + b02 * b09 + b03 * b08 - b04 * b07 + b05 * b06;

            if (!det)
                return null;

            det = 1 / det;
            mat[0]  = (a11 * b11 - a12 * b10 + a13 * b09) * det;
            mat[1]  = (a02 * b10 - a01 * b11 - a03 * b09) * det;
            mat[2]  = (a31 * b05 - a32 * b04 + a33 * b03) * det;
            mat[3]  = (a22 * b04 - a21 * b05 - a23 * b03) * det;
            mat[4]  = (a12 * b08 - a10 * b11 - a13 * b07) * det;
            mat[5]  = (a00 * b11 - a02 * b08 + a03 * b07) * det;
            mat[6]  = (a32 * b02 - a30 * b05 - a33 * b01) * det;
            mat[7]  = (a20 * b05 - a22 * b02 + a23 * b01) * det;
            mat[8]  = (a10 * b10 - a11 * b08 + a13 * b06) * det;
            mat[9]  = (a01 * b08 - a00 * b10 - a03 * b06) * det;
            mat[10] = (a30 * b04 - a31 * b02 + a33 * b00) * det;
            mat[11] = (a21 * b02 - a20 * b04 - a23 * b00) * det;
            mat[12] = (a11 * b07 - a10 * b09 - a12 * b06) * det;
            mat[13] = (a00 * b09 - a01 * b07 + a02 * b06) * det;
            mat[14] = (a31 * b01 - a30 * b03 - a32 * b00) * det;
            mat[15] = (a20 * b03 - a21 * b01 + a22 * b00) * det;

            return mat;
    }

    function mat4Mult(mat, a, b) {
        var a00 = a[0],
            a01 = a[1],
            a02 = a[2],
            a03 = a[3],
            a10 = a[4],
            a11 = a[5],
            a12 = a[6],
            a13 = a[7],
            a20 = a[8],
            a21 = a[9],
            a22 = a[10],
            a23 = a[11],
            a30 = a[12],
            a31 = a[13],
            a32 = a[14],
            a33 = a[15];

        var b0 = b[0],
            b1 = b[1],
            b2 = b[2],
            b3 = b[3];

        mat[0] = b0 * a00 + b1 * a10 + b2 * a20 + b3 * a30;
        mat[1] = b0 * a01 + b1 * a11 + b2 * a21 + b3 * a31;
        mat[2] = b0 * a02 + b1 * a12 + b2 * a22 + b3 * a32;
        mat[3] = b0 * a03 + b1 * a13 + b2 * a23 + b3 * a33;
        b0 = b[4];
        b1 = b[5];
        b2 = b[6];
        b3 = b[7];
        mat[4] = b0 * a00 + b1 * a10 + b2 * a20 + b3 * a30;
        mat[5] = b0 * a01 + b1 * a11 + b2 * a21 + b3 * a31;
        mat[6] = b0 * a02 + b1 * a12 + b2 * a22 + b3 * a32;
        mat[7] = b0 * a03 + b1 * a13 + b2 * a23 + b3 * a33;
        b0 = b[8];
        b1 = b[9];
        b2 = b[10];
        b3 = b[11];
        mat[8] = b0 * a00 + b1 * a10 + b2 * a20 + b3 * a30;
        mat[9] = b0 * a01 + b1 * a11 + b2 * a21 + b3 * a31;
        mat[10] = b0 * a02 + b1 * a12 + b2 * a22 + b3 * a32;
        mat[11] = b0 * a03 + b1 * a13 + b2 * a23 + b3 * a33;
        b0 = b[12];
        b1 = b[13];
        b2 = b[14];
        b3 = b[15];
        mat[12] = b0 * a00 + b1 * a10 + b2 * a20 + b3 * a30;
        mat[13] = b0 * a01 + b1 * a11 + b2 * a21 + b3 * a31;
        mat[14] = b0 * a02 + b1 * a12 + b2 * a22 + b3 * a32;
        mat[15] = b0 * a03 + b1 * a13 + b2 * a23 + b3 * a33;

        return mat;
    };

    function mat4Translate(mat, a, v)
    {
        var x = v[0],
            y = v[1],
            z = v[2],
            a00, a01, a02, a03, a10, a11, a12, a13, a20, a21, a22, a23;

        if (a === mat)
        {
            mat[12] = a[0] * x + a[4] * y + a[8]  * z + a[12];
            mat[13] = a[1] * x + a[5] * y + a[9]  * z + a[13];
            mat[14] = a[2] * x + a[6] * y + a[10] * z + a[14];
            mat[15] = a[3] * x + a[7] * y + a[11] * z + a[15];
        }
        else
        {
            a00 = a[0];
            a01 = a[1];
            a02 = a[2];
            a03 = a[3];
            a10 = a[4];
            a11 = a[5];
            a12 = a[6];
            a13 = a[7];
            a20 = a[8];
            a21 = a[9];
            a22 = a[10];
            a23 = a[11];
            mat[0]  = a00;
            mat[1]  = a01;
            mat[2]  = a02;
            mat[3]  = a03;
            mat[4]  = a10;
            mat[5]  = a11;
            mat[6]  = a12;
            mat[7]  = a13;
            mat[8]  = a20;
            mat[9]  = a21;
            mat[10] = a22;
            mat[11] = a23;
            mat[12] = a00 * x + a10 * y + a20 * z + a[12];
            mat[13] = a01 * x + a11 * y + a21 * z + a[13];
            mat[14] = a02 * x + a12 * y + a22 * z + a[14];
            mat[15] = a03 * x + a13 * y + a23 * z + a[15];
        }

        return mat;
    };

    function mat4Scale(mat, a, v)
    {
        var x = v[0],
            y = v[1],
            z = v[2];

        mat[0]  = a[0]  * x;
        mat[1]  = a[1]  * x;
        mat[2]  = a[2]  * x;
        mat[3]  = a[3]  * x;
        mat[4]  = a[4]  * y;
        mat[5]  = a[5]  * y;
        mat[6]  = a[6]  * y;
        mat[7]  = a[7]  * y;
        mat[8]  = a[8]  * z;
        mat[9]  = a[9]  * z;
        mat[10] = a[10] * z;
        mat[11] = a[11] * z;
        mat[12] = a[12];
        mat[13] = a[13];
        mat[14] = a[14];
        mat[15] = a[15];

        return mat;
    };

    function mat4RotX(mat, a, rad)
    {
        var s = Math.sin(rad),
            c = Math.cos(rad),
            a10 = a[4],
            a11 = a[5],
            a12 = a[6],
            a13 = a[7],
            a20 = a[8],
            a21 = a[9],
            a22 = a[10],
            a23 = a[11];

        if (a !== mat)
        {
            mat[0]  = a[0];
            mat[1]  = a[1];
            mat[2]  = a[2];
            mat[3]  = a[3];
            mat[12] = a[12];
            mat[13] = a[13];
            mat[14] = a[14];
            mat[15] = a[15];
        }

        mat[4]  = a10 * c + a20 * s;
        mat[5]  = a11 * c + a21 * s;
        mat[6]  = a12 * c + a22 * s;
        mat[7]  = a13 * c + a23 * s;
        mat[8]  = a20 * c - a10 * s;
        mat[9]  = a21 * c - a11 * s;
        mat[10] = a22 * c - a12 * s;
        mat[11] = a23 * c - a13 * s;

        return mat;
    };

    function mat4RotY(mat, a, rad)
    {
        var s = Math.sin(rad),
            c = Math.cos(rad),
            a00 = a[0],
            a01 = a[1],
            a02 = a[2],
            a03 = a[3],
            a20 = a[8],
            a21 = a[9],
            a22 = a[10],
            a23 = a[11];

        if (a !== mat)
        {
            mat[4]  = a[4];
            mat[5]  = a[5];
            mat[6]  = a[6];
            mat[7]  = a[7];
            mat[12] = a[12];
            mat[13] = a[13];
            mat[14] = a[14];
            mat[15] = a[15];
        }

        mat[0]  = a00 * c - a20 * s;
        mat[1]  = a01 * c - a21 * s;
        mat[2]  = a02 * c - a22 * s;
        mat[3]  = a03 * c - a23 * s;
        mat[8]  = a00 * s + a20 * c;
        mat[9]  = a01 * s + a21 * c;
        mat[10] = a02 * s + a22 * c;
        mat[11] = a03 * s + a23 * c;

        return mat;
    };

    function mat4FromRotTranslation(mat, q, v)
    {
        var x = q[0],
            y = q[1],
            z = q[2],
            w = q[3],
            x2 = x + x,
            y2 = y + y,
            z2 = z + z,
            xx = x * x2,
            xy = x * y2,
            xz = x * z2,
            yy = y * y2,
            yz = y * z2,
            zz = z * z2,
            wx = w * x2,
            wy = w * y2,
            wz = w * z2;

        mat[0]  = 1  - (yy + zz);
        mat[1]  = xy + wz;
        mat[2]  = xz - wy;
        mat[3]  = 0;
        mat[4]  = xy - wz;
        mat[5]  = 1  - (xx + zz);
        mat[6]  = yz + wx;
        mat[7]  = 0;
        mat[8]  = xz + wy;
        mat[9]  = yz - wx;
        mat[10] = 1  - (xx + yy);
        mat[11] = 0;
        mat[12] = v[0];
        mat[13] = v[1];
        mat[14] = v[2];
        mat[15] = 1;

        return mat;
    };

    function mat4GetTranslation(vec, mat)
    {
        vec[0] = mat[12];
        vec[1] = mat[13];
        vec[2] = mat[14];

        return vec;
    }

    function mat4ToVec3_UNK(vec, mat)
    {
        var a00 = mat[0],
            a01 = mat[1],
            a02 = mat[2],
            a10 = mat[4],
            a11 = mat[5],
            a12 = mat[6],
            a20 = mat[8],
            a21 = mat[9],
            a22 = mat[10];

        vec[0] = Math.hypot(a00, a01, a02);
        vec[1] = Math.hypot(a10, a11, a12);
        vec[2] = Math.hypot(a20, a21, a22);

        return vec;
    }

    var perspective = function (dest, fov, aspect, zNear, zFar)
    {
        var n,
            f = 1 / Math.tan(fov / 2);

        dest[0]  = f / aspect;
        dest[1]  = 0;
        dest[2]  = 0;
        dest[3]  = 0;
        dest[4]  = 0;
        dest[5]  = f;
        dest[6]  = 0;
        dest[7]  = 0;
        dest[8]  = 0;
        dest[9]  = 0;
        dest[10] = -1;
        dest[11] = -1;
        dest[12] = 0;
        dest[13] = 0;
        dest[14] = -2 * zNear;
        dest[15] = 0;

        if (zFar != null && zFar !== Infinity)
        {
            n = 1 / (zNear - zFar);
            dest[10] = (zFar + zNear) * n;
            dest[14] = 2 * zFar * zNear * n;
        }

        return dest;
    };

}
