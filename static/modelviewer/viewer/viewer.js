(() => {
    "use strict";
    var httpData, obj;

    window.requestAnimFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || function (callback, element) {
        window.setTimeout(callback, 1000 / 60);
    };

    jQuery.support.cors = true;

    if ($.ajaxTransport)
    {
        $.ajaxSetup({ flatOptions: { renderer: true } });
        $.ajaxTransport("+binary", function (options, originalOptions, jqXHR)
        {
            if (window.FormData && ((options.dataType && options.dataType == "binary") || (options.data && ((window.ArrayBuffer && options.data instanceof ArrayBuffer) || (window.Blob && options.data instanceof Blob)))))
            {
                return {
                    send: function (_, callback)
                    {
                        var xhr      = new XMLHttpRequest(),
                            url      = options.url,
                            type     = options.type,
                            dataType = options.responseType || "blob",
                            data     = options.data || null;

                        if (options.renderer)
                        {
                            xhr.addEventListener("progress", function(event) {
                                if (!event.lengthComputable)
                                    return;

                                if (options.renderer.downloads[this.responseURL])
                                    options.renderer.downloads[this.responseURL].loaded = event.loaded;
                                else
                                    options.renderer.downloads[this.responseURL] = { loaded: event.loaded, total: event.total };

                                options.renderer.updateProgress();
                            });
                        }

                        xhr.addEventListener("load", function() {
                            if (options.renderer)
                            {
                                delete options.renderer.downloads[this.responseURL];
                                options.renderer.updateProgress();
                            }

                            var data = {};
                            data[options.dataType] = xhr.response;

                            callback(xhr.status, xhr.statusText, data, xhr.getAllResponseHeaders());
                        }),

                        xhr.open(type, url, true);
                        xhr.responseType = dataType;
                        xhr.send(data);
                    },
                    abort: function ()
                    {
                        jqXHR.abort();
                    },
                };
            }
        });
    }
    else
    {
        (function() {
            httpData = $.httpData;
            $.httpData = function(xhr, type, s) {
                if (type == "binary")
                    return xhr.response;
                else
                    return httpData(xhr, type, s);
            }
        })();

        $.ajaxSetup({
            beforeSend: function (xhr, options)
            {
                if (options.dataType == "binary")
                {
                    xhr.responseType = options.responseType || "arraybuffer";
                    xhr.addEventListener("progress", function(event) {
                        if (!options.renderer || !event.lengthComputable)
                            return;

                        if (options.renderer.downloads[this.responseURL])
                            options.renderer.downloads[this.responseURL].loaded = event.loaded;
                        else
                            options.renderer.downloads[this.responseURL] = { loaded: event.loaded, total: event.total };

                        options.renderer.updateProgress();
                    }, false),
                    xhr.addEventListener("load", function() {
                        if (!options.renderer)
                            return;

                        delete options.renderer.downloads[this.responseURL];
                        options.renderer.updateProgress();
                    }, false);
                }
            }
        });
    }

    Math.randomInt = Math.randomInt || function (min, max)
    {
        return Math.floor(Math.random() * (max - min)) + min;
    };

    if (typeof Object.create != "function")
    {
        Object.create = function()
        {
            obj = function () {},
            function (prototype)
            {
                if (arguments.length > 1)
                    throw Error("Second argument not supported");

                if (typeof prototype != "object")
                    throw TypeError("Argument must be an object");

                obj.prototype = prototype;
                var result = new obj();
                obj.prototype = null

                return result;
            };
        }();
    }

    window.console = window.console || {
        log: function () {},
        error: function () {},
        warn: function () {}
    };


    /*
     * aowow - twgl was included in file
     */

    import { twgl } from "./mod.twgl";


    var Programs = {};

    const attributeTypes = { position: 3, normal: 3, tangent: 3, texcoord: 2, texcoord0: 2, texcoord1: 2, texcoord2: 2 };
    var programs = {};

    class AttributeState
    {
        constructor()
        {
            this.attribs = {};
        }

        disableAll()
        {
            for (let i in this.attribs)
                this.gl.disableVertexAttribArray(this.attribs[i]);

            this.attribs = {};
        }

        enable(gl, attribs)
        {
            this.gl = gl;
            var newAttrs = {};

            for (let i in attribs)
            {
                var a = attribs[i];
                if (a.locundefined === undefined)
                    continue;

                if (this.attribs[a.loc] === undefined)
                    gl.enableVertexAttribArray(a.loc);

                gl.vertexAttribPointer(a.loc, a.size, a.type, false, a.stride, a.offset);
                newAttrs[a.loc] = a.loc;
                this.attribs[i] = null;
            }

            // aowow - optimizer did a poo bah?
            // for (let i in this.attribs);
            // older version has loop content: if (this.attribs[i] !== null) this.gl.disableVertexAttribArray(this.attribs[i])

            this.attribs = newAttrs;
        }
    }

    // aowow - here
    class ProgramTool
    {
        static CreateProgramAttributes(gl, def)
        {
            var attributes = {},
                offset = 0;

            for (let i in def)
            {
                var name = def[i],
                    size = attributeTypes[i];

                attributes[name] = {
                    type: gl.FLOAT,
                    size: size,
                    offset: 4 * offset
                };

                offset += size;
            }

            for (let i in attributes)
                attributes[i].stride = 4 * offset;

            return attributes;
        }

        CleanUpPrograms()
        {
            programs = {};
        }

        ReleaseProgram(name) {}

        static _GetProgram(name)
        {
            return programs[name];
        }

        static RegisterProgram(name, programDef)
        {
            if (!programs[name])
            {
                var shaders = programDef.shaders;
                programs[name] = {
                    shaders: [shaders[0], shaders[1]],
                    attributes: programDef.attributes
                };
            }

            return programs[name];
        }

        static GetProgram(gl, name, config, attributes)
        {
            var programCache = programs[name],
                configBranch = "";

            for (var i in config)
                configBranch += i + ":" + config[i] + "-";

            if (!programCache)
            {
                var def = name.split("."),
                    programDef = Programs[def[0]][def[1]];

                if (programDef)
                    programCache = ProgramTool.RegisterProgram(name, programDef);
            }

            if (!programCache)
                throw "Program not registered: " + def;

            programCache.programInfo || (programCache.programInfo = {});
            programCache.programInfo[configBranch] = ProgramTool.CompileProgram(gl, programCache.shaders, config);
            (attributes = attributes || (programCache.attributes && ProgramTool.CreateProgramAttributes(gl, programCache.attributes)));

            var programInfo = programCache.programInfo[configBranch];
            if (attributes)
            {
                for (var i in attributes)
                {
                    var a = programInfo.attribSetters[i];
                    if (!a)
                        continue;

                    attributes[i] = attributes[i] || {};
                    attributes[i].loc = a.location;
                }
            }

            return (programInfo.attributes = attributes), programInfo;
        }

        static CompileProgram(gl, shader, config, attributes)
        {
            var header = "";

            for (var idx in config)
            {
                var val = config[idx];
                header = "#define " + idx + " " + (null === val ? "" : val) + "\n";
            }

            var uniforms = {};
            const h = createProgramInfo(gl, [header + shader[0], header + shader[1]], null, null);
            if (attributes)
                for (var idx in attributes) {
                    var l = h.attribSetters[idx];
                    l && ((attributes[idx] = attributes[idx] || {}), (attributes[idx].loc = l.location));
                }
            for (var idx in h.uniformSetters) uniforms[idx] = h.uniformSetters[idx].location;
            return (h.uniforms = uniforms), h;
        }
    }

    var Tools = new ProgramTool();


    /*
     * aowow - gl-matrix was included in file
     */

    import { gl_matrix } from "./mod.gl-matrix";


    var mat4Multiply = mat4Mult;

    const validTypes = { 2: "Wowhead" }; // 3: "LolKing", 6: "HeroKing", 7: "DestinyDB"

    class ModelViewer
    {
        constructor(opts)
        {
            if (!opts.type || !validTypes[opts.type])
                throw "Viewer error: Bad viewer type given";
            if (!opts.container)
                throw "Viewer error: Bad container given";
            if (!opts.aspect)
                throw "Viewer error: Bad aspect ratio given";
            if (!opts.contentPath)
                throw "Viewer error: No content path given";

            console.log("Creating viewer with options", opts);

            this.type = opts.type;
            this.container = opts.container;
            this.aspect = parseFloat(opts.aspect);
            this.renderer = null;
            this.options = opts;

            const width = this.container.width(),
                height = Math.round(width / this.aspect);

            this.init(width, height);
        }

        destroy()
        {
            this.renderer && this.renderer.destroy();
            this.options = null;
            this.container = null;
        }

        init(width, height)
        {
            if (typeof window.Uint8Array !== undefined && typeof window.DataView !== undefined)
                try
                {
                    const canvas = document.createElement("canvas");
                    if (!(canvas.getContext("webgl", { alpha: false }) || canvas.getContext("experimental-webgl", { alpha: false })))
                        return void console.log("viewer init failed");
                }
                catch (e)
                {
                    return void console.log("viewer init failed");
                }

            this.mode = 1; // Modelviewer.WEBGL
            this.renderer = new WebGL(this);
            this.renderer.resize(width, height);
            this.renderer.init();
        }

        setAdaptiveMode(mode)
        {
            this.renderer.setAdaptiveMode(mode);
        }

        setZoom(zoom)
        {
            this.renderer.zoom.target = zoom;
        }

        setOffset(x, y)
        {
            this.renderer.setTranslation(x, y, 0);
        }

        setFullscreen(enable)
        {
            enable ? ModelViewer.requestFullscreen(this.renderer.canvas[0]) : ModelViewer.exitFullscreen();
        }

        method(name, params)
        {
            if (params === undefined)
                params = [];

            return this.renderer ? this.renderer.method(name, [].concat(params)) : null;
        }

        option(key, val)
        {
            if (val !== undefined)
                this.options[key] = val;

            return this.options[key];
        }

        static isFullscreen()
        {
            return !!(
                document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement
            );
        }

        static requestFullscreen(e)
        {
            if (document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement)
                return;

            e.requestFullscreen ? e.requestFullscreen() :
                e.webkitRequestFullscreen ? e.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT) :
                    e.mozRequestFullScreen ? e.mozRequestFullScreen() :
                        e.msRequestFullscreen && e.msRequestFullscreen();
        }

        static exitFullscreen()
        {
            if (document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement)
                return;

            document.exitFullscreen ? document.exitFullscreen() :
                document.webkitExitFullscreen ? document.webkitExitFullscreen() :
                    document.mozCancelFullScreen ? document.mozCancelFullScreen() :
                        document.msExitFullscreen && document.msExitFullscreen();
        }
    }

    const WoWModelViewer = ModelViewer;


    const yi = class {
        constructor(t, e, i) {
            (this.f = t), (this.e = e), (this.ba = i), (this.g = false), (this.dc = t.createBuffer()), (this.e = 0);
        }
        d(t) {
            const gl = this.f;
            gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, this.dc),
                !this.g || this.e < t.byteLength
                    ? (gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, t, this.ba ? gl.DYNAMIC_DRAW : gl.STATIC_DRAW),
                      (this.e = t.byteLength),
                      (this.g = true))
                    : gl.bufferSubData(gl.ELEMENT_ARRAY_BUFFER, 0, t),
                gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, null);
        }
        b() {
            return this.e;
        }
        a() {
            const t = this.f;
            t.bindBuffer(t.ELEMENT_ARRAY_BUFFER, this.dc);
        }
        c() {
            const t = this.f;
            t.bindBuffer(t.ELEMENT_ARRAY_BUFFER, null);
        }
    };
    const Ai = class {
        constructor(t, e, i) {
            (this.g = t), (this.f = e), (this.dc = i), (this.ba = false), (this.e = t.createBuffer()), (this.f = 0);
        }
        a() {
            const t = this.g;
            t.bindBuffer(t.ARRAY_BUFFER, this.e);
        }
        c() {
            const t = this.g;
            t.bindBuffer(t.ARRAY_BUFFER, null);
        }
        d(t) {
            const e = this.g;
            e.bindBuffer(e.ARRAY_BUFFER, this.e),
                !this.ba || this.f < t.byteLength
                    ? (e.bufferData(e.ARRAY_BUFFER, t, this.dc ? e.DYNAMIC_DRAW : e.STATIC_DRAW),
                      (this.ba = true),
                      (this.f = t.byteLength))
                    : e.bufferSubData(e.ARRAY_BUFFER, 0, t),
                e.bindBuffer(e.ARRAY_BUFFER, null);
        }
        b() {
            return this.f;
        }
    };
    var Ei;
    !(function (t) {
        (t[(t.GFLOAT = 0)] = "GFLOAT"), (t[(t.GUNSIGNED_BYTE = 1)] = "GUNSIGNED_BYTE");
    })(Ei || (Ei = {}));
    class Ci {
        constructor(t, e, i, s, r, n) {
            (this.c = t), (this.f = e), (this.e = i), (this.a = s), (this.d = r), (this.b = n);
        }
    }
    function Mi(t, e) {
        switch (e) {
            case Ei.GFLOAT:
                return t.FLOAT;
            case Ei.GUNSIGNED_BYTE:
                return t.UNSIGNED_BYTE;
        }
    }
    const ki = class {
        constructor(t, e) {
            (this.ba = t), (this.dc = e), (this.e = null), (this.f = []), (this.e = e.createVertexArrayOES());
        }
        c(t) {
            this.ba;
            this.b(), t.a(), this.d(), this.f.push(t);
        }
        a(t, e) {
            const i = this.ba;
            this.b(), t.a();
            for (const t of e)
                i.enableVertexAttribArray(t.c), i.vertexAttribPointer(t.c, t.f, Mi(i, t.e), t.a, t.d, t.b);
            this.d(), this.f.push(t);
        }
        b() {
            this.dc.bindVertexArrayOES(this.e);
        }
        d() {
            this.dc.bindVertexArrayOES(null);
        }
    };
    const Si = class {
        constructor(t, e) {
            (this.e = t), (this.ba = e), (this.f = []);
        }
        c(t) {
            this.dc = t;
        }
        a(t, e) {
            this.f.push({ buffer: t, bindings: e });
        }
        b() {
            const t = this.e;
            this.dc.a();
            const e = this.ba.a();
            for (const e of this.f) {
                e.b.a();
                for (const i of e.a) this.ba.d(i.c), t.vertexAttribPointer(i.c, i.f, Mi(t, i.e), i.a, i.d, i.b);
            }
            this.ba.c(e);
        }
        d() {}
    };
    class Fi {}
    class Ii {
        constructor(t, e, i, s) {
            (this.a = t), (this.b = e), (this.c = i), (this.d = s);
        }
    }
    class Di {
        constructor(t, e) {
            (this.a = t), (this.b = e);
        }
    }
    class Ri {
        constructor(t, e) {
            (this.b = t), (this.a = e);
        }
    }
    class Ui extends Fi {}
    const Bi = class extends Ui {
        constructor(t, e, i, s) {
            super(), (this.cba = t), (this.d = e), (this.e = i), (this.ba = s);
        }
        b() {
            return this.e.a;
        }
        a(t) {
            const e = this.e;
            t.d(this.d), t.b(e.a), t.a(e.e), t.c(e.b), t.f(e.d), t.i(e.c), t.e(e.f), setUniforms(this.d.ba(), this.ba);
        }
    };
    const Oi = class {
        constructor(t, e, i, s) {
            if (((this.d = t), (this.c = createProgramInfo(t, [i, s], Object.keys(e), null)), !this.c))
                throw "Failed to create program";
        }
        a() {
            this.d.useProgram(this.c.program);
        }
        ba() {
            return this.c;
        }
    };

    class ShaderTool
    {
        static getShaderEffect(shaderId)
        {
            const id = shaderId & 0x7FFF;

            if (id < shaderMap.length)
                return shaderMap[id];

            WH.debug("Unknown shader effect:", id);
            return ["PS_Combiners_Opaque", "VS_Diffuse_T1"];
        }

        static GetWowPSShaderName(shaderId, opCount)
        {
            let shader = "";
            if (shaderId == -1000  && opCount == 3)
                return "Skin";

            if (shaderId & 0x8000)
                return ShaderTool.getShaderEffect(shaderId)[0];

            if (opCount == 1)
                shader = (shaderId & 0x70) ? "PS_Combiners_Mod" : "PS_Combiners_Opaque";
            else
            {
                const shadernames1 = ["Opaque", "Mod", "Mod", "Add", "Mod2x", "Mod", "Mod2xNA", "AddNA"];
                const shadernames2 = ["Opaque", "Mod", "Mod", "AddAlpha", "Mod2x", "Mod", "Mod2xNA", "AddAlpha"];
                shader = (shaderId & 0x70 ? "PS_Combiners_Mod" : "PS_Combiners_Opaque") + "_" + (shaderId & 0x70 ? shadernames1 : shadernames2)[shaderId & 0x7];
            }

            return shader;
        }

        static GetWowVSShaderName(shaderId, opCount)
        {
            let shader = "";
            if (shaderId == -1000 && opCount == 3)
                shader = "T1_T1_T1";
            else
            {
                if (shaderId & 0x8000)
                    return ShaderTool.getShaderEffect(shaderId)[1];

                if (opCount == 1)
                    shader = shaderId & 0x80 ? "Env" : shaderId & 0x4000 ? "T2" : "T1";
                else if (shaderId & 0x80)
                    shader = shaderId & 8 ? "Env_Env" : "Env_T1";
                else
                    shader = shaderId & 0x8 ? "T1_Env" : shaderId & 0x4000 ? "T1_T2" : "T1_T1";
            }

            return "VS_Diffuse_" + shader;
        }

        static GetWowProgram(shaderId, opCount, renderFlag)
        {
            const ps = ShaderTool.GetWowPSShaderName(shaderId, opCount),
                  vs = ShaderTool.GetWowVSShaderName(shaderId, opCount),
                  name = "Wow." + vs + "_" + ps;

            if (ProgramTool._GetProgram(name))
                return { name: name };

            const program = {
                shaders: [ShaderTool.GenerateVS(vs, renderFlag), ShaderTool.GeneratePS(vs, ps, false)],
                attributes: {
                    position: "aPosition",
                    normal: "aNormal",
                    texcoord0: "aTexCoord0",
                    texcoord1: "aTexCoord1"
                },
            };

            ProgramTool.RegisterProgram(name, program);

            return { name: name };
        }

        static Get(opts)
        {
            const config = {},
                  optConfFunc = {
                      texcoord1: function (shader, idx) { shader.INPUT_TEXCOORD1 = "aTexCoord" + idx; },
                  };

            for (let cfn in opts.options)
            {
                const i = opts.options[cfn];
                optConfFunc[cfn](config, i);
            }

            return { name: "Wow." + opts.name, config: config };
        }

        static GenerateTexCoord(vsName)
        {
            var base = "";
            base += "lTexCoord1 = (uTextureMatrix1 * vec4(vTexCoord1, 0, 1)).st;\n";
            base += "lTexCoord2 = (uTextureMatrix2 * vec4(vTexCoord2, 0, 1)).st;\n";

            if (vsName.slice(0, 2) === "VS")
            {
                vsName = vsName.slice(3);

                let types = vsName.split("_"),
                    kind  = types[0];

                if (kind === "Diffuse" || kind === "Color")
                {
                    base = "";
                    types.splice(0, 1);

                    let srcs = {
                            T1: ["uTextureMatrix1", "vTexCoord1"],
                            T2: ["uTextureMatrix2", "vTexCoord2"],
                            T3: ["", "aTexCoord2"],
                            Env: ["", "texEnv"]
                        },
                        r = 1;

                    for (let i in types)
                    {
                        if (!srcs[types[i]])
                        {
                            WH.debug("Missing vertex shader def?", vsName);
                            continue;
                        }

                        if (srcs[types[i]][0] && srcs[types[i]][1] != "texEnv")
                            base += "lTexCoord" + r + " = (" + srcs[types[i]][0] + " * vec4(" + srcs[types[i]][1] + ", 0, 1)).st;\n";
                        else if (srcs[types[i]][1] == "texEnv")
                            base += "lTexCoord" + r + " = texEnv;\n";
                        else
                            base += "lTexCoord" + r + " = (uTextureMatrix" + r + " * vec4(" + srcs[types[i]][1] + ", 0, 1)).st;\n";

                        r++;
                    }
                }
            }

            return base;
        }

        static GenerateVS(vsName, skinning)
        {
            var options = { SKINNING: skinning };
            let shader = "attribute vec3 aPosition;\nattribute vec3 aNormal;\nattribute vec2 aTexCoord0;\nattribute vec2 aTexCoord1;\nattribute vec3 aColor;\n";

            if (options.SKINNING)
                shader += "attribute vec4 aBoneWeights;\nattribute vec4 aBones;\n";

            shader += "varying vec3 vPosition;\nvarying vec3 vNormal;\nvarying vec2 vTexCoord1;\nvarying vec2 vTexCoord2;\nuniform mat4 uModelMatrix;\nuniform mat4 uPanningMatrix;\nuniform mat4 uViewMatrix;\nuniform mat4 uInvTranspViewModelMat;\nuniform mat4 uProjMatrix;\nuniform vec3 uCameraPos;\n";

            if (options.SKINNING)
                shader += "uniform sampler2D uBoneMatricesTex;\n#define ROW0_U ((0.5 + 0.0) / 4.)\n#define ROW1_U ((0.5 + 1.0) / 4.)\n#define ROW2_U ((0.5 + 2.0) / 4.)\n#define ROW3_U ((0.5 + 3.0) / 4.)\nconst float numBones = 256.0;\nmat4 getBoneMatrix(float boneNdx) {\nfloat v = (boneNdx + 0.5) / numBones;\nreturn mat4(\ntexture2D(uBoneMatricesTex, vec2(ROW0_U, v)),\ntexture2D(uBoneMatricesTex, vec2(ROW1_U, v)),\ntexture2D(uBoneMatricesTex, vec2(ROW2_U, v)),\ntexture2D(uBoneMatricesTex, vec2(ROW3_U, v))\n);\n}\nhighp mat4 transpose(in highp mat4 inMatrix) {\nhighp vec4 i0 = inMatrix[0];\nhighp vec4 i1 = inMatrix[1];\nhighp vec4 i2 = inMatrix[2];\nhighp vec4 i3 = inMatrix[3];\nhighp mat4 outMatrix = mat4(\nvec4(i0.x, i1.x, i2.x, i3.x),\nvec4(i0.y, i1.y, i2.y, i3.y),\nvec4(i0.z, i1.z, i2.z, i3.z),\nvec4(i0.w, i1.w, i2.w, i3.w)\n);\nreturn outMatrix;\n}\nmat4 inverse(mat4 m) {\nfloat\na00 = m[0][0], a01 = m[0][1], a02 = m[0][2], a03 = m[0][3],\na10 = m[1][0], a11 = m[1][1], a12 = m[1][2], a13 = m[1][3],\na20 = m[2][0], a21 = m[2][1], a22 = m[2][2], a23 = m[2][3],\na30 = m[3][0], a31 = m[3][1], a32 = m[3][2], a33 = m[3][3],\nb00 = a00 * a11 - a01 * a10,\nb01 = a00 * a12 - a02 * a10,\nb02 = a00 * a13 - a03 * a10,\nb03 = a01 * a12 - a02 * a11,\nb04 = a01 * a13 - a03 * a11,\nb05 = a02 * a13 - a03 * a12,\nb06 = a20 * a31 - a21 * a30,\nb07 = a20 * a32 - a22 * a30,\nb08 = a20 * a33 - a23 * a30,\nb09 = a21 * a32 - a22 * a31,\nb10 = a21 * a33 - a23 * a31,\nb11 = a22 * a33 - a23 * a32,\ndet = b00 * b11 - b01 * b10 + b02 * b09 + b03 * b08 - b04 * b07 + b05 * b06;\nreturn mat4(\na11 * b11 - a12 * b10 + a13 * b09,\na02 * b10 - a01 * b11 - a03 * b09,\na31 * b05 - a32 * b04 + a33 * b03,\na22 * b04 - a21 * b05 - a23 * b03,\na12 * b08 - a10 * b11 - a13 * b07,\na00 * b11 - a02 * b08 + a03 * b07,\na32 * b02 - a30 * b05 - a33 * b01,\na20 * b05 - a22 * b02 + a23 * b01,\na10 * b10 - a11 * b08 + a13 * b06,\na01 * b08 - a00 * b10 - a03 * b06,\na30 * b04 - a31 * b02 + a33 * b00,\na21 * b02 - a20 * b04 - a23 * b00,\na11 * b07 - a10 * b09 - a12 * b06,\na00 * b09 - a01 * b07 + a02 * b06,\na31 * b01 - a30 * b03 - a32 * b00,\na20 * b03 - a21 * b01 + a22 * b00) / det;\n}\n";

            shader += "void main(void) {\nmat4 boneTransformMat =  mat4(1.0);\n";

            if (options.SKINNING)
                shader += "if (length(aBoneWeights) > 0.0) {\nboneTransformMat =  mat4(0.0);\nfor (int i = 0; i < 4; i++) {\nboneTransformMat += getBoneMatrix(aBones[i]) * aBoneWeights[i];\n}\n}\nmat4 viewModelMat = uViewMatrix * uModelMatrix * boneTransformMat;\nmat4 invTranspViewModelMat = transpose(inverse(viewModelMat));\n";
            else
                shader += "mat4 viewModelMat = uViewMatrix * uModelMatrix;\nmat4 invTranspViewModelMat = uInvTranspViewModelMat;\n";

            shader += "vec4 pos = viewModelMat * vec4(aPosition, 1);\nvPosition = pos.rgb;\ngl_Position = uProjMatrix * pos;\nvTexCoord1 = aTexCoord0;\nvTexCoord2 = aTexCoord1;\nvNormal = normalize((invTranspViewModelMat * vec4(aNormal, 0.0)).xyz);\n}\n";

            return shader;
        }

        static GeneratePS(vsName, psName, gradient)
        {
            let ps = psCombiners[psName];
            if (!ps)
            {
                WH.debug("Missing pixel shader def", psName);

                psName = "PS_Combiners_Opaque_Mod";
                ps = psCombiners[psName];
            }

            let base = "\t\t" + ps.slice(1, ps.length).join("\n\t\t");

            for (let i = 0; i < ps[0]; i++)
            {
                let idx = i + 1;
                base = "vec4 tex" + i + " = texture2D(uTexture" + idx + ", lTexCoord" + idx + ".st);\n" + base;
            }

            let texCoord = this.GenerateTexCoord(vsName);
            var options = { EXCERPT_TEX_COORD: texCoord, EXCERPT_BASE: base, GRADIENT: gradient };
            let shader = "precision mediump float;\nvarying vec3 vPosition;\nvarying vec3 vNormal;\nvarying vec2 vTexCoord1;\nvarying vec2 vTexCoord2;\nvarying vec2 vTexCoord3;\nvarying vec2 vTexCoord4;\nuniform bool uHasAlpha;\nuniform bool uHasSpecEmiss;\nuniform bool uHasEmissiveGlowing;\nuniform int uBlendMode;\nuniform bool uUnlit;\nuniform vec4 uColor;\nuniform vec4 uAmbientColor;\nuniform vec4 uDiffuseColor;\nuniform vec4 uPrimaryColor;\nuniform vec4 uSecondaryColor;\nuniform vec3 uLightDir1;\nuniform vec3 uLightDir2;\nuniform vec3 uLightDir3;\nuniform mat4 uTextureMatrix1;\nuniform mat4 uTextureMatrix2;\nuniform mat4 uTextureMatrix3;\nuniform mat4 uTextureMatrix4;\nuniform sampler2D uTexture1;\nuniform sampler2D uTexture2;\nuniform sampler2D uTexture3;\nuniform sampler2D uTexture4;\nuniform sampler2D uAlpha;\nuniform vec4 uTexSampleAlpha;\n";

            if (options.GRADIENT)
                shader += "uniform vec4 u_gradGradientColors_0;\nuniform vec4 u_gradGradientColors_1;\nuniform vec4 u_gradGradientColors_2;\nuniform vec4 u_gradEdgeColor;\nuniform vec4 u_gradBoundingBox;\nuniform vec4 u_gradUpVec;\nuniform vec4 u_gradFlags;\nuniform vec4 u_mulLum_OpaqMat;\n";

            shader += "vec2 sphereMap(vec3 vertex, vec3 normal) {\nvec3 normPos = (normalize(vertex.xyz));\nvec3 reflection = reflect(normPos, normalize(normal));\nreflection = vec3(reflection.x, reflection.y, reflection.z + 1.0);\nvec2 texCoord = ((normalize(reflection).xy * 0.5) + vec2(0.5));\nreturn texCoord;\n}\nvoid main(void) {\nvec2 lTexCoord1 = vec2(0.0);\nvec2 lTexCoord2 = vec2(0.0);\nvec2 lTexCoord3 = vec2(0.0);\nvec4 _output = vec4(1.0);\nvec4 _input = uColor;\nvec3 _specular = vec3(0.0);\nvec2 texEnv = sphereMap(vPosition.xyz,normalize(vNormal.xyz));\n";
            shader += (options.EXCERPT_TEX_COORD || "")
            shader += (options.EXCERPT_BASE || "")
            shader += "_output.a = _output.a * uDiffuseColor.a;\nif (uBlendMode == 13) {\n_output.a = _output.a * _input.a;\n} else if (uBlendMode == 1) {\nif (_output.a < (128.0/255.0))\ndiscard;\n_output.a = _input.a;\n} else if (uBlendMode == 0) {\n_output.a = _input.a;\n} else {\n_output.a = _output.a * _input.a;\n}\nif (!uUnlit) {\nvec4 litColor = uAmbientColor;\nvec3 normal = normalize(vNormal);\nfloat dp = max(0.0, dot(normal, uLightDir1));\nlitColor += uPrimaryColor * dp;\ndp = max(0.0, dot(normal, uLightDir2));\nlitColor += uSecondaryColor * dp;\ndp = max(0.0, dot(normal, uLightDir3));\nlitColor += uSecondaryColor * dp;\nlitColor = clamp(litColor, vec4(0,0,0,0), vec4(1,1,1,1));\n_output.rgb *= (litColor * uDiffuseColor).rgb;\n}\n_output += vec4(_specular, 0.0);\n";

            if (options.GRADIENT)
                shader += "float power = u_gradEdgeColor.w;\nfloat midValue = u_gradGradientColors_2.w;\nfloat opaqueMaterial = u_mulLum_OpaqMat.y;\nfloat lum = clamp(dot(_output.xyz, vec3(0.212599993, 0.715200007, 0.0722000003)), 0.0, 1.0);\nfloat val0 = 0.0;\nfloat val1 = midValue;\nif (lum > midValue) {\nval0 = midValue;\nval1 = 1.0;\n}\nfloat lerpValue = clamp(((lum - val0) / (val1 - val0)), 0.0, 1.0);\nvec3 gradColor0 = u_gradGradientColors_0.xyz;\nvec3 gradColor1 = u_gradGradientColors_1.xyz;\nif (lum > midValue) {\ngradColor0 = u_gradGradientColors_1.xyz;\ngradColor1 = u_gradGradientColors_2.xyz;\n}\nvec3 gradientColor = mix(gradColor0, gradColor1, vec3(lerpValue));\nbool flipNormal = ((u_gradGradientColors_0.w > 0.0) && (vNormal.z > 0.0));\nvec3 normal = flipNormal ? -vNormal.xyz : vNormal.xyz;\nvec2 term = vec2(dot(-(vPosition.xyz), normal), dot(normalize(-(vPosition.xyz)), (normal * vec3(0.05, 0.05, 1.0))));\nvec2 invTerm = (vec2(1.0) - clamp(term, 0.0, 1.0));\nvec2 f = (invTerm * invTerm);\nfloat fresnel_rim = pow((f.x + f.y), power);\nbool disableHeightFade = bool(u_gradFlags.x);\nfloat visMod = 0.0;\nvec4 res = _output;\nvec3 distVecTest = vec3(0,0,0);\nif (!(disableHeightFade))\n{\nvec3 distVec = (vPosition - u_gradBoundingBox.xyz);\nfloat _dot = dot(distVec, u_gradUpVec.xyz);\nfloat relHeight = (_dot * u_gradBoundingBox.w);\nbool invertHeightFade = bool(u_gradFlags.w);\ndistVecTest = vec3(relHeight);\nrelHeight = invertHeightFade ? clamp((1.0 - relHeight), 0.0, 1.0) : relHeight;\nfloat visMod = clamp((relHeight * 1.66666663), 0.0, 0.899999976);\nvisMod = (visMod * visMod);\nres = vec4(_output.r, _output.g, _output.b, (_output.w * visMod));\n}\nvec3 lerp = mix(gradientColor, u_gradEdgeColor.xyz, vec3(fresnel_rim));\nfloat multiplyLum = u_mulLum_OpaqMat.x;\nif (bool(multiplyLum))\n{\nres = vec4(lerp.xyz, (res.w * lum));\n}\nelse\n{\nres = vec4(lerp.xyz, res.w);\n}\n_output = mix(_output, res, vec4(u_gradFlags.y));\n_output = vec4(_output.xyz, res.a * _output.a);\n";

            shader += "gl_FragColor = _output;\n}\n";

            return shader;
        }
    }

    const shaderMap = [
        [ "PS_Combiners_Opaque_Mod2xNA_Alpha",           "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_AddAlpha",                "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_AddAlpha_Alpha",          "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_Mod2xNA_Alpha_Add",       "VS_Diffuse_T1_Env_T1",      "HS_T1_T2_T3", "DS_T1_T2_T3" ],
        [ "PS_Combiners_Mod_AddAlpha",                   "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_AddAlpha",                "VS_Diffuse_T1_T1",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_AddAlpha",                   "VS_Diffuse_T1_T1",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_AddAlpha_Alpha",             "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_Alpha_Alpha",             "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_Mod2xNA_Alpha_3s",        "VS_Diffuse_T1_Env_T1",      "HS_T1_T2_T3", "DS_T1_T2_T3" ],
        [ "PS_Combiners_Opaque_AddAlpha_Wgt",            "VS_Diffuse_T1_T1",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_Add_Alpha",                  "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_ModNA_Alpha",             "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_AddAlpha_Wgt",               "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_AddAlpha_Wgt",               "VS_Diffuse_T1_T1",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_AddAlpha_Wgt",            "VS_Diffuse_T1_T2",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_Mod_Add_Wgt",             "VS_Diffuse_T1_Env",         "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_Mod2xNA_Alpha_UnshAlpha", "VS_Diffuse_T1_Env_T1",      "HS_T1_T2_T3", "DS_T1_T2_T3" ],
        [ "PS_Combiners_Mod_Dual_Crossfade",             "VS_Diffuse_T1",             "HS_T1",       "DS_T1"       ],
        [ "PS_Combiners_Mod_Depth",                      "VS_Diffuse_EdgeFade_T1",    "HS_T1",       "DS_T1"       ],
        [ "PS_Combiners_Opaque_Mod2xNA_Alpha_Alpha",     "VS_Diffuse_T1_Env_T2",      "HS_T1_T2_T3", "DS_T1_T2_T3" ],
        [ "PS_Combiners_Mod_Mod",                        "VS_Diffuse_EdgeFade_T1_T2", "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_Masked_Dual_Crossfade",      "VS_Diffuse_T1_T2",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_Alpha",                   "VS_Diffuse_T1_T1",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Opaque_Mod2xNA_Alpha_UnshAlpha", "VS_Diffuse_T1_Env_T2",      "HS_T1_T2_T3", "DS_T1_T2_T3" ],
        [ "PS_Combiners_Mod_Depth",                      "VS_Diffuse_EdgeFade_Env",   "HS_T1",       "DS_T1"       ],
        [ "PS_Guild",                                    "VS_Diffuse_T1_T2_T1",       "HS_T1_T2_T3", "DS_T1_T2"    ],
        [ "PS_Guild_NoBorder",                           "VS_Diffuse_T1_T2",          "HS_T1_T2",    "DS_T1_T2_T3" ],
        [ "PS_Guild_Opaque",                             "VS_Diffuse_T1_T2_T1",       "HS_T1_T2_T3", "DS_T1_T2"    ],
        [ "PS_Illum",                                    "VS_Diffuse_T1_T1",          "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_Mod_Mod_Const",              "VS_Diffuse_T1_T2_T3",       "HS_T1_T2_T3", "DS_T1_T2_T3" ],
        [ "PS_Combiners_Mod_Mod_Mod_Const",              "VS_Color_T1_T2_T3",         "HS_T1_T2_T3", "DS_T1_T2_T3" ],
        [ "PS_Combiners_Opaque",                         "VS_Diffuse_T1",             "HS_T1",       "DS_T1"       ],
        [ "PS_Combiners_Mod_Mod2x",                      "VS_Diffuse_EdgeFade_T1_T2", "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod",                            "VS_Diffuse_EdgeFade_T1",    "HS_T1_T2",    "DS_T1_T2"    ],
        [ "PS_Combiners_Mod_Mod_Depth",                  "VS_Diffuse_EdgeFade_T1_T2", "HS_T1_T2",    "DS_T1_T2"    ],
    ],

    psCombiners = {
        PS_Combiners_Add:                            [1, "_output.rgb = _input.rgb + tex0.rgb;", "_output.a = _input.a + tex0.a;"],
        PS_Combiners_Decal:                          [1, "_output.rgb = mix(_input.rgb, tex0.rgb, _input.a);", "_output.a = _input.a;"],
        PS_Combiners_Fade:                           [1, "_output.rgb = mix(tex0.rgb, _input.rgb, _input.a);", "_output.a = _input.a;"],
        PS_Combiners_Mod:                            [1, "_output.rgb = _input.rgb * tex0.rgb;", "_output.a = tex0.a;"],
        PS_Combiners_Mod2x:                          [1, "_output.rgb = _input.rgb * tex0.rgb * 2.0;", "_output.a = tex0.a * 2.0;"],
        PS_Combiners_Opaque:                         [1, "_output.rgb = _input.rgb * tex0.rgb;", "_output.a = 1.0;"],
        PS_Combiners_Add_Add:                        [2, "_output.rgb = (_input.rgb + tex0.rgb) + tex1.rgb;", "_output.a = (_input.a + tex0.a) + tex1.a;",],
        PS_Combiners_Add_Mod:                        [2, "_output.rgb = (_input.rgb + tex0.rgb) * tex1.rgb;", "_output.a = (_input.a + tex0.a) * tex1.a;",],
        PS_Combiners_Add_Mod2x:                      [2, "_output.rgb = (_input.rgb + tex0.rgb) * tex1.rgb * 2.0;", "_output.a = (_input.a + tex0.a) * tex1.a * 2.0;",],
        PS_Combiners_Add_Opaque:                     [2, "_output.rgb = (_input.rgb + tex0.rgb) * tex1.rgb;", "_output.a = _input.a + tex0.a;", ],
        PS_Combiners_Mod_AddNA:                      [2, "_output.rgb = (_input.rgb * tex0.rgb);", "_output.a = tex0.a;", "_specular = tex1.rgb;",],
        PS_Combiners_Mod_Mod:                        [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb;", "_output.a = tex0.a * tex1.a;", ],
        PS_Combiners_Mod_Mod2x:                      [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb * 2.0;", "_output.a = tex0.a * tex1.a * 2.0;", ],
        PS_Combiners_Mod_Add:                        [2, "_output.rgb = (_input.rgb * tex0.rgb);", "_output.a = tex0.a + tex1.a;", "_specular = tex1.rgb;",],
        PS_Combiners_Mod_Mod2xNA:                    [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb * 2.0;", "_output.a = tex0.a;", ],
        PS_Combiners_Mod_Opaque:                     [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb;", "_output.a = tex0.a;"],
        PS_Combiners_Mod2x_Add:                      [2, "_output.rgb = (_input.rgb * tex0.rgb) * 2.0 + tex1.rgb;", "_output.a = (tex0.a) * 2.0 + tex1.a;", ],
        PS_Combiners_Mod2x_Mod2x:                    [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb * 4.0;", "_output.a = (tex0.a) * tex1.a * 4.0;", ],
        PS_Combiners_Mod2x_Opaque:                   [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb * 2.0;", "_output.a = tex0.a * 2.0;", ],
        PS_Combiners_Opaque_Add:                     [2, "_output.rgb = (_input.rgb * tex0.rgb) + tex1.rgb;", "_output.a = _input.a + tex1.a;", ],
        PS_Combiners_Opaque_AddAlpha:                [2, "_output.rgb = (_input.rgb * tex0.rgb);", "_specular = (tex1.rgb * tex1.a);", ],
        PS_Combiners_Opaque_AddAlpha_Wgt:            [2, "_output.rgb = (_input.rgb * tex0.rgb);", "_specular = (tex1.rgb * tex1.a) * uTexSampleAlpha.g;", ],
        PS_Combiners_Opaque_AddAlpha_Alpha:          [2, "_output.rgb = (_input.rgb * tex0.rgb);", "_specular = (tex1.rgb * tex1.a * (1.0 - tex0.a));", ],
        PS_Combiners_Opaque_AddNA:                   [2, "_output.rgb = (_input.rgb * tex0.rgb) + tex1.rgb;", "_output.a = _input.a;", ],
        PS_Combiners_Opaque_Mod:                     [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb;", "_output.a = tex1.a;"],
        PS_Combiners_Opaque_Mod2x:                   [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb * 2.0;", "_output.a = tex1.a * 2.0;",],
        PS_Combiners_Opaque_Mod2xNA:                 [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb * 2.0;", ""],
        PS_Combiners_Opaque_Mod2xNA_Alpha:           [2, "_output.rgb = _input.rgb * mix(tex0.rgb * tex1.rgb * 2.0, tex0.rgb, vec3(tex0.a));", "",],
        PS_Combiners_Opaque_Opaque:                  [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb;", ""],
        PS_Combiners_Opaque_Mod2xNA_Alpha_Add:       [3, "_output.rgb = _input.rgb * mix(tex0.rgb * tex1.rgb * 2.0, tex0.rgb, vec3(tex0.a));", "_specular = tex2.rgb * tex2.a * uTexSampleAlpha.b;",],
        PS_Combiners_Mod_Mod_Mod_Const:              [3, "_output.rgb = _input.rgb * (tex0 * tex1 * tex2).rgb;", "_output.a = (tex0 * tex1 * tex2).a;",],
        PS_Combiners_Mod_AddAlpha:                   [2, "_output.rgb = (_input.rgb * tex0.rgb);", "_output.a = tex0.a;","_specular = tex1.rgb * tex1.a;",],
        PS_Combiners_Mod_AddAlpha_Wgt:               [2, "_output.rgb = (_input.rgb * tex0.rgb);", "_output.a = tex0.a;","_specular = tex1.rgb * tex1.a * uTexSampleAlpha.g;",],
        PS_Combiners_Mod_AddAlpha_Alpha:             [2, "_output.rgb = _input.rgb * tex0.rgb;", "_output.a = (tex0.a + tex1.a * (0.3 * tex1.r + 0.59 * tex1.g + 0.11 * tex1.b));","_specular = tex1.rgb * tex1.a * (1.0 - tex0.a);",],
        PS_Combiners_Opaque_Mod_Add_Wgt:             [2, "_output.rgb = _input.rgb * mix(tex0.rgb, tex1.rgb, vec3(tex1.a));", "_specular = (tex0.rgb * tex0.a) * uTexSampleAlpha.r;",],
        PS_Guild:                                    [3, "_output.rgb = _input.rgb * mix(tex0.rgb * mix(vec3(1.0, 1.0, 1.0), tex1.rgb * vec3(1.0, 1.0, 1.0), vec3(tex1.a)), tex2.rgb * vec3(1.0, 1.0, 1.0), vec3(tex2.a));", "_output.a = tex0.a;", ],
        PS_Guild_Opaque:                             [3, "_output.rgb = _input.rgb * mix(tex0.rgb * mix(vec3(1.0, 1.0, 1.0), tex1.rgb * vec3(1.0, 1.0, 1.0), vec3(tex1.a)), tex2.rgb * vec3(1.0, 1.0, 1.0), vec3(tex2.a));", "", ],
        PS_Guild_NoBorder:                           [2, "_output.rgb = _input.rgb * tex0.rgb * mix(vec3(1.0, 1.0, 1.0), tex1.rgb * vec3(1.0, 1.0, 1.0), vec3(tex1.a));", "_output.a = tex0.a;", ],
        PS_Combiners_Opaque_Alpha_Alpha:             [2, "_output.rgb = _input.rgb * mix(mix(tex0.rgb, tex1.rgb, vec3(tex1.a)), tex0.rgb, vec3(tex0.a));", "", ],
        PS_Combiners_Opaque_Mod2xNA_Alpha_3s:        [3, "_output.rgb = _input.rgb * mix(tex0.rgb * tex1.rgb * 2.0, tex2.rgb, vec3(tex2.a));", ],
        PS_Combiners_Mod_Add_Alpha:                  [2, "_output.rgb = _input.rgb * tex0.rgb;", "_output.a = (tex0.a + tex1.a);", "_specular = tex1.rgb * (1.0 - tex0.a);",],
        PS_Combiners_Opaque_ModNA_Alpha:             [2, "_output.rgb = _input.rgb * mix(tex0.rgb * tex1.rgb, tex0.rgb, vec3(tex0.a));", "", ],
        PS_Combiners_Opaque_Mod2xNA_Alpha_UnshAlpha: [3, "float glowOpacity = clamp((tex2.a * vec4(1.0, 1.0, 1.0, 1.0).z), 0.0, 1.0); _output.rgb = _input.rgb * mix(tex0.rgb * tex1.rgb * 2.000000, tex0.rgb, vec3(tex0.a)) * (1.0 - glowOpacity);", "_specular = tex2.rgb * glowOpacity;", ],
        PS_Combiners_Opaque_Mod2xNA_Alpha_Alpha:     [3, "_output.rgb = _input.rgb * mix(mix(tex0.rgb * tex1.rgb * 2.000000, tex2.rgb, vec3(tex2.a)), tex0.rgb, vec3(tex0.a));", "", ],
        PS_Combiners_Mod_Depth:                      [1, "_output.rgb = _input.rgb * tex0.rgb;", "_output.a = tex0.a;"],
        PS_Combiners_Opaque_Alpha:                   [2, "_output.rgb = _input.rgb * mix(tex0.rgb, tex1.rgb, vec3(tex1.a));", ""],
        Skin:                                        [3, "//Fresnel Rim\r\nif (uHasSpecEmiss) {\r\n    vec3 emissiveColor = tex2.rgb;\r\n    vec3 emissiveTerm = tex2.rgb;\r\n    if (uHasEmissiveGlowing) {\r\n        vec3 eyeVec_120 = vPosition.xyz;\r\n        vec3 t121 = -(eyeVec_120);\r\n        vec2 term_126 = vec2(dot(t121, vNormal), dot(normalize(t121), (vNormal * vec3(0.0500000007, 0.0500000007, 1.0))));\r\n        vec2 invTerm_128 = (vec2(1.0) - clamp(term_126, 0.0, 1.0));\r\n        vec2 f_129 = (invTerm_128 * invTerm_128);\r\n        float fresnel_rim_133 = pow((f_129.x + f_129.y), 0.600000024);\r\n        vec3 t136 = (tex2.rgb /*+ ((vec3(0.0500000007, 0.0, 0.400000006) * 1.0) * fresnel_rim_133)*/);\r\n        emissiveColor = vec3(t136.r, tex2.g, t136.b);\r\n\r\n        float t267 = dot(normalize(vNormal),  normalize(-(vPosition.xyz)));\r\n        emissiveTerm = mix(vec3(0.0), 2.0*emissiveColor, vec3(pow(clamp(t267, 0.0, 1.0), (( 128.0 * (tex2.a)) + 9.99999975e-006))));\r\n    }\r\n\r\n    _output.rgb = _input.rgb * tex0.rgb + tex1.rgb + emissiveTerm.rgb;\r\n} else {\r\n    _output.rgb = _input.rgb * tex0.rgb;\r\n}\r\n_output.a = tex0.a; //\r\n", ],
        PS_Combiners_Mod_Dual_Crossfade:             [3, "_output.rgb = _input.rgb * mix(mix(tex0, texture2D(uTexture2,vTexCoord1), vec4(clamp(uTexSampleAlpha.g, 0.000000, 1.000000))), texture2D(uTexture3,vTexCoord1), vec4(clamp(uTexSampleAlpha.b, 0.000000, 1.000000))).rgb;", "_output.a = mix(mix(tex0, texture2D(uTexture2,vTexCoord1), vec4(clamp(uTexSampleAlpha.g, 0.000000, 1.000000))), texture2D(uTexture3,vTexCoord1), vec4(clamp(uTexSampleAlpha.b, 0.000000, 1.000000))).a;",],
        PS_Combiners_Mod_Masked_Dual_Crossfade:      [4, "_output.rgb = _input.rgb * mix(mix(tex0, texture2D(uTexture2,texCoord), vec4(clamp(uTexSampleAlpha.g, 0.000000, 1.000000))), texture2D(uTexture3,texCoord), vec4(clamp(uTexSampleAlpha.b, 0.000000, 1.000000))).rgb;", "_output.a = mix(mix(tex0, texture2D(uTexture2,texCoord), vec4(clamp(uTexSampleAlpha.g, 0.000000, 1.000000))), texture2D(uTexture3,texCoord), vec4(clamp(uTexSampleAlpha.b, 0.000000, 1.000000))).a * texture(uTexture4,texCoord2).a;",],
        PS_Combiners_Mod_Mod_Depth:                  [2, "_output.rgb = (_input.rgb * tex0.rgb) * tex1.rgb;", "_output.a = tex0.a * tex1.a;",],
    },

    WoWShaderTool = ShaderTool;

    const Gi = class {
        constructor(t) {
            (this.b = t), (this.e = new Set());
        }
        a() {
            const t = this.e;
            return (this.e = new Set()), t;
        }
        d(t) {
            this.e.has(t) || (this.b.enableVertexAttribArray(t), this.e.add(t));
        }
        c(t) {
            const e = this.b;
            [...t].filter((t) => !this.e.has(t)).forEach((t) => e.disableVertexAttribArray(t));
        }
    };
    const Li = class {};
    const ji = class {};
    const qi = class extends ji {};
    function Vi() {
        var t = new GLMAT_ARRAY_TYPE(4);
        return GLMAT_ARRAY_TYPE != Float32Array && ((t[0] = 0), (t[1] = 0), (t[2] = 0), (t[3] = 0)), t;
    }
    function Wi(t, e, i, s) {
        var r = new GLMAT_ARRAY_TYPE(4);
        return (r[0] = t), (r[1] = e), (r[2] = i), (r[3] = s), r;
    }
    function Xi(t, e) {
        return (t[0] = e[0]), (t[1] = e[1]), (t[2] = e[2]), (t[3] = e[3]), t;
    }
    function Yi(t, e, i, s, r) {
        return (t[0] = e), (t[1] = i), (t[2] = s), (t[3] = r), t;
    }
    function Zi(t, e, i) {
        return (t[0] = e[0] + i[0]), (t[1] = e[1] + i[1]), (t[2] = e[2] + i[2]), (t[3] = e[3] + i[3]), t;
    }
    function Ki(t, e, i) {
        return (t[0] = e[0] - i[0]), (t[1] = e[1] - i[1]), (t[2] = e[2] - i[2]), (t[3] = e[3] - i[3]), t;
    }
    function $i(t, e, i) {
        return (t[0] = e[0] * i), (t[1] = e[1] * i), (t[2] = e[2] * i), (t[3] = e[3] * i), t;
    }
    function Ji(t) {
        var e = t[0],
            i = t[1],
            s = t[2],
            r = t[3];
        return Math.hypot(e, i, s, r);
    }
    function Qi(t, e) {
        var i = e[0],
            s = e[1],
            r = e[2],
            n = e[3],
            a = i * i + s * s + r * r + n * n;
        return a > 0 && (a = 1 / Math.sqrt(a)), (t[0] = i * a), (t[1] = s * a), (t[2] = r * a), (t[3] = n * a), t;
    }
    function ts(t, e, i) {
        var s = e[0],
            r = e[1],
            n = e[2],
            a = e[3];
        return (
            (t[0] = i[0] * s + i[4] * r + i[8] * n + i[12] * a),
            (t[1] = i[1] * s + i[5] * r + i[9] * n + i[13] * a),
            (t[2] = i[2] * s + i[6] * r + i[10] * n + i[14] * a),
            (t[3] = i[3] * s + i[7] * r + i[11] * n + i[15] * a),
            t
        );
    }
    var es = Ji;
    !(function () {
        var t = Vi();
    })();
    const is = class extends qi {
        constructor(t, e) {
            super(),
                (this.f = e),
                (this.h = vec3Create()),
                (this.cba = vec3FromValues(0, 0, 0)),
                (this.j = Vi()),
                (this.i = Wi(0, 0, 0, 0)),
                (this.g = new Ai(t, (40 * e.length) / 4, true)),
                this.dc(e);
        }
        ba(t, e) {
            const i = this.ed;
            let s = this.f.length;
            for (let r = 0; r < s; ++r) {
                if (!e.has(r)) continue;
                vec3Set(this.cba, 0, 0, 0), Yi(this.i, 0, 0, 0, 0);
                const s = this.f[r];
                let n = false;
                for (let e = 0; e < 4; ++e) {
                    const i = s.g[e] / 255;
                    if (i > 0) {
                        const r = t[s.a[e]];
                        vec3TransformMat4(this.h, s.c, r.i),
                            ts(this.j, s.d, r.g),
                            (this.cba[0] = this.cba[0] + this.h[0] * i),
                            (this.cba[1] = this.cba[1] + this.h[1] * i),
                            (this.cba[2] = this.cba[2] + this.h[2] * i),
                            (this.i[0] = this.i[0] + this.j[0] * i),
                            (this.i[1] = this.i[1] + this.j[1] * i),
                            (this.i[2] = this.i[2] + this.j[2] * i),
                            (n = true);
                    }
                }
                if (n) {
                    let t = 10 * r;
                    (i[t++] = this.cba[0]),
                        (i[t++] = this.cba[1]),
                        (i[t++] = this.cba[2]),
                        (i[t++] = this.i[0]),
                        (i[t++] = this.i[1]),
                        (i[t++] = this.i[2]);
                }
            }
            this.g.d(this.ed);
        }
        dc(t) {
            const e = (40 * t.length) / 4;
            this.ed = new Float32Array(e);
            const i = this.ed,
                s = t;
            let r = 0;
            for (let t = 0; t < s.length; ++t)
                (i[r++] = s[t].c[0]),
                    (i[r++] = s[t].c[1]),
                    (i[r++] = s[t].c[2]),
                    (i[r++] = s[t].d[0]),
                    (i[r++] = s[t].d[1]),
                    (i[r++] = s[t].d[2]),
                    (i[r++] = s[t].i),
                    (i[r++] = s[t].h),
                    (i[r++] = s[t].f),
                    (i[r++] = s[t].e);
            this.g.d(this.ed);
        }
        d(t) {
            this.g.d(t);
        }
        b() {
            return this.g.b();
        }
        a() {
            this.g.a();
        }
        c() {
            this.g.c();
        }
    };
    const ss = class extends qi {
        constructor(t, e) {
            super(), (this.cba = e), (this.f = new Ai(t, 48 * e.length, true)), this.dc(e);
        }
        ba(t, e) {}
        dc(t) {
            const e = 48 * t.length;
            this.ed = new Uint8Array(e);
            let i = new DataView(this.ed.buffer);
            const s = t;
            let r = 0;
            for (let t = 0; t < s.length; ++t)
                i.setFloat32(r, s[t].c[0], true),
                    (r += 4),
                    i.setFloat32(r, s[t].c[1], true),
                    (r += 4),
                    i.setFloat32(r, s[t].c[2], true),
                    (r += 4),
                    i.setFloat32(r, s[t].d[0], true),
                    (r += 4),
                    i.setFloat32(r, s[t].d[1], true),
                    (r += 4),
                    i.setFloat32(r, s[t].d[2], true),
                    (r += 4),
                    i.setUint8(r, s[t].a[0]),
                    (r += 1),
                    i.setUint8(r, s[t].a[1]),
                    (r += 1),
                    i.setUint8(r, s[t].a[2]),
                    (r += 1),
                    i.setUint8(r, s[t].a[3]),
                    (r += 1),
                    i.setUint8(r, s[t].g[0]),
                    (r += 1),
                    i.setUint8(r, s[t].g[1]),
                    (r += 1),
                    i.setUint8(r, s[t].g[2]),
                    (r += 1),
                    i.setUint8(r, s[t].g[3]),
                    (r += 1),
                    i.setFloat32(r, s[t].i, true),
                    (r += 4),
                    i.setFloat32(r, s[t].h, true),
                    (r += 4),
                    i.setFloat32(r, s[t].f, true),
                    (r += 4),
                    i.setFloat32(r, s[t].e, true),
                    (r += 4);
            this.f.d(this.ed);
        }
        d(t) {
            this.f.d(t);
        }
        b() {
            return this.f.b();
        }
        a() {
            this.f.a();
        }
        c() {
            this.f.c();
        }
    };
    const rs = class {
        a() {
            return {};
        }
        b(t) {}
    };
    const ns = class {
        constructor(t, e) {
            (this.d = t),
                (this.c = e),
                (this.c = 256),
                (this.e = new Float32Array(16 * this.c)),
                (this.ba = t.createTexture()),
                t.bindTexture(t.TEXTURE_2D, this.ba),
                t.texParameteri(t.TEXTURE_2D, t.TEXTURE_MIN_FILTER, t.NEAREST),
                t.texParameteri(t.TEXTURE_2D, t.TEXTURE_MAG_FILTER, t.NEAREST),
                t.texParameteri(t.TEXTURE_2D, t.TEXTURE_WRAP_S, t.CLAMP_TO_EDGE),
                t.texParameteri(t.TEXTURE_2D, t.TEXTURE_WRAP_T, t.CLAMP_TO_EDGE),
                t.bindTexture(t.TEXTURE_2D, null);
        }
        a() {
            return { uBoneMatricesTex: this.ba };
        }
        b(t) {
            const e = Math.min(256, t.length);
            for (let i = 0; i < e; i++) this.e.set(t[i].i, 16 * i);
            const i = this.d;
            i.bindTexture(i.TEXTURE_2D, this.ba),
                i.texImage2D(i.TEXTURE_2D, 0, i.RGBA, 4, this.c, 0, i.RGBA, i.FLOAT, this.e),
                i.bindTexture(i.TEXTURE_2D, null);
        }
    };
    var as;
    !(function (t) {
        (t[(t.aPosition = 0)] = "aPosition"),
            (t[(t.aNormal = 1)] = "aNormal"),
            (t[(t.aTexCoord0 = 2)] = "aTexCoord0"),
            (t[(t.aTexCoord1 = 3)] = "aTexCoord1");
    })(as || (as = {}));
    const os = { aPosition: as.aPosition, aNormal: as.aNormal, aTexCoord0: as.aTexCoord0, aTexCoord1: as.aTexCoord1 },
        hs = [
            new Ci(as.aPosition, 3, Ei.GFLOAT, false, 40, 0),
            new Ci(as.aNormal, 3, Ei.GFLOAT, false, 40, 12),
            new Ci(as.aTexCoord0, 2, Ei.GFLOAT, false, 40, 24),
            new Ci(as.aTexCoord1, 2, Ei.GFLOAT, false, 40, 32),
        ];
    var ls;
    !(function (t) {
        (t[(t.aPosition = 0)] = "aPosition"),
            (t[(t.aNormal = 1)] = "aNormal"),
            (t[(t.aBones = 2)] = "aBones"),
            (t[(t.aBoneWeights = 3)] = "aBoneWeights"),
            (t[(t.aTexCoord0 = 4)] = "aTexCoord0"),
            (t[(t.aTexCoord1 = 5)] = "aTexCoord1");
    })(ls || (ls = {}));
    const us = {
            aPosition: ls.aPosition,
            aNormal: ls.aNormal,
            aBones: ls.aBones,
            aBoneWeights: ls.aBoneWeights,
            aTexCoord0: ls.aTexCoord0,
            aTexCoord1: ls.aTexCoord1,
        },
        cs = [
            new Ci(ls.aPosition, 3, Ei.GFLOAT, false, 48, 0),
            new Ci(ls.aNormal, 3, Ei.GFLOAT, false, 48, 12),
            new Ci(ls.aBones, 4, Ei.GUNSIGNED_BYTE, false, 48, 24),
            new Ci(ls.aBoneWeights, 4, Ei.GUNSIGNED_BYTE, true, 48, 28),
            new Ci(ls.aTexCoord0, 2, Ei.GFLOAT, false, 48, 32),
            new Ci(ls.aTexCoord1, 2, Ei.GFLOAT, false, 48, 40),
        ];
    var ds;
    !(function (t) {
        (t[(t.aPosition = 0)] = "aPosition"),
            (t[(t.aColor = 1)] = "aColor"),
            (t[(t.aTexcoord0 = 2)] = "aTexcoord0"),
            (t[(t.aTexcoord1 = 3)] = "aTexcoord1"),
            (t[(t.aTexcoord2 = 4)] = "aTexcoord2"),
            (t[(t.aAlphaCutoff = 5)] = "aAlphaCutoff");
    })(ds || (ds = {}));
    const fs = {
            [ds.aPosition]: ds.aPosition,
            [ds.aColor]: ds.aColor,
            [ds.aTexcoord0]: ds.aTexcoord0,
            [ds.aTexcoord1]: ds.aTexcoord1,
            [ds.aTexcoord2]: ds.aTexcoord2,
            [ds.aAlphaCutoff]: ds.aAlphaCutoff,
        },
        gs = [
            new Ci(ds.aPosition, 3, Ei.GFLOAT, false, 56, 0),
            new Ci(ds.aColor, 4, Ei.GFLOAT, false, 56, 12),
            new Ci(ds.aTexcoord0, 2, Ei.GFLOAT, false, 56, 28),
            new Ci(ds.aTexcoord1, 2, Ei.GFLOAT, false, 56, 36),
            new Ci(ds.aTexcoord2, 2, Ei.GFLOAT, false, 56, 44),
            new Ci(ds.aAlphaCutoff, 1, Ei.GFLOAT, false, 56, 52),
        ];
    var _s;
    !(function (t) {
        (t[(t.aPosition = 0)] = "aPosition"), (t[(t.aColor = 1)] = "aColor"), (t[(t.aTexcoord0 = 2)] = "aTexcoord0");
    })(_s || (_s = {}));
    const bs = { [_s.aPosition]: _s.aPosition, [_s.aColor]: _s.aColor, [_s.aTexcoord0]: _s.aTexcoord0 },
        ms = [
            new Ci(_s.aPosition, 3, Ei.GFLOAT, false, 36, 0),
            new Ci(_s.aColor, 4, Ei.GFLOAT, false, 36, 12),
            new Ci(_s.aTexcoord0, 2, Ei.GFLOAT, false, 36, 28),
        ];
    const ps = class {
        constructor(t, e) {
            (this.ji = t), (this.hg = e), (this.dc = new Map()), (this.fe = new Gi(t.k()));
            const i = t.k();
            (this.ba = i.createTexture()),
                i.bindTexture(i.TEXTURE_2D, this.ba),
                i.texImage2D(i.TEXTURE_2D, 0, i.RGBA, 1, 1, 0, i.RGBA, i.UNSIGNED_BYTE, new Uint8Array([0, 0, 0, 0])),
                i.bindTexture(i.TEXTURE_2D, null);
        }
        lk() {
            throw new Error("Method not implemented.");
        }
        a() {
            return this.ji;
        }
        d(t) {
            return new yi(this.ji.k(), t, false);
        }
        k(t) {
            return this.ji.o ? new ss(this.ji.k(), t) : new is(this.ji.k(), t);
        }
        n(t) {
            return new Ai(this.ji.k(), t, true);
        }
        c(t) {
            return new Ai(this.ji.k(), t, true);
        }
        g(t, e) {
            const i = this.ji.g(),
                s = i ? new ki(this.ji.k(), i) : new Si(this.ji.k(), this.fe),
                r = this.ji.o ? cs : hs;
            return s.a(t, r), s.c(e), s;
        }
        i(t, e) {
            const i = this.ji.g(),
                s = i ? new ki(this.ji.k(), i) : new Si(this.ji.k(), this.fe);
            return s.c(e), s.a(t, gs), s;
        }
        b(t, e) {
            const i = this.ji.g(),
                s = i ? new ki(this.ji.k(), i) : new Si(this.ji.k(), this.fe);
            return s.c(e), s.a(t, ms), s;
        }
        j(t, e, i, s) {
            return this.ji.o ? new ns(this.ji.k(), t) : new rs();
        }
        o(t, e, i) {
            const s = WoWShaderTool.GetWowPSShaderName(i.a, i.b.length),
                r = WoWShaderTool.GetWowVSShaderName(i.a, i.b.length),
                n = "Wow." + r + "_" + s + (i.d ? "_gradient" : "");
            let a;
            this.dc.has(n)
                ? (a = this.dc.get(n))
                : ((a = new Oi(this.ji.k(), this.ji.o ? us : os, WoWShaderTool.GenerateVS(r, this.ji.o), WoWShaderTool.GeneratePS(r, s, i.d))),
                  this.dc.set(n, a));
            const o = Object.assign(Object.assign(Object.assign({}, this.hg), t.a()), i.c);
            for (let t = 0; t < Math.max(i.b.length, 4); t++) {
                let e = t < i.b.length ? i.b[t].a : this.ba;
                o["uTexture" + (t + 1).toString()] = e;
            }
            return new Bi(this.ji.k(), a, e, o);
        }
        f(t, e, i) {
            const s = Object.assign(Object.assign(Object.assign({}, this.hg), t.a()), i.b);
            let r;
            const n = "ParticleShader";
            this.dc.has(n)
                ? (r = this.dc.get(n))
                : ((r = new Oi(
                      this.ji.k(),
                      fs,
                      "attribute vec3 aPosition;\r\nattribute vec4 aColor;\r\nattribute vec2 aTexcoord0;\r\nattribute vec2 aTexcoord1;\r\nattribute vec2 aTexcoord2;\r\nattribute float aAlphaCutoff;\r\n\r\nvarying vec4 vColor;\r\nvarying vec2 vTexcoord0;\r\nvarying vec2 vTexcoord1;\r\nvarying vec2 vTexcoord2;\r\nvarying float vAlphaCutoff;\r\n\r\nuniform mat4 uModelMatrix;\r\nuniform mat4 uViewMatrix;\r\nuniform mat4 uProjMatrix;\r\n\r\nvoid main(void) {\r\n    vec4 pos = vec4(aPosition, 1);\r\n\r\n    gl_Position = uProjMatrix * pos;\r\n\r\n    vColor = aColor;\r\n    vTexcoord0 = aTexcoord0;\r\n    vTexcoord1 = aTexcoord1;\r\n    vTexcoord2 = aTexcoord2;\r\n    vAlphaCutoff = aAlphaCutoff;\r\n}",
                      "precision mediump float;\r\n\r\nvarying vec4 vColor;\r\nvarying vec2 vTexcoord0;\r\nvarying vec2 vTexcoord1;\r\nvarying vec2 vTexcoord2;\r\nvarying float vAlphaCutoff;\r\n\r\nuniform bool uHasTexture;\r\nuniform bool uHasTexture2;\r\nuniform bool uHasTexture3;\r\nuniform bool uHasAlpha;\r\nuniform int uBlendMode;\r\nuniform int uPixelShader;\r\nuniform sampler2D uTexture0;\r\nuniform sampler2D uTexture1;\r\nuniform sampler2D uTexture2;\r\nuniform float uAlphaTreshold;\r\n\r\nuniform float alphaMult;\r\nuniform float colorMult;\r\n\r\nvoid main(void) {\r\n    float lo_thresh = 0.01;\r\n    vec4 color = vec4(1, 1, 1, 1);\r\n    vec4 tex = vec4(1, 1, 1, 1);\r\n    vec4 tex2 = vec4(1, 1, 1, 1);\r\n    vec4 tex3 = vec4(1, 1, 1, 1);\r\n    if (uHasTexture) {\r\n        tex = texture2D(uTexture0, vTexcoord0).rgba;\r\n    }\r\n    if (uHasTexture2) {\r\n        tex2 = texture2D(uTexture1, vTexcoord1).rgba;\r\n    }\r\n    if (uHasTexture3) {\r\n        tex3 = texture2D(uTexture2, vTexcoord2).rgba;\r\n    }\r\n    vec4 finalColor = vec4((tex * vColor ).rgb, tex.a*vColor.a );\r\n    vec3 matDiffuse = vec3(1.0);\r\n    float opacity = 1.0;\r\n    if (uPixelShader == 0) {\r\n        matDiffuse = vColor.xyz * tex.rgb;\r\n        opacity = tex.a*vColor.a;\r\n    } else if (uPixelShader == 1) {\r\n        vec4 textureMod = tex*tex2;\r\n        float texAlpha = (textureMod.w * tex3.w);\r\n        opacity = texAlpha*vColor.a;\r\n        matDiffuse = vColor.xyz * 4.0 * textureMod.rgb;\r\n    } else if (uPixelShader == 2) {\r\n        vec4 textureMod = tex*tex2*tex3;\r\n        float texAlpha = (textureMod.w);\r\n        opacity = texAlpha*vColor.a;\r\n        matDiffuse = vColor.xyz * textureMod.rgb;\r\n    } else if (uPixelShader == 3) {\r\n        vec4 textureMod = tex*tex2*tex3;\r\n        float texAlpha = (textureMod.w);\r\n        opacity = texAlpha*vColor.a;\r\n\r\n        matDiffuse = vColor.xyz * textureMod.rgb;\r\n    } else if (uPixelShader == 4) {\r\n        discard;\r\n    }\r\n\r\n    finalColor = vec4(matDiffuse.rgb * colorMult, opacity * alphaMult);\r\n\r\n    if (finalColor.a < vAlphaCutoff ) discard;\r\n    if (finalColor.a < uAlphaTreshold ) discard;\r\n    gl_FragColor = finalColor;\r\n}\r\n"
                  )),
                  this.dc.set(n, r));
            for (let t = 0; t < i.a.length; t++) i.a[t] && (s["uTexture" + t.toString()] = i.a[t].a);
            return new Bi(this.ji.k(), r, e, s);
        }
        e(t, e, i) {
            const s = Object.assign(Object.assign(Object.assign({}, this.hg), t.a()), i.a);
            let r;
            const n = "RibbonShader";
            return (
                this.dc.has(n)
                    ? (r = this.dc.get(n))
                    : ((r = new Oi(
                          this.ji.k(),
                          bs,
                          "attribute vec3 aPosition;\r\nattribute vec4 aColor;\r\nattribute vec2 aTexcoord0;\r\n\r\nuniform mat4 uViewMatrix;\r\nuniform mat4 uProjMatrix;\r\n\r\nvarying vec4 vColor;\r\nvarying vec2 vTexcoord0;\r\n\r\nvoid main() {\r\n    vec4 aPositionVec4 = vec4(aPosition, 1);\r\n    vColor = aColor;\r\n    vTexcoord0 = aTexcoord0;\r\n\r\n    gl_Position = uProjMatrix * uViewMatrix * aPositionVec4;\r\n}\r\n\r\n\r\n",
                          "precision mediump float;\r\n\r\nvarying vec4 vColor;\r\nvarying vec2 vTexcoord0;\r\nuniform sampler2D uTexture;\r\n\r\nvoid main() {\r\n    vec4 tex = texture2D(uTexture, vTexcoord0).rgba;\r\n    gl_FragColor = vec4((vColor.rgb*tex.rgb), tex.a * vColor.a );\r\n}\r\n"
                      )),
                      this.dc.set(n, r)),
                (s["uTexture".toString()] = i.b[0].a),
                new Bi(this.ji.k(), r, e, s)
            );
        }
        l(t, e, i, s) {
            const r = this.ji.k();
            let n = new Li();
            return (n.d = e), (n.e = t.c), (n.c = r.TRIANGLES), (n.a = t.b), (n.f = t.a), (n.b = s), (n.h = i), n;
        }
        m(t, e, i, s) {
            const r = this.ji.k();
            let n = new Li();
            return (n.d = e), (n.e = t.c), (n.c = r.TRIANGLE_STRIP), (n.a = t.b), (n.f = t.a), (n.b = s), (n.h = i), n;
        }
        h(t) {
            const e = this.ji.k(),
                i = e.createTexture();
            function s(t) {
                return !(t & (t - 1));
            }
            e.bindTexture(e.TEXTURE_2D, i),
                e.pixelStorei(e.UNPACK_PREMULTIPLY_ALPHA_WEBGL, false),
                e.texImage2D(e.TEXTURE_2D, 0, e.RGBA, e.RGBA, e.UNSIGNED_BYTE, t),
                s(t.width) && s(t.height)
                    ? e.generateMipmap(e.TEXTURE_2D)
                    : (e.texParameteri(e.TEXTURE_2D, e.TEXTURE_WRAP_S, e.CLAMP_TO_EDGE),
                      e.texParameteri(e.TEXTURE_2D, e.TEXTURE_WRAP_T, e.CLAMP_TO_EDGE),
                      e.texParameteri(e.TEXTURE_2D, e.TEXTURE_MIN_FILTER, e.LINEAR));
            const r = this.ji.ba;
            return r && e.texParameteri(e.TEXTURE_2D, r.TEXTURE_MAX_ANISOTROPY_EXT, this.ji.l), i;
        }
    };
    var xs;
    !(function (t) {
        (t[(t.GxBlend_UNDEFINED = -1)] = "GxBlend_UNDEFINED"),
            (t[(t.GxBlend_Opaque = 0)] = "GxBlend_Opaque"),
            (t[(t.GxBlend_AlphaKey = 1)] = "GxBlend_AlphaKey"),
            (t[(t.GxBlend_Alpha = 2)] = "GxBlend_Alpha"),
            (t[(t.GxBlend_Add = 3)] = "GxBlend_Add"),
            (t[(t.GxBlend_Mod = 4)] = "GxBlend_Mod"),
            (t[(t.GxBlend_Mod2x = 5)] = "GxBlend_Mod2x"),
            (t[(t.GxBlend_ModAdd = 6)] = "GxBlend_ModAdd"),
            (t[(t.GxBlend_InvSrcAlphaAdd = 7)] = "GxBlend_InvSrcAlphaAdd"),
            (t[(t.GxBlend_InvSrcAlphaOpaque = 8)] = "GxBlend_InvSrcAlphaOpaque"),
            (t[(t.GxBlend_SrcAlphaOpaque = 9)] = "GxBlend_SrcAlphaOpaque"),
            (t[(t.GxBlend_NoAlphaAdd = 10)] = "GxBlend_NoAlphaAdd"),
            (t[(t.GxBlend_ConstantAlpha = 11)] = "GxBlend_ConstantAlpha"),
            (t[(t.GxBlend_Screen = 12)] = "GxBlend_Screen"),
            (t[(t.GxBlend_BlendAdd = 13)] = "GxBlend_BlendAdd"),
            (t[(t.GxBlend_MAX = 14)] = "GxBlend_MAX");
    })(xs || (xs = {}));
    const vs = xs;


    const TexUnit = class {
        constructor(t) {
            (this.m = t),
                (this.ji = -1),
                (this.ba = -1),
                (this.l = -1),
                (this.k = -1),
                (this.n = -1),
                (this.hg = vs.GxBlend_UNDEFINED),
                (this.s = null),
                (this.o = null),
                (this.q = null),
                (this.dc = null),
                (this.t = null);
        }
        p() {
            (this.ji = -1),
                (this.ba = -1),
                (this.l = -1),
                (this.k = -1),
                (this.n = -1),
                (this.hg = vs.GxBlend_UNDEFINED),
                (this.s = null),
                (this.o = null),
                (this.q = null),
                (this.dc = null),
                (this.t = null);
        }
        b(t) {
            this.hg != t && (this.r(t), (this.hg = t));
        }
        c(t) {
            const e = t ? 1 : 0;
            e != this.ji && (this.m.depthMask(t), (this.ji = e));
        }
        a(t) {
            const e = t ? 1 : 0;
            e != this.ba && (t ? this.m.enable(this.m.DEPTH_TEST) : this.m.disable(this.m.DEPTH_TEST), (this.ba = e));
        }
        f(t) {
            const e = t ? 1 : 0;
            e != this.l && (t ? this.m.enable(this.m.CULL_FACE) : this.m.disable(this.m.CULL_FACE), (this.l = e));
        }
        e(t) {
            const e = t ? 1 : 0;
            e != this.k && (t ? this.m.frontFace(this.m.CCW) : this.m.frontFace(this.m.CW), (this.k = e));
        }
        i(t) {
            this.n != t && (this.m.colorMask((1 & t) > 0, (2 & t) > 0, (4 & t) > 0, (8 & t) > 0), (this.n = t));
        }
        h(t) {
            this.o != t && (t ? t.a() : t.c(), (this.o = t));
        }
        j(t) {
            this.s != t && (t ? t.a() : t.c(), (this.s = t));
        }
        g(t) {
            this.q != t && (t ? t.b() : this.q.d(), (this.q = t), (this.s = null), (this.o = null));
        }
        d(t) {
            t != this.dc && (t && t.a(), (this.dc = t));
        }
        r(t) {
            const e = this.m;
            switch ((0 == t ? e.disable(e.BLEND) : (e.enable(e.BLEND), e.blendEquation(e.FUNC_ADD)), t)) {
                case 0:
                    break;
                case 1:
                    e.blendFuncSeparate(e.ONE, e.ZERO, e.ONE, e.ONE);
                    break;
                case 2:
                    e.blendFuncSeparate(e.SRC_ALPHA, e.ONE_MINUS_SRC_ALPHA, e.ONE, e.ONE);
                    break;
                case 3:
                    e.blendFuncSeparate(e.SRC_ALPHA, e.ONE, e.ONE, e.ONE);
                    break;
                case 4:
                    e.blendFuncSeparate(e.DST_COLOR, e.ZERO, e.ONE, e.ONE);
                    break;
                case 5:
                    e.blendFuncSeparate(e.DST_COLOR, e.SRC_COLOR, e.ONE, e.ONE);
                    break;
                case 6:
                    e.blendFuncSeparate(e.DST_COLOR, e.ONE, e.ONE, e.ONE);
                    break;
                case 10:
                    e.blendFunc(e.ONE, e.ONE);
                    break;
                case 7:
                    e.blendFuncSeparate(e.ONE_MINUS_SRC_ALPHA, e.ONE, e.ONE, e.ONE);
                    break;
                case 8:
                    e.blendFuncSeparate(e.ONE_MINUS_SRC_ALPHA, e.ZERO, e.ONE, e.ONE);
                    break;
                case 13:
                    e.blendFuncSeparate(e.ONE, e.ONE_MINUS_SRC_ALPHA, e.ONE, e.ONE);
                    break;
                default:
                    throw 3735927486;
            }
        }
        fe(t) {
            this.t != t && (t.a(this), (this.t = t));
        }
    };
    const GXDevice = class {
        constructor(t, e) {
            (this.j = t),
            (this.m = e),
            (this.h = false),
            (this.o = false),
            (this.i = t.getExtension("OES_vertex_array_object")),
            (this.ba =
                t.getExtension("EXT_texture_filter_anisotropic") ||
                t.getExtension("MOZ_EXT_texture_filter_anisotropic") ||
                t.getExtension("WEBKIT_EXT_texture_filter_anisotropic")),
            this.ba
                ? ((this.l = t.getParameter(this.ba.MAX_TEXTURE_MAX_ANISOTROPY_EXT)),
                    WH.debug("Texture anisotropy enabled", this.l))
                : WH.debug("Texture anisotropy disabled (not supported)"),
            (this.h = t.getParameter(t.MAX_VERTEX_TEXTURE_IMAGE_UNITS) > 0),
            (this.fe = t.getExtension("OES_texture_float")),
            (this.o = this.h && null != this.fe),
            this.o
                ? WH.debug("(float texture) Skinning in shader is supported")
                : WH.debug("(float texture) Skinning in shader is (not supported) "),
            (this.n = new ps(this, e)),
            (this.dc = new TexUnit(t));
        }
        k() {
            return this.j;
        }
        g() {
            return this.i;
        }
        e() {
            return this.n;
        }
        d(t) {
            const e = this.dc,
                i = this.k();
            e.g(t.e), e.fe(t.d), i.drawElements(t.c, t.f, i.UNSIGNED_SHORT, t.a);
        }
        c() {
            this.dc.p();
        }
        b() {
            this.dc.g(null);
        }
        a(t) {
            this.c(),
                t.forEach((t) => {
                    this.d(t);
                }),
                this.b();
        }
    };

    const Texture = class {
        constructor(webGL, file)
        {
            this.d = webGL;
            this.l = file;
            this.a = null;
            this.e = false;
            this.f = 0;
            this.b = 0;

            if (file == 0)
                return;

            this.c = webGL.options.contentPath + "textures/" + file + WH.WebP.getImageExtension();

            this.g = new Image();
            this.g.onload = () => { this.k(); };
            this.g.onerror = () => { this.g = null; };

            this.load(this.g, this.c);
        }

        load(image, path)
        {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", path, true);
            xhr.responseType = "arraybuffer";
            xhr.onload = (event) => {
                var data = new Blob([xhr.response]);
                image.src = window.URL.createObjectURL(data);
            };
            xhr.addEventListener("progress", (event) => {
                const webGL = this.d;
                if (webGL && event.lengthComputable)
                {
                    if (webGL.downloads[path])
                        webGL.downloads[path].loaded = event.loaded;
                    else
                        webGL.downloads[path] = { loaded: event.loaded, total: event.total };

                    webGL.updateProgress();
                };
            });
            xhr.addEventListener("load", () => {
                const webGL = this.d;
                if (webGL)
                {
                    delete webGL.downloads[path];
                    webGL.updateProgress();
                }
            });
            xhr.send();
        }

        i()
        {
            return this.e;
        }

        j()
        {
            this.a = null;
        }

        k()
        {
                (this.f = this.g.width),
                (this.b = this.g.height),
                (this.a = this.d.renderer.h(this.g)),
                (this.e = true),
                (this.g = null);
        }
    };

    const
        ReversedModels = { 147259: true },
        GeosetDefaults = [1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 0, 0, 0, 2, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
        GeosetOverrides = {
            2:  { GeosetType: 15, Original: 2,  Override: 11 },
            3:  { GeosetType: 15, Original: 3,  Override: 12 },
            4:  { GeosetType: 15, Original: 4,  Override: 13 },
            5:  { GeosetType: 15, Original: 5,  Override: 14 },
            6:  { GeosetType: 15, Original: 6,  Override: 15 },
            7:  { GeosetType: 15, Original: 7,  Override: 16 },
            8:  { GeosetType: 15, Original: 8,  Override: 17 },
            9:  { GeosetType: 15, Original: 9,  Override: 18 },
            10: { GeosetType: 15, Original: 10, Override: 19 },
            11: { GeosetType: 12, Original: 2,  Override: 0  },
            12: { GeosetType: 12, Original: 3,  Override: 0  },
            13: { GeosetType: 12, Original: 1,  Override: 5  },
            14: { GeosetType: 12, Original: 2,  Override: 3  },
            15: { GeosetType: 12, Original: 2,  Override: 2  },
            16: { GeosetType: 22, Original: 2,  Override: 1  },
            17: { GeosetType: 22, Original: 1,  Override: 2  },
            18: { GeosetType: 22, Original: 1,  Override: 3  },
            19: { GeosetType: 22, Original: 2,  Override: 3  },
            20: { GeosetType: 12, Original: 1,  Override: 1  },
            21: { GeosetType: 12, Original: 1,  Override: 9  },
            22: { GeosetType: 12, Original: 2,  Override: 10 },
            23: { GeosetType: 12, Original: 2,  Override: 6  },
            24: { GeosetType: 12, Original: 1,  Override: 5  },
            25: { GeosetType: 27, Original: 0,  Override: 1  },
            26: { GeosetType: 27, Original: 0,  Override: 1  },
            27: { GeosetType: 27, Original: 0,  Override: 1  },
            28: { GeosetType: 13, Original: 1,  Override: 0  },
            31: { GeosetType: 12, Original: 1,  Override: 13 },
            32: { GeosetType: 12, Original: 2,  Override: 14 },
            33: { GeosetType: 42, Original: 11, Override: 1  },
            38: { GeosetType: 20, Original: 1,  Override: 9  },
        },
        Types = {
            ITEM: 1,
            HELM: 2,
            SHOULDER: 4,
            NPC: 8,
            CHARACTER: 16,
            HUMANOIDNPC: 32,
            OBJECT: 64,
            ARMOR: 128,
            PATH: 256,
            ITEMVISUAL: 512,
            COLLECTION: 1024,
        },
        UniqueSlots = [0, 1, 0, 3, 4, 5, 6, 7, 8, 9, 10, 0, 0, 21, 22, 22, 16, 21, 0, 19, 5, 21, 22, 22, 0, 21, 21, 27],
        SlotOrder = [0, 16, 0, 15, 1, 7, 10, 5, 6, 6, 8, 0, 0, 17, 18, 19, 14, 20, 0, 9, 7, 21, 22, 23, 0, 24, 25, 0],
        SlotAlternate = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 22, 0, 0, 0, 22, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        SlotType = [0, 2, 0, 4, 128, 128, 128, 128, 128, 128, 128, 0, 0, 1, 1, 1, 128, 1, 0, 128, 128, 1, 1, 1, 0, 1, 1, 2],
        Ds = [13, 14, 15, 16, 17, 88, 89], // aowow - some race ids .. something with hands closed animation
        Rs = [8, 9, 10, 11, 12, 86, 87], // aowow - some race ids .. something with hands closed animation
        RaceFallbacks = {
            86: [4, 0, 4, 1, 4, 0, 4, 1],
            85: [84, 0, 84, 1, 84, 0, 84, 1],
            84: [3, 0, 3, 1, 3, 0, 3, 1],
            77: [5, 1, 0, -1, 5, 0, 0, -1],
            76: [10, 0, 1, 1, 10, 0, 1, 1],
            75: [10, 0, 1, 1, 10, 0, 1, 1],
            74: [5, 1, 0, -1, 5, 0, 0, -1],
            73: [5, 1, 0, -1, 5, 0, 0, -1],
            72: [5, 1, 0, -1, 5, 0, 0, -1],
            71: [5, 1, 0, -1, 5, 0, 0, -1],
            37: [7, 0, 7, 1, 7, 0, 7, 1],
            36: [2, 0, 2, 1, 2, 0, 2, 1],
            34: [3, 0, 3, 1, 3, 0, 3, 1],
            33: [5, 1, 0, -1, 5, 0, 0, -1],
            31: [0, -1, 8, 1, 0, -1, 8, 1],
            30: [11, 0, 11, 1, 11, 0, 11, 1],
            29: [10, 0, 10, 1, 10, 0, 10, 1],
            28: [6, 0, 6, 1, 6, 0, 6, 1],
            27: [4, 0, 4, 1, 4, 0, 4, 1],
            26: [24, 0, 24, 1, 24, 0, 24, 1],
            25: [24, 0, 24, 1, 24, 0, 24, 1],
            23: [1, 0, 1, 1, 1, 0, 1, 1],
            15: [5, 0, 5, 1, 5, 0, 5, 1],
            1:  [0, -1, 0, -1, 0, -1, 0, 3],
        },
        sheathStandardOverrides = { 21: 26, 22: 27, 15: 28, 17: 26, 25: 32, 13: 32, 23: 33, 14: 28, 26: 26 },
        SheathWeaponOverrides = {
            0: { 21: 26, 22: 27 },
            1: { 21: 26, 22: 27 },
            2: { 21: 30, 22: 31 },
            3: { 21: 32, 22: 33 },
            4: { 21: 26, 22: 27, 15: 28 },
            5: { 21: 26 },
            6: { 21: 26, 22: 27 },
            7: { 21: 26, 22: 27 },
            8: { 21: 26, 22: 27 },
            9: { 21: 33, 22: 28 },
        },
        Ps = 5300,
        zs =
            "precision mediump float;\r\n\r\nuniform float x;\r\nuniform float y;\r\nuniform float width;\r\nuniform float height;\r\n\r\nattribute vec2 aTextCoord;\r\nvarying vec2 vTextCoords;\r\nvoid main() {\r\n    vTextCoords = aTextCoord;\r\n\r\n    vec2 pos = vec2(\r\n        (x + aTextCoord.x*width)* 2.0 - 1.0,\r\n        (y + aTextCoord.y*height)* 2.0 - 1.0\r\n    );\r\n\r\n    gl_Position = vec4(pos.x, pos.y, 0, 1);\r\n}";
    class Hs {
        constructor() {
            (this.b = null), (this.e = null), (this.c = null);
        }
        a() {
            null != this.b && this.b.j(), null != this.e && this.e.j(), null != this.c && this.c.j();
        }
        d() {
            return !(this.b && !this.b.i()) && !(this.e && !this.e.i()) && !(this.c && !this.c.i());
        }
    }
    class Ns {
        constructor() {
            (this.d = null),
                (this.h = null),
                (this.i = null),
                (this.k = {}),
                (this.c = new AttributeState()),
                (this.a = null),
                (this.j = null);
        }
    }
    const Gs = { uDiffuseTexture: null, uSpecularTexture: null, uEmissiveTexture: null };
    class Ls {
        constructor(t, e, i) {
            (this.c = e),
                (this.r = i),
                (this.t = null),
                (this.q = null),
                (this.l = null),
                (this.m = null),
                (this.k = null),
                (this.o = false),
                (this.b = 0),
                (this.h = ""),
                (this.d = t),
                (this.k = (function (t) {
                    let e = t.createTexture();
                    t.bindTexture(t.TEXTURE_2D, e),
                        t.texImage2D(
                            t.TEXTURE_2D,
                            0,
                            t.RGBA,
                            1,
                            1,
                            0,
                            t.RGBA,
                            t.UNSIGNED_BYTE,
                            new Uint8Array([0, 0, 0, 0])
                        ),
                        t.bindTexture(t.TEXTURE_2D, null);
                    let i = t.createTexture();
                    t.bindTexture(t.TEXTURE_2D, i),
                        t.texImage2D(
                            t.TEXTURE_2D,
                            0,
                            t.RGBA,
                            1,
                            1,
                            0,
                            t.RGBA,
                            t.UNSIGNED_BYTE,
                            new Uint8Array([0, 0, 0, 255])
                        ),
                        t.bindTexture(t.TEXTURE_2D, null);
                    let s = new Ns();
                    return (
                        (s.a = e),
                        (s.j = i),
                        (s.d = createProgramInfo(
                            t,
                            [
                                zs,
                                "precision mediump float;\r\n\r\nvarying vec2 vTextCoords;\r\nuniform sampler2D uDiffuseTexture;\r\nuniform sampler2D uSpecularTexture;\r\nuniform sampler2D uEmissiveTexture;\r\nuniform sampler2D renderResultTexture;\r\nuniform int uBlendMode;\r\nuniform vec2 screenResolution;\r\nuniform int layer;\r\n\r\nuniform float diffuseTexWidth;\r\nuniform float diffuseTexHeight;\r\n\r\nfloat overlayBlend(float a, float b) {\r\n    if (b > 0.5) {\r\n        return (1.0 - (1.0 - 2.0 * (a - 0.5)) * (1.0 - b));\r\n    } else {\r\n        return ((2.0 * a) * b);\r\n    }\r\n}\r\n\r\nfloat alphaStraightBlend(float a, float b, float alpha) {\r\n    return (a * alpha) + (b * (1.0 - alpha));\r\n}\r\n\r\nvoid main() {\r\n    vec2 l_texCoord = vTextCoords.xy;\r\n\r\n\r\n    l_texCoord.x = max(min(l_texCoord.x, (diffuseTexWidth-0.5)/diffuseTexWidth), 0.5/diffuseTexWidth);\r\n    l_texCoord.y = max(min(l_texCoord.y, (diffuseTexHeight-0.5)/diffuseTexHeight), 0.5/diffuseTexHeight);\r\n\r\n    vec4 diffuse = texture2D( uDiffuseTexture, l_texCoord );\r\n    vec4 backGround = texture2D( renderResultTexture, gl_FragCoord.xy / screenResolution );\r\n\r\n    if (uBlendMode == 1) {\r\n        // Blit (we do nothing?)\r\n        //if (diffuse.a < 0.001) discard;\r\n\r\n        //vec4 finalColor = diffuse;\r\n\r\n        //diffuse = vec4(finalColor.rgb, finalColor.a);\r\n    } else if (uBlendMode == 2) {\r\n        // Multiply\r\n        if (diffuse.a < 0.001) discard;\r\n\r\n        vec4 multTexture = diffuse;\r\n        vec3 finalColor = (backGround.rgb * multTexture.rgb);\r\n\r\n        diffuse = vec4(finalColor.rgb, 1.0);\r\n    } else if (uBlendMode == 3) {\r\n        // Overlay\r\n        if (diffuse.a < 0.001) discard;\r\n\r\n        vec4 overlayTex = diffuse;\r\n\r\n        vec3 finalColor = vec3(\r\n            overlayBlend(overlayTex.r, backGround.r),\r\n            overlayBlend(overlayTex.g, backGround.g),\r\n            overlayBlend(overlayTex.b, backGround.b)\r\n        );\r\n\r\n        vec3 mainTexVisible = backGround.rgb * (1.0 - overlayTex.a);\r\n        vec3 overlayTexVisible = finalColor.rgb * (overlayTex.a);\r\n        finalColor = (mainTexVisible + overlayTexVisible);\r\n\r\n        diffuse = vec4(finalColor, backGround.a);\r\n    } else if (uBlendMode == 5) {\r\n        // AlphaStraight\r\n        vec4 overlayTex = diffuse;\r\n\r\n        //float alphaMult = 1.0;\r\n        //vec3 finalColor = vec3(\r\n        //    alphaStraightBlend(overlayTex.r, backGround.r, alphaMult*overlayTex.a),\r\n        //    alphaStraightBlend(overlayTex.g, backGround.g, alphaMult*overlayTex.a),\r\n        //    alphaStraightBlend(overlayTex.b, backGround.b, alphaMult*overlayTex.a)\r\n        //);\r\n        vec3 finalColor = overlayTex.rgb * overlayTex.a + backGround.rgb * (1.0 - overlayTex.a);\r\n\r\n        diffuse = vec4(finalColor.rgb, 1.0);\r\n    } else if (uBlendMode == 0 || uBlendMode == 4 || uBlendMode == 6 || uBlendMode == 7) {\r\n        // default, Screen, InferAlphaBlend, Unknown1\r\n        if (diffuse.a < 0.001) discard;\r\n\r\n        vec3 finalColor = mix(backGround.rgb, diffuse.rgb, diffuse.a);\r\n\r\n        diffuse = vec4(finalColor.rgb, 1.0);\r\n    }\r\n\r\n    gl_FragColor = diffuse;\r\n}",
                            ],
                            null,
                            null
                        )),
                        (s.h = createProgramInfo(
                            t,
                            [
                                zs,
                                "precision mediump float;\r\n\r\nvarying vec2 vTextCoords;\r\nuniform sampler2D uDiffuseTexture;\r\nuniform sampler2D uSpecularTexture;\r\nuniform sampler2D uEmissiveTexture;\r\nuniform sampler2D renderResultTexture;\r\nuniform int uBlendMode;\r\n\r\nvoid main() {\r\n    vec4 diffuse = texture2D( uDiffuseTexture, vTextCoords.xy );\r\n    vec4 specular = texture2D( uSpecularTexture, vTextCoords.xy );\r\n    if (diffuse.a < 0.001) discard;\r\n    gl_FragColor = vec4(specular.rgb, 1.0);\r\n}",
                            ],
                            null,
                            null
                        )),
                        (s.i = createProgramInfo(
                            t,
                            [
                                zs,
                                "precision mediump float;\r\n\r\nvarying vec2 vTextCoords;\r\nuniform sampler2D uDiffuseTexture;\r\nuniform sampler2D uSpecularTexture;\r\nuniform sampler2D uEmissiveTexture;\r\nuniform sampler2D renderResultTexture;\r\nuniform int uBlendMode;\r\nuniform vec2 screenResolution;\r\nuniform float emissiveAlphaOverride;\r\nuniform int layer;\r\n\r\nvoid main() {\r\n    vec4 diffuse = texture2D( uDiffuseTexture, vTextCoords.xy );\r\n    vec4 emissive = texture2D( uEmissiveTexture, vTextCoords.xy );\r\n    vec4 backGround = texture2D( renderResultTexture, gl_FragCoord.xy / screenResolution );\r\n\r\n    if (diffuse.a < 0.001) discard;\r\n//    if (emissive.a < 0.001) discard;\r\n\r\n    //TODO: This is a hack from what was observed in Nightborne texture customization with tatoos.\r\n    //TODO: But Maybe switch should be over layer or something else instead of blend\r\n    float alpha = 1.0;\r\n\r\n    if (emissiveAlphaOverride > -1.0) {\r\n        alpha = emissiveAlphaOverride;\r\n    } else if (layer <= 1) {\r\n        alpha = 0.0;\r\n    } else {\r\n        alpha = emissive.a;\r\n    }\r\n\r\n    gl_FragColor = vec4(emissive.rgb, alpha);\r\n}",
                            ],
                            null,
                            null
                        )),
                        (s.k = {}),
                        (s.f = t.createBuffer()),
                        t.bindBuffer(t.ARRAY_BUFFER, s.f),
                        t.bufferData(t.ARRAY_BUFFER, new Float32Array([0, 0, 1, 0, 0, 1, 1, 1]), t.STATIC_DRAW),
                        t.bindBuffer(t.ARRAY_BUFFER, null),
                        (s.g = t.createBuffer()),
                        t.bindBuffer(t.ELEMENT_ARRAY_BUFFER, s.g),
                        t.bufferData(t.ELEMENT_ARRAY_BUFFER, new Int16Array([0, 1, 2, 1, 3, 2]), t.STATIC_DRAW),
                        t.bindBuffer(t.ELEMENT_ARRAY_BUFFER, null),
                        (s.b = t.createFramebuffer()),
                        (s.e = {
                            loc: t.getAttribLocation(s.d.program, "aTextCoord"),
                            type: t.FLOAT,
                            size: 2,
                            offset: 0,
                            stride: 0,
                        }),
                        s
                    );
                })(t));
        }
        j() {
            const t = new Date(),
                e = t.toISOString().split("T")[0],
                i = `${String(t.getHours()).padStart(2, "0")}-${String(t.getMinutes()).padStart(2, "0")}-${String(t.getSeconds()).padStart(2, "0")}`,
                s = (65536 * Math.random()) | 0;
            (this.h = `${s}_${e}_${i}`), (this.b = 0);
            let r = this.d;
            this.m ||
                ((this.m = r.createTexture()),
                r.bindTexture(r.TEXTURE_2D, this.m),
                r.texImage2D(r.TEXTURE_2D, 0, r.RGBA, this.c, this.r, 0, r.RGBA, r.UNSIGNED_BYTE, null),
                r.texParameteri(r.TEXTURE_2D, r.TEXTURE_MIN_FILTER, r.LINEAR)),
                this.t ||
                    ((this.t = r.createTexture()),
                    r.bindTexture(r.TEXTURE_2D, this.t),
                    r.texImage2D(r.TEXTURE_2D, 0, r.RGBA, this.c, this.r, 0, r.RGBA, r.UNSIGNED_BYTE, null),
                    r.texParameteri(r.TEXTURE_2D, r.TEXTURE_MIN_FILTER, r.LINEAR)),
                this.q ||
                    ((this.q = r.createTexture()),
                    r.bindTexture(r.TEXTURE_2D, this.q),
                    r.texImage2D(r.TEXTURE_2D, 0, r.RGBA, this.c, this.r, 0, r.RGBA, r.UNSIGNED_BYTE, null),
                    r.texParameteri(r.TEXTURE_2D, r.TEXTURE_MIN_FILTER, r.LINEAR)),
                this.l ||
                    ((this.l = r.createTexture()),
                    r.bindTexture(r.TEXTURE_2D, this.l),
                    r.texImage2D(r.TEXTURE_2D, 0, r.RGBA, this.c, this.r, 0, r.RGBA, r.UNSIGNED_BYTE, null),
                    r.texParameteri(r.TEXTURE_2D, r.TEXTURE_MIN_FILTER, r.LINEAR),
                    r.bindTexture(r.TEXTURE_2D, null)),
                r.bindFramebuffer(r.FRAMEBUFFER, this.k.b),
                r.framebufferTexture2D(r.FRAMEBUFFER, r.COLOR_ATTACHMENT0, r.TEXTURE_2D, this.t, 0),
                r.clear(r.COLOR_BUFFER_BIT | r.DEPTH_BUFFER_BIT),
                r.framebufferTexture2D(r.FRAMEBUFFER, r.COLOR_ATTACHMENT0, r.TEXTURE_2D, this.q, 0),
                r.clear(r.COLOR_BUFFER_BIT | r.DEPTH_BUFFER_BIT),
                r.framebufferTexture2D(r.FRAMEBUFFER, r.COLOR_ATTACHMENT0, r.TEXTURE_2D, this.l, 0),
                r.clear(r.COLOR_BUFFER_BIT | r.DEPTH_BUFFER_BIT),
                r.useProgram(this.k.h.program),
                r.bindBuffer(r.ARRAY_BUFFER, this.k.f),
                r.bindBuffer(r.ELEMENT_ARRAY_BUFFER, this.k.g),
                this.k.c.disableAll(),
                this.k.c.enable(r, [this.k.e]),
                r.viewport(0, 0, this.c, this.r);
        }
        n(t, e, i, s, r, n, a, o) {
            const h = this.d,
                l = this.k;
            (l.k.x = e),
                (l.k.y = i),
                (l.k.width = s),
                (l.k.height = r),
                (l.k.diffuseTexWidth = t.b.f),
                (l.k.diffuseTexHeight = t.b.b),
                (null == t.e && null == t.c) || (this.o = true);
            let u = 0;
            1 == n
                ? (u = 1)
                : 4 == n
                  ? (u = 2)
                  : 6 == n
                    ? (u = 3)
                    : 7 == n
                      ? (u = 4)
                      : 9 == n
                        ? (u = 5)
                        : 15 == n
                          ? (u = 6)
                          : 16 == n && (u = 7),
                (l.k.uBlendMode = u),
                (l.k.screenResolution = new Float32Array([this.c, this.r])),
                (l.k.uDiffuseTexture = null != t.b ? t.b.a : l.a),
                (l.k.uSpecularTexture = null != t.e ? t.e.a : l.a),
                (l.k.uEmissiveTexture = null != t.c ? t.c.a : l.j),
                (l.k.renderResultTexture = null != this.m ? this.m : l.a),
                (l.k.layer = a),
                (l.k.emissiveAlphaOverride = o),
                h.disable(h.CULL_FACE),
                h.disable(h.DEPTH_TEST),
                h.disable(h.BLEND),
                this.f(l.d, l.k, this.t),
                this.f(l.h, l.k, this.q),
                this.f(l.i, l.k, this.l),
                this.b++,
                h.useProgram(null);
        }
        p() {
            let t = this.d;
            t.bindFramebuffer(t.FRAMEBUFFER, null),
                t.bindTexture(t.TEXTURE_2D, null),
                t.enable(t.CULL_FACE),
                t.enable(t.DEPTH_TEST);
        }
        f(t, e, i) {
            let s = this.d;
            s.useProgram(t.program),
                s.framebufferTexture2D(s.FRAMEBUFFER, s.COLOR_ATTACHMENT0, s.TEXTURE_2D, i, 0),
                s.bindTexture(s.TEXTURE_2D, this.m),
                s.copyTexImage2D(s.TEXTURE_2D, 0, s.RGBA, 0, 0, this.c, this.r, 0),
                s.bindTexture(s.TEXTURE_2D, null),
                setUniforms(t, e),
                s.drawElements(s.TRIANGLES, 6, s.UNSIGNED_SHORT, 0),
                setUniforms(t, Gs);
        }
        u(t) {
            if (0 == t) return this.t;
            if (1 == t) return this.q;
            if (2 == t) return this.l;
            throw new Error("unknown usage " + t);
        }
        e(t) {
            let e = null;
            return (e = { a: t, f: this.c, b: this.r, e: true }), e;
        }
        a() {
            let t = this.d;
            this.m && t.deleteTexture(this.m),
                this.t && t.deleteTexture(this.t),
                this.q && t.deleteTexture(this.q),
                this.l && t.deleteTexture(this.l),
                (this.t = null),
                (this.q = null),
                (this.l = null),
                (this.m = null),
                (this.k = null),
                (this.d = null);
        }
        i(t) {
            switch (t) {
                case 0:
                    return this.e(this.t);
                case 1:
                    return this.e(this.q);
                case 2:
                    return this.e(this.l);
                default:
                    return null;
            }
        }
        g() {}
        s(t) {}
    }
    function js() {
        var t = new GLMAT_ARRAY_TYPE(2);
        return GLMAT_ARRAY_TYPE != Float32Array && ((t[0] = 0), (t[1] = 0)), t;
    }
    function qs(t, e) {
        var i = new GLMAT_ARRAY_TYPE(2);
        return (i[0] = t), (i[1] = e), i;
    }
    function Vs(t, e, i) {
        return (t[0] = e), (t[1] = i), t;
    }
    function Ws(t, e, i) {
        return (t[0] = e[0] + i[0]), (t[1] = e[1] + i[1]), t;
    }
    function Xs(t, e, i) {
        return (t[0] = e[0] * i[0]), (t[1] = e[1] * i[1]), t;
    }
    function Ys(t, e, i) {
        return (t[0] = e[0] * i), (t[1] = e[1] * i), t;
    }
    !(function () {
        var t = js();
    })();
    function Zs() {
        var t = new GLMAT_ARRAY_TYPE(9);
        return (
            GLMAT_ARRAY_TYPE != Float32Array && ((t[1] = 0), (t[2] = 0), (t[3] = 0), (t[5] = 0), (t[6] = 0), (t[7] = 0)),
            (t[0] = 1),
            (t[4] = 1),
            (t[8] = 1),
            t
        );
    }
    function Ks(t, e) {
        return (
            (t[0] = e[0]),
            (t[1] = e[1]),
            (t[2] = e[2]),
            (t[3] = e[4]),
            (t[4] = e[5]),
            (t[5] = e[6]),
            (t[6] = e[8]),
            (t[7] = e[9]),
            (t[8] = e[10]),
            t
        );
    }
    function $s(t, e, i) {
        var s = e[0],
            r = e[1],
            n = e[2],
            a = e[3],
            o = e[4],
            h = e[5],
            l = e[6],
            u = e[7],
            c = e[8],
            d = i[0],
            f = i[1],
            g = i[2],
            _ = i[3],
            b = i[4],
            m = i[5],
            p = i[6],
            x = i[7],
            v = i[8];
        return (
            (t[0] = d * s + f * a + g * l),
            (t[1] = d * r + f * o + g * u),
            (t[2] = d * n + f * h + g * c),
            (t[3] = _ * s + b * a + m * l),
            (t[4] = _ * r + b * o + m * u),
            (t[5] = _ * n + b * h + m * c),
            (t[6] = p * s + x * a + v * l),
            (t[7] = p * r + x * o + v * u),
            (t[8] = p * n + x * h + v * c),
            t
        );
    }
    function Js() {
        var t = new GLMAT_ARRAY_TYPE(4);
        return GLMAT_ARRAY_TYPE != Float32Array && ((t[0] = 0), (t[1] = 0), (t[2] = 0)), (t[3] = 1), t;
    }
    function Qs(t, e, i) {
        i *= 0.5;
        var s = Math.sin(i);
        return (t[0] = s * e[0]), (t[1] = s * e[1]), (t[2] = s * e[2]), (t[3] = Math.cos(i)), t;
    }
    function tr(t, e, i, s) {
        var r,
            n,
            a,
            o,
            h,
            l = e[0],
            u = e[1],
            c = e[2],
            d = e[3],
            f = i[0],
            g = i[1],
            _ = i[2],
            b = i[3];
        return (
            (n = l * f + u * g + c * _ + d * b) < 0 && ((n = -n), (f = -f), (g = -g), (_ = -_), (b = -b)),
            1 - n > GLMAT_EPSILON
                ? ((r = Math.acos(n)), (a = Math.sin(r)), (o = Math.sin((1 - s) * r) / a), (h = Math.sin(s * r) / a))
                : ((o = 1 - s), (h = s)),
            (t[0] = o * l + h * f),
            (t[1] = o * u + h * g),
            (t[2] = o * c + h * _),
            (t[3] = o * d + h * b),
            t
        );
    }
    var er,
        ir,
        sr,
        rr,
        nr,
        ar,
        or = Xi,
        hr = Yi,
        lr = Qi;
    (er = vec3Create()), (ir = vec3FromValues(1, 0, 0)), (sr = vec3FromValues(0, 1, 0)), (rr = Js()), (nr = Js()), (ar = Zs());
    class ur {
        constructor() {
            (this.e = -1), (this.d = null), (this.a = 0), (this.c = -1), (this.b = false);
        }
    }
    class cr {
        constructor() {
            (this.a = new ur()), (this.d = new ur()), (this.c = new ur()), (this.f = 0), (this.e = 1), (this.b = false);
        }
    }
    class dr {
        k() {
            var t = this;
            if (t.e) for (var e = 0; e < t.e.length; ++e) t.e[e] = null;
            return (t.j = null), (t.e = null), null;
        }
        g(t, e, i, s) {
            let r = this;
            if (
                (null == s && (s = this.h()),
                this.d >= 0 && (t = this.d < e.length ? e[this.d] : e[0]),
                0 != r.b || r.e.length > 1)
            ) {
                if (r.j.length > 1) {
                    for (var n = -1, a = r.j.length - 1, o = 0; o < a; ++o)
                        if (t >= r.j[o] && t <= r.j[o + 1]) {
                            n = o;
                            break;
                        }
                    if (-1 == n) return (s = r.i(s, r.e[r.e.length - 1]));
                    if (1 == r.b) {
                        var h = r.j[n],
                            l = r.j[n + 1],
                            u = 0;
                        return (
                            t > l ? (u = 1) : h != l && (u = (t - h) / (l - h)),
                            (u = Math.max(0, Math.min(1, u))),
                            r.a(r.e[n], r.e[n + 1], u, s)
                        );
                    }
                    return (s = r.i(s, r.e[n]));
                }
                return r.e.length > 0 ? (s = r.i(s, r.e[0])) : i;
            }
            return 0 == r.e.length ? s : (s = r.i(s, r.e[0]));
        }
        l(t) {
            var e,
                i = this;
            (i.b = t.getInt16()), (i.d = t.getInt16()), (i.c = t.getBool());
            var s = t.getInt32();
            for (i.j = new Array(s), e = 0; e < s; ++e) i.j[e] = t.getInt32();
            var r = t.getInt32();
            for (i.e = new Array(r), e = 0; e < r; ++e) i.e[e] = i.f(t);
        }
    }
    class fr extends dr {
        constructor(t) {
            super();
            (this.ba = vec3Create()), this.l(t);
        }
        h() {
            return vec3Create();
        }
        a(t, e, i, s) {
            return vec3Lerp(s, t, e, i);
        }
        i(t, e) {
            return vec3Copy(t, e), t;
        }
        f(t) {
            return vec3Set(vec3Create(), t.getFloat(), t.getFloat(), t.getFloat());
        }
    }
    class gr extends dr {
        constructor(t) {
            super();
            this.l(t), (this.ba = Js());
        }
        h() {
            return Js();
        }
        a(t, e, i, s) {
            return tr(s, t, e, i);
        }
        i(t, e) {
            return or(t, e), t;
        }
        f(t) {
            let e = t.getFloat(),
                i = t.getFloat(),
                s = t.getFloat(),
                r = t.getFloat();
            const n = hr(Js(), -e, -i, -s, r);
            return lr(n, n), n;
        }
    }
    class _r extends dr {
        constructor(t) {
            super();
            this.l(t);
        }
        f(t) {
            return t.getUint16();
        }
        h() {
            return 0;
        }
        a(t, e, i, s) {
            return t + (e - t) * i;
        }
        i(t, e) {
            return e;
        }
    }
    class br extends _r {
        f(t) {
            return t.getFloat();
        }
    }
    class mr extends _r {
        f(t) {
            return t.getUint8();
        }
    }
    class pr {
        e() {
            for (var t = this, e = 0; e < t.j.length; ++e) t.j[e] = null;
            return (t.g = null), (t.j = null), (t.c = null), null;
        }
        b(t, e, i, s) {
            let r = this;
            i || (i = this.d());
            let n = s || r.j;
            if (r.j.length > 1 && r.g.length > 1) {
                for (var a = -1, o = r.g.length, h = 0; h < o - 1; ++h)
                    if (t >= r.g[h] && t <= r.g[h + 1]) {
                        a = h;
                        break;
                    }
                -1 == a && (a = r.g.length - 1);
                var l = r.g[a],
                    u = r.g[a + 1],
                    c = 0;
                return l != u && (c = (t - l) / (u - l)), r.h(n[a], n[a + 1], c, i);
            }
            return n.length > 0 ? (i = r.a(i, n[0])) : e;
        }
        i(t) {
            var e,
                i = this,
                s = t.getInt32();
            for (i.g = new Array(s), e = 0; e < s; ++e) i.g[e] = t.getInt16() / 32767;
            var r = t.getInt32();
            for (i.j = new Array(r), e = 0; e < r; ++e) i.j[e] = i.f(t);
        }
    }
    class xr extends pr {
        constructor(t) {
            super();
            (this.ba = js()), this.i(t);
        }
        d() {
            return js();
        }
        h(t, e, i, s) {
            return (
                (r = s),
                (a = e),
                (o = i),
                (h = (n = t)[0]),
                (l = n[1]),
                (r[0] = h + o * (a[0] - h)),
                (r[1] = l + o * (a[1] - l)),
                r
            );
            var r, n, a, o, h, l;
        }
        a(t, e) {
            var i, s;
            return (s = e), ((i = t)[0] = s[0]), (i[1] = s[1]), t;
        }
        f(t) {
            return Vs(js(), t.getFloat(), t.getFloat());
        }
    }
    class vr extends pr {
        constructor(t) {
            super();
            this.i(t);
        }
        d() {
            return vec3Create();
        }
        h(t, e, i, s) {
            return vec3Lerp(s, t, e, i);
        }
        a(t, e) {
            return vec3Copy(t, e), t;
        }
        f(t) {
            return vec3Set(vec3Create(), t.getFloat(), t.getFloat(), t.getFloat());
        }
    }
    class Tr extends pr {
        constructor(t) {
            super();
            this.i(t);
        }
        d() {
            return 0;
        }
        h(t, e, i, s) {
            return t + (e - t) * i;
        }
        a(t, e) {
            return t;
        }
        f(t) {
            return t.getUint16();
        }
    }
    class wr {
        constructor(t, e) {
            this.d(t, e);
        }
        d(t, e) {
            var i = t.getInt32();
            this.a = new Array(i);
            for (let s = 0; s < i; ++s) this.a[s] = new e(t);
        }
        e(t) {
            return !(!this.a || 0 == this.a.length) && (t >= this.a.length && (t = 0), this.a[t].c);
        }
        c(t, e, i, s) {
            if (!this.a || 0 == this.a.length) return i;
            let r = t.d.e;
            r >= this.a.length && (r = 0);
            let n = this.a[r].g(t.d.a, e, i, s);
            if (t.f > 0 && t.f < 1) {
                let s = this.a[0].h(),
                    r = t.c.e;
                r >= this.a.length && (r = 0);
                let a = this.a[r].g(t.c.a, e, i, s);
                a || (a = s), (s = this.a[0].h()), (n = this.a[0].a(a, n, t.f, s));
            }
            if (t.e > 0 && t.e <= 1 && t.a.e != t.d.e && -1 != t.a.e) {
                let s = this.a[0].h(),
                    r = t.a.e;
                r >= this.a.length && (r = 0);
                let a = this.a[r].g(t.a.a, e, i, s);
                a || (a = s), (s = this.a[0].h()), (n = this.a[0].a(a, n, 1 - t.e, s));
            }
            return n;
        }
        b() {
            if (this.a && 0 != this.a.length) {
                for (var t = 0; t < this.a.length; ++t) this.a[t].k(), (this.a[t] = null);
                return null;
            }
        }
    }
    function yr(t, e) {
        return Wi(t[4 * e + 0], t[4 * e + 1], t[4 * e + 2], 0);
    }
    function Ar(t, e, i) {
        for (let s = 0; s < 4; s++) t[4 * e + s] = i[s];
    }
    const Er = class {
        constructor(t, e, i) {
            (this.f = t),
                (this.c = i),
                (this.A = null),
                (this.k = null),
                (this.p = null),
                (this.d = mat4Create()),
                (this.n = mat4Create()),
                (this.q = mat4Create());
            const s = this;
            (s.e = e),
                (s.j = vec3Create()),
                (s.i = mat4Create()),
                (s.a = mat4Create()),
                (s.g = mat4Create()),
                (s.b = vec3Create()),
                (s.y = Js()),
                (s.h = mat4Create()),
                (s.u = false),
                (s.x = false),
                (s.o = false);
        }
        w() {
            var t = this;
            (t.j = null), (t.i = null), (t.b = null), (t.y = null), (t.h = null);
        }
        m() {
            this.u = true;
            for (var t = 0; t < 16; ++t) this.i[t] = 0;
        }
        v(t) {
            t ? (null == this.A && (this.A = new cr()), this.f.G(t, this.A)) : (this.A = null);
            let e = this.f.D[this.e];
            for (let i = 0; i < e.length; i++) this.f.aw[e[i]].v(t);
        }
        z(t) {
            t ? (null == this.k && (this.k = new cr()), this.f.G(t, this.k)) : (this.k = null);
            let e = this.f.D[this.e];
            for (let i = 0; i < e.length; i++) this.f.aw[e[i]].z(t);
        }
        r(t) {
            const e = this.c;
            var i = this;
            if (i.u) return void i.m();
            if ((null != this.A && this.f.I(this.A, t), i.x || i.o)) return;
            if (((i.x = true), !i.f)) return;
            mat4Identity(i.i);
            var s = i.f.m;
            if (!s) return;
            let r = this.n;
            if ((mat4Identity(r), mat4Mult(r, r, this.f.renderer.viewMatrix), mat4Mult(r, r, this.f.am), mat4Mult(i.i, i.i, r), e.i > -1)) {
                i.f.aw[e.i].r(t);
                let s = this.q;
                if ((mat4Copy(s, i.f.aw[e.i].i), mat4Mult(s, r, s), 1 & e.e || 2 & e.e || 4 & e.e)) {
                    if (4 & e.e && 2 & e.e) Ar(s, 0, yr(r, 0)), Ar(s, 1, yr(r, 1)), Ar(s, 2, yr(r, 2));
                    else if (4 & e.e) {
                        {
                            let t = yr(r, 0),
                                e = Ji(t);
                            $i(t, t, Ji(yr(s, 0)) / e), Ar(s, 0, t);
                        }
                        {
                            let t = yr(r, 1),
                                e = Ji(t);
                            $i(t, t, Ji(yr(s, 1)) / e), Ar(s, 1, t);
                        }
                        {
                            let t = yr(r, 2),
                                e = Ji(t);
                            $i(t, t, Ji(yr(s, 2)) / e), Ar(s, 2, t);
                        }
                    } else if (2 & e.e) {
                        {
                            let t = yr(r, 0);
                            $i(t, t, 1 / Ji(yr(s, 0))), $i(t, t, Ji(yr(r, 0))), Ar(s, 0, t);
                        }
                        {
                            let t = yr(r, 1);
                            $i(t, t, 1 / Ji(yr(s, 1))), $i(t, t, Ji(yr(r, 1))), Ar(s, 1, t);
                        }
                        {
                            let t = yr(r, 2);
                            $i(t, t, 1 / Ji(yr(s, 2))), $i(t, t, Ji(yr(r, 2))), Ar(s, 2, t);
                        }
                    }
                    if (1 & e.e) Ar(s, 3, yr(r, 3));
                    else {
                        let t = Wi(e.c[0], e.c[1], e.c[2], 1),
                            n = Vi();
                        Xi(n, t), (n[3] = 0);
                        let a = Vi(),
                            o = Vi();
                        ts(a, t, i.f.aw[i.c.i].i), ts(a, a, r), ts(o, n, s), Ki(a, a, o), (a[3] = 1), Ar(s, 3, a);
                    }
                }
                let n = this.d;
                mat4Invert(n, r), mat4Mult(s, n, s), mat4Mult(i.i, i.i, s);
            }
            let n = null;
            if (null != this.A) {
                let t = this.t(this.A);
                this.f.R || (this.s = t), this.f.h || (n = this.f.R ? this.s : t);
            } else {
                let t = this.t(s);
                this.f.R || (this.s = t), this.f.h || (n = this.f.R ? this.s : t);
            }
            let a = null;
            if (null != this.k) {
                let t = this.t(this.k);
                this.f.R || (this.l = t), (a = this.f.R ? this.l : t);
            }
            let o = null != n || null != a,
                h = mat4Create();
            o && (null != n && mat4Mult(h, h, n), null != a && mat4Mult(h, h, a)),
                null != this.p && (mat4Translate(h, h, this.c.c), mat4Mult(h, h, this.p), mat4Translate(h, h, vec3Negate(this.b, this.c.c))),
                mat4Mult(i.i, i.i, h);
            let l = 120 & e.e;
            if (l) {
                let t = mat4Create();
                mat4Copy(t, i.i);
                let e = i.i,
                    s = vec3Create();
                mat4ToVec3_UNK(s, i.i);
                let r = Vi();
                if (16 == l) {
                    let t = yr(i.i, 0);
                    $i(t, t, 1 / vec3Len(t)), Ar(i.i, 0, t);
                    let s = Wi(e[4], -e[0], 0, 0);
                    Ar(e, 1, Qi(s, s)), vec3Cross(r, s, t), (r[3] = 0), Ar(e, 2, r);
                } else if (l > 16) {
                    if (32 == l) {
                        let t = yr(e, 1);
                        $i(t, t, 1 / Ji(t)), Ar(i.i, 1, t);
                        let s = Wi(-e[5], e[1], 0, 0);
                        Ar(e, 0, Qi(s, s)), (r[3] = 0), Ar(e, 2, r);
                    } else if (64 == l) {
                        let t = yr(e, 2);
                        Qi(t, t), Ar(e, 2, t);
                        let i = Wi(t[1], -t[0], 0, 0);
                        Qi(i, i), Ar(e, 1, i), vec3Cross(r, t, i), (r[3] = 0), Ar(e, 0, r);
                    }
                } else if (8 == l) {
                    let t = this.f.isMirrored;
                    if (o) {
                        let i = yr(h, 0);
                        (i = Wi(i[1], i[2], -i[0], 0)), Qi(i, i), Ar(e, 0, i);
                        let s = yr(h, 1);
                        (s = Wi(t ? -s[1] : s[1], t ? -s[2] : s[2], t ? s[0] : -s[0], 0)), Qi(s, s), Ar(e, 1, s);
                        let r = yr(h, 2);
                        (r = Wi(r[1], r[2], -r[0], 0)), Qi(r, r), Ar(e, 2, r);
                    } else {
                        Ar(e, 0, Wi(0, 0, -1, 0)), Ar(e, 1, Wi(t ? -1 : 1, 0, 0, 0)), Ar(e, 2, Wi(0, 1, 0, 0));
                    }
                }
                let n = Wi(this.c.c[0], this.c.c[1], this.c.c[2], 1),
                    a = Wi(this.c.c[0], this.c.c[1], this.c.c[2], 0),
                    u = yr(e, 0),
                    c = yr(e, 1),
                    d = yr(e, 2);
                $i(u, u, s[0]),
                    $i(c, c, s[1]),
                    $i(d, d, s[2]),
                    Ar(e, 0, u),
                    Ar(e, 1, c),
                    Ar(e, 2, d),
                    ts(n, n, t),
                    ts(a, a, e);
                let f = Vi();
                Ki(f, n, a), (f[3] = 1), Ar(e, 3, f);
            }
            mat4Invert(r, r), mat4Mult(i.i, r, i.i), mat4Invert(i.a, i.i), mat4Transpose(i.g, i.a), vec3TransformMat4(i.j, i.c.c, i.i);
        }
        t(t) {
            const e = this.c;
            if (!!(640 & e.e)) {
                let v = mat4Create();
                return (
                    mat4Identity(v),
                    mat4Translate(v, v, this.c.c),
                    (this.b = e.h.c(t, this.f.F, vec3Create())),
                    mat4Translate(v, v, this.b),
                    (this.y = e.f.c(t, this.f.F, Js())),
                    (i = this.h),
                    (s = this.y),
                    (r = s[0]),
                    (n = s[1]),
                    (a = s[2]),
                    (o = s[3]),
                    (c = r * (h = r + r)),
                    (d = n * h),
                    (f = n * (l = n + n)),
                    (g = a * h),
                    (_ = a * l),
                    (b = a * (u = a + a)),
                    (m = o * h),
                    (p = o * l),
                    (x = o * u),
                    (i[0] = 1 - f - b),
                    (i[1] = d + x),
                    (i[2] = g - p),
                    (i[3] = 0),
                    (i[4] = d - x),
                    (i[5] = 1 - c - b),
                    (i[6] = _ + m),
                    (i[7] = 0),
                    (i[8] = g + p),
                    (i[9] = _ - m),
                    (i[10] = 1 - c - f),
                    (i[11] = 0),
                    (i[12] = 0),
                    (i[13] = 0),
                    (i[14] = 0),
                    (i[15] = 1),
                    mat4Mult(v, v, this.h),
                    (this.b = e.a.c(t, this.f.F, vec3FromValues(1, 1, 1))),
                    mat4Scale(v, v, this.b),
                    mat4Translate(v, v, vec3Negate(this.b, this.c.c)),
                    v
                );
            }
            var i, s, r, n, a, o, h, l, u, c, d, f, g, _, b, m, p, x;
            return null;
        }
    };
    const Cr = class {
        constructor(t) {
            (this.e = t), (this.a = 267320826 ^ t);
            let e = new ArrayBuffer(4);
            this.b = new DataView(e);
        }
        f() {
            let t = this.a;
            return (t ^= t << 13), (t ^= t >> 17), (t ^= t << 5), (this.a = t), t;
        }
        c() {
            let t,
                e = this.f();
            return (
                this.b.setInt32(0, 1065353216 | (8388607 & e)),
                (t = 2147483648 & e ? 2 - this.b.getFloat32(0) : this.b.getFloat32(0) - 2),
                t
            );
        }
        d() {
            let t = this.f();
            return this.b.setInt32(0, 1065353216 | (8388607 & t)), this.b.getFloat32(0) - 1;
        }
    };
    const Mr = class {
        constructor() {
            (this.d = 0),
                (this.a = 0),
                (this.c = 0),
                (this.b = 0),
                (this.h = vec3Create()),
                (this.i = 0),
                (this.g = 0),
                (this.j = 0),
                (this.f = 0),
                (this.e = 0);
        }
    };
    const kr = class {
        constructor(t, e) {
            (this.j = t), (this.b = e), (this.k = new Mr());
        }
        d() {
            return this.k.b + this.j.c() * this.b.B;
        }
        g() {
            return this.k.b + this.b.B;
        }
        e() {
            return this.k.c + this.b.A;
        }
        f(t) {
            return this.k.c + 30518509e-12 * t * this.b.A;
        }
        h() {
            let t = this.k.d;
            return (t *= 1 + this.k.a * this.j.c()), t;
        }
        i() {
            return this.k;
        }
        a(t) {
            vec3Copy(t, this.k.h);
        }
    };
    const Sr = class extends kr {
        c(t, e) {
            let i,
                s = e * this.j.d(),
                r = this.j.c();
            (i = r < 1 ? (r > -1 ? Math.trunc(32767 * r + 0.5) : -32767) : 32767), (t.e = i);
            let n = this.f(i);
            n < 0.001 && (n = 0.001),
                (t.d = (function (t, e) {
                    let i = Math.abs(t),
                        s = Math.abs(e);
                    return Number((i - Math.floor(i / s) * s).toPrecision(8)) * Math.sign(t);
                })(s, n)),
                (t.g = 65535 & this.j.f()),
                vec3Set(t.a, this.j.c() * this.k.g * 0.5, this.j.c() * this.k.j * 0.5, 0);
            let a = this.h(),
                o = this.k.i;
            if (o < 0.001) {
                let e = this.k.f * this.j.c(),
                    i = this.k.e * this.j.c(),
                    s = Math.sin(e),
                    r = Math.sin(i),
                    n = Math.cos(e),
                    o = Math.cos(i);
                vec3Set(t.f, o * s * a, r * s * a, n * a);
            } else {
                let e = vec3Create();
                vec3Copy(e, t.a), (e[2] = e[2] - o), vec3Len(e) > 1e-4 && (vec3Normalize(e, e), vec3Scale(t.f, e, a));
            }
        }
    };
    const Fr = class extends kr {
        constructor(t, e, i) {
            super(t, e), (this.ba = i);
        }
        c(t, e) {
            let i,
                s = e * this.j.d(),
                r = this.j.c();
            (i = r < 1 ? (r > -1 ? Math.trunc(32767 * r + 0.5) : -32767) : 32767), (t.e = i);
            let n = this.f(i);
            n < 0.001 && (n = 0.001),
                (t.d = (function (t, e) {
                    let i = Math.abs(t),
                        s = Math.abs(e);
                    return Number((i - Math.floor(i / s) * s).toPrecision(8)) * Math.sign(t);
                })(s, n)),
                (t.g = 65535 & this.j.f());
            let a = this.k.j - this.k.g,
                o = this.k.g + a * this.j.d(),
                h = this.k.f * this.j.c(),
                l = this.k.e * this.j.c(),
                u = Math.cos(h),
                c = vec3FromValues(u * Math.cos(l), u * Math.sin(l), Math.sin(h));
            vec3Scale(t.a, c, o);
            let d = this.h(),
                f = this.k.i,
                g = vec3FromValues(0.5, 0.5, 0.5);
            0 == f
                ? this.ba
                    ? vec3Set(g, 0, 0, 1)
                    : vec3Set(g, u * Math.cos(l), u * Math.sin(l), Math.sin(h))
                : (vec3Set(g, 0, 0, f), vec3Sub(g, t.a, g), vec3Len(g) > 1e-4 && vec3Normalize(g, g)),
                vec3Scale(t.f, g, d);
        }
    };
    const Ir = class {
        constructor() {
            (this.a = vec3Create()),
                (this.d = 0),
                (this.f = vec3Create()),
                (this.e = 0),
                (this.g = (2147483647 * Math.random()) | 0),
                (this.c = [js(), js()]),
                (this.b = [js(), js()]);
        }
    };
    const Dr = class {
        constructor(t, e, i, s, r, n) {
            (this.d = t), (this.f = e), (this.a = i), (this.e = s), (this.b = r), (this.c = n);
        }
    };
    const Rr = class {
        constructor(t, e, i) {
            (this.c = t), (this.b = e), (this.a = i);
        }
    };
    let Ur = new Array(128);
    for (let t = 0; t < 128; t++) Ur[t] = Math.random();
    const Br = mat4FromValues(0, 1, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1),
        Or = 1e3;
    class Pr {}
    class zr {
        constructor() {
            (this.c = vec3Create()), (this.b = 0), (this.a = { c: js(), f: vec3Create(), b: 0, e: 0, a: 1, d: 0 });
        }
    }
    function Hr(t) {
        return Wi(((t >> 16) & 255) / 255, ((t >> 8) & 255) / 255, (255 & t) / 255, ((t >> 24) & 255) / 255);
    }
    const Nr = [0, 0, 1, 2, 3, 4];
    const Gr = class {
        constructor(t, e) {
            (this.aa = t),
                (this.M = e),
                (this.ao = null),
                (this.G = 0),
                (this.O = true),
                (this.C = null),
                (this.l = new Date().getTime()),
                (this.H = e),
                (this.ar = mat4Create()),
                (this.aq = mat4Create()),
                (this.U = mat4Create()),
                (this.al = mat4Create()),
                (this.w = Vi()),
                (this.au = Zs()),
                (this.u = vec3Create()),
                (this.k = 1),
                (this.i = vec3Create()),
                (this.at = 0),
                (this.E = vec3Create()),
                (this.as = vec3Create()),
                (this.g = []),
                (this.af = vec3Create()),
                (this.a = 0),
                (this.S = 0),
                (this.an = 0),
                (this.ak = 0),
                (this.d = vec3Create()),
                (this.X = vec3Create()),
                (this.c = 0),
                (this.ae = 0),
                (this.z = 0),
                (this.W = 0),
                (this.Q = 0),
                (this.s = 0),
                (this.v = 0),
                (this.A = []),
                (this.ac = []);
            for (let t = 0; t < Or; t++)
                this.ac.push(4 * t + 0),
                    this.ac.push(4 * t + 1),
                    this.ac.push(4 * t + 2),
                    this.ac.push(4 * t + 3),
                    this.ac.push(4 * t + 2),
                    this.ac.push(4 * t + 1);
            switch (((this.f = new Cr((2147483647 * Math.random()) | 0)), this.H.x)) {
                case 1:
                    this.D = new Sr(this.f, e);
                    break;
                case 2:
                    this.D = new Fr(this.f, e, !!(256 & this.H.W));
                    break;
                default:
                    (this.D = null), WH.debug("Found unimplemented generator ", this.H.x);
            }
            const i = this.H.aa - this.H.l;
            0 != i
                ? ((this.S = (this.H.O - this.H.g) / i), (this.an = this.H.g - this.H.l * this.S))
                : ((this.S = 0), (this.an = 0));
            let s = this.H.p;
            s <= 0 && (s = 1);
            let r = this.H.r;
            r <= 0 && (r = 1), (this.ae = s * r - 1), (this.z = 0);
            let n = s,
                a = -1;
            do {
                ++a, (n >>= 1);
            } while (n);
            if (((this.W = a), (this.Q = s - 1), (this.z = 0), (0x8000 & this.H.W) > 0)) {
                let t = (this.ae + 1) * this.f.f();
                this.z = (t / 4294967296) | 0;
            }
            (this.s = 1 / s), (this.v = 1 / r);
            let o = false;
            (269484032 & this.H.W) > 0 ? ((o = !!(1 & (this.H.W >> 28))), (this.a = o ? 2 : 3)) : (this.a = 0);
            let h = false,
                l = false;
            (268435456 & this.H.W) > 0
                ? (l = (1073741824 & this.H.W) > 0)
                : 1048576 & this.H.W || (h = !(1 & this.H.W)),
                2 == this.a || (4 == this.a && o)
                    ? (this.Y = l ? 3 : 2)
                    : 3 == this.a
                      ? (this.Y = 5)
                      : (this.Y = h ? 1 : 0),
                (this.x = e.b > 1),
                (this.m = this.aa.au.n(224e3)),
                (this.K = this.aa.au.d(8e3)),
                (this.N = this.aa.au.i(this.m, this.K));
        }
        R() {
            var t = this;
            (t.H.U = null),
                (t.H.E = null),
                (t.H.m = null),
                (t.H.e = t.H.e.b()),
                (t.H.G = t.H.G.b()),
                (t.H.w = t.H.w.b()),
                (t.H.h = t.H.h.b()),
                (t.H.C = t.H.C.b()),
                (t.H.t = t.H.t.b()),
                (t.H.o = t.H.o.b()),
                (t.H.P = t.H.P.b()),
                (t.H.D = t.H.D.b()),
                (t.H.I = t.H.I.b()),
                (t.H.f = t.H.f.b()),
                (t.H.u = t.H.u.e()),
                (t.H.c = t.H.c.e()),
                (t.H.q = t.H.q.e()),
                (t.H.k = t.H.k.e()),
                (t.H.Q = t.H.Q.e()),
                (t.g = null);
        }
        B(t) {
            const e = this.M;
            e.S >= 11 &&
                e.S <= 13 &&
                t &&
                ((this.aj = [Vi(), Vi(), Vi()]),
                Xi(this.aj[0], Hr(t.Start[e.S - 11])),
                Xi(this.aj[1], Hr(t.Mid[e.S - 11])),
                Xi(this.aj[2], Hr(t.End[e.S - 11])));
        }
        ad(t) {
            this.ao = t;
        }
        ag() {
            if (this.C) return;
            this.aa.renderer.context;
            if (!this.P)
                if (((this.P = [null, null, null]), 268435456 & this.H.W))
                    for (let t = 0; t < this.H.V.length; t++) {
                        const e = this.H.V[t];
                        e > -1 && e < this.aa.X.length && (this.P[t] = this.aa.X[e]);
                    }
                else this.H.X > -1 && this.H.X < this.aa.X.length && (this.P[0] = this.aa.X[this.H.X]);
            let t = true;
            for (let e of this.P) t = t && (!e || (e.c && e.c.e));
            if (!t) return;
            const e = this.aa.au;
            let i = this.H.b;
            4 == i && (i = 3);
            let s = {};
            (s.uViewMatrix = this.aa.renderer.viewMatrix),
                (s.uProjMatrix = this.aa.renderer.projMatrix),
                (s.uBlendMode = this.H.b),
                (s.uPixelShader = Nr[this.Y]),
                (s.colorMult = this.ao ? this.ao.c : 1),
                (s.alphaMult = this.ao ? this.ao.b : 1);
            let r = [
                this.P[0] && this.P[0].c && this.P[0].c.e,
                this.P[1] && this.P[1].c && this.P[1].c.e,
                this.P[2] && this.P[2].c && this.P[2].c.e,
            ];
            (s.uTexture = this.P[0].c.a),
                (s.uTexture2 = r[1] ? this.P[1].c.a : null),
                (s.uTexture3 = r[2] ? this.P[2].c.a : null),
                (s.uHasTexture = r[0] ? 1 : 0),
                (s.uHasTexture2 = r[1] ? 1 : 0),
                (s.uHasTexture3 = r[2] ? 1 : 0);
            let n = -1;
            1 == i ? (n = 0.501960814) : i > 1 && (n = 1 / 255), (s.uAlphaTreshold = n);
            const a = e.f(
                this.aa.aM,
                new Dr(false, !this.aa.Z, i, true, false, 15),
                new Di(
                    this.P.map((t) => t && t.c),
                    s
                )
            );
            this.C = e.l(new Rr(this.N, 0, 0), a, 0, this.H.n);
        }
        r(t, e) {
            if (!this.D) return;
            let i = mat4Create(),
                s = this.D.i(),
                r = true;
            this.H.f.e(t.d.e) && (r = this.H.f.c(t, this.aa.F) > 0), (this.Z = r);
            const n = vec3FromValues(0, 0, 0);
            r &&
                ((s.d = this.H.e.c(t, this.aa.F, 0)),
                (s.a = this.H.G.c(t, this.aa.F, 0)),
                (s.f = this.H.w.c(t, this.aa.F, 0)),
                (s.e = this.H.h.c(t, this.aa.F, 0)),
                this.H.C.c(t, this.aa.F, n, s.h),
                (s.c = this.H.t.c(t, this.aa.F, 0)),
                (s.b = this.H.o.c(t, this.aa.F, 0)),
                (s.j = this.H.D.c(t, this.aa.F, 0)),
                (s.g = this.H.P.c(t, this.aa.F, 0)),
                this.ao ? (s.i = this.ao.d) : (s.i = this.H.I.c(t, this.aa.F, 0))),
                mat4Mult(i, i, this.aa.am),
                mat4Mult(i, i, this.aa.aw[this.H.K].i);
            let a = mat4Create();
            var o, h;
            (o = a),
                (h = vec3FromValues(this.H.U[0], this.H.U[1], this.H.U[2])),
                (o[0] = 1),
                (o[1] = 0),
                (o[2] = 0),
                (o[3] = 0),
                (o[4] = 0),
                (o[5] = 1),
                (o[6] = 0),
                (o[7] = 0),
                (o[8] = 0),
                (o[9] = 0),
                (o[10] = 1),
                (o[11] = 0),
                (o[12] = h[0]),
                (o[13] = h[1]),
                (o[14] = h[2]),
                (o[15] = 1),
                mat4Mult(i, i, a),
                mat4Mult(i, i, Br);
            let l = mat4Create(),
                u = vec3Create();
            mat4Invert(l, this.aa.renderer.viewMatrix),
                mat4GetTranslation(u, l),
                this.F(e, i, u, null, this.aa.renderer.viewMatrix),
                this.L(this.aa.renderer.viewMatrix),
                this.m.d(new Float32Array(this.A)),
                this.K.d(new Uint16Array(this.ac)),
                this.C && ((this.C.f = (6 * this.G) | 0), (this.C.a = 0));
        }
        y(t) {
            if (this.g.length <= 0) return;
            if ((this.C || this.ag(), !this.C)) return;
            if (!t && this.C.d.b() > vs.GxBlend_AlphaKey) return;
            this.aa.au.a().d(this.C);
        }
        b(t, e) {
            if (!(16 & this.H.W))
                for (let i = 0; i < this.g.length; i++) {
                    const s = this.g[i];
                    vec3TransformMat4(s.a, s.a, t), vec3TransformMat3(s.f, s.f, e);
                }
        }
        F(t, e, i, s, r) {
            if (null == this.D) return;
            if (this.aa.R) return;
            mat4GetTranslation(this.i, this.ar);
            let n = Vi();
            mat4GetTranslation(n, e), (n[3] = 1), ts(n, n, r), (this.at = n[2]);
            let a = vec3Create();
            if ((mat4GetTranslation(a, r), this.o(e, a, s), t > 0)) {
                let e = vec3Create();
                if ((mat4GetTranslation(e, this.ar), 0x4000 & this.H.W)) {
                    vec3Sub(this.as, e, this.i);
                    let i = this.S * (vec3Len(this.as) / t) + this.an;
                    i >= 0 && (i = Math.min(i, 1)), vec3Scale(this.E, this.as, i);
                }
                if (64 & this.H.W) {
                    this.ak += t;
                    let i = 0.03;
                    if (this.ak > i)
                        if (((this.ak = 0), 0 == this.g.length)) {
                            let t = i / this.ak,
                                s = vec3Create();
                            vec3Sub(s, e, this.i);
                            let r = t * this.H.T;
                            vec3Mult(this.d, s, vec3FromValues(r, r, r));
                        } else vec3Set(this.d, 0, 0, 0);
                }
                this.T(t);
            }
        }
        o(t, e, i) {
            if ((vec3Copy(this.X, e), null == i || 16 & this.H.W)) mat4Copy(this.ar, t);
            else {
                let e = mat4Create();
                mat4Invert(e, i), mat4Mult(this.ar, e, t);
            }
            let s = vec3Create();
            mat4ToVec3_UNK(s, t), (this.k = s[0]);
        }
        T(t) {
            if ((t = Math.max(t, 0)) < 0.1) vec3Copy(this.E, this.as);
            else {
                let e = Math.floor(t / 0.1);
                t = -0.1 * e + t;
                let i = Math.min(Math.floor(this.D.i().c / 0.1), e),
                    s = i + 1,
                    r = 1;
                (r = s < 0 ? ((1 & s) | (s >> 1)) + ((1 & s) | (s >> 1)) : s), vec3Scale(this.E, this.as, 1 / r);
                for (let t = 0; t < i; t++) this.p(0.1);
            }
            this.p(t);
        }
        p(t) {
            let e = new Pr();
            if (t < 0) return;
            this.H.W, this.ab(e, t), this.J(t);
            let i = 0;
            for (; i < this.g.length; ) {
                let s = this.g[i];
                (s.d = s.d + t),
                    s.d > Math.max(this.D.f(s.g), 0.001) ? (this.ah(i), i--) : this.j(s, t, e) || (this.ah(i), i--),
                    i++;
            }
        }
        ab(t, e) {
            (t.a = vec3Create()), (t.d = vec3Create()), (t.c = vec3Create()), (t.b = 0);
            let i = vec3FromValues(e, e, e),
                s = e * e * 0.5,
                r = vec3FromValues(s, s, s);
            vec3Mult(t.a, this.H.H, i);
            let n = vec3Create();
            this.D.a(n), vec3Mult(t.d, n, i), vec3Mult(t.c, n, r), (t.b = this.H.a * e);
        }
        J(t) {
            if (!this.Z) return;
            if (!this.O) return;
            let e = this.D.d();
            for (this.c = this.c + t * e; this.c > 1; ) this.am(t), (this.c -= 1);
        }
        am(t) {
            let e = this.I();
            if ((this.D.c(e, t), !(16 & this.H.W))) {
                let t = Wi(e.a[0], e.a[1], e.a[2], 1),
                    i = Wi(e.f[0], e.f[1], e.f[2], 0);
                ts(t, t, this.ar), ts(i, i, this.ar), vec3Copy(e.a, t), vec3Copy(e.f, i), 8192 & this.H.W && (e.a[2] = 0);
            }
            if (64 & this.H.W) {
                let t = 1 + this.D.i().a * this.f.c(),
                    i = vec3Create();
                vec3Scale(i, this.d, t), vec3Add(e.f, e.f, i);
            }
            if (this.a >= 2)
                for (let t = 0; t < 2; t++) {
                    (e.c[t][0] = this.f.d()), (e.c[t][1] = this.f.d());
                    let i = js();
                    Ys(i, this.H.v[t], this.f.c()), Ws(e.b[t], i, this.H.Z[t]);
                }
        }
        I() {
            let t = new Ir();
            return this.g.push(t), t;
        }
        ah(t) {
            this.g.splice(t, 1);
        }
        j(t, e, i) {
            if (this.a >= 2)
                for (let i = 0; i < 2; i++) {
                    let s = t.c[i][0] + e * t.b[i][0];
                    (t.c[i][0] = s - Math.floor(s)), (s = t.c[i][1] + e * t.b[i][1]), (t.c[i][1] = s - Math.floor(s));
                }
            vec3Add(t.f, t.f, i.a), 0x4000 & this.H.W && 2 * e < t.d && vec3Add(t.a, t.a, this.E);
            let s = vec3FromValues(e, e, e),
                r = vec3Create();
            if (
                (vec3Mult(r, t.f, s),
                vec3Add(t.f, t.f, i.d),
                vec3Scale(t.f, t.f, 1 - i.b),
                vec3Add(t.a, t.a, r),
                vec3Add(t.a, t.a, i.c),
                2 == this.H.x && 128 & this.H.W)
            ) {
                let e = vec3Create();
                if ((vec3Copy(e, t.a), 16 & this.H.W)) {
                    if (vec3Dot(e, r) > 0) return false;
                } else {
                    let i = vec3Create();
                    if ((mat4GetTranslation(i, this.ar), vec3Sub(e, t.a, i), vec3Dot(e, r) > 0)) return false;
                }
            }
            return true;
        }
        L(t) {
            if (((this.A.length = 0), 0 == this.g.length && null != this.D)) return;
            mat4Invert(this.U, t), Ks(Zs(), t), this.q(null, t);
            let e = 0;
            for (let t = 0; t < this.g.length; t++) {
                let i = this.g[t],
                    s = new zr();
                if (
                    (this.ai(i, s) &&
                        (131072 & this.H.W && (this.e(i, s), e++), 262144 & this.H.W && (this.h(i, s), e++)),
                    e >= Or)
                )
                    break;
            }
            this.G = e;
        }
        q(t, e) {
            var i, s, r;
            16 & this.H.W ? mat4Mult(this.al, e, this.ar) : null != t ? mat4Mult(this.al, e, t) : mat4Copy(this.al, e),
                mat4GetTranslation(this.w, e),
                4096 & this.H.W &&
                    (Ks(this.au, this.al),
                    16 & this.H.W &&
                        Math.abs(this.k) > 0 &&
                        ((i = this.au),
                        (s = this.au),
                        (r = 1 / this.k),
                        (i[0] = s[0] * r),
                        (i[1] = s[1] * r),
                        (i[2] = s[2] * r),
                        (i[3] = s[3] * r),
                        (i[4] = s[4] * r),
                        (i[5] = s[5] * r),
                        (i[6] = s[6] * r),
                        (i[7] = s[7] * r),
                        (i[8] = s[8] * r)),
                    vec3Set(this.u, this.au[6], this.au[7], this.au[8]),
                    vec3SquareLen(this.u) <= 2.3841858e-7 ? vec3Set(this.u, 0, 0, 1) : vec3Normalize(this.u, this.u));
        }
        V(t) {
            let e = 0,
                i = 0;
            if (0 != this.H.s || 0 != this.H.j) {
                let s = new Cr(t.g);
                (e = 0 == this.H.Y ? this.H.s : this.H.s + s.c() * this.H.Y),
                    (i = 0 == this.H.j ? this.H.N : this.H.N + s.c() * this.H.j);
            } else (e = this.H.s), (i = this.H.N);
            return { deltaSpin: i, baseSpin: e };
        }
        ai(t, e) {
            let i = this.H.J,
                s = this.H.F,
                r = s[0],
                n = s[1] - r,
                a = 0,
                o = t.g,
                h = t.d;
            if (((i < 1 || 0 != n) && (a = 127 & (h * this.H.R + o)), i < Ur[a])) return 0;
            this.t(t, e, o);
            let l = n * Ur[a] + r;
            Ys(e.a.c, e.a.c, l), 32 & this.H.W && Ys(e.a.c, e.a.c, this.k);
            let u = Wi(t.a[0], t.a[1], t.a[2], 1);
            return ts(u, u, this.al), vec3Copy(e.c, u), (e.b = 1), 1;
        }
        t(t, e, i) {
            let s = t.d / this.D.e(),
                r = new Cr(i);
            Math.min(s, 1) <= 0 ? (s = 0) : s >= 1 && (s = 1);
            let n = vec3FromValues(255, 255, 255),
                a = qs(1, 1),
                o = 1,
                h = e.a;
            this.H.u.b(s, n, h.f, this.aj),
                this.aj || vec3Scale(h.f, h.f, 1 / 255),
                this.H.q.b(s, a, h.c),
                (h.a = this.H.c.b(s, 32767) / 32767),
                this.ao ? (h.d = this.ao.a.b(s, 0) / 32767) : (h.d = 0);
            let l = 0;
            this.H.k.g.length > 0
                ? ((o = 0), (h.b = this.H.k.b(s, o)), (h.b = this.ae & (h.b + this.z)))
                : 65536 & this.H.W
                  ? ((l = (this.ae + 1) * r.f()), (h.b = (l / 4294967296) | 0))
                  : (h.b = 0),
                (o = 0),
                (h.e = this.H.Q.b(s, o)),
                (h.e = (h.e + this.z) & this.ae);
            let u = 1;
            524288 & this.H.W
                ? ((u = Math.max(1 + r.c() * this.H.M[1], 99999997e-12)),
                  (h.c[0] = Math.max(1 + r.c() * this.H.M[0], 99999997e-12) * h.c[0]))
                : ((u = Math.max(1 + r.c() * this.H.M[0], 99999997e-12)), (h.c[0] = u * h.c[0])),
                (h.c[1] = u * h.c[1]);
        }
        e(t, e) {
            let i = qs((e.a.b & this.Q) * this.s, (e.a.b >> this.W) * this.v),
                s = 0,
                r = 0,
                n = this.V(t);
            (s = n.baseSpin), (r = n.deltaSpin);
            let a = 0,
                o = vec3FromValues(0, 0, 0),
                h = vec3FromValues(0, 0, 0),
                l = false,
                u = false;
            if (4 & this.H.W && vec3SquareLen(t.f) > 2.3841858e-7)
                if (((a = 1), 4096 & this.H.W)) l = true;
                else {
                    let i = Wi(-t.f[0], -t.f[1], -t.f[2], 0);
                    ts(i, i, this.al);
                    let s = vec3Create();
                    vec3Copy(s, i);
                    let r = 0,
                        n = vec3SquareLen(s);
                    r = n <= 2.3841858e-7 ? 0 : 1 / Math.sqrt(n);
                    let a = vec3Create();
                    vec3Copy(a, s),
                        vec3Scale(a, a, r),
                        vec3Copy(o, a),
                        vec3Scale(o, o, e.a.c[0]),
                        (h = vec3FromValues(a[1], -a[0], 0)),
                        vec3Scale(h, h, e.a.c[1]),
                        (u = true),
                        (l = false);
                }
            if ((4096 & this.H.W || l) && !u) {
                let i = Zs();
                (c = i),
                    (d = this.au),
                    (c[0] = d[0]),
                    (c[1] = d[1]),
                    (c[2] = d[2]),
                    (c[3] = d[3]),
                    (c[4] = d[4]),
                    (c[5] = d[5]),
                    (c[6] = d[6]),
                    (c[7] = d[7]),
                    (c[8] = d[8]);
                let n = e.a.c[0];
                if (a) {
                    let s = 0,
                        r = vec3FromValues(-t.f[0], -t.f[1], -t.f[2]),
                        a = vec3SquareLen(r);
                    (s = a <= 2.3841858e-7 ? 0 : 1 / Math.sqrt(a)),
                        $s(
                            i,
                            this.au,
                            (function (t, e, i, s, r, n, a, o, h) {
                                var l = new GLMAT_ARRAY_TYPE(9);
                                return (
                                    (l[0] = t),
                                    (l[1] = e),
                                    (l[2] = i),
                                    (l[3] = s),
                                    (l[4] = r),
                                    (l[5] = n),
                                    (l[6] = a),
                                    (l[7] = o),
                                    (l[8] = h),
                                    l
                                );
                            })(r[0] * s, r[1] * s, 0, -r[1] * s, r[0] * s, 0, 0, 0, 1)
                        ),
                        s > 2.3841858e-7 && (n = e.a.c[0] * (1 / Math.sqrt(vec3SquareLen(t.f)) / s));
                }
                if (
                    (this.a,
                    vec3Set(o, i[0], i[1], i[2]),
                    vec3Scale(o, o, n),
                    vec3Set(h, i[3], i[4], i[5]),
                    vec3Scale(h, h, e.a.c[1]),
                    (r = h[0]),
                    (u = true),
                    0 != this.H.N || 0 != this.H.j)
                ) {
                    let e = s + r * t.d;
                    512 & this.H.W && 1 & t.g && (e = -e);
                    let i = vec3Create();
                    vec3Copy(i, this.u), this.a;
                    let n = Zs(),
                        a = Js();
                    Qs(a, i, e),
                        (function (t, e) {
                            var i = e[0],
                                s = e[1],
                                r = e[2],
                                n = e[3],
                                a = i + i,
                                o = s + s,
                                h = r + r,
                                l = i * a,
                                u = s * a,
                                c = s * o,
                                d = r * a,
                                f = r * o,
                                g = r * h,
                                _ = n * a,
                                b = n * o,
                                m = n * h;
                            (t[0] = 1 - c - g),
                                (t[3] = u - m),
                                (t[6] = d + b),
                                (t[1] = u + m),
                                (t[4] = 1 - l - g),
                                (t[7] = f - _),
                                (t[2] = d - b),
                                (t[5] = f + _),
                                (t[8] = 1 - l - c);
                        })(n, a),
                        vec3TransformMat3(o, o, n),
                        vec3Set(h, r, h[1], h[2]),
                        vec3TransformMat3(h, h, n);
                }
            }
            var c, d;
            if (!u)
                if (0 != this.H.N || 0 != this.H.j) {
                    let i = s + r * t.d;
                    512 & this.H.W && 1 & t.g && (i = -i);
                    let n = Math.cos(i),
                        a = Math.sin(i);
                    vec3Set(o, n, a, 0),
                        vec3Scale(o, o, e.a.c[0]),
                        vec3Set(h, -a, n, 0),
                        vec3Scale(h, h, e.a.c[1]),
                        134217728 & this.H.W && vec3Add(e.c, e.c, vec3FromValues(h[0], h[1], 0));
                } else vec3Set(o, e.a.c[0], 0, 0), vec3Set(h, 0, e.a.c[1], 0);
            return this.n(o, h, e.c, e.a.f, e.a.a, e.a.d, i[0], i[1], t.c), 0;
        }
        h(t, e) {
            let i = qs((e.a.e & this.Q) * this.s, (e.a.e >> this.W) * this.v),
                s = vec3FromValues(0, 0, 0),
                r = vec3FromValues(0, 0, 0),
                n = this.H.z;
            1024 & this.H.W && (n = Math.min(t.d, n));
            let a = Vi();
            vec3Scale(a, t.f, -1), (a[3] = 0), ts(a, a, this.al), vec3Scale(a, a, n);
            let o = vec3FromValues(a[0], a[1], 0);
            if (vec3Dot(o, o) > 1e-4) {
                let t = 1 / vec3Len(o);
                Ys(e.a.c, e.a.c, t), Xs(o, o, e.a.c), (r = vec3FromValues(-o[1], o[0], 0)), vec3Scale(s, a, 0.5), vec3Add(e.c, e.c, s);
            } else (s = vec3FromValues(0.05 * e.a.c[0], 0, 0)), (r = vec3FromValues(0, 0.05 * e.a.c[1], 0));
            return this.n(s, r, e.c, e.a.f, e.a.a, e.a.d, i[0], i[1], t.c), 1;
        }
        n(t, e, i, s, r, n, a, o, h) {
            const l = [-1, -1, 1, 1],
                u = [1, -1, 1, -1],
                c = [0, 0, 1, 1],
                d = [0, 1, 0, 1];
            let f = vec3Create(),
                g = js(),
                _ = js(),
                b = js();
            for (let m = 0; m < 4; m++)
                vec3Set(f, 0, 0, 0),
                    vec3ScaleAdd(f, f, t, l[m]),
                    vec3ScaleAdd(f, f, e, u[m]),
                    vec3Add(f, f, i),
                    Vs(g, c[m] * this.s + a, d[m] * this.v + o),
                    Vs(_, c[m] * this.H.L[0] + h[0][0], d[m] * this.H.L[0] + h[0][1]),
                    Vs(b, c[m] * this.H.L[1] + h[1][0], d[m] * this.H.L[1] + h[1][1]),
                    this.A.push(f[0]),
                    this.A.push(f[1]),
                    this.A.push(f[2]),
                    this.A.push(s[0]),
                    this.A.push(s[1]),
                    this.A.push(s[2]),
                    this.A.push(r),
                    this.A.push(g[0]),
                    this.A.push(g[1]),
                    this.A.push(_[0]),
                    this.A.push(_[1]),
                    this.A.push(b[0]),
                    this.A.push(b[1]),
                    this.A.push(n);
        }
    };
    class Lr {
        constructor() {
            (this.b = vec3Create()), (this.a = Vi()), (this.c = js());
        }
    }
    class jr {}
    const qr = [0, 1, 2, 10, 3, 4, 5, 13];
    function Vr(t, e) {
        return vec3FromValues(t[4 * e + 0], t[4 * e + 1], t[4 * e + 2]);
    }
    const Wr = class {
        constructor(t, e) {
            (this.e = t),
                (this.af = e),
                (this.av = vec3Create()),
                (this.S = vec3Create()),
                (this.L = new jr()),
                (this.Q = vec3Create()),
                (this.f = vec3Create()),
                (this.ae = vec3Create()),
                (this.w = vec3Create()),
                (this.as = vec3Create()),
                (this.C = vec3Create()),
                (this.P = vec3Create()),
                (this.aj = vec3Create()),
                (this.K = vec3Create()),
                (this.ai = vec3Create()),
                (this.I = vec3Create()),
                (this.aa = vec3Create()),
                (this.n = vec3Create()),
                (this.Y = t.renderer.context),
                (this.y = new Array(e.c.length)),
                (this.ao = new Array(e.c.length));
            for (let i = 0; i < e.c.length; i++) this.ao[i] = t.r.O[e.c[i]];
            let i = Wi(255, 255, 255, 255),
                s = new jr();
            (s.b = 0), (s.c = 0), (s.d = 1), (s.a = 1), this.ar(e.r, e.m, i, s, e.p, e.f), this.t(e.g), this.k(false);
        }
        k(t) {
            (this.H = t), this.H || (this.au = false);
        }
        t(t) {
            this.b = t;
        }
        ag() {
            return this.aq == this.c;
        }
        a(t) {
            this.R = t;
        }
        q(t) {
            this.ab = t;
        }
        v(t) {
            this.N[3] = Math.max(t, 0);
        }
        s() {
            let t = vec3Create();
            vec3Subtract(t, this.av, this.n);
            let e = vec3SquareLen(t);
            vec3Scale(t, this.Q, this.R),
                vec3Sub(this.P, this.av, t),
                vec3Scale(t, this.f, this.R),
                vec3Sub(this.aj, this.n, t),
                vec3Scale(t, this.Q, this.ab),
                vec3Add(this.K, this.av, t),
                vec3Scale(t, this.f, this.ab),
                vec3Add(this.ai, this.n, t),
                vec3Scale(this.as, this.ae, e),
                vec3Scale(this.C, this.w, e);
        }
        Z(t, e, i) {
            let s;
            if (this.U && this.H) {
                s = t;
                let i = vec3Create();
                mat4GetTranslation(i, s),
                    vec3Add(i, i, e),
                    vec3Copy(this.S, e),
                    this.au
                        ? (vec3Copy(this.av, this.n), vec3Copy(this.ae, this.w), vec3Copy(this.Q, this.f))
                        : (vec3Copy(this.av, i), (this.ae = Vr(s, 2)), (this.Q = Vr(s, 1)), (this.B = 0), (this.au = true)),
                    (this.n = i),
                    (this.w = Vr(s, 2)),
                    (this.f = Vr(s, 1));
            }
        }
        F(t) {
            var e = Zs();
            Ks(e, t),
                (this.ae = vec3TransformMat3(this.ae, this.ae, e)),
                (this.Q = vec3TransformMat3(this.Q, this.Q, e)),
                (this.w = vec3TransformMat3(this.w, this.w, e)),
                (this.f = vec3TransformMat3(this.f, this.f, e)),
                (this.av = vec3TransformMat4(this.av, this.av, t)),
                (this.n = vec3TransformMat4(this.n, this.n, t));
            for (var i = 0; i < this.am.length; i++) vec3TransformMat4(this.am[i].b, this.am[i].b, t);
        }
        al(t, e, i) {
            (this.N[2] = i), (this.N[1] = e), (this.N[0] = t);
        }
        ac(t) {
            if (this.p != t) {
                this.p = t;
                let e = t % this.X,
                    i = e;
                2147483648 & e && (i = ((1 & e) | (e >> 1)) + ((1 & e) | (e >> 1)));
                let s = i * this.W + this.i.c;
                this.L.c = s;
                let r = t / this.X,
                    n = r;
                2147483648 & r && ((r = (1 & r) | (r >> 1)), (n = r + r), (s = this.L.c));
                let a = n * this.u + this.i.b;
                (this.L.b = a), (this.L.a = s + this.W), (this.L.d = a + this.u);
            }
        }
        G(t, e, i) {
            let s,
                r = this.am[2 * this.c],
                n = this.am[2 * this.c + 1],
                a = vec3Create();
            vec3Scale(a, this.C, 1 - e),
                vec3Sub(a, this.aj, a),
                vec3Scale(r.b, a, e),
                vec3Scale(a, this.as, e),
                vec3Add(a, this.P, a),
                vec3Scale(a, a, 1 - e),
                vec3Add(r.b, r.b, a),
                vec3Scale(a, this.C, 1 - e),
                vec3Sub(a, this.ai, a),
                vec3Scale(n.b, a, e),
                vec3Scale(a, this.as, e),
                vec3Add(a, this.K, a),
                vec3Scale(a, a, 1 - e),
                vec3Add(n.b, n.b, a),
                (this.j[this.c] = t),
                (s = i),
                (this.c = this.c + s),
                this.c >= this.j.length && (this.c -= this.j.length);
        }
        ak(t, e) {
            if (this.e.R) return;
            let i = vec3Create(),
                s = 1;
            (i = this.af.i.c(t, this.e.F, i)),
                (s = this.af.l.c(t, this.e.F)),
                this.al(i[0], i[1], i[2]),
                this.v(s / 32767);
            let r = this.af.n.c(t, this.e.F);
            this.q(r);
            let n = this.af.e.c(t, this.e.F);
            this.a(n);
            let a = this.af.a.c(t, this.e.F);
            this.ac(a);
            let o = this.af.o.c(t, this.e.F, 1);
            this.k(0 != o);
            let h = mat4Create();
            mat4Multiply(h, this.e.am, this.e.aw[this.af.h].i), mat4Translate(h, h, this.af.q);
            let l = vec3Create();
            this.Z(h, l, null), this.z(e, false);
        }
        z(t, e) {
            let i,
                s,
                r,
                n,
                a,
                o,
                h,
                l,
                u,
                c,
                d,
                f,
                g,
                _,
                b,
                m,
                p,
                x,
                v,
                T,
                w,
                y,
                A,
                E,
                C,
                M,
                k,
                S,
                F,
                I,
                D,
                R,
                U,
                B,
                O,
                P,
                z,
                H,
                N,
                G,
                L,
                j,
                q,
                V,
                W,
                X,
                Y,
                Z;
            for (
                this.ap || (this.M > 0 && (t = 1 / this.M + 99999997e-12)),
                    t >= 0 ? this.d <= t && (t = this.d) : (t = 0),
                    x = this.aq;
                x != this.c && !(t + this.j[x] <= this.d);
                x = this.aq
            )
                this.aq = this.m(this.aq, 1);
            if (!e && this.U && this.H && this.au) {
                (D = t * this.M + this.B), (Z = this.N), this.s();
                let e = false;
                if (
                    ((B = 0),
                    D < 1
                        ? (e = true)
                        : ((Y = this.B), (U = 1 / (D - Y)), (p = Math.floor(D - 1)), (B = Math.ceil(Math.max(p, 0)))),
                    -1 == B || e)
                );
                else
                    for (
                        R = 1, x = 1;
                        (I = this.c),
                            (H = this.am.length),
                            (this.am[2 * I].a = Z),
                            (v = 2 * this.c + 1),
                            (N = this.am.length),
                            (this.am[v].a = Z),
                            this.G((x - Y) * U * -t, (x - Y) * U, 1),
                            -1 != --B;
                        x = R
                    )
                        (R += 1), (Y = this.B);
                (T = Math.floor(D)),
                    (this.B = D - T),
                    this.G(0, 1, 0),
                    (F = this.c),
                    (G = this.am.length),
                    (w = this.am[2 * F]),
                    (y = this.L.c),
                    (w.c[1] = this.L.b),
                    (w.c[0] = y),
                    (A = 2 * this.c + 1),
                    (L = this.am.length),
                    (E = this.am[A]),
                    (C = this.L.c),
                    (E.c[1] = this.L.d),
                    (E.c[0] = C),
                    (S = this.c),
                    (j = this.am.length),
                    (this.am[2 * S].a = Z),
                    (M = 2 * this.c + 1),
                    (q = this.am.length),
                    (this.am[M].a = Z);
            }
            (this.I[2] = 34028235e31),
                (this.I[1] = 34028235e31),
                (this.I[0] = 34028235e31),
                (this.aa[2] = -34028235e31),
                (this.aa[1] = -34028235e31),
                (this.aa[0] = -34028235e31),
                (O = this.aq);
            for (let e = this.aq; e != this.c; O = e)
                (m = 2 * e),
                    (X = this.am.length),
                    (k = O),
                    (z = this.am[2 * e]),
                    (i = m + 1),
                    (s = this.am[2 * e + 1]),
                    (r = (this.b + this.b) * this.j[k] * t + t * this.b * t),
                    (z.b[2] = z.b[2] + r),
                    (s.b[2] = r + s.b[2]),
                    (n = z.b[0]),
                    (a = this.I[0]),
                    a > z.b[0] && ((a = z.b[0]), (this.I[0] = n), (n = z.b[0])),
                    (o = z.b[1]),
                    (h = this.I[1]),
                    h > o && ((h = z.b[1]), (this.I[1] = o), (o = z.b[1])),
                    (l = z.b[2]),
                    (u = this.I[2]),
                    u > l && ((u = z.b[2]), (this.I[2] = l), (l = z.b[2])),
                    n > this.aa[0] && (this.aa[0] = n),
                    o > this.aa[1] && (this.aa[1] = o),
                    l > this.aa[2] && (this.aa[2] = l),
                    (c = s.b[0]),
                    a > s.b[0] && ((this.I[0] = c), (c = s.b[0])),
                    (d = s.b[1]),
                    h > d && ((this.I[1] = d), (d = s.b[1])),
                    (f = s.b[2]),
                    u > f && ((this.I[2] = f), (f = s.b[2])),
                    c > this.aa[0] && (this.aa[0] = c),
                    d > this.aa[1] && (this.aa[1] = d),
                    f > this.aa[2] && (this.aa[2] = f),
                    (V = this.j.length),
                    (this.j[k] = t + this.j[k]),
                    (g = this.W),
                    (W = this.j.length),
                    (_ = g * this.j[k] * this.r + this.L.c),
                    (z.c[1] = this.L.b),
                    (z.c[0] = _),
                    (s.c[1] = this.L.d),
                    (s.c[0] = _),
                    (b = this.j.length),
                    (P = O + 1),
                    (e = P - b),
                    b > P && (e = P);
            this.ap = true;
        }
        m(t, e) {
            let i = e + t;
            t = i;
            let s = this.j.length;
            return i >= s && (t = i - s), t;
        }
        ar(t, e, i, s, r, n) {
            let a, o, h, l, u, c, d, f;
            (d = Math.ceil(t)),
                (f = Math.max(0.25, e)),
                (a = Math.ceil(f * d)),
                (o = Math.ceil(Math.max(a + 1 + 1, 0))),
                (this.j = new Array(o)),
                (this.aq = 0),
                (this.c = 0),
                (this.B = 0),
                (this.au = false),
                (this.am = new Array(2 * o));
            for (let t = 0; t < this.am.length; t++) {
                this.am[t] = new Lr();
                let e = this.am[t];
                (e.b[0] = 0), (e.b[1] = 0), (e.b[2] = 0), (e.a = Wi(0, 0, 0, 0)), (e.c[0] = 0), (e.c[1] = 0);
            }
            this.E = new Array(4 * o);
            for (let t = 0; t < this.E.length; t++) this.E[t] = t % (2 * o);
            (this.r = 1 / f),
                (h = n),
                2147483648 & n && (h = ((1 & n) | (n >> 1)) + ((1 & n) | (n >> 1))),
                (this.W = (s.a - s.c) / h),
                (l = r),
                2147483648 & r && (l = ((1 & r) | (r >> 1)) + ((1 & r) | (r >> 1))),
                (this.u = (s.d - s.b) / l),
                (this.an = 1 / this.W),
                (this.h = 1 / this.u),
                (this.M = d),
                (this.d = f),
                $i(i, i, 1 / 255),
                (this.N = i),
                (this.i = s),
                (this.ah = r),
                (this.X = n),
                (this.p = 0),
                (u = 0 * this.W + this.i.c),
                (this.L.c = u),
                (c = 0 * this.u + this.i.b),
                (this.L.b = c),
                (this.L.a = u + this.W),
                (this.L.d = c + this.u),
                (this.ab = 10),
                (this.R = 10),
                (this.b = 0),
                (this.U = true),
                (this.H = true),
                (this.O = true),
                (this.J = this.e.au.c(0)),
                (this.x = this.e.au.d(0)),
                (this.T = this.e.au.b(this.J, this.x));
        }
        D() {
            let t = new Array(this.am.length);
            for (let e = 0, i = 0; e < this.am.length; ++e)
                (t[i++] = this.am[e].b[0]),
                    (t[i++] = this.am[e].b[1]),
                    (t[i++] = this.am[e].b[2]),
                    (t[i++] = this.am[e].a[0]),
                    (t[i++] = this.am[e].a[1]),
                    (t[i++] = this.am[e].a[2]),
                    (t[i++] = this.am[e].a[3]),
                    (t[i++] = this.am[e].c[0]),
                    (t[i++] = this.am[e].c[1]);
            this.ag() || (this.J.d(new Float32Array(t)), this.x.d(new Uint16Array(this.E)));
        }
        A(t) {
            const e = this.e.au;
            var i = this.af.b[t];
            if (i <= -1 || i > this.e.X.length) return null;
            let s = this.e.X[i];
            if (!s.c || !s.c.e) return null;
            let r = t;
            r >= this.af.c.length && (r = 0);
            let n = this.e.r.O[this.af.c[r]],
                a = Object.assign({}, this.e.aE);
            const o = e.e(this.e.aM, new Dr(false, !this.e.Z, qr[n.b], true, false, 15), new Ri([s.c], a));
            return e.m(new Rr(this.T, 0, 0), o, 0, 0);
        }
        ad(t) {
            if (this.ag()) return;
            const e = this.e.au.a();
            for (let i = 0; i < this.af.b.length; i++) {
                if ((this.y[i] || (this.y[i] = this.A(i)), !this.y[i])) continue;
                if (!t && this.y[i].d.b() > vs.GxBlend_AlphaKey) continue;
                let s = this.c > this.aq ? 2 * (this.c - this.aq) + 2 : 2 * (this.j.length + this.c - this.aq) + 2;
                (this.y[i].f = s), (this.y[i].a = 2 * this.aq * 2), e.d(this.y[i]);
            }
        }
    };
    const Xr = class {
        constructor(t) {
            var e = this;
            (e.c = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (e.d = Wi(t.getFloat(), t.getFloat(), t.getFloat(), 0)),
                (e.i = t.getFloat()),
                (e.h = t.getFloat()),
                (e.f = t.getFloat()),
                (e.e = t.getFloat()),
                (e.g = [t.getUint8(), t.getUint8(), t.getUint8(), t.getUint8()]),
                (e.a = [t.getUint8(), t.getUint8(), t.getUint8(), t.getUint8()]);
        }
        b() {
            var t = this;
            (t.c = null), (t.d = null), (t.g = null), (t.a = null);
        }
    };
    const Yr = class {
        constructor(t) {
            var e = this;
            (e.k = t.getUint16()),
                (e.g = t.getUint16()),
                (e.e = t.getUint16()),
                (e.j = t.getUint16()),
                (e.c = t.getUint16() + 65536 * e.g),
                (e.b = t.getUint16()),
                (e.d = t.getUint16()),
                (e.h = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (e.i = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (e.a = t.getFloat());
        }
        f() {
            (this.h = null), (this.i = null);
        }
    };
    const Zr = class {
        constructor(t) {
            (this.a = t.getUint16()), (this.b = t.getUint16());
        }
        static c(t) {
            const e = t.k.r,
                i = t.x;
            e.O && i.e < e.O.length ? (t.c = e.O[i.e]) : (t.c = { a: 0, b: 0 }),
                (t.t = !!(1 & t.c.a)),
                (t.C = !(4 & t.c.a)),
                (t.s = !!(16 & t.c.a));
        }
    };
    const Kr = class {
        constructor(t) {
            (this.d = new wr(t, fr)), (this.c = new wr(t, gr)), (this.a = new wr(t, fr));
        }
        b() {
            var t = this;
            t.d && (t.d.b(), (t.d = null)), t.c && (t.c.b(), (t.c = null)), t.a && (t.a.b(), (t.a = null));
        }
    };
    const $r = class {
        constructor(t) {
            var e = this;
            (e.e = t.getInt32()),
                (e.c = t.getInt32()),
                (e.b = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (e.d = -1);
        }
        a() {
            this.b = null;
        }
    };
    const Jr = class {
        constructor(t) {
            (this.b = new wr(t, fr)), (this.e = new wr(t, _r));
        }
        f() {
            var t = this;
            t.b && t.b.b(), t.e && t.e.b();
        }
        c(t) {
            return !!this.b && this.b.e(t);
        }
        a(t) {
            return !!this.e && this.e.e(t);
        }
        g(t) {
            return this.c(t) || this.a(t);
        }
        d(t, e, i) {
            var s = this;
            i ? (i[0] = i[1] = i[2] = i[3] = 1) : (i = Wi(1, 1, 1, 1));
            let r = vec3FromValues(1, 1, 1);
            return (
                s.c(t.d.e) && s.b.c(t, e, r, r),
                s.a(t.d.e) && (i[3] = s.e.c(t, e, 32767) / 32767),
                (i[0] = r[0]),
                (i[1] = r[1]),
                (i[2] = r[2]),
                i
            );
        }
    };
    const Qr = class {
        constructor(t) {
            this.c = new wr(t, _r);
        }
        d() {
            this.c.b(), (this.c = null);
        }
        b(t) {
            return this.c.e(t);
        }
        a(t, e) {
            var i = 1;
            this.b(t.d.e) && (i = this.c.c(t, e, i) / 32767);
            return i > 1 ? (i = 1) : i < 0 && (i = 0), i;
        }
    };
    const tn = class {
        constructor(t) {
            var e = this;
            (e.d = t.getFloat()), (e.c = t.getFloat()), (e.b = t.getFloat()), (e.a = new Tr(t));
        }
    };
    const en = class {
        constructor(t) {
            (this.d = t.getInt32()),
                (this.W = t.getUint32()),
                (this.U = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (this.K = t.getInt16()),
                (this.X = t.getInt16()),
                268435456 & this.W &&
                    ((this.V = [0, 0, 0]),
                    (this.V[0] = 31 & this.X),
                    (this.V[1] = (this.X >> 5) & 31),
                    (this.V[2] = (this.X >> 10) & 31)),
                (this.b = t.getUint8()),
                (this.x = t.getUint8()),
                (this.S = t.getUint16()),
                (this.n = t.getUint16()),
                (this.r = t.getUint16()),
                (this.p = t.getUint16()),
                (this.e = new wr(t, br)),
                (this.G = new wr(t, br)),
                (this.w = new wr(t, br)),
                (this.h = new wr(t, br)),
                (this.C = new wr(t, fr)),
                (this.t = new wr(t, br)),
                (this.A = t.getFloat()),
                (this.o = new wr(t, br)),
                (this.B = t.getFloat()),
                (this.P = new wr(t, br)),
                (this.D = new wr(t, br)),
                (this.I = new wr(t, br)),
                (this.u = new vr(t)),
                (this.c = new Tr(t)),
                (this.q = new xr(t)),
                (this.M = [t.getFloat(), t.getFloat()]),
                (this.k = new Tr(t)),
                (this.Q = new Tr(t)),
                (this.z = t.getFloat()),
                (this.R = t.getFloat()),
                (this.J = t.getFloat()),
                (this.F = [t.getFloat(), t.getFloat()]),
                (this.T = t.getFloat()),
                (this.a = t.getFloat()),
                (this.s = t.getFloat()),
                (this.Y = t.getFloat()),
                (this.N = t.getFloat()),
                (this.j = t.getFloat()),
                (this.E = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (this.m = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (this.H = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (this.y = t.getFloat()),
                (this.l = t.getFloat()),
                (this.g = t.getFloat()),
                (this.aa = t.getFloat()),
                (this.O = t.getFloat());
            var e = t.getInt32();
            this.i = new Array(e);
            for (var i = 0; i < e; i++) this.i[i] = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat());
            (this.f = new wr(t, mr)),
                (this.L = qs(t.getFloat(), t.getFloat())),
                (this.Z = [qs(t.getFloat(), t.getFloat()), qs(t.getFloat(), t.getFloat())]),
                (this.v = [qs(t.getFloat(), t.getFloat()), qs(t.getFloat(), t.getFloat())]);
        }
    };
    class sn {
        constructor(t) {
            (this.g = t.getInt32()),
                (this.e = t.getUint32()),
                (this.i = t.getInt16()),
                (this.d = t.getUint16()),
                (this.b = t.getUint32()),
                (this.c = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (this.h = new wr(t, fr)),
                (this.f = new wr(t, gr)),
                (this.a = new wr(t, fr));
        }
    }
    const DataView = class {
        constructor(t) {
            (this.buffer = new DataView(t)), (this.position = 0);
        }
        getBool() {
            var t = 0 != this.buffer.getUint8(this.position);
            return (this.position += 1), t;
        }
        getUint8() {
            var t = this.buffer.getUint8(this.position);
            return (this.position += 1), t;
        }
        getInt8() {
            var t = this.buffer.getInt8(this.position);
            return (this.position += 1), t;
        }
        getUint16() {
            var t = this.buffer.getUint16(this.position, true);
            return (this.position += 2), t;
        }
        getInt16() {
            var t = this.buffer.getInt16(this.position, true);
            return (this.position += 2), t;
        }
        getUint32() {
            var t = this.buffer.getUint32(this.position, true);
            return (this.position += 4), t;
        }
        getInt32() {
            var t = this.buffer.getInt32(this.position, true);
            return (this.position += 4), t;
        }
        getFloat() {
            var t = this.buffer.getFloat32(this.position, true);
            return (this.position += 4), t;
        }
        getString(t) {
            undefined === t && (t = this.getUint16());
            for (var e = "", i = 0; i < t; ++i) e += String.fromCharCode(this.getUint8());
            return e;
        }
        setBool(t) {
            this.buffer.setUint8(this.position, t ? 1 : 0), (this.position += 1);
        }
        setUint8(t) {
            this.buffer.setUint8(this.position, t), (this.position += 1);
        }
        setInt8(t) {
            this.buffer.setInt8(this.position, t), (this.position += 1);
        }
        setUint16(t) {
            this.buffer.setUint16(this.position, t, true), (this.position += 2);
        }
        setInt16(t) {
            this.buffer.setInt16(this.position, t, true), (this.position += 2);
        }
        setUint32(t) {
            this.buffer.setUint32(this.position, t, true), (this.position += 4);
        }
        setInt32(t) {
            this.buffer.setInt32(this.position, t, true), (this.position += 4);
        }
        setFloat(t) {
            this.buffer.setFloat32(this.position, t, true), (this.position += 4);
        }
    };


    /*
     * aowow - PAKO was included in file
     */

    import { pako } from "./pako";


    var pakoInflate = inflate;

    class kh {
        constructor(t) {
            var e = this;
            (e.g = t.getUint16()),
                (e.l = t.getUint16()),
                (e.h = t.getUint32()),
                (e.a = t.getUint32()),
                (e.e = t.getUint16()),
                (e.b = t.getUint16()),
                (e.j = t.getUint16()),
                (e.f = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (e.c = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (e.m = t.getInt16()),
                (e.k = t.getUint16()),
                t.getBool() && (e.i = t.getString());
        }
        d() {}
    }
    class Sh {
        constructor(t) {
            var e;
            if (
                ((this.d = t.getInt32()),
                (this.h = t.getInt32()),
                (this.q = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (e = t.getInt32()) > 0)
            ) {
                this.b = new Array(e);
                for (let i = 0; i < e; ++i) this.b[i] = t.getInt16();
            }
            if ((e = t.getInt32()) > 0) {
                this.c = new Array(e);
                for (let i = 0; i < e; ++i) this.c[i] = t.getInt16();
            }
            (this.i = new wr(t, fr)),
                (this.l = new wr(t, _r)),
                (this.n = new wr(t, br)),
                (this.e = new wr(t, br)),
                (this.r = t.getFloat()),
                (this.m = t.getFloat()),
                (this.g = t.getFloat()),
                (this.f = t.getInt16()),
                (this.p = t.getInt16()),
                (this.a = new wr(t, _r)),
                (this.o = new wr(t, mr)),
                (this.k = t.getInt16());
        }
    }
    class Fh {
        constructor(t) {
            (this.c = t.getInt32()), (this.b = t.getUint32()), (this.a = t.getUint32());
        }
    }
    class Ih {
        constructor(t) {
            (this.i = t.getUint8()),
                (this.j = t.getInt8()),
                (this.k = t.getUint16()),
                (this.f = t.getUint16()),
                (this.d = t.getUint16()),
                (this.g = t.getInt16()),
                (this.e = t.getUint16()),
                (this.l = t.getUint16()),
                (this.m = t.getUint16()),
                (this.b = t.getInt16()),
                (this.a = t.getUint16()),
                (this.c = t.getInt16()),
                (this.h = t.getInt16());
        }
    }
    class Dh {
        constructor(t) {
            (this.b = t.getInt16()),
                (this.f = t.getInt16()),
                (this.d = vec3FromValues(t.getFloat(), t.getFloat(), t.getFloat())),
                (this.h = new wr(t, fr)),
                (this.i = new wr(t, br)),
                (this.j = new wr(t, fr)),
                (this.e = new wr(t, br)),
                (this.g = new wr(t, br)),
                (this.c = new wr(t, br)),
                (this.a = new wr(t, mr));
        }
    }
    const Rh = class {
        constructor(t) {
            var e = this;
            (e.e = t.getInt16()),
                (e.a = t.getFloat()),
                (e.d = t.getFloat()),
                (e.b = t.getUint16()),
                (e.c = t.getUint32());
        }
    };
    const Uh = class {
        constructor(t) {
            var e = this;
            (e.b = t.getFloat()), (e.c = t.getFloat()), (e.a = t.getUint32()), (e.d = t.getUint32());
        }
    };
    class Model {
        constructor(t) {
            if (
                ((this.w = []),
                (this.z = []),
                (this.L = []),
                (this.y = []),
                (this.I = []),
                (this.R = []),
                (this.P = []),
                (this.n = []),
                (this.r = []),
                (this.D = []),
                (this.m = []),
                (this.K = []),
                (this.A = []),
                (this.u = []),
                (this.j = []),
                (this.Q = []),
                (this.l = []),
                (this.G = []),
                (this.g = []),
                (this.d = []),
                (this.B = []),
                (this.h = []),
                (this.q = []),
                (this.s = []),
                (this.M = []),
                (this.i = []),
                (this.a = []),
                (this.J = []),
                (this.x = []),
                (this.F = []),
                (this.o = []),
                (this.b = []),
                (this.f = []),
                (this.v = []),
                (this.S = []),
                (this.c = []),
                !t)
            )
                return void console.error("Bad buffer for DataView");
            let e = new DataView(t);
            if (604210112 != e.getUint32()) return void console.log("Bad magic value");
            if (e.getUint32() < 2e3) return void console.log("Bad version");
            this.t = e.getUint32();
            var i = e.getUint32(),
                s = e.getUint32(),
                r = e.getUint32(),
                n = e.getUint32(),
                a = e.getUint32(),
                o = e.getUint32(),
                h = e.getUint32(),
                l = e.getUint32(),
                u = e.getUint32(),
                c = e.getUint32(),
                d = e.getUint32(),
                f = e.getUint32(),
                g = e.getUint32(),
                _ = e.getUint32(),
                b = e.getUint32(),
                m = e.getUint32(),
                p = e.getUint32(),
                x = e.getUint32(),
                v = e.getUint32(),
                T = e.getUint32(),
                w = e.getUint32(),
                y = e.getUint32(),
                A = e.getUint32(),
                E = e.getUint32(),
                C = e.getUint32(),
                M = e.getUint32(),
                k = e.getUint32(),
                S = e.getUint32(),
                F = e.getUint32(),
                I = e.getUint32(),
                D = e.getUint32(),
                R = e.getUint32(),
                U = e.getUint32(),
                B = e.getUint32(),
                O = e.getUint32(),
                P = e.getUint32();
            let z = new Uint8Array(t, e.position),
                H = null;
            try {
                H = pakoInflate(z);
            } catch (t) {
                return void console.log("Decompression error: " + t);
            }
            if (H.length < P) console.log("Unexpected data size", H.length, P);
            else {
                (e = new DataView(H.buffer)), (e.position = i);
                var N = e.getInt32();
                if (N > 0) {
                    this.w = new Array(N);
                    for (let t = 0; t < N; ++t) this.w[t] = new Xr(e);
                }
                e.position = s;
                var G = e.getInt32();
                if (G > 0) {
                    this.z = new Array(G);
                    for (let t = 0; t < G; ++t) this.z[t] = e.getUint16();
                }
                e.position = r;
                var L = e.getInt32();
                if (L > 0) {
                    this.L = new Array(L);
                    for (let t = 0; t < L; ++t) this.L[t] = e.getUint32();
                }
                e.position = n;
                var j = e.getInt32();
                if (j > 0) {
                    this.y = new Array(j);
                    for (let t = 0; t < j; ++t) this.y[t] = new kh(e);
                }
                e.position = a;
                var q = e.getInt32();
                if (q > 0) {
                    this.I = new Array(q);
                    for (let t = 0; t < q; ++t) this.I[t] = e.getInt16();
                }
                e.position = o;
                var V = e.getInt32();
                if (V > 0) {
                    this.R = new Array(V);
                    for (let t = 0; t < V; ++t) this.R[t] = new sn(e);
                }
                e.position = h;
                var W = e.getInt32();
                if (W > 0) {
                    this.P = new Array(W);
                    for (let t = 0; t < W; ++t) this.P[t] = e.getInt16();
                }
                e.position = l;
                var X = e.getInt32();
                if (X > 0) {
                    this.n = new Array(X);
                    for (let t = 0; t < X; ++t) this.n[t] = e.getInt16();
                }
                e.position = u;
                var Y = e.getInt32();
                if (Y > 0) {
                    this.r = new Array(Y);
                    for (let t = 0; t < Y; ++t) this.r[t] = new Yr(e);
                }
                e.position = c;
                var Z = e.getInt32();
                if (Z > 0) {
                    this.D = new Array(Z);
                    for (let t = 0; t < Z; ++t) this.D[t] = new Ih(e);
                }
                e.position = d;
                var K = e.getInt32();
                if (K > 0) {
                    this.m = new Array(K);
                    for (let t = 0; t < K; ++t) this.m[t] = e.getInt16();
                }
                e.position = f;
                var $ = e.getInt32();
                if ($ > 0) {
                    this.O = new Array($);
                    for (let t = 0; t < $; ++t) this.O[t] = new Zr(e);
                }
                e.position = g;
                var J = e.getInt32();
                if (J > 0) {
                    this.K = new Array(J);
                    for (let t = 0; t < J; ++t) this.K[t] = new Fh(e);
                }
                e.position = _;
                var Q = e.getInt32();
                if (Q > 0) {
                    this.A = new Array(Q);
                    for (let t = 0; t < Q; ++t) this.A[t] = e.getInt16();
                }
                e.position = b;
                var tt = e.getInt32();
                if (tt > 0) {
                    this.l = new Array(tt);
                    for (let t = 0; t < tt; ++t) this.l[t] = new Kr(e);
                }
                e.position = m;
                var et = e.getInt32();
                if (et > 0) {
                    this.G = new Array(et);
                    for (let t = 0; t < et; ++t) this.G[t] = e.getInt16();
                }
                (e.position = p),
                    (this.N = vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat())),
                    (this.e = vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat())),
                    (this.C = e.getFloat()),
                    (this.k = vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat())),
                    (this.E = vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat())),
                    (this.H = e.getFloat()),
                    (e.position = x);
                var it = e.getInt32();
                if (it > 0) {
                    this.u = new Array(it);
                    for (let t = 0; t < it; ++t) this.u[t] = e.getUint16();
                }
                e.position = v;
                var st = e.getInt32();
                if (st > 0) {
                    this.j = new Array(st);
                    for (let t = 0; t < st; ++t) this.j[t] = vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat());
                }
                e.position = T;
                var rt = e.getInt32();
                if (rt > 0) {
                    this.Q = new Array(rt);
                    for (let t = 0; t < rt; ++t) this.Q[t] = vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat());
                }
                e.position = w;
                var nt = e.getInt32();
                if (nt > 0) {
                    this.g = new Array(nt);
                    for (let t = 0; t < nt; ++t) this.g[t] = e.getInt16();
                }
                e.position = y;
                var at = e.getInt32();
                if (at > 0) {
                    this.d = new Array(at);
                    for (let t = 0; t < at; ++t) this.d[t] = new $r(e);
                }
                e.position = A;
                var ot = e.getInt32();
                if (ot > 0) {
                    this.B = new Array(ot);
                    for (let t = 0; t < ot; ++t) this.B[t] = e.getInt16();
                }
                e.position = E;
                var ht = e.getInt32();
                if (ht > 0) {
                    this.h = new Array(ht);
                    for (let t = 0; t < ht; ++t) this.h[t] = new Jr(e);
                }
                e.position = C;
                var lt = e.getInt32();
                if (lt > 0) {
                    this.q = new Array(lt);
                    for (let t = 0; t < lt; ++t) this.q[t] = new Qr(e);
                }
                e.position = M;
                var ut = e.getInt32();
                if (ut > 0) {
                    this.s = new Array(ut);
                    for (let t = 0; t < ut; ++t) this.s[t] = e.getInt16();
                }
                e.position = k;
                var ct = e.getInt32();
                if (ct > 0) {
                    this.M = new Array(ct);
                    for (let t = 0; t < ct; ++t) this.M[t] = new Dh(e);
                }
                e.position = S;
                var dt = e.getInt32();
                if (dt > 0) {
                    this.i = new Array(dt);
                    for (let t = 0; t < dt; ++t) this.i[t] = new en(e);
                }
                e.position = I;
                var ft = e.getInt32();
                if (ft > 0) {
                    this.J = new Array(ft);
                    for (let t = 0; t < ft; ++t) this.J[t] = new tn(e);
                }
                e.position = D;
                var gt = e.getInt32();
                if (gt > 0) {
                    this.x = new Array(gt);
                    for (let t = 0; t < ft; ++t) this.x[t] = e.getInt16();
                }
                e.position = R;
                var _t = e.getInt32();
                if (_t > 0) {
                    this.F = new Array(_t);
                    for (let t = 0; t < _t; ++t) this.F[t] = new Rh(e);
                }
                e.position = O;
                var bt = e.getInt32();
                if (bt > 0) {
                    this.c = new Array(bt);
                    for (let t = 0; t < bt; ++t) this.c[t] = new Uh(e);
                }
                if (U > 0) {
                    e.position = U;
                    if (e.getUint32()) {
                        const t = e.position,
                            i = e.getInt32(),
                            s = e.getUint32(),
                            r = e.getInt32(),
                            n = e.getUint32(),
                            a = e.getInt32(),
                            o = e.getUint32(),
                            h = e.getInt32(),
                            l = e.getUint32();
                        let u = e.position;
                        e.position = t + s;
                        for (let t = 0; t < i; t++) this.o.push(vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat()));
                        e.position = t + n;
                        for (let t = 0; t < r; t++) this.b.push(vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat()));
                        e.position = t + o;
                        for (let t = 0; t < a; t++) this.f.push(e.getUint16());
                        e.position = t + l;
                        for (let t = 0; t < h; t++) this.v.push(e.getUint16());
                        e.position = u;
                    }
                }
                if (B > 0) {
                    e.position = B;
                    if (e.getUint32()) {
                        (this.p = vec3FromValues(e.getFloat(), e.getFloat(), e.getFloat())), (this.S = []);
                        for (let t = 0; t < 5; t++) this.S.push(e.getInt32());
                    }
                }
                e.position = F;
                var mt = e.getInt32();
                if (mt > 0) {
                    this.a = new Array(mt);
                    for (let t = 0; t < mt; ++t) this.a[t] = new Sh(e);
                }
            }
        }
    }
    const Oh = class {
        constructor(t) {
            (this.x = t),
                (this.F = new Float32Array([1, 1, 1, 1])),
                (this.m = false),
                (this.b = true),
                (this.k = null),
                (this.D = null),
                (this.y = 0),
                (this.c = null),
                (this.n = []),
                (this.w = []),
                (this.A = new Array()),
                (this.i = null),
                (this.j = []),
                (this.l = t.k),
                (this.z = t.m),
                (this.t = false),
                (this.C = false),
                (this.s = false),
                (this.E = Vi()),
                (this.a = vec3Create()),
                (this.q = Js());
        }
        g(t) {
            this.k = t;
            const e = t.r,
                i = this.x;
            (this.D = e.r[this.x.f]), (this.y = this.D.k), Zr.c(this);
            let s = e.A[i.b];
            1 == i.m && s > -1 && 1 == e.K[s].c && ((this.l = -1e3), (this.z = 3));
            for (let s = 0; s < this.z; s++) {
                if (i.b > -1 && i.b < e.A.length) {
                    let r = e.A[i.b + s];
                    r > -1 && r < e.K.length && this.n.splice(s, 0, t.X[r]);
                }
                if (i.h > -1 && i.h < e.G.length) {
                    let t = e.G[i.h + s];
                    t > -1 && e.l && t < e.l.length
                        ? (this.w.splice(s, 0, e.l[t]),
                          console.log("TextureAnim found for batch:", this.x.b, "animIdx:", t))
                        : this.w.splice(s, 0, null);
                }
                if (i.c > -1 && i.c < e.s.length) {
                    let t = e.s[i.c + s];
                    t > -1 && t < e.q.length ? this.j.splice(s, 0, e.q[t]) : this.j.splice(s, 0, null);
                }
            }
            this.A = new Array(this.w.length);
            for (let t = 0; t < this.A.length; t++) this.A[t] = mat4Create();
            e.h && i.g > -1 && i.g < e.h.length && (this.i = e.h[i.g]);
        }
        e() {
            this.k.r;
            let t = Wi(this.D.i[0], this.D.i[1], this.D.i[2], 1),
                e = this.k.aw[this.D.d].i,
                i = mat4Create();
            mat4Mult(i, i, this.k.renderer.viewMatrix), mat4Mult(i, i, this.k.am), mat4Mult(i, i, e), ts(t, t, i), (t[3] = 0);
            let s = es(t);
            if ((3 & this.x.i) > 0) {
                let e = Vi();
                s > 0 ? $i(e, t, 1 / s) : Xi(e, t),
                    $i(e, e, vec3Len(vec3FromValues(i[8], i[9], i[10])) * this.D.a),
                    1 & this.x.i ? Ki(e, t, e) : Zi(e, t, e),
                    (s = Ji(e));
            }
            return s;
        }
        u() {
            this.k, this.k.renderer.context;
            const t = this.k.m;
            if (
                ((this.E[0] = this.E[1] = this.E[2] = this.E[3] = 1),
                this.i && this.i.d(t, this.k.F, this.E),
                this.j[0] && (this.E[3] *= this.j[0].a(t, this.k.F)),
                (this.E[3] *= this.k.ar[3]),
                !(this.E[3] <= 0.001))
            ) {
                for (let e = 0; e < this.j.length; e++) {
                    const i = this.j[e];
                    i && (this.F[e] = i.a(t, this.k.F));
                }
                if (!this.m || this.k.aq) {
                    const t = this.p();
                    let e = true;
                    for (const i of t) {
                        const t = i.a;
                        e = e && null != t;
                    }
                    if (((this.m = e), !e)) return;
                    (this.r = this.B(false, false)), (this.f = this.B(true, false)), (this.h = this.B(false, true));
                }
                if (
                    (this.w.forEach((e, i) => {
                        if (!this.k.R && (mat4Identity(this.A[i]), this.w[i])) {
                            let e = false,
                                s = false;
                            this.w[i].d && this.w[i].d.e(t.d.e)
                                ? ((this.a = this.w[i].d.c(t, this.k.F)), (s = true))
                                : vec3Set(this.a, 0, 0, 0),
                                this.w[i].c && this.w[i].c.e(t.d.e)
                                    ? ((this.q = this.w[i].c.c(t, this.k.F)), (e = true))
                                    : hr(this.q, 0, 0, 0, 1);
                            let r,
                                n = false;
                            if (
                                (this.w[i].a && this.w[i].a.e(t.d.e) && ((r = this.w[i].a.c(t, this.k.F)), (n = true)),
                                mat4Identity(this.A[i]),
                                mat4Translate(this.A[i], this.A[i], vec3FromValues(0.5, 0.5, 0)),
                                n && mat4Scale(this.A[i], this.A[i], r),
                                e)
                            ) {
                                let t = mat4Create();
                                mat4FromRotTranslation(t, this.q, [0, 0, 0]), mat4Mult(this.A[i], this.A[i], t);
                            }
                            s && mat4Translate(this.A[i], this.A[i], this.a), mat4Translate(this.A[i], this.A[i], vec3FromValues(-0.5, -0.5, 0));
                        }
                    }),
                    this.m)
                ) {
                    (this.E[3] < 1 ? this.f : this.r).g = this.e();
                }
            }
        }
        o(t, e) {
            if (!this.r) return;
            const i = this.k.au.a();
            if (e) i.d(this.h);
            else {
                const e = this.r.d.b() <= vs.GxBlend_AlphaKey,
                    s = null != this.k.gradientEffect,
                    r = this.E[3] < 1;
                t && e && (r || s) ? (i.d(this.h), i.d(this.f)) : ((!t && e) || (t && !e)) && i.d(this.r);
            }
        }
        B(t, e) {
            const i = this,
                s = t && i.c.b < 2 ? vs.GxBlend_Alpha : i.c.b,
                r = [0, 1, 2, 10, 3, 4, 5, 13],
                n = r[s],
                a = this.k.au,
                o = this.k,
                h = Object.assign(Object.assign({}, this.k.aE), this.k.M);
            for (let t = 0; t < this.w.length; t++) h["uTextureMatrix" + (t + 1).toString()] = this.A[t];
            (h.uColor = this.E),
                (h.uTexSampleAlpha = this.F),
                (h.uBlendMode = n),
                (h.uHasSpecEmiss = !!o.U[0] && !!o.U[2]),
                (h.uHasEmissiveGlowing = o.p),
                (h.uUnlit = this.t ? 1 : 0),
                this.k.gradientEffect && (h.u_mulLum_OpaqMat = [0, 1, 0, 0]);
            let l = !this.k.Z;
            const u = a.o(
                this.k.aM,
                new Dr(i.C, l, r[s], true, !i.s, e ? 0 : 15),
                new Ii(this.l, i.p(), h, null != this.k.gradientEffect && s <= 2)
            );
            return a.l(new Rr(o.J, 2 * i.D.c, i.D.b), u, this.x.l, this.x.j);
        }
        v() {
            return this.n;
        }
        p() {
            const t = [],
                e = this.k;
            return (
                this.n.forEach((i, s) => {
                    let r = null;
                    i &&
                        (-1e3 == this.l
                            ? e.U
                                ? ((r = e.U[s]), r || (r = { a: null, e: false }))
                                : (r = { a: null, e: false })
                            : (r =
                                  0 == i.f.c
                                      ? i.c
                                      : i.f.c > 0 && this.k.ah[i.f.c]
                                        ? this.k.ah[i.f.c]
                                        : { a: null, e: false }),
                        r ||
                            (this.n[s].e ||
                                (WH.debug("can't find texture for material", s, "type", this.n[s].type, "index", this.n[s].a),
                                (this.n[s].e = true)),
                            (r = { a: this.k.renderer.greenPixelTexture }))),
                        (t[s] = r);
                }),
                t
            );
        }
        get show() {
            return this.b;
        }
        set show(t) {
            this.b = t;
        }
        get meshId() {
            return this.y;
        }
        d() {
            (this.k = null),
                (this.D = null),
                (this.c = null),
                (this.n = null),
                (this.w = null),
                (this.i = null),
                (this.j = null),
                (this.E = null),
                (this.A = null),
                (this.a = null),
                (this.q = null);
        }
    };
    class Ph {
        constructor(t, e) {
            (this.f = e), (this.a = t), (this.c = null), (this.e = false);
        }
        d() {
            this.c && this.c.j(), (this.c = null);
        }
        b(t) {
            0 != this.f.a && (this.c || (this.c = t.getTexture(this.f.a)));
        }
        get type() {
            return this.f.c;
        }
    }
    const zh = function (t, e) {
        const i = Math.abs(t),
            s = Math.abs(e);
        return Number((i - Math.floor(i / s) * s).toPrecision(8)) * Math.sign(t);
    },
    Hh = "DressingRoom",
    Nh = "Stand";
    class Gh {
        constructor() {
            (this.b = null), (this.a = -1), (this.c = mat4Create()), (this.d = 1);
        }
    }


    class Modelviewer
    {
        constructor(gl, e, displayId)
        {
            this.renderer = gl,
            this.au = e,
            this.d = displayId,
            this.C = false,
            this.F = [],
            this.Z = false,
            this.l = true,
            this.W = true,
            this.R = false,
            this.B = false,
            this.m = new cr(),
            this.as = null,
            this.aF = 0,
            this.K = null,
            this.T = null,
            this.ah = {},
            this.U = [],
            this.p = false,
            this.aq = false,
            this.aT = 1,
            this.boundsMin = vec3Create(),
            this.boundsMax = vec3Create(),
            this.j = null,
            this.L = null,
            this.S = new Set(),
            this.J = null,
            this.am = mat4Create(),
            this.P = mat4Create(),
            this.ap = mat4Create(),
            this.aj = mat4Create(),
            this.ar = Wi(1, 1, 1, 1),
            this.y = null,
            this.M = {},
            this.aD = -1,
            this.h = false,
            this.aC = mat4Create(),
            this.ao = vec3Create(),
            this.aH = vec3Create(),
            this.aK = Vi(),
            this.b = Vi(),
            this.al = false,
            this.n = false,
            this.aN = null,
            this.Q = [],
            this.o = 0,
            this.m.f = 0,
            this.m.d.e = -1,
            this.ax(displayId);
        }

        s(t)
        {
            this.Q.push(t);
        }

        ax(displayId)
        {
            const url = this.renderer.options.contentPath + "mo3/" + displayId + ".mo3";
            $.ajax({
                url: url,
                type: "GET",
                dataType: "binary",
                responseType: "arraybuffer",
                processData: false,
                renderer: this.renderer,
                success: (buffer) => {
                    this.loadMo3(buffer);
                },
                error: function (t, e, i) {
                    console.log(i);
                },
            });
        }

        loadMo3(buffer)
        {
            this.r = new Model(buffer),
            this.onLoaded();
        }

        i(t)
        {
            this.aT = t;
        }

        onLoaded()
        {
            const t = this.r,
                e = t.K.length,
                i = t.R.length,
                s = t.L.length,
                r = t.i.length,
                n = t.a.length;

            this.F = new Array(s);

            for (let t = 0; t < s; ++t)
                this.F[t] = 0;

            if (i > 0)
            {
                this.aw = new Array(i);
                for (let e = 0; e < i; e++)
                    this.aw[e] = new Er(this, e, t.R[e]);

                this.D = new Array(i);
                for (let e = 0; e < i; e++)
                {
                    this.D[e] = [];
                    for (let s = 0; s < i; s++)
                        t.R[s].i == e && this.D[e].push(s);
                }
            }

            this.X = new Array();
            for (let i = 0; i < e; i++)
                (this.X[i] = new Ph(i, t.K[i])), this.X[i].b(this.renderer);

            this.ae = new Array(r);
            for (let e = 0; e < r; e++)
                (this.ae[e] = new Gr(this, t.i[e])), t.J && t.J.length && e < t.J.length && this.ae[e].ad(t.J[e]);

            this.ak = new Array(n);
            for (let e = 0; e < n; e++)
                this.ak[e] = new Wr(this, t.a[e]);

            if ((this.as && this.ay(this.as), t.D))
            {
                const e = t.D.length;
                this.aO = new Array(e);
                for (let i = 0; i < e; ++i)
                    (this.aO[i] = new Oh(t.D[i])), this.aO[i].g(this);

                this.af = this.aO.concat();
            }
            this.r.w && t.z &&
                ((this.j = this.au.k(t.w)),
                (this.L = this.au.d(t.z.length)),
                (this.J = this.au.g(this.j, this.L)),
                this.L.d(new Uint16Array(t.z))),
                (this.aM = this.au.j(t.R.length, t.h.length, t.q.length, t.l.length)),
                (this.aE = { uInvTranspViewModelMat: this.aj, uModelMatrix: this.am, uDiffuseColor: this.ar }),
                this.ac("Stand");
            for (let t of this.Q) t();
            if (
                ((this.Q = []),
                vec3Set(this.aH, this.aT, this.aT, this.aT),
                mat4Identity(this.am),
                this.al && mat4RotY(this.am, this.am, Math.PI / 2),
                this.n)
            ) {
                let t = vec3Create();
                mat4Identity(mat4Create()), vec3Set(t, 1, -1, 1), mat4Scale(this.am, this.am, t);
            }
            mat4Scale(this.am, this.am, this.aH), (this.renderer.doUpdateBounds = true), (this.C = true);
        }

        aB() {
            this.al = true;
        }
        N() {
            this.n = true;
        }
        at(t) {
            const e = this.r;
            return e && e.y && t > -1 && t < e.y.length ? e.y[t].i : t == e.y.length ? Hh : "";
        }
        w() {
            this.ac(Nh);
        }
        get isMirrored() {
            return this.e;
        }
        set isMirrored(t) {
            (this.aq = this.e != t), (this.e = t);
        }
        g(t, e, i, s = 1) {
            null != t || null != i
                ? (this.aN || (this.aN = new Gh()),
                  (this.aN.b = t),
                  (this.aN.a = e),
                  i ? mat4Copy(this.aN.c, i) : mat4Identity(this.aN.c),
                  (this.aN.d = s),
                  (this.renderer.doUpdateBounds = true))
                : (this.aN = null);
        }
        ab() {
            this.renderer.context;
            this.r.w &&
                this.r.z &&
                this.aD != this.renderer.currFrame &&
                (this.j && this.j.ba(this.aw, this.S), this.aM.b(this.aw), (this.aD = this.renderer.currFrame));
        }
        aA(t, e, i) {
            const s = [
                    vec3FromValues(t[0], t[1], t[2]),
                    vec3FromValues(t[0], t[1], e[2]),
                    vec3FromValues(t[0], e[1], t[2]),
                    vec3FromValues(t[0], e[1], e[2]),
                    vec3FromValues(e[0], t[1], t[2]),
                    vec3FromValues(e[0], t[1], e[2]),
                    vec3FromValues(e[0], e[1], t[2]),
                    vec3FromValues(e[0], e[1], e[2]),
                ].map((t) => {
                    const e = vec3Create();
                    return vec3TransformMat4(e, t, i), e;
                }),
                r = vec3FromValues(Number.POSITIVE_INFINITY, Number.POSITIVE_INFINITY, Number.POSITIVE_INFINITY),
                n = vec3FromValues(Number.NEGATIVE_INFINITY, Number.NEGATIVE_INFINITY, Number.NEGATIVE_INFINITY);
            return (
                s.forEach((t) => {
                    vec3Min(r, r, t), vec3Max(n, n, t);
                }),
                [r, n]
            );
        }
        updateBounds() {
            var t, e, i, s, r, n;
            if (!this.aO) return null;
            let min = this.boundsMin,
                max = this.boundsMax;
            return (
                vec3Set(min, 9999, 9999, 999),
                vec3Set(max, -9999, -9999, -9999),
                vec3Min(min, min, null === (
                    i = null === (e = null === (t = this.m) || undefined === t ? undefined : t.d) || undefined === e
                        ? undefined : e.d) || undefined === i
                        ? undefined : i.f
                ),
                vec3Max(max, max, null === (
                    n = null === (r = null === (s = this.m) || undefined === s ? undefined : s.d) || undefined === r
                        ? undefined : r.d) || undefined === n
                        ? undefined : n.c
                ),
                this.aA(min, max, this.am)
            );
        }
        V() {
            const t = this.r;
            if (!this.C) return;
            if (this.aN) {
                vec3Set(this.aH, this.aT, this.aT, this.aT);
                const t = this.aN.b,
                    s = this.aN;
                if (!t.C) return;
                vec3Scale(this.aH, this.aH, s.d),
                    (e = this.am),
                    (i = this.aH),
                    (e[0] = i[0]),
                    (e[1] = 0),
                    (e[2] = 0),
                    (e[3] = 0),
                    (e[4] = 0),
                    (e[5] = i[1]),
                    (e[6] = 0),
                    (e[7] = 0),
                    (e[8] = 0),
                    (e[9] = 0),
                    (e[10] = i[2]),
                    (e[11] = 0),
                    (e[12] = 0),
                    (e[13] = 0),
                    (e[14] = 0),
                    (e[15] = 1),
                    mat4Mult(this.am, s.c, this.am),
                    s.a >= 0 && s.a < t.aw.length && mat4Mult(this.am, t.aw[s.a].i, this.am),
                    mat4Mult(this.am, t.am, this.am);
            }
            var e, i;
            mat4Mult(this.P, this.renderer.viewMatrix, this.am),
                mat4Invert(this.ap, this.P),
                mat4Transpose(this.aj, this.ap),
                this.gradientEffect && this.k();
            let s = 1e3 * this.renderer.delta;
            if (!this.R && this.m.d.e > -1) {
                let e = s;
                for (let i = 0; i < this.F.length; i++) (this.F[i] += e), t.L[i] > 0 && (this.F[i] %= t.L[i]);
                this.I(this.m, e);
            }
            let r = this.aO ? this.aO.length : 0;
            this.S.clear();
            for (let t = 0; t < r; ++t) {
                let e = this.aO[t];
                if (!e.show) continue;
                let i = e.D.b,
                    s = e.D.c;
                for (let t = 0; t < i; ++t) this.S.add(this.r.z[s + t]);
            }
            let n = t.R.length;
            if (this.aw) {
                for (let t = 0; t < n; ++t) this.aw[t].x = false;
                for (let t = 0; t < n; ++t) this.aw[t].r(s);
                this.ab();
            }
            if (
                (this.aO && this.aO.forEach((t) => t.u()),
                this.af &&
                    this.af.sort(function (t, e) {
                        return t.x.j != e.x.j ? t.x.j - e.x.j : t.meshId - e.meshId;
                    }),
                (this.aq = false),
                this.ae && this.l)
            )
                for (let t = 0; t < this.ae.length; ++t) this.ae[t].r(this.m, this.renderer.delta);
            if (this.ak && this.W)
                for (let t = 0; t < this.ak.length; ++t) this.ak[t].ak(this.m, this.renderer.delta), this.ak[t].D();
        }
        aL(t, e) {
            (this.ah[t] = e), (this.aq = true);
        }
        aQ(t, e, i) {
            (this.U = [t, e, i]), (this.aq = true);
        }
        ag(t) {
            this.p = t;
        }
        H(t) {
            this.B = !!t;
        }
        v(t) {
            this.l = !!t;
        }
        t(t) {
            this.W = !!t;
        }
        z(t, e) {
            const i = this;
            if (!i.C) return;
            let s = 100 * e,
                r = s + GeosetDefaults[e] + t,
                n = i.aO.some((t) => t.meshId == r);
            (r = n ? r : 100 * e + 1), i.an(s, s + 99, false), i.an(r, r, true);
        }
        an(t, e, i) {
            const s = this.r;
            if (!this.aO || 0 == this.aO.length) return false;
            for (let s = 0; s < this.aO.length; ++s) {
                const r = this.aO[s];
                r.meshId >= t && r.meshId <= e && (r.show = i);
            }
            if (s.x && s.x.length > 0)
                for (let r = 0; r < s.x.length; ++r) {
                    let n = s.x[r];
                    n >= t && n <= e && (this.ae[r].O = i);
                }
            return true;
        }
        aJ(t, e) {
            if (!this.aO) return;
            const i = e + 1;
            let s = t > 0 ? e + t : -2 == t ? e + 0 : i,
                r = this.aO.some((t) => t.meshId == s);
            (s = r || -2 == t ? s : i), this.an(s, s, true);
        }
        A(t) {
            this.R = t;
        }
        O(t) {
            this.r.y && (this.G(t, this.m), (this.m.b = false), (this.m.d.b = false), (this.m.d.a = 0));
        }
        I(t, e) {
            var i, s, r, n;
            const a = this.r;
            if (((t.d.a += e), Modelviewer.Y && this.aw && this.aw.length > 0 && ((this.o += e), this.o >= Modelviewer.aa))) {
                this.o = 0;
                const e = t.d,
                    i = t.c,
                    s = t.a,
                    r = e.d ? e.d.h : -1,
                    n = e.d ? e.d.i : "none";
                let a = 0,
                    o = 0;
                for (let t = 0; t < this.aw.length; t++) {
                    const i = this.aw[t].c.f;
                    if (i && i.a && e.e < i.a.length) {
                        const t = i.a[e.e];
                        if (t && t.j && t.j.length > 1) {
                            o++;
                            const e = t.j[t.j.length - 1];
                            e > a && (a = e);
                        }
                    }
                }
                console.log(
                    `[ANIM DEBUG] "${n}" time=${e.a.toFixed(1)} dur=${r} idx=${e.e} blend=${t.f.toFixed(3)} crossFade=${t.e.toFixed(3)} nextIdx=${i.e} prevIdx=${s.e} | bones=${this.aw.length} animated=${o} maxKF=${a.toFixed(1)}`
                );
            }
            if (t.c.e < 0 && !this.B && !t.b)
                if (t.d.d.m > -1) {
                    let e = 32767 * Math.random(),
                        i = 0,
                        s = t.d.e,
                        r = a.y[s];
                    for (i += r.e; i < e && r.m > -1; ) (s = r.m), (r = a.y[s]), (i += r.e);
                    (t.c.e = s), (t.c.d = a.y[s]), (t.c.a = 0);
                } else {
                    let e = a.y.find((e) => e.g == t.d.d.g && 0 == e.l);
                    e && ((t.c.e = e.k), (t.c.d = e), (t.c.a = 0));
                }
            let o = t.d,
                h = t.c,
                l = o.d.h - o.a,
                u = 0,
                c = null;
            if (
                (h.e > -1 && ((c = a.y[h.e]), (u = c.b)),
                u > 0 && l < u ? ((h.a = zh(u - l, c.h)), (t.f = l / u)) : (t.f = 1),
                t.e > 0)
            ) {
                let i = e / 1e3;
                (t.a.a += e), (t.e -= i / this.renderer.crossFadeDuration);
            }
            if (o.a >= o.d.h) {
                if (
                    Modelviewer.Y &&
                    (console.log(
                        `[ANIM DEBUG] SWITCH: time=${o.a.toFixed(1)} >= dur=${o.d.h} | nextIdx=${h.e} nextName=${null !== (s = null === (i = h.d) || undefined === i ? undefined : i.i) && undefined !== s ? s : "none"} freeze=${o.b}`
                    ),
                    h.e > -1 && this.aw)
                ) {
                    let t = h.e,
                        e = o.e,
                        i = [];
                    for (let s = 0; s < this.aw.length; s++) {
                        const a = this.aw[s],
                            o = a.c.f;
                        let h = o.e(e),
                            l = o.e(t),
                            u = o.e(0);
                        h &&
                            !l &&
                            i.push(
                                `bone[${s}] flags=0x${a.c.e.toString(16)} parent=${a.c.i} keyId=${a.c.g} fallback0=${u} dataLen=${null !== (n = null === (r = o.a) || undefined === r ? undefined : r.length) && undefined !== n ? n : 0}`
                            );
                    }
                    i.length > 0 && console.log(`[ANIM DEBUG] BONES LOSING DATA (${i.length}):`, i.join(" | "));
                }
                if (h.e > -1 && !o.b) {
                    if (h.e > -1)
                        for (
                            ;
                            !(32 & a.y[h.e].a) &&
                            (64 & a.y[h.e].a) > 0 &&
                            ((h.e = a.y[h.e].k), (h.d = a.y[h.e]), !(h.e < 0));

                        );
                    (t.d = h), (t.c = new ur()), (t.f = 1);
                } else o.d.h > 0 && (o.a = zh(o.a, o.d.h));
            }
        }
        ac(t, e = true) {
            this.G(t, this.m, e);
        }
        G(t, e, i = true) {
            const s = this.r;
            let r = false,
                n = false;
            const a = t == Hh;
            a && ((t = Nh), this.H(true));
            for (let o = 0; o < s.y.length; ++o) {
                const h = s.y[o];
                if (h.i && h.i == t && 0 == h.l) {
                    (r = true),
                        i &&
                            null != e.d &&
                            (null != e.a && (e.e = 1),
                            (e.a = new ur()),
                            (e.a.e = e.d.e),
                            (e.a.d = e.d.d),
                            (e.a.a = e.d.a)),
                        (n = e.d.e != o),
                        (e.d.e = o),
                        (e.d.d = h),
                        (e.d.a = 0),
                        (e.c = new ur()),
                        (e.f = 0),
                        (e.b = a),
                        WH.debug("Set animation to", h.g, h.i);
                    break;
                }
            }
            return t == Nh || r ? n : this.G(Nh, e);
        }
        aR(t) {
            this.h = t;
        }
        av(t) {
            const e = this.r;
            let i = null;
            if (!e.B || !e.B.length) return null;
            if (t < e.B.length) i = e.d[e.B[t]];
            else
                for (let t = 0; t < e.B.length; t++) {
                    const s = e.B[t];
                    if (-1 != s) {
                        i = e.d[s];
                        break;
                    }
                }
            return i;
        }
        get gradientEffect() {
            return this.y;
        }
        set gradientEffect(t) {
            (this.aq = true), (this.y = t), this.aS();
        }
        aG(t) {
            if (this.aN) {
                const t = this.aN.b;
                if (t && !t.C) return;
            }
            if (this.j && this.af)
                if (this.gradientEffect) {
                    if (t) for (let t = 0; t < this.af.length; ++t) this.af[t].show && this.af[t].o(false, true);
                    for (let e = 0; e < this.af.length; ++e) this.af[e].show && this.af[e].o(t, false);
                } else for (let e = 0; e < this.af.length; ++e) this.af[e].show && this.af[e].o(t, false);
            if (this.ae && this.l) for (let e = 0; e < this.ae.length; ++e) this.ae[e].y(t);
            if (this.ak && this.W) for (let e = 0; e < this.ak.length; ++e) this.ak[e].ad(t);
        }
        aP(t) {
            if (this.aF == t) return;
            if (this.C) for (let t = 0; t < this.aw.length; t++) this.aw[t].p = null;
            if (((this.aF = t), t <= 0)) return;
            let e = this.renderer.options.contentPath + "bone/" + t + ".bone",
                i = this;
            $.ajax({
                url: e,
                type: "GET",
                dataType: "binary",
                responseType: "arraybuffer",
                processData: false,
                renderer: this.renderer,
                success: function (t) {
                    i.q(t);
                },
                error: function (t, e, i) {
                    console.log(i);
                },
            });
        }
        q(t) {
            let e = new DataView(t);
            e.getInt32();
            for (; e.position < e.buffer.byteLength; ) {
                let t = String.fromCharCode(e.getUint8(), e.getUint8(), e.getUint8(), e.getUint8()),
                    i = e.getUint32();
                if ("BIDA" == t) {
                    let t = i / 2;
                    this.K = new Array(t);
                    for (let i = 0; i < t; i++) this.K[i] = e.getUint16();
                }
                if ("BOMT" == t) {
                    let t = i / 64;
                    this.T = new Array(t);
                    for (let i = 0; i < t; i++) {
                        let t = mat4FromValues(
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat(),
                            e.getFloat()
                        );
                        this.T[i] = t;
                    }
                }
            }
            this.C && this.E();
        }
        E() {
            if (!(this.aF <= 0) && this.K && this.K.length)
                for (let t = 0; t < this.K.length; t++) this.aw[this.K[t]].p = this.T[t];
        }
        aS() {
            if (!this.gradientEffect) return;
            const t = this.gradientEffect,
                e = this.M;
            (e.u_gradGradientColors_0 = [...t.Colors0, 0]),
                (e.u_gradGradientColors_1 = [...t.Colors1, 0]),
                (e.u_gradGradientColors_2 = [...t.Colors2, t.Alpha[0]]),
                (e.u_gradEdgeColor = [...t.EdgeColor, t.Alpha[1]]),
                (e.u_gradBoundingBox = [this.aK[0], this.aK[1], this.aK[2], 1 / (this.boundsMax[2] - this.boundsMin[2])]),
                (e.u_gradUpVec = [this.aH[0], this.aH[1], this.aH[2], 0]),
                (e.u_gradFlags = [
                    (1 & t.gradFlags) > 0 ? 1 : 0,
                    0.7,
                    (4 & t.gradFlags) > 0 ? 1 : 0,
                    (8 & t.gradFlags) > 0 ? 1 : 0,
                ]);
        }
        k() {
            if (!this.gradientEffect) return;
            this.gradientEffect;
            const t = this.M;
            Yi(this.aK, this.boundsMin[2], this.boundsMin[2], this.boundsMin[2], 1),
                ts(this.aK, this.aK, this.P),
                Yi(this.b, 0, 0, 1, 0),
                ts(this.b, this.b, this.aj),
                vec3Set(this.aH, this.b[0], this.b[1], this.b[2]),
                vec3Normalize(this.aH, this.aH),
                (t.u_gradBoundingBox[0] = this.aK[0]),
                (t.u_gradBoundingBox[1] = this.aK[1]),
                (t.u_gradBoundingBox[2] = this.aK[2]),
                (t.u_gradBoundingBox[3] = 1 / (this.boundsMax[2] - this.boundsMin[2])),
                (t.u_gradUpVec[0] = this.aH[0]),
                (t.u_gradUpVec[1] = this.aH[1]),
                (t.u_gradUpVec[2] = this.aH[2]);
        }
        ad(t) {
            let e = Zs();
            if ((Ks(e, t), this.ae)) for (let i = 0; i < this.ae.length; i++) this.ae[i].b(t, e);
            if (this.ak) for (let e = 0; e < this.ak.length; e++) this.ak[e].F(t);
        }
        c() {
            return this.aN;
        }
        ay(t) {
            if (this.ae) for (let e = 0; e < this.ae.length; e++) this.ae[e].B(t);
            this.as = t;
        }
    }
    Modelviewer.Y = true,
    Modelviewer.aa = 500;


    class jh {
        static c(t, e, i) {
            const s = RaceFallbacks[e];
            if (s) {
                const e = i ? 4 : 0;
                return s.slice(2 * t + e, 2 * t + e + 2);
            }
        }
        static a(t, e, i, s) {
            let r = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            if (!t) return WH.debug("selectBestTexture:", "textures are null"), null;
            for (let n = 0; n < t.length; n++) {
                let a = t[n],
                    o = a.Gender,
                    h = a.Class,
                    l = a.Race,
                    u = a.ExtraData,
                    c = 0;
                if (e > 1 || o != e) {
                    if (o < 2) continue;
                    c = 0;
                } else c = 2;
                let d = 1;
                if (i > 0 && h == i) d = 0;
                else if (h > 0) continue;
                let f = 1;
                if (s > 0 && l == s) f = 0;
                else if (l > 0) continue;
                r[u + 3 * (f + 2 * (c + d))] = a.FileDataId;
            }
            for (let t = 0; t < 2; t++)
                for (let e = 0; e < 2; e++)
                    for (let i = 0; i < 2; i++) {
                        let s = 3 * (t + 2 * (e + 2 * i));
                        if (r[s] > 0) {
                            let t;
                            return (t = { a: r[s], c: r[s + 1], b: r[s + 2] }), t;
                        }
                    }
            const n = jh.c(e, s, true);
            return n && 0 != n[0] ? ((s = n[0]), (e = n[1]), jh.a(t, e, i, s)) : null;
        }
        static b(t, e, i, s, r) {
            let n = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            for (let a = 0; a < t.length; a++) {
                let o = t[a],
                    h = o.Gender,
                    l = o.Class,
                    u = o.Race,
                    c = o.ExtraData,
                    d = 0;
                if (i > 1 || h != i) {
                    if (h < 2) continue;
                    d = 0;
                } else d = 2;
                let f = 1;
                if (s > 0 && l == s) f = 0;
                else if (l > 0) continue;
                let g = 1;
                if (r > 0 && u == r) g = 0;
                else if (u > 0) continue;
                let _ = 1;
                if (-1 == e || c != e) {
                    if (-1 != c && -1 != e) continue;
                } else _ = 0;
                n[_ + 2 * (g + 2 * (d + f))] = o.FileDataId;
            }
            for (let t = 0; t < 2; t++)
                for (let e = 0; e < 2; e++)
                    for (let i = 0; i < 2; i++)
                        for (let s = 0; s < 2; s++) {
                            let r = s + 2 * (t + 2 * (e + 2 * i));
                            if (n[r]) return n[r];
                        }
            const a = jh.c(i, r, false);
            return a && 0 != a[0] ? ((r = a[0]), (i = a[1]), jh.b(t, e, i, s, r)) : 0;
        }
    }
    class qh {
        constructor() {
            (this.d = false), (this.g = []);
        }
        get loaded() {
            let t = !!this.c && this.c.C;
            if (t && this.g.length > 0) {
                for (let t of this.g) t();
                this.g = [];
            }
            return t;
        }
        isLoaded() {
            return this.loaded;
        }
        b(t) {
            this.g.push(t);
        }
        h() {
            return this.c;
        }
        e(t) {
            t.a(this.c, this.d);
        }
        getNumAnimations() {
            var t;
            return (null === (t = this.c) || undefined === t ? undefined : t.C)
                ? 0 == this.c.r.y.length
                    ? 0
                    : this.c.r.y.length + 1
                : 0;
        }
        getAnimation(t) {
            var e;
            return (null === (e = this.c) || undefined === e ? undefined : e.C) ? this.c.at(t) : "";
        }
        resetAnimation() {
            var t;
            if (null === (t = this.c) || undefined === t ? undefined : t.C) return this.c.w();
        }
        setAnimPaused(t) {
            var e;
            if (null === (e = this.c) || undefined === e ? undefined : e.C) return this.c.A(t);
        }
        setTPose(t) {
            var e;
            if (null === (e = this.c) || undefined === e ? undefined : e.C) return this.c.aR(t);
        }
        setAnimation(t, e) {
            var i;
            (null === (i = this.c) || undefined === i ? undefined : i.C) && this.c.ac(t, !!e);
        }
        setParticlesEnabled(t) {
            var e;
            (null === (e = this.c) || undefined === e ? undefined : e.C) && this.c.v(t);
        }
        setRibbonsEnabled(t) {
            var e;
            (null === (e = this.c) || undefined === e ? undefined : e.C) && this.c.t(t);
        }
        getTexUnits() {
            var t;
            return (null === (t = this.c) || undefined === t ? undefined : t.C) ? this.c.aO : null;
        }
        setAnimNoSubAnim(t) {
            this.c && this.c.H(t);
        }
        attachList(t) {}
        setItems(t) {}
        clearSlots(t) {}
        setSheath(t, e) {}
        setAppearance(t) {}
        setShouldersOverride(t) {}
        setCustomizationsLoadedCallback(t) {}
        setModelLoadedCallback(t) {
            throw new Error("Method not implemented.");
        }
        setAnimationOverride(t, e) {
            throw new Error("Method not implemented.");
        }
        resetAnimationOverride(t) {
            throw new Error("Method not implemented.");
        }
        getAnimationDuration(t) {
            throw new Error("Method not implemented.");
        }
        getModelBounds() {
            throw new Error("Method not implemented.");
        }
        isRenderReady() {
            throw new Error("Method not implemented.");
        }
    }
    class Vh extends qh {
        constructor(t, e, i, s, r, n) {
            super(), (this.l = t), (this.i = e), (this.k = i), (this.m = s), (this.dc = r), (this.d = n);
        }
        get fileDataId() {
            return this.c ? this.c.d : 0;
        }
        get modelInstance() {
            return this.c;
        }
        ba() {
            256 & this.i.Item.Flags && ((this.winding = true), (this.isMirrored = true), this.c.N());
        }
        j(t, e, i) {
            this.c && this.c.g(t, e, i);
        }
        fe(t, e) {
            this.c && this.c.z(t, e);
        }
        hg(t, e, i) {
            this.c && this.c.an(t, e, i);
        }
        setParticlesEnabled(t) {
            this.c && this.c.v(t);
        }
        get winding() {
            return !!this.c && this.c.Z;
        }
        set winding(t) {
            this.c && (this.c.Z = t);
        }
        get isMirrored() {
            return !!this.c && this.c.isMirrored;
        }
        set isMirrored(t) {
            this.c && (this.c.isMirrored = t);
        }
        getBounds() {
            return this.modelInstance.C ? this.modelInstance.a() : [null, null];
        }
        a() {
            this.c && this.c.V();
        }
        f(t) {
            this.c && this.c.aG(t);
        }
    }
    class Wh extends Vh {
        constructor(t, e, i, s) {
            if ((super(t, null, 0, 0, 0, s), (this.c = new Modelviewer(t, t.renderer, e)), i))
                for (let e in i) 0 != i[e] && this.c.aL(+e, t.getTexture(i[e]));
        }
    }
    class Xh {
        constructor() {
            this.b = false;
        }
    }
    const Yh = class {
        constructor(t, e) {
            (this.c = t), (this.k = []), (this.i = false), (this.e = false), (this.j = e);
        }
        a(t) {
            for (let e = 0; e < this.j.length; e++)
                this.k[e] && this.k[e].c && this.k[e].c.loaded && this.k[e].c.h().ac(t);
        }
        d(t) {
            this.i = t;
        }
        f() {
            if (this.c.loaded)
                for (let t = 0; t < this.j.length; t++)
                    switch (this.j[t].EffectType) {
                        case 1:
                            this.h(t);
                            break;
                        case 2:
                            this.b(t);
                            break;
                        case 6:
                        case 7:
                        case 11:
                        case 12:
                        case 13:
                            break;
                        case 16:
                            this.l(t);
                    }
        }
        h(t) {
            let e = this.c.h();
            if (1 == this.j[t].ProcEffectType) {
                let i = this.j[t].Value[0];
                e.ar = Wi(((i >> 16) & 255) / 255, ((i >> 8) & 255) / 255, (255 & i) / 255, e.ar[3]);
            } else if (14 == this.j[t].ProcEffectType) {
                let i = Math.min(Math.max(this.j[t].Value[0], 0), 1);
                e.ar[3] = i;
            } else if (22 == this.j[t].ProcEffectType) {
                let i = this.j[t].Value[3];
                e.ar = Wi(((i >> 16) & 255) / 255, ((i >> 8) & 255) / 255, (255 & i) / 255, e.ar[3]);
            }
        }
        b(t) {
            if (!this.c) return;
            if (!this.c.loaded) return;
            let e = this.c.h();
            if (!this.k[t]) {
                const i = new Xh();
                if (((this.k[t] = i), 0 == this.j[t].ModelType)) {
                    const s = new Wh(e.renderer, this.j[t].Model, { 2: this.j[t].Texture }, false);
                    i.c = s;
                } else
                    1 == this.j[t].ModelType ||
                        (2 == this.j[t].ModelType &&
                            fl.a(e.renderer, Types.NPC, this.j[t].Model).then((t) => {
                                i.c = t;
                            }));
            }
            const i = this.k[t];
            if (!i.b) {
                if (!i.c && !i.a) return;
                if (i.c && !i.c.loaded) return;
                if (i.a && !i.a.f) return;
                let b = this.j[t].AttachmentID;
                this.j[t].Positioner > -1 && (b = this.j[t].Positioner), b < 0 && (b = 19);
                let m = e.av(b);
                const p = m ? m.c : -1;
                let x = mat4Create();
                if (m) {
                    let t = m.b;
                    mat4Translate(x, x, vec3FromValues(t[0], t[1], t[2]));
                }
                if (
                    (mat4Translate(x, x, vec3FromValues(this.j[t].Offset[0], -this.j[t].Offset[1], this.j[t].Offset[2])),
                    mat4RotY(x, x, -this.j[t].Yaw),
                    (s = x),
                    (r = x),
                    (n = this.j[t].Pitch),
                    (a = Math.sin(n)),
                    (o = Math.cos(n)),
                    (h = r[0]),
                    (l = r[1]),
                    (u = r[2]),
                    (c = r[3]),
                    (d = r[8]),
                    (f = r[9]),
                    (g = r[10]),
                    (_ = r[11]),
                    r !== s &&
                        ((s[4] = r[4]),
                        (s[5] = r[5]),
                        (s[6] = r[6]),
                        (s[7] = r[7]),
                        (s[12] = r[12]),
                        (s[13] = r[13]),
                        (s[14] = r[14]),
                        (s[15] = r[15])),
                    (s[0] = h * o - d * a),
                    (s[1] = l * o - f * a),
                    (s[2] = u * o - g * a),
                    (s[3] = c * o - _ * a),
                    (s[8] = h * a + d * o),
                    (s[9] = l * a + f * o),
                    (s[10] = u * a + g * o),
                    (s[11] = c * a + _ * o),
                    mat4RotX(x, x, this.j[t].Roll),
                    mat4Scale(x, x, [this.j[t].Scale1, this.j[t].Scale1, this.j[t].Scale1]),
                    mat4Scale(x, x, [this.j[t].Scale2, this.j[t].Scale2, this.j[t].Scale2]),
                    i.c)
                ) {
                    const e = i.c.h();
                    e.A(this.i), this.j[t].ModelAlpha && (e.ar[3] = this.j[t].ModelAlpha), e.g(this.c.h(), p, x);
                }
                this.k[t].b = true;
            }
            var s, r, n, a, o, h, l, u, c, d, f, g, _;
            this.k[t].c && this.k[t].c.a(), this.k[t].a && this.k[t].a.r();
        }
        l(t) {
            const e = this.c.h();
            e.gradientEffect || (e.gradientEffect = this.j[t]);
        }
        g(t) {
            for (const e of this.k) e && e.b && (e.c && e.c.f(t), e.a && e.a.v(t));
        }
        n() {
            for (const t of this.k)
                t && ((t.b = false), t.c && t.c.loaded && t.c.h().g(null, -1, null), t.a && t.a && t.a.t());
        }
        m(t) {
            this.k.forEach((e) => {
                e.c && e.c.e(t), e.a && e.a.c(t);
            });
        }
    };
    class Zh extends qh {
        constructor(t, e) {
            super(),
                (this.i = t),
                (this.C = e),
                (this.n = false),
                (this.l = false),
                (this.hg = false),
                (this.u = -1),
                (this.B = -1),
                (this.z = []),
                (this.m = {}),
                (this.s = []),
                (this.y = false),
                (this.dc = null),
                (this.ba = 0),
                this.j(e);
        }
        j(t) {
            if (this.y) return;
            this.i.options;
            if (
                (t.StateKit && this.s.push(new Yh(this, t.StateKit.effects)),
                t.Creature && ((this.dc = t.Creature.CreatureGeosetData), (this.ba = t.Creature.CreatureGeosetDataID)),
                t.Model &&
                    ((this.c = new Modelviewer(this.i, this.i.renderer, t.Model)),
                    this.c.aB(),
                    t.Creature && t.Creature.ParticleColor && this.c.ay(t.Creature.ParticleColor),
                    t.Scale && this.c.i(t.Scale)),
                this.C.Creature &&
                    this.C.Creature.Texture &&
                    ((this.p = this.t(-1, jh.a(this.C.TextureFiles[this.C.Creature.Texture], 3, 0, 0))),
                    this.c.aQ(this.p.b, this.p.e, this.p.c)),
                t.Textures)
            )
                for (let e in t.Textures) 0 != t.Textures[e] && this.c.aL(+e, this.i.getTexture(t.Textures[e]));
            (this.hg = true), this.v();
        }
        t(t, e) {
            let i = new Hs();
            return (
                e.a > 0 && (i.b = this.i.getTexture(e.a)),
                e.c > 0 && (i.e = this.i.getTexture(e.c)),
                e.b > 0 && (i.c = this.i.getTexture(e.b)),
                i
            );
        }
        v() {
            this.y || ((this.hg = true), this.p || (this.l = true));
        }
        q(t) {
            (this.fe = null),
                t <= 0 ||
                    ((this.n = false),
                    fl.a(this.i, Types.NPC, t).then((t) => {
                        t instanceof Zh && (this.fe = t);
                    }));
        }
        o() {
            this.c, this.fe;
        }
        setAnimation(t, e = true) {
            this.fe && (this.fe.setAnimation(t), (t = "Mount")),
                this.c
                    ? this.c.ac(t, e)
                    : this.b(() => {
                          var i;
                          null === (i = this.c) || undefined === i || i.ac(t, e);
                      });
        }
        A() {
            const t = this.c;
            if ((t.an(0, 0, true), 0 != this.ba && (t.an(1, 1699, false), this.dc)))
                for (let e of this.dc) {
                    let i = 100 * (e.GeosetIndex + 1),
                        s = i + e.GeosetValue;
                    t.an(i, i + 99, false), t.an(s, s, true);
                }
        }
        w() {
            this.A();
        }
        x() {
            const t = this.c;
            t.C && this.hg && t.aO && 0 != t.aO.length && (this.w(), (this.hg = false));
        }
        setParticlesEnabled(t) {
            this.c && this.c.v(t);
        }
        getBounds() {
            if (this.c && this.c.C) {
                const [t, e] = this.c.updateBounds();
                if (this.fe && this.fe.loaded && this.n) {
                    const [i, s] = this.fe.getBounds();
                    vec3Max(i, i, vec3FromValues(0, 0, 0)), vec3Min(t, t, i), vec3Max(e, e, s);
                }
                return [t, e];
            }
            return [null, null];
        }
        a() {
            if (!this.y && this.loaded) {
                if (!this.n && this.c && this.fe && this.fe.loaded) {
                    const t = this.fe.c.r,
                        e = t.d[t.B[0]],
                        i = mat4Create();
                    mat4Translate(i, i, e.b), this.c.g(this.fe.c, e.c, i, 1 / this.fe.c.aT), this.c.ac("Mount", false), (this.n = true);
                }
                this.k && this.k.a(),
                    this.fe && this.fe.a(),
                    this.s && this.s.forEach((t) => t.f()),
                    this.x(),
                    this.c.V();
            }
        }
        f(t) {
            this.c.aG(t), this.fe && this.fe.f(t), this.s && this.s.forEach((e) => e.g(t));
        }
        e(t) {
            super.e(t), this.fe && this.fe.e(t), this.k && this.k.e(t), this.s && this.s.forEach((e) => e.m(t));
        }
    }
    function Kh(t) {
        return new Promise((e, i) => {
            $.getJSON(t)
                .done(function (t) {
                    e(t);
                })
                .fail(function (t, e, s) {
                    let r = e + ", " + s;
                    console.log("Error loading metadata: " + r), i(e);
                });
        });
    }
    function $h(t, e, i) {
        let s;
        return e == Types.HELM
            ? Jh(t, 1, i)
            : e == Types.SHOULDER
              ? Jh(t, 3, i)
              : e == Types.ITEM
                ? Jh(t, -1, i)
                : (e == Types.NPC || e == Types.HUMANOIDNPC
                      ? (s = "meta/npc/")
                      : e == Types.OBJECT
                        ? (s = "meta/object/")
                        : e == Types.CHARACTER
                          ? (s = "meta/character/")
                          : e == Types.ITEMVISUAL && (s = "meta/itemvisual/"),
                  Kh(t + s + i + ".json"));
    }
    function Jh(t, e, i) {
        let s = "meta/item/";
        return (
            (1 != e &&
                3 != e &&
                4 != e &&
                5 != e &&
                6 != e &&
                7 != e &&
                8 != e &&
                9 != e &&
                10 != e &&
                16 != e &&
                19 != e &&
                20 != e) ||
                (s = "meta/armor/" + e + "/"),
            Kh(t + s + i + ".json")
        );
    }
    class Qh {
        constructor() {
            (this.d = null), (this.c = 1), (this.a = 0), (this.e = -1), (this.b = false);
        }
    }
    class tl {
        constructor(t, e) {
            (this.a = t), (this.b = e);
        }
    }
    class el extends Qh {
        constructor() {
            super(...arguments), (this.ba = []);
        }
    }
    class il {
        constructor(t, e) {
            (this.a = t), (this.b = e);
        }
    }
    function sl(t, e) {
        return (
            t == e ||
            (!!t &&
                !!e &&
                t.b == e.b &&
                (t.a == e.a || (!!t.a && !!e.a && t.a.a == e.a.a && t.a.b == e.a.b && t.a.c == e.a.c)))
        );
    }
    class rl {
        constructor(t, e) {
            (this.h = t), (this.j = []), (this.d = e), (this.f = {}), (this.g = {});
        }
        i() {
            const t = [];
            for (let e of this.d.Options)
                for (let i of e.Choices)
                    for (let e of i.Elements) e.SkinnedModel && t.push(e.SkinnedModel.CollectionFileDataID);
            const e = new Set(t),
                i = this.h.i;
            i.renderer;
            if (0 != e.size)
                for (let t of e) {
                    const e = new el();
                    (e.d = new Wh(i, t, {}, true)), (this.h.N[t] = e);
                }
        }
        c(t) {
            return jh.a(this.d.TextureFiles[t], this.h.ut, this.h.M, this.h.O);
        }
        b(t) {
            WH.debug("applyCustomization options", t), (this.j = []), (this.h.z = []);
            for (const t in this.h.N) {
                this.h.N[t].ba = [];
            }
            let e = 0,
                i = {},
                s = {};
            for (let r = 0; r < t.length; r++) {
                let n = this.d.Options.find((e) => e.Id == t[r].optionId);
                if ((WH.debug("option", n), n)) {
                    let a = n.Choices.find((e) => e.Id == t[r].choiceId);
                    if ((WH.debug("choice", a), a)) {
                        let r = a.Elements.filter(
                            (e) =>
                                e.BoneSet &&
                                e.BoneSet.BoneFileDataID &&
                                (0 == e.VariationChoiceID || t.some((t) => t.choiceId == e.VariationChoiceID))
                        );
                        r.length > 0 && (e = r[0].BoneSet.BoneFileDataID);
                        let o = a.Elements.filter(
                            (e) =>
                                e.Material &&
                                (0 == e.VariationChoiceID || t.some((t) => t.choiceId == e.VariationChoiceID))
                        );
                        o.sort((t, e) => e.VariationChoiceID - t.VariationChoiceID),
                            o.forEach((t) => {
                                WH.debug("element material", t);
                                let e = this.c(t.Material.MaterialResourcesID);
                                if (!e)
                                    return void WH.debug("element material: can't get texture files for material", t);
                                let r = this.d.TextureLayers.find(
                                    (e) => e.ChrModelTextureTargetID == t.Material.TextureTarget
                                );
                                if (!r)
                                    return void WH.debug("element material: can't get texture layer for material", t);
                                const n = new il(e, r.TextureType);
                                sl(n, this.g[t.Material.TextureTarget])
                                    ? ((i[t.Material.TextureTarget] = this.f[t.Material.TextureTarget]),
                                      (s[t.Material.TextureTarget] = this.g[t.Material.TextureTarget]))
                                    : ((i[t.Material.TextureTarget] = this.h.t(r.TextureType, e)),
                                      (s[t.Material.TextureTarget] = n));
                            }),
                            a.Elements.filter(
                                (e) =>
                                    e.Geoset &&
                                    (0 == e.VariationChoiceID || t.some((t) => t.choiceId == e.VariationChoiceID))
                            )
                                .sort(
                                    (t, e) =>
                                        t.Geoset.GeosetType - e.Geoset.GeosetType ||
                                        t.Geoset.GeosetID - e.Geoset.GeosetID
                                )
                                .forEach((t) => {
                                    WH.debug("element geoset", t),
                                        this.j.push(100 * t.Geoset.GeosetType + t.Geoset.GeosetID);
                                }),
                            a.Elements.filter(
                                (e) =>
                                    e.SkinnedModel &&
                                    (0 == e.VariationChoiceID || t.some((t) => t.choiceId == e.VariationChoiceID))
                            ).forEach((t) => {
                                WH.debug("element skinnedmodel", t), t.ChrCustItemGeoModifyID;
                                const e = this.h.N[t.SkinnedModel.CollectionFileDataID];
                                t.SkinnedModel.GeosetID < 100 &&
                                    e.ba.push(
                                        new tl(
                                            100 * t.SkinnedModel.GeosetType + t.SkinnedModel.GeosetID,
                                            (1 & t.SkinnedModel.Flags) > 0
                                        )
                                    );
                            });
                        let h = a.Elements.find(
                            (e) =>
                                0 != e.CondModelFileDataId &&
                                (0 == e.VariationChoiceID || t.some((t) => t.choiceId == e.VariationChoiceID))
                        );
                        (24 != n.Id && 353 != n.Id) ||
                            (h && !this.h.overrideModelFile
                                ? (this.h.overrideModelFile = h.CondModelFileDataId)
                                : !h && this.h.overrideModelFile && (this.h.overrideModelFile = 0)),
                            a.Elements.filter(
                                (e) =>
                                    e.ChrCustItemGeoModifyID &&
                                    (0 == e.VariationChoiceID || t.some((t) => t.choiceId == e.VariationChoiceID))
                            ).forEach((t) => {
                                WH.debug("element ChrCustItemGeoModify", t),
                                    this.h && this.h.z.push(t.ChrCustItemGeoModifyID);
                            });
                    }
                }
            }
            if (!this.f[10]) {
                let e = this.d.Options.find((t) => t.Id == this.d.HairStyleOptionId);
                if (e) {
                    let r = e.Choices[1];
                    if (r) {
                        let e = r.Elements.filter(
                            (e) =>
                                e.Material &&
                                10 == e.Material.TextureTarget &&
                                (0 == e.VariationChoiceID || t.some((t) => t.choiceId == e.VariationChoiceID))
                        );
                        if (e.length > 0) {
                            let t = this.c(e[0].Material.MaterialResourcesID);
                            if (t) {
                                const r = new il(t, 0);
                                sl(r, this.g[e[0].Material.TextureTarget])
                                    ? ((i[e[0].Material.TextureTarget] = this.f[e[0].Material.TextureTarget]),
                                      (s[e[0].Material.TextureTarget] = this.g[e[0].Material.TextureTarget]))
                                    : ((i[e[0].Material.TextureTarget] = this.h.t(6, t)),
                                      (s[e[0].Material.TextureTarget] = r));
                            }
                        }
                    }
                }
            }
            (this.f = i), (this.g = s), this.h.h().aP(e), (this.h.l = true);
        }
        a() {
            let t = [];
            for (let e = 0; e < this.d.Options.length; e++) {
                let i = this.d.Options[e];
                if (i) {
                    let e = i.Choices[0];
                    e && t.push({ optionId: i.Id, choiceId: e.Id });
                }
            }
            this.b(t);
        }
        e(t) {
            let e = { options: t, sheathMain: -1, sheathOff: -1 };
            for (let t of this.d.Options)
                e.options.some((e) => e.optionId == t.Id) ||
                    e.options.push({ optionId: t.Id, choiceId: t.Choices[0].Id });
            return e;
        }
    }
    class nl {
        constructor() {
            (this.c = null), (this.a = 1), (this.b = false);
        }
    }
    const al = class {
        constructor(t, e, i) {
            (this.i = t), (this.e = e), (this.h = []), (this.l = false), (this.a = []), i && this.d(i);
        }
        k() {}
        d(t) {
            this.g = t;
            $h(this.i.l.options.contentPath, Types.ITEMVISUAL, t).then((t) => {
                this.j(t);
            });
        }
        j(t) {
            if (((this.h = new Array(7)), t.ItemEffects))
                for (let e = 0; e < t.ItemEffects.length; ++e) {
                    let i = t.ItemEffects[e];
                    if (-1 == i.SubClass || this.e == i.SubClass) {
                        if (i.Model) {
                            const t = new nl();
                            (this.h[i.Slot - 1] = t),
                                (t.c = new Modelviewer(this.i.l, this.i.l.renderer, i.Model)),
                                (t.a = i.Scale && 1 != i.Scale ? i.Scale : 1);
                        }
                        if (i.kit) {
                            const t = new Yh(this.i, i.kit.effects);
                            this.a.push(t);
                        }
                    }
                }
            for (var e = 0; e < this.h.length; ++e)
                t.Equipment[e] &&
                    null == this.h[e] &&
                    ((this.h[e] = new nl()), (this.h[e].c = new Modelviewer(this.i.l, this.i.l.renderer, t.Equipment[e])));
            this.l = true;
        }
        b(t) {
            for (let e = 0; e < this.h.length; e++) {
                const i = this.h[e];
                i && i.b && i.c.aG(t);
            }
        }
        c(t) {
            for (let e = 0; e < this.h.length; e++) {
                const i = this.h[e];
                i && i.b && i.c && i.c.C && t.a(i.c, false);
            }
        }
        f(t, e, i) {
            if (t.b) return;
            if (!i.loaded) return;
            if (!t.c || !t.c.C) return;
            const s = i.modelInstance.r;
            let r = null;
            if (e <= 8 && 6 != e && 7 != e) {
                if (!s.d[e]) return;
                r = s.d[e];
            } else r = i.modelInstance.av(19);
            let n = mat4Create();
            mat4Translate(n, n, r.b), mat4Scale(n, n, vec3FromValues(t.a, t.a, t.a)), t.c.g(i.modelInstance, r.c, n), (t.b = true);
        }
        m() {
            if (this.i.loaded) {
                for (const t of this.a) t && t.f();
                for (let t = 0; t < this.h.length; t++) {
                    const e = this.h[t];
                    e && (this.f(e, t, this.i), e.c.V());
                }
            }
        }
    };
    class ol {
        constructor(t, e, i) {
            (this.I = t),
                (this.k = []),
                (this.f = false),
                (this.s = null),
                (this.q = []),
                (this.w = mat4Create()),
                WH.debug("Creating item", i),
                (this.B = e),
                (this.A = i),
                (this.l = t.O),
                (this.G = t.ut),
                (this.n = t.M),
                (this.h = UniqueSlots[e]),
                (this.u = SlotOrder[e]),
                (this.i = null),
                (this.d = null),
                (this.C = null),
                (this.e = 0),
                (this.o = 0),
                (this.f = false),
                (this.D = false),
                (this.J = 0),
                (this.F = 3),
                (this.g = 0),
                i && this.x();
        }
        y() {
            var t = this;
            if (t.k) {
                for (let e = 0; e < t.k.length; ++e) t.k[e] && ((t.k[e].d = null), (t.k[e] = null));
                t.k = null;
            }
            if (t.i) {
                for (let e = 0; e < t.i.length; ++e)
                    t.i[e].texture && t.i[e].texture.j(), (t.i[e].texture = null), (t.i[e] = null);
                t.i = null;
            }
            if (((t.d = null), (t.C = null), t.q)) {
                for (let e = 0; e < t.q.length; e++) t.q[e].k();
                t.q = null;
            }
            (t.f = false), WH.debug("Destroyed item", this.A);
        }
        x() {
            let t = this,
                e = this.I.i.options;
            WH.debug("Loading item", this.A),
                Jh(e.contentPath, this.B, this.A)
                    .then((t) => {
                        this.E(t);
                    })
                    .catch(() => {
                        t.D = true;
                    });
        }
        E(t) {
            if (!this.I) return void WH.debug("Char model was destroyed before it was loaded", this.A);
            const e = this.I.i,
                i = (e.options, this.G),
                s = this.l,
                r = this.n;
            if (
                ((this.o = t.Item.Flags),
                (this.e = t.Item.InventoryType),
                (this.K = t.Item.ItemClass),
                (this.L = t.Item.ItemSubClass),
                t.ComponentTextures)
            ) {
                this.i = [];
                for (let n in t.ComponentTextures) {
                    const a = parseInt(n),
                        o = jh.a(t.TextureFiles[t.ComponentTextures[n]], i, r, s);
                    if (o) {
                        let t;
                        (t = { region: a, gender: this.G, file: o.a, texture: null }),
                            12 != a
                                ? (t.texture = e.getTexture(o.a))
                                : 16 == this.B && this.I.h().aL(2, e.getTexture(o.a)),
                            this.i.push(t);
                    }
                }
            }
            (this.d = t.Item.GeosetGroup),
                (this.C = t.Item.AttachGeosetGroup),
                (this.g = t.Item.GeosetGroupOverride),
                1 == this.B && (0 == i ? (this.b = t.Item.HideGeosetMale) : (this.H = t.Item.HideGeosetFemale));
            let n = 0;
            if ((3 == this.B ? (n = 2) : SlotType[this.B] != Types.ARMOR && (n = 1), n > 0 && t.ComponentModels))
                for (let i = 0; i < n; ++i) {
                    let s = fl.b(e, t, SlotType[this.B], this.l, this.G, this.n);
                    if ((3 == this.B && s.cba(i + 1), null == s.modelInstance)) continue;
                    const r = new Qh();
                    (r.d = s),
                        (r.a = i),
                        t.Item && t.Item.ParticleColor && r.d.modelInstance.ay(t.Item.ParticleColor),
                        this.k.push(r);
                }
            if ((6 == this.B || 16 == this.B) && t.ComponentModels) {
                let n = 0;
                if ((16 == this.B && (n = 1), t.ComponentModels[n])) {
                    const a = t.ComponentModels[n],
                        o = jh.b(t.ModelFiles[a], -1, i, r, s),
                        h = new Qh(),
                        l = 0 == n ? t.Textures : t.Textures2;
                    (h.d = new Wh(e, o, l, false)), (this.k = [h]);
                }
            }
            const a = this.B;
            if (
                (4 == a ||
                    5 == a ||
                    20 == a ||
                    6 == a ||
                    7 == a ||
                    10 == a ||
                    8 == a ||
                    1 == a ||
                    9 == a ||
                    19 == a ||
                    16 == a) &&
                t.ComponentModels
            ) {
                let n = 0;
                if (((1 != a && 6 != a) || (n = 1), t.ComponentModels[n])) {
                    const a = t.ComponentModels[n];
                    if (a && t.ModelFiles && t.ModelFiles[a]) {
                        const o = jh.b(t.ModelFiles[a], -1, i, r, s);
                        if (o) {
                            const i = 0 == n ? t.Textures : t.Textures2;
                            (this.s = new Qh()),
                                (this.s.d = new Wh(e, o, i, true)),
                                this.s.d.b(() => {
                                    this.I.hg = true;
                                });
                        }
                    }
                }
            }
            7 == a && this.d[2] > 0 && (this.u += 2);
            const o = 0 != this.J ? this.J : 0 != t.Item.ItemVisual ? t.Item.ItemVisual : 0;
            if (0 != o) {
                const t = 2 == this.K ? this.L : -1;
                for (let e = 0; e < this.k.length; e++) this.q.push(new al(this.k[e].d, t, o));
            }
            (this.f = true), WH.debug("Loaded item:", "DisplayId", this.A, "InventoryType", this.e), (this.I.hg = true);
        }
        j(t) {
            for (let t = 0; t < this.q.length; t++) this.q[t].k();
            (this.q = []), (this.J = t);
        }
        p(t) {
            this.F = t;
        }
        m(t) {
            if (3 == this.B) {
                const e = t.d.shoulderIndex;
                if (1 == e && !(1 & this.F)) return true;
                if (2 == e && !(2 & this.F)) return true;
            }
            return false;
        }
        v(t) {
            for (let e = 0; e < this.q.length; ++e) this.q[e] && this.q[e].b(t);
            for (let e = 0; e < this.k.length; ++e) {
                const i = this.k[e];
                if (i && i.d) {
                    if (this.m(i)) continue;
                    i.d.f(t);
                }
            }
        }
        t() {
            if (this.k)
                for (let t = 0; t < this.k.length; ++t)
                    (this.k[t].b = false), this.k[t].d && this.k[t].d.j(null, -1, null);
            this.s && (this.s.b = false);
        }
        a(t, e, i) {
            if (!t) return;
            if (!t.d) return;
            if (!t.d.modelInstance.C) return;
            const s = t.a;
            if (s < i.length) {
                let r = e.d[i[s]];
                if (t.b && r.c == t.e) return;
                let n = false,
                    a = ReversedModels[t.d.fileDataId],
                    o = vec3Create(),
                    h = mat4Create();
                if (
                    (mat4Identity(h),
                    a && (vec3Set(o, 1, 1, -1), mat4Scale(h, h, o), (n = true)),
                    (22 == this.B || 23 == this.B || 22 == this.h) &&
                        256 & this.o &&
                        (vec3Set(o, 1, -1, 1), mat4Scale(h, h, o), (n = true), (t.d.isMirrored = true)),
                    13 == this.B && 1024 & this.o && (vec3Set(o, 1, -1, 1), mat4Scale(h, h, o), (n = true), (t.d.isMirrored = true)),
                    (t.d.winding = n),
                    5 == this.I.u && 26 == this.B && 2 == this.K && 18 == this.L && (mat4Identity(h), mat4RotX(h, h, -Math.PI / 2)),
                    mat4Translate(h, h, r.b),
                    mat4Mult(h, h, this.w),
                    27 == this.B)
                ) {
                    let e = t.d.i.Scale;
                    vec3Set(o, e, e, e), mat4Scale(h, h, o);
                }
                t.d.j(this.I.h(), r.c, h), (t.b = true), (t.e = r.c);
            }
            t.b = true;
        }
        z(t) {
            mat4Copy(this.w, t);
            for (let t = 0; t < this.k.length; ++t) this.k[t].b = false;
        }
        r() {
            if (!this.I.loaded) return;
            const t = this.I.h().r,
                e = this.I.D(this.h, this);
            for (let i = 0; i < this.k.length; ++i) this.a(this.k[i], t, e), this.q[i] && this.q[i].m();
            this.s && this.I.Az(this.s);
            for (let t = 0; t < this.k.length; ++t) {
                const e = this.k[t];
                if (e && e.d) {
                    if (this.m(e)) continue;
                    e.d.a();
                }
            }
        }
        c(t) {
            this.k.forEach((e) => {
                e.d.e(t);
            }),
                this.s && this.s.d.e(t);
        }
    }
    class hl extends Zh {
        constructor(t, e) {
            super(t, e),
                (this.ml = new Map()),
                (this.G = []),
                (this.N = {}),
                (this.K = null),
                t.options.charCustomization && (this.P = t.options.charCustomization),
                (this.H = new Array(52));
            for (let t = 0; t < 52; t++) this.H[t] = 100 * t + GeosetDefaults[t];
        }
        get overrideModelFile() {
            return this.ed;
        }
        set overrideModelFile(t) {
            const e = this.ed;
            (this.ed = t), e != t && (this.cba(), this.CB(), (this.hg = true));
        }
        cba() {
            let t = this.ed ? this.ed : this.E.Model;
            (this.c = new Modelviewer(this.i, this.i.renderer, t)),
                this.c.aB(),
                this.c.ag(27 == this.O || 30 == this.O),
                (this.K = null),
                (this.hg = true);
        }
        j(t) {
            const e = this.i.options;
            (this.O = t.Character.Race), (this.ut = t.Character.Gender), (this.M = e.cls ? e.cls : 0);
            const i = e && e.items;
            $h(e.contentPath, Types.CHARACTER, t.Character.ChrModelId).then((s) => {
                var r, n;
                (this.E = s),
                    this.cba(),
                    ((r = e.contentPath),
                    (n = t.Character.ChrModelId),
                    new Promise((t, e) => {
                        const i = r + "meta/charactercustomization/" + n + ".json";
                        $.getJSON(i, function (e) {
                            t(e);
                        });
                    })).then((e) => {
                        var s, r;
                        if (
                            (WH.debug("Got customization data v2", e),
                            (this.ih = new rl(this, e)),
                            null === (s = this.on) || undefined === s || s.call(this, this.ih.d),
                            this.ih.i(),
                            this.P)
                        )
                            this.setAppearance(this.P);
                        else if (
                            t.Character.Race > 0 &&
                            (null === (r = null == t ? undefined : t.Creature) || undefined === r
                                ? undefined
                                : r.CreatureCustomizations)
                        ) {
                            let e = this.ih.e(t.Creature.CreatureCustomizations);
                            this.setAppearance(e);
                        } else this.ih.a();
                        this.l && this.v(), t.Equipment && this.F(t.Equipment), i && this.F(i);
                    });
            }),
                (this.l = true);
        }
        CB() {
            for (const [t, e] of this.ml) e.t();
            for (const t in this.N) {
                this.N[t].b = false;
            }
            for (const t of this.s) t.n();
        }
        wv(t, e, i) {
            if (!this.ml) return;
            if (3 == t && this.G && this.G[0]) return;
            let s = new ol(this, t, e);
            i && s.j(i);
            let r = s.h,
                n = SlotAlternate[t];
            this.ml.get(r) && 0 != n ? ((s.h = n), this.ml.set(n, s)) : this.ml.set(r, s);
        }
        yx(t) {
            var e = this.ml.get(t);
            e || ((t = UniqueSlots[t]), (e = this.ml.get(t))), e && (this.ml.delete(t), e.y());
        }
        D(t, e) {
            const i = this.c.r,
                s = [],
                r = { 14: (t) => [0], 26: (t) => (2 == t.K && 18 == t.L ? [1] : null) };
            if (i.d && i.B) {
                const n = {
                    1: (t) => [11],
                    3: (t) => [6, 5],
                    22: (t) => {
                        var e;
                        return (null === (e = r[t.B]) || undefined === e ? undefined : e.call(r, t)) || [2];
                    },
                    21: (t) => [1],
                    17: (t) => [1],
                    15: (t) => [2],
                    25: (t) => [1],
                    13: (t) => [1],
                    14: (t) => [0],
                    23: (t) => [2],
                    6: (t) => [53],
                    26: (t) => [1],
                    16: (t) => [57],
                    27: (t) => [55],
                };
                if (n[t]) {
                    const r = n[t](e);
                    for (let n = 0; n < r.length; ++n) {
                        let a = r[n];
                        (this.u >= 0 || this.B >= 0 || this.fe) && sheathStandardOverrides[t] && (a = sheathStandardOverrides[t]),
                            this.u >= 0 && 21 == t && SheathWeaponOverrides[this.u][t] && (a = SheathWeaponOverrides[this.u][t]),
                            this.B >= 0 && 22 == t && SheathWeaponOverrides[this.B][t] && (a = SheathWeaponOverrides[this.B][t]),
                            15 == e.e && this.B >= 0 && 22 == t && SheathWeaponOverrides[this.B][e.B] && (a = SheathWeaponOverrides[this.B][e.B]),
                            a >= i.B.length || -1 == i.B[a] || s.push(i.B[a]);
                    }
                }
            }
            return s;
        }
        F(t) {
            if ($.isArray(t)) for (let e = 0; e < t.length; ++e) this.wv(t[e][0], t[e][1], t[e][2]);
            else for (let e in t) this.wv(parseInt(e), t[e]);
            (this.hg = true), this.qp();
        }
        kj(t, e, i) {
            for (const s in this.N) {
                this.N[s].d.hg(t, e, i);
            }
        }
        w() {
            var t;
            const e = this.c;
            for (let t = 0; t < 52; t++) this.H[t] = 100 * t + GeosetDefaults[t];
            for (const e of (null === (t = this.ih) || undefined === t ? undefined : t.j) || [])
                e >= 0 && (this.H[Math.floor(e / 100)] = e);
            for (const t in this.N) {
                const e = this.N[t].ba,
                    i = this.N[t].d;
                i.hg(0, Ps, false);
                for (const t of e) i.hg(t.a, t.a, true), (this.H[Math.floor(t.a / 100)] = t.a);
            }
            e.an(0, Ps, false), e.an(0, 0, true);
            for (let t = 0; t < this.H.length; t++) e.an(this.H[t], this.H[t], true);
            let i = this.ml.get(1),
                s = this.ml.get(3),
                r = this.ml.get(4),
                n = this.ml.get(5),
                a = this.ml.get(6),
                o = this.ml.get(7),
                h = this.ml.get(8),
                l = this.ml.get(9),
                u = this.ml.get(10),
                c = this.ml.get(19),
                d = this.ml.get(16),
                f = false,
                g = false;
            n && n.d && n.d[2] ? (g = true) : o && o.d && o.d[2] && (f = true);
            let _ = g || f;
            this.ml.forEach((t) => {
                if (t && t.f && t.s) {
                    let e = t.s.d.modelInstance;
                    if (!e.C) return;
                    e.an(0, Ps, false),
                        1 == t.B
                            ? (e.aJ(t.d[0], 2700), e.aJ(t.d[1], 2100))
                            : 3 == t.B
                              ? e.aJ(t.d[0], 2600)
                              : 4 == t.B
                                ? (e.aJ(t.d[0], 800), e.aJ(t.d[1], 1e3))
                                : 5 == t.B || 20 == t.B
                                  ? (u && u.f && u.d[0] ? e.aJ(-2, 800) : e.aJ(t.d[0], 800),
                                    e.aJ(t.d[1], 1e3),
                                    _ && e.aJ(t.d[2], 1300),
                                    e.aJ(t.d[3], 2200),
                                    e.aJ(t.d[4], 2800))
                                  : 6 == t.B
                                    ? e.aJ(t.d[0], 1800)
                                    : 7 == t.B
                                      ? (e.aJ(t.d[0], 1100), e.aJ(t.d[1], 900), _ && e.aJ(t.d[2], 1300))
                                      : 8 == t.B
                                        ? (e.aJ(t.d[0], 500), e.aJ(t.d[1], 2e3))
                                        : 10 == t.B
                                          ? (0 == t.d[0] && n && n.f && n.d[0] ? e.aJ(-2, 400) : e.aJ(t.d[0], 400),
                                            e.aJ(t.d[1], 2300))
                                          : 16 == t.B
                                            ? e.aJ(t.d[0], 1500)
                                            : 19 == t.B
                                              ? e.aJ(t.d[0], 1200)
                                              : 9 == t.B &&
                                                ((u && u.f && u.d[0]) ||
                                                null != (null == u ? undefined : u.s) ||
                                                (n && n.f && n.d[2] && n.d[0] > 0)
                                                    ? e.aJ(-2, 2300)
                                                    : e.aJ(t.d[0], 2300));
                }
            }),
                this.G.forEach((t) => {
                    if (t && t.s) {
                        const e = t.s.d.modelInstance;
                        e.an(0, Ps, false), e.aJ(t.d[0], 2600);
                    }
                }),
                this.ml.forEach((t) => {
                    if (t && t.f && t.k)
                        for (let e of t.k) {
                            if (!e) continue;
                            let i = e.d;
                            1 == t.B
                                ? (i.fe(t.C[0], 27), i.fe(t.C[1], 21))
                                : 3 == t.B
                                  ? i.fe(t.C[0], 26)
                                  : 4 == t.B
                                    ? (i.fe(t.C[0], 8), i.fe(t.C[1], 10))
                                    : 5 == t.B || 20 == t.B
                                      ? (i.fe(t.C[0], 8),
                                        i.fe(t.C[1], 10),
                                        i.fe(t.C[2], 13),
                                        i.fe(t.C[3], 22),
                                        i.fe(t.C[4], 28))
                                      : 6 == t.B
                                        ? i.fe(t.C[0], 18)
                                        : 7 == t.B
                                          ? (i.fe(t.C[0], 11), i.fe(t.C[1], 9), i.fe(t.C[2], 13))
                                          : 8 == t.B
                                            ? (i.fe(t.C[0], 5), i.fe(t.C[1], 20))
                                            : 10 == t.B
                                              ? (i.fe(t.C[0], 4), i.fe(t.C[1], 23))
                                              : 16 == t.B
                                                ? i.fe(t.C[0], 15)
                                                : 19 == t.B
                                                  ? i.fe(t.C[0], 12)
                                                  : 9 == t.B && i.fe(t.C[0], 23);
                        }
                }),
                this.G.forEach((t) => {
                    if (t && t.k)
                        for (let e of t.k) {
                            let i = e.d;
                            i.fe(t.C[0], 26), t.g > 0 && (i.hg(2600, 2699, false), i.fe(t.g, 26));
                        }
                });
            if (i && i.f) {
                const t = i.s || i.k[0],
                    s = this.O,
                    r = 0 == this.ut ? i.b : i.H;
                if (t && r)
                    for (let t = 0; t < r.length; t++)
                        if (r[t].RaceId == s) {
                            const i = r[t].GeosetGroup;
                            if (5 == s && (1 == i || 2 == i)) continue;
                            if (i < 52)
                                if (0 == i) e.an(1, 99, false);
                                else {
                                    const t = 100 * i;
                                    e.an(t, t + 99, false);
                                }
                        }
            }
            if (i && i.k && i.g > 0)
                for (let t of i.k) {
                    let e = t.d;
                    e.hg(2600, 2799, false), e.fe(i.g, 27);
                }
            if (s && s.k && s.g > 0)
                for (let t of s.k) {
                    let e = t.d;
                    e.hg(2600, 2699, false), e.fe(s.g, 26);
                }
            if (a && a.k && a.g > 0)
                for (let t of a.k) {
                    let e = t.d;
                    e.hg(1800, 1899, false), e.fe(a.g, 18);
                }
            let b = 0;
            if ((c && (b |= 16), u && u.f && u.d && u.d[0])) {
                let t = 401 + u.d[0];
                e.an(401, 499, false), e.an(t, t, true);
            } else if (n && n.f && n.d && n.d[0]) {
                let t = 801 + n.d[0];
                e.an(t, t, true),
                    u && u.d && 0 == u.d[0] && ((u.u = 7), (n.u = 8), WH.debug("updating sorting for chest/gloves"));
            }
            if (!(n || a || l) && r && r.f && r.d && r.d[0]) {
                let t = 801 + r.d[0];
                e.an(t, t, true);
            }
            if (c && c.f) 1048576 & c.o || (e.an(2200, 2299, false), e.an(2202, 2202, true));
            else if (n && n.f && n.d && n.d[3]) {
                let t = 2201 + n.d[3];
                e.an(2200, 2299, false), e.an(t, t, true);
            }
            let m,
                p = false;
            if ((a && a.f && a.d && a.d[0] && (p = !!(512 & a.o)), g)) {
                e.an(501, 599, false), e.an(902, 999, false), e.an(1100, 1199, false), e.an(1300, 1399, false);
                let t = 1301 + n.d[2];
                e.an(t, t, true);
            } else if (f) {
                e.an(501, 599, false), e.an(902, 999, false), e.an(1100, 1199, false), e.an(1300, 1399, false);
                let t = 1301 + o.d[2];
                e.an(t, t, true);
            } else if (h && h.f && h.d && h.d[0]) {
                e.an(501, 599, false), e.an(901, 901, true);
                let t = 501 + h.d[0];
                e.an(t, t, true);
            } else {
                let t;
                (t = o && o.f && o.d && o.d[1] ? 901 + o.d[1] : 901), e.an(t, t, true);
            }
            (m = h && h.f && h.d && h.d[1] ? 2e3 + h.d[1] : !h || !h.f || 1048576 & h.o ? 2001 : 2002),
                e.an(2001, 2099, false),
                e.an(m, m, true);
            let x = false;
            if (!_ && c && c.f && c.d && c.d[0]) {
                let t;
                (x = false), p ? ((x = true), (t = 1203)) : ((x = true), (t = 1201 + c.d[0])), e.an(t, t, true);
            } else 16 & b && (e.an(1201, 1201, true), _ || (e.an(1202, 1202, true), (x = true)));
            if (!x && !g)
                if (n && n.f && n.d && n.d[1]) {
                    let t = 1001 + n.d[1];
                    e.an(t, t, true);
                } else if (r && r.f && r.d && r.d[1]) {
                    let t = 1001 + r.d[1];
                    e.an(t, t, true);
                }
            if (!g && o && o.f && o.d && o.d[0]) {
                let t = o.d[0],
                    i = 1101 + t,
                    s = e.aO.some((t) => t.meshId == i);
                t > 2 ? (e.an(1300, 1399, false), s ? e.an(i, i, true) : e.an(1301, 1301, true)) : x || e.an(i, i, true);
            }
            if (c && c.f && c.d && c.d[0] && this.z.length > 0)
                for (let t of this.z) {
                    const i = GeosetOverrides[t];
                    if (i && 12 == i.GeosetType && i.Original == c.d[0] + 1) {
                        e.an(1200, 1299, false);
                        let t = 1200 + i.Override;
                        e.an(t, t, true);
                        break;
                    }
                }
            if (d && d.f && d.d && d.d[0]) {
                e.an(1500, 1599, false);
                let t = 1501 + d.d[0];
                if (this.z.length > 0)
                    for (let e of this.z) {
                        const i = GeosetOverrides[e];
                        if (i && 15 == i.GeosetType && i.Original == d.d[0] + 1) {
                            t = 1500 + i.Override;
                            break;
                        }
                    }
                e.an(t, t, true);
            }
            if (a && a.f && a.d && a.d[0]) {
                e.an(1800, 1899, false);
                let t = 1801 + a.d[0];
                e.an(t, t, true);
            }
            o || g || f || x || p ? e.an(1400, 1499, false) : e.an(1401, 1401, true);
        }
        setParticlesEnabled(t) {
            super.setParticlesEnabled(t),
                this.ml.forEach((e) => {
                    if (e.k) for (let i = 0; i < e.k.length; ++i) e.k[i] && e.k[i].d.setParticlesEnabled(t);
                });
        }
        v() {}
        I() {
            if (!this.l) return;
            let t = false;
            if (
                (this.ml.forEach((e) => {
                    if (e.f || e.D) {
                        if (e.i)
                            for (let i = 0; i < e.i.length; ++i)
                                if (e.i[i].texture && !e.i[i].texture.i()) return void (t = true);
                    } else t = true;
                }),
                t)
            )
                return;
            if (!this.ih) return;
            const e = this.ih.d.Materials,
                i = this.ih.d.TextureLayers,
                s = this.ih.d.TextureSections;
            let r = true,
                n = true;
            (15 != this.O && 21 != this.O) || (n = false),
                this.ml.forEach((t) => {
                    let e = t.h;
                    (4 != e && 5 != e && 19 != e) || ((r = false), null == t.i && (r = true)),
                        7 == e && ((n = false), null == t.i && (n = true));
                });
            let a = -1;
            if (27 == this.O) for (let t of i) 9 == t.BlendMode && 1 == t.TextureType && t.Layer > a && (a = t.Layer);
            const o =
                ((h = (t) => t.TextureType),
                i.reduce((t, e) => {
                    var i;
                    return (t[(i = h(e))] || (t[i] = [])).push(e), t;
                }, {}));
            var h;
            for (const t in o) {
                const e = o[t];
                for (const t of e) {
                    const e = this.ih.f[t.ChrModelTextureTargetID];
                    if (e && !e.d()) return;
                }
            }
            for (const t in o) {
                const i = o[t],
                    h = i[0].TextureType;
                if (!this.m[t]) {
                    const i = e.find((t) => t.TextureType == h);
                    if (!i) {
                        WH.debug("unable to find material info", h);
                        continue;
                    }
                    this.m[t] = new Ls(this.i.context, i.Width, i.Height);
                }
                const l = this.m[t];
                l.j();
                for (const t of i) {
                    let e = -1;
                    t.Layer == a && (e = 0);
                    const i = this.ih.f[t.ChrModelTextureTargetID];
                    if (!i) continue;
                    const o = t.TextureSection;
                    if ((3 != o && 5 != o) || (r && 3 == o) || (n && 5 == o)) {
                        let r = 0,
                            n = 0,
                            a = 1,
                            h = 1;
                        if (-1 != o && s) {
                            const t = s.find((t) => t.SectionType == o);
                            if (!t) {
                                WH.debug("can't find texture section data", o);
                                continue;
                            }
                            (r = t.X), (n = t.Y), (a = t.Width), (h = t.Height);
                        }
                        l.n(i, r, n, a, h, t.BlendMode, t.Layer, e);
                    }
                }
                1 == h && 52 != this.O && 70 != this.O && this.L(l),
                    26 != h || (52 != this.O && 70 != this.O) || this.L(l),
                    l.p();
            }
            this.gf(this.c);
            for (let t in this.N) {
                const e = this.N[t];
                e.d && e.d.loaded && this.gf(e.d.h());
            }
            this.l = false;
        }
        gf(t) {
            if (this.m[1]) {
                const e = this.m[1];
                t.aQ(e.i(0), e.i(1), e.i(2));
            }
            for (let e in this.m) {
                this.m[e];
                t.aL(e, this.m[e].i(0));
            }
        }
        L(t) {
            const e = [];
            this.ml.forEach((t) => {
                e.push(t);
            }),
                e.sort(function (t, e) {
                    return t.u - e.u;
                });
            const i = this.ih.d.TextureSections;
            for (let s = 0; s < e.length; s++) {
                const r = e[s];
                if (r.i)
                    for (let e = 0; e < r.i.length; e++) {
                        const s = r.i[e];
                        if (s.gender == this.ut && s.texture && s.texture.i() && 12 != s.region) {
                            if (1 & this.C.Character.ChrModelFlags && 7 == s.region) continue;
                            const e = i.find((t) => t.SectionType == s.region);
                            if (!e) {
                                WH.debug("can't find texture section data", s.region);
                                continue;
                            }
                            const r = new Hs();
                            (r.b = s.texture), t.n(r, e.X, e.Y, e.Width, e.Height, 0, -1, -1);
                        }
                    }
            }
        }
        setAppearance(t) {
            var e;
            (this.P = t),
            (this.u = t.sheathMain),
            (this.B = t.sheathOff),
            null === (e = this.ih) || undefined === e || e.b(t.options),
            (this.l = true),
            (this.hg = true),
            this.v(),
            this.qp();
        }
        setCustomizationsLoadedCallback(t) {
            this.on = t;
        }
        setItems(t) {
            const e = this.i.options;
            WH.debug("setItems", t);
            const i = [];
            for (let e = 0; e < t.length; e++) i.push([t[e].slot, t[e].display, t[e].visual]);
            i.forEach((t) => {
                const i = [parseInt(t[0]), parseInt(t[1])];
                e.items.push(i);
            }),
                this.F(i),
                (this.l = true);
        }
        attachList(t) {
            const e = this.i.options;
            WH.debug("attachList", t);
            const i = t.split(","),
                s = [];
            for (let t = 0; t < i.length; t += 2) s.push([i[t], i[t + 1]]);
            s.forEach((t) => {
                const i = [parseInt(t[0]), parseInt(t[1])];
                e.items.push(i);
            }),
                this.F(s),
                (this.l = true);
        }
        clearSlots(t) {
            const e = this.i.options;
            WH.debug("clearSlots", t);
            const i = t.split(",");
            for (let t = 0; t < i.length; ++t) {
                this.yx(parseInt(i[t]));
                const s = [];
                e.items.forEach((i) => {
                    0 != e.items[t].indexOf(parseInt(i)) && s.push(i);
                }),
                    (e.items = s);
            }
            this.qp(), (this.l = true);
        }
        setShouldersOverride(t) {
            if ((WH.debug("setShouldersOverride", t), !t || 2 != t.length)) return;
            for (let t = 0; t < 2; t++) {
                const e = this.G[t];
                e && e.y(), (this.G[t] = null);
            }
            for (let e = 0; e < 2; e++)
                if (null != t[e]) {
                    const i = new ol(this, 3, t[e]);
                    let s = 0;
                    (s = 0 == e ? 1 : 2), i.p(s), (this.G[e] = i);
                }
            const e = this.ml.get(3);
            if (e) {
                let t = 3;
                for (let e = 0; e < 2; e++) this.G[e] && (t &= ~(1 << e));
                e.p(t);
            }
            this.G && (this.G[0] || this.G[1]) && this.yx(3);
        }
        setSheath(t, e) {
            (this.u = t), (this.B = e), this.qp();
        }
        qp() {
            if (!this.loaded) return;
            const t = this.c;
            let e = (-1 == this.B || !this.B) && null != this.ml.get(22),
                i = !((-1 != this.u && this.u) || (null == this.ml.get(13) && null == this.ml.get(21)));
            for (let i of Ds) {
                let s = t.r.n[i];
                s > 0 && s < t.aw.length && this.c.aw[s].v(e ? "HandsClosed" : "");
            }
            for (let e of Rs) {
                let s = t.r.n[e];
                s > 0 && s < t.aw.length && t.aw[s].v(i ? "HandsClosed" : "");
            }
        }
        Az(t) {
            const e = this.c;
            if (!e.C) return;
            const i = t.d.modelInstance;
            if (!i || !i.C) return;
            t.b || (i.g(e, -1, null), (t.b = true));
            let s = i.aw;
            if (s) {
                for (let t = 0; t < s.length; t++) {
                    let i = s[t],
                        r = this.K[i.c.b];
                    if ("number" != typeof r) continue;
                    let n = s[t].i,
                        a = e.aw[r].i;
                    (s[t].o = true), mat4Copy(n, a);
                }
                i.V();
            }
        }
        sr() {
            const t = this.c;
            let e = {};
            for (let i = 0; i < t.aw.length; i++) e[t.aw[i].c.b] = i;
            this.K = e;
        }
        a() {
            if (this.c && this.c.C) {
                this.K || (this.sr(), this.qp()), super.a();
                for (const t in this.N) {
                    const e = this.N[t];
                    this.Az(e);
                }
                this.ml.forEach((t) => {
                    if (t) {
                        if (2 == t.K && 13 == t.L) {
                            if (21 == t.h && -1 != this.u) return;
                            if (22 == t.h && -1 != this.B) return;
                        }
                        t.r();
                    }
                }),
                    this.G.forEach((t) => {
                        t && t.k && t.r();
                    }),
                    this.I();
            }
        }
        static J(t, e) {
            const i = t.d;
            if (!i.loaded) return;
            const s = i.modelInstance;
            if (!s || !s.C) return;
            s.aw && s.aG(e);
        }
        f(t) {
            if (this.c && this.c.C) {
                super.f(t);
                for (const e in this.N) {
                    const i = this.N[e];
                    hl.J(i, t);
                }
                if (
                    (this.ml.forEach((e) => {
                        if (e) {
                            if (2 == e.K && 13 == e.L) {
                                if (21 == e.h && -1 != this.u) return;
                                if (22 == e.h && -1 != this.B) return;
                            }
                            e.v(t);
                        }
                    }),
                    this.G.forEach((e) => {
                        e && e.k && e.v(t);
                    }),
                    this.r)
                )
                    for (let e = 0; e < this.r.length; e++) {
                        let i = this.r[e];
                        i.C && i.aG(t);
                    }
                this.ml.forEach((e) => {
                    e && e.s && e.s.d && e.s.b && hl.J(e.s, t);
                });
            }
        }
        e(t) {
            super.e(t);
            for (const e in this.N) {
                const i = this.N[e];
                i.b && i.d && i.d.loaded && i.d.e(t);
            }
            if (
                (this.ml.forEach((e) => {
                    if (e) {
                        if (2 == e.K && 13 == e.L) {
                            if (21 == e.h && -1 != this.u) return;
                            if (22 == e.h && -1 != this.B) return;
                        }
                        e.c(t);
                    }
                }),
                this.G.forEach((e) => {
                    e && e.k && e.c(t);
                }),
                this.r)
            )
                for (let e = 0; e < this.r.length; e++) {
                    let i = this.r[e];
                    i.C && t.a(i, false);
                }
        }
    }
    class ll extends Vh {
        constructor(t, e, i, s, r, n) {
            super(t, e, i, s, r, n), this.cba();
        }
        cba() {
            let t = this.k;
            const e = this.m,
                i = this.dc,
                s = this.i;
            if (s.ComponentModels) {
                let r = s.ComponentModels[0] || s.ComponentModels[1];
                r &&
                    s.ModelFiles &&
                    s.ModelFiles[r] &&
                    (27 == s.Item.InventoryType
                        ? (this.c = new Modelviewer(this.l, this.l.renderer, s.ModelFiles[r][0].FileDataId))
                        : (this.c = new Modelviewer(this.l, this.l.renderer, jh.b(s.ModelFiles[r], -1, e, i, t))),
                    this.c.aB()),
                    this.c &&
                        s.Item.AttachGeosetGroup &&
                        (this.c.z(s.Item.AttachGeosetGroup[0], 27), this.c.z(s.Item.AttachGeosetGroup[1], 21));
            }
            if (s.Textures)
                for (let t in s.Textures)
                    0 != s.Textures[t] && this.c.aL(parseInt(t), this.l.getTexture(s.Textures[t]));
        }
    }
    class ul extends Vh {
        constructor(t, e, i, s, r, n) {
            super(t, e, i, s, r, n), (this.gf = 0), this.ed();
        }
        get shoulderIndex() {
            return this.gf;
        }
        cba(t) {
            this.gf != t && ((this.gf = t), this.ed());
        }
        ed() {
            this.c = null;
            let t = this.k;
            const e = this.m,
                i = this.dc,
                s = this.i;
            if (s.ComponentModels) {
                let r = s.ComponentModels[0],
                    n = s.ComponentModels[1];
                if (!r || (1 != this.gf && 0 != this.gf)) {
                    if (
                        n &&
                        (2 == this.gf || 0 == this.gf) &&
                        (n &&
                            s.ModelFiles[n] &&
                            ((this.c = new Modelviewer(this.l, this.l.renderer, jh.b(s.ModelFiles[n], 1, e, i, t))),
                            this.c.aB()),
                        s.Textures2 && this.c)
                    )
                        for (let t in s.Textures2)
                            0 != s.Textures2[t] && this.c.aL(+t, this.l.getTexture(s.Textures2[t]));
                } else if (
                    (r &&
                        s.ModelFiles[r] &&
                        ((this.c = new Modelviewer(this.l, this.l.renderer, jh.b(s.ModelFiles[r], 0, e, i, t))), this.c.aB()),
                    this.c && s.Textures)
                )
                    for (let t in s.Textures) 0 != s.Textures[t] && this.c.aL(+t, this.l.getTexture(s.Textures[t]));
            }
            this.c && s.Item.AttachGeosetGroup && this.c.z(s.Item.AttachGeosetGroup[0], 26);
        }
        a() {
            this.c.V();
        }
        f(t) {
            this.c.aG(t);
        }
    }
    class cl extends Vh {
        constructor(t, e, i, s, r, n) {
            super(t, e, i, s, r, n), this.cba();
        }
        cba() {
            let t = this.k;
            const e = this.m,
                i = this.dc,
                s = this.i;
            if (s.ComponentModels) {
                let r = s.ComponentModels[0];
                r &&
                    s.ModelFiles &&
                    s.ModelFiles[r] &&
                    (this.c = new Modelviewer(this.l, this.l.renderer, jh.b(s.ModelFiles[r], -1, e, i, t)));
            }
            if (this.c && s.Textures)
                for (let t in s.Textures) 0 != s.Textures[t] && (this.c.ah[+t] = this.l.getTexture(s.Textures[t]));
        }
        isLoaded() {
            return (!this.c && !this.i.ComponentModels) || this.loaded;
        }
    }
    class dl extends qh {
        constructor(t, e) {
            super(), (this.ba = t), (this.fe = e), this.dc();
        }
        dc() {
            (this.c = new Modelviewer(this.ba, this.ba.renderer, this.fe.Model)), this.c.i(1 | this.fe.Scale);
        }
        a() {
            this.c && this.c.V();
        }
        getBounds() {
            return this.c.C ? this.c.updateBounds() : [null, null];
        }
        f(t) {
            this.c && this.c.aG(t);
        }
    }
    class fl {
        static c(t, e, i) {
            if (e.Character || i == Types.CHARACTER) return new hl(t, e);
            if (i == Types.NPC || i == Types.HUMANOIDNPC) return new Zh(t, e);
            if (i == Types.HELM || i == Types.SHOULDER || i == Types.ITEM) {
                const s = fl.b(t, e, i, 1, 0, 0);
                return s.ba(), s;
            }
            if (i == Types.OBJECT) return new dl(t, e);
            throw "Couldn't create actor";
        }
        static b(t, e, i, s, r, n) {
            if (i == Types.HELM) return new ll(t, e, s, r, n, false);
            if (i == Types.SHOULDER) return new ul(t, e, s, r, n, false);
            if (i == Types.ITEM) return new cl(t, e, s, r, n, false);
            throw "Couldn't create item actor";
        }
        static a(t, e, i) {
            return e == Types.PATH
                ? new Promise((e, s) => {
                      e(new Wh(t, i, {}, false));
                  })
                : $h(t.options.contentPath, e, i).then((i) => fl.c(t, i, e));
        }
    }

    const WebGL = class
    {
        constructor(t) {
            (this.currFrame = 0),
            (this.clearColor = vec3FromValues(0, 0, 0)),
            (this.addedCss = false),
            (this.progressShown = false),
            (this.doUpdateBounds = false),
            (this.attributeState = new AttributeState()),
            (this.gxDevice = null),
            (this.textureCache = new Map()),
            (this.crossFadeDuration = 0.3),
            (this.onContextMenu = function (t) {
                return false;
            });
            var e = this;
            (e.viewer = t),
            (e.options = t.options),
            (e.downloads = {}),
            (e.context = null),
            (e.bgImgLoaded = false),
            (e.width = 0),
            (e.height = 0),
            (e.time = 0),
            (e.delta = 0),
            (e.actors = []),
            (e.screenshotDataURL = null),
            (e.makeDataURL = false),
            (e.screenshotCallback = null),
            (e.azimuth = 1.5 * Math.PI),
            (e.zenith = Math.PI / 2),
            (e.distance = 15),
            (e.fov = 30),
            (e.zoom = {
                rateStep: 0.1,
                rateAccelerationDecay: 0.4,
                interpolationRate: 0.3,
                range: [0.3, 4],
                rateCurrent: 0,
                target: 1,
                current: 1,
            }),
            (e.zoom.range = e.zoom.range.map(function (t) {
                return Math.log(t) / Math.log(1 + e.zoom.rateStep);
            })),
            (e.translation = vec3FromValues(0, 0, 0)),
            (e.translationFromModel = vec3FromValues(0, 0, 0)),
            (e.target = vec3FromValues(0, 0, 0)),
            (e.eye = vec3FromValues(0, 0, 0)),
            (e.up = vec3FromValues(0, 0, 1)),
            (e.lookDir = vec3Create()),
            (e.fullscreen = false),
            (e.projMatrix = mat4Create()),
            (e.viewMatrix = mat4Create()),
            (e.panningMatrix = mat4Create()),
            (e.viewOffset = vec3Create()),
            this.addedCss ||
                ((this.addedCss = true),
                $("head").append(
                    '<link rel="stylesheet" href="//wow.zamimg.com/modelviewer/viewer/viewer.css" type="text/css" />'
                ));
        }
        updateProgress() {
            if (!this.stop) {
                var t = this,
                    e = 0,
                    i = 0;
                for (var s in t.downloads) (e += t.downloads[s].total), (i += t.downloads[s].loaded);
                if (e <= 0) t.progressShown && (t.progressBg.hide(), t.progressBar.hide(), (t.progressShown = false));
                else {
                    t.progressShown || (t.progressBg.show(), t.progressBar.show(), (t.progressShown = true));
                    var r = i / e;
                    t.progressBar.width(Math.round(t.width * r) + "px");
                }
            }
        }
        destroy() {
            var t = this;
            (t.stop = true),
                t.canvas &&
                    ($(t.canvas).off(),
                    t.canvas.detach(),
                    t.progressBg.detach(),
                    t.progressBar.detach(),
                    (t.canvas = t.progressBg = t.progressBar = null)),
                t.clearBackground(),
                (t.actors = []);
        }
        method(t, e) {
            if ("isBackgroundLoaded" === t) return this.bgImgLoaded;
            if ("setBackground" !== t) {
                if (this.actors.length > 0 && this.actors[0]) {
                    const i = this.actors[0][t];
                    return i ? i.apply(this.actors[0], e) : void WH.debug("Unknown viewer method", t, "args", e);
                }
                this.actorPromises.length > 0 &&
                    this.actorPromises[0] &&
                    (this.actorPromises[0] = this.actorPromises[0].then((i) => {
                        const s = i[t];
                        if (s) return s.apply(i, e), i;
                        WH.debug("Unknown viewer method", t, "args", e);
                    }));
            } else this.setBackground(e[0]);
        }
        getTime() {
            return window.performance && window.performance.now ? window.performance.now() : Date.now();
        }
        draw(t) {
            var e,
                i = this,
                s = i.context;
            if (
                ((i.delta = 0.001 * (t - i.time)),
                (i.time = t),
                i.currFrame++,
                this.doUpdateBounds && i.actors.length > 0)
            ) {
                let [t, s] = [vec3Create(), vec3Create()];
                for (e = 0; e < i.actors.length; ++e) {
                    const [r, n] = i.actors[e].getBounds();
                    r && vec3Min(t, t, r), n && vec3Max(s, s, n);
                }
                const r = vec3Create(),
                    n = vec3Create();
                vec3Sub(r, s, t), vec3ScaleAdd(n, t, r, 0.5);
                let a = r[2],
                    o = r[0],
                    h = r[1];
                const l = this.width / this.height,
                    u = 2 * Math.tan((this.fov / 2) * 0.0174532925),
                    c = (1.2 * a) / u,
                    d = (1.2 * o) / (u * l);
                (this.distance = Math.max(Math.max(c, d), 2 * h)),
                    vec3Set(this.translationFromModel, n[0], -n[2], 0),
                    (this.doUpdateBounds = false);
            }
            for (
                i.updateCamera(),
                    s.bindFramebuffer(s.FRAMEBUFFER, null),
                    s.viewport(0, 0, i.width, i.height),
                    s.clearColor(this.clearColor[0], this.clearColor[1], this.clearColor[2], 0),
                    s.clear(s.COLOR_BUFFER_BIT | s.DEPTH_BUFFER_BIT),
                    i.bgTexture &&
                        i.program &&
                        (s.useProgram(i.program),
                        s.activeTexture(s.TEXTURE0),
                        s.bindTexture(s.TEXTURE_2D, i.bgTexture),
                        s.uniform1i(i.uTexture, 0),
                        s.uniform4f(
                            i.uBGTransform,
                            i.viewer.options.bgPosition[0] || 0,
                            i.viewer.options.bgPosition[1] || 0,
                            i.viewer.options.bgScale[0] || 1,
                            i.viewer.options.bgScale[1] || 1
                        ),
                        i.options.backgroundRotatation &&
                            (s.uniform1f(i.uRotation, i.options.backgroundRotatation),
                            s.uniform2f(i.uResolution, i.width, i.height)),
                        s.bindBuffer(s.ARRAY_BUFFER, i.vb),
                        s.bindBuffer(s.ELEMENT_ARRAY_BUFFER, null),
                        s.enableVertexAttribArray(i.aPosition),
                        s.vertexAttribPointer(i.aPosition, 2, s.FLOAT, false, 16, 0),
                        s.enableVertexAttribArray(i.aTexCoord),
                        s.vertexAttribPointer(i.aTexCoord, 2, s.FLOAT, false, 16, 8),
                        s.depthMask(false),
                        s.disable(s.CULL_FACE),
                        s.blendFunc(s.ONE, s.ZERO),
                        s.drawArrays(s.TRIANGLE_STRIP, 0, 4),
                        s.blendFunc(s.SRC_ALPHA, s.ONE_MINUS_SRC_ALPHA),
                        s.enable(s.CULL_FACE),
                        s.depthMask(true),
                        s.disableVertexAttribArray(i.aPosition),
                        s.disableVertexAttribArray(i.aTexCoord)),
                    e = 0;
                e < i.actors.length;
                ++e
            )
                i.actors[e].a();
            for (s.viewport(0, 0, i.width, i.height), this.gxDevice.c(), e = 0; e < i.actors.length; ++e)
                i.actors[e].f(false);
            for (e = 0; e < i.actors.length; ++e) i.actors[e].f(true);
            this.gxDevice.b();
        }
        setAdaptiveMode(t) {
            (this.addaptiveMode = t), t && $(window).trigger("resize");
        }
        setTranslation(t, e, i) {
            this.translation = vec3FromValues(t, e, i);
        }
        setBackground(t) {
            var e = this;
            (e.bgImgLoaded = false), (e.options.background = t), e.clearBackground(), e.loadBackground();
        }
        clearBackground() {
            var t = this;
            if (t.context) {
                var e = t.context;
                t.bgTexture && e.deleteTexture(t.bgTexture),
                    (t.bgTexture = null),
                    t.program && e.deleteProgram(t.program),
                    (t.program = null),
                    t.vb && e.deleteBuffer(t.vb),
                    t.vs && e.deleteShader(t.vs),
                    t.fs && e.deleteShader(t.fs),
                    (t.vb = t.vs = t.fs = null);
            }
            t.bgImg && (t.bgImg = null);
        }
        updateCamera() {
            var t = this;
            (t.zoom.target += t.zoom.rateCurrent),
                (t.zoom.rateCurrent *= 1 - t.zoom.rateAccelerationDecay),
                (t.zoom.target = -Math.max(Math.min(-t.zoom.target, t.zoom.range[1]), t.zoom.range[0])),
                (t.zoom.current += (t.zoom.target - t.zoom.current) * t.zoom.interpolationRate);
            var e = t.distance * Math.pow(t.zoom.rateStep + 1, -t.zoom.current),
                i = t.azimuth,
                s = t.zenith;
            1 == t.up[2]
                ? ((t.eye[0] = -e * Math.sin(s) * Math.cos(i) + t.target[0]),
                  (t.eye[1] = -e * Math.sin(s) * Math.sin(i) + t.target[1]),
                  (t.eye[2] = -e * Math.cos(s) + t.target[2]))
                : ((t.eye[0] = -e * Math.sin(s) * Math.cos(i) + t.target[0]),
                  (t.eye[1] = -e * Math.cos(s) + t.target[1]),
                  (t.eye[2] = -e * Math.sin(s) * Math.sin(i) + t.target[2])),
                vec3Sub(t.lookDir, t.target, t.eye),
                vec3Normalize(t.lookDir, t.lookDir),
                (function (t, e, i, s) {
                    var r,
                        n,
                        a,
                        o,
                        h,
                        l,
                        u,
                        c,
                        d,
                        f,
                        g = e[0],
                        _ = e[1],
                        b = e[2],
                        m = s[0],
                        p = s[1],
                        x = s[2],
                        v = i[0],
                        T = i[1],
                        w = i[2];
                    Math.abs(g - v) < GLMAT_EPSILON && Math.abs(_ - T) < GLMAT_EPSILON && Math.abs(b - w) < GLMAT_EPSILON
                        ? mat4Identity(t)
                        : ((u = g - v),
                          (c = _ - T),
                          (d = b - w),
                          (r = p * (d *= f = 1 / Math.hypot(u, c, d)) - x * (c *= f)),
                          (n = x * (u *= f) - m * d),
                          (a = m * c - p * u),
                          (f = Math.hypot(r, n, a))
                              ? ((r *= f = 1 / f), (n *= f), (a *= f))
                              : ((r = 0), (n = 0), (a = 0)),
                          (o = c * a - d * n),
                          (h = d * r - u * a),
                          (l = u * n - c * r),
                          (f = Math.hypot(o, h, l))
                              ? ((o *= f = 1 / f), (h *= f), (l *= f))
                              : ((o = 0), (h = 0), (l = 0)),
                          (t[0] = r),
                          (t[1] = o),
                          (t[2] = u),
                          (t[3] = 0),
                          (t[4] = n),
                          (t[5] = h),
                          (t[6] = c),
                          (t[7] = 0),
                          (t[8] = a),
                          (t[9] = l),
                          (t[10] = d),
                          (t[11] = 0),
                          (t[12] = -(r * g + n * _ + a * b)),
                          (t[13] = -(o * g + h * _ + l * b)),
                          (t[14] = -(u * g + c * _ + d * b)),
                          (t[15] = 1));
                })(t.viewMatrix, t.eye, t.target, t.up),
                mat4Identity(t.panningMatrix),
                1 == t.up[2]
                    ? vec3Set(t.viewOffset, t.translation[0], -t.translation[1], 0)
                    : vec3Set(t.viewOffset, t.translation[0], 0, t.translation[1]),
                vec3Add(t.viewOffset, t.viewOffset, this.translationFromModel),
                mat4Translate(t.panningMatrix, t.panningMatrix, t.viewOffset),
                mat4Mult(t.viewMatrix, t.panningMatrix, t.viewMatrix);
        }
        init() {
            var t,
                e = this,
                i = e.context;
            if (
                ((this.blackPixelTexture = i.createTexture()),
                i.bindTexture(i.TEXTURE_2D, this.blackPixelTexture),
                i.texImage2D(i.TEXTURE_2D, 0, i.RGBA, 1, 1, 0, i.RGBA, i.UNSIGNED_BYTE, new Uint8Array([0, 0, 0, 255])),
                i.bindTexture(i.TEXTURE_2D, null),
                (this.greenPixelTexture = i.createTexture()),
                i.bindTexture(i.TEXTURE_2D, this.greenPixelTexture),
                i.texImage2D(
                    i.TEXTURE_2D,
                    0,
                    i.RGBA,
                    1,
                    1,
                    0,
                    i.RGBA,
                    i.UNSIGNED_BYTE,
                    new Uint8Array([0, 255, 0, 255])
                ),
                i.bindTexture(i.TEXTURE_2D, null),
                perspective(e.projMatrix, 0.0174532925 * e.fov, e.viewer.aspect, 0.1, 500),
                e.updateCamera(),
                i.clearColor(this.clearColor[0], this.clearColor[1], this.clearColor[2], 0),
                i.enable(i.DEPTH_TEST),
                i.depthFunc(i.LEQUAL),
                i.blendFunc(i.SRC_ALPHA, i.ONE_MINUS_SRC_ALPHA),
                i.enable(i.BLEND),
                e.options.models || e.options.items)
            ) {
                e.actorPromises = [];
                var s = [].concat(e.options.models);
                if (s.length > 0) {
                    const i = e.options.mount,
                        r = e.options.shouldersOverride;
                    for (t = 0; t < s.length; ++t) {
                        const n = fl
                            .a(this, s[t].type, s[t].id)
                            .then(
                                (t) => (
                                    i && i.id && t instanceof Zh && t.q(i.id),
                                    t instanceof hl && t.setShouldersOverride(r),
                                    e.actors.push(t),
                                    t
                                )
                            )
                            .then((t) => t);
                        e.actorPromises.push(n);
                    }
                }
            }
            !(function t() {
                if (!e.stop && (window.requestAnimationFrame(t), e.gxDevice)) {
                    var s = e.getTime();
                    if (false !== e.makeDataURL) {
                        if (e.canvas[0].toDataURL) {
                            var r = e.clearColor,
                                n = e.bgTexture;
                            e.options.transparent && ((e.bgTexture = null), (e.clearColor = vec3FromValues(0, 0, 0))), e.draw(s);
                            var a = e.width * e.height * 4,
                                o = new Uint8Array(a);
                            i.readPixels(0, 0, e.width, e.height, i.RGBA, i.UNSIGNED_BYTE, o);
                            let t = null;
                            e.options.transparent
                                ? ((e.clearColor = vec3FromValues(1, 1, 1)),
                                  e.draw(s),
                                  (t = new Uint8Array(a)),
                                  i.readPixels(0, 0, e.width, e.height, i.RGBA, i.UNSIGNED_BYTE, t))
                                : (t = o);
                            for (var h = new Uint8Array(a), l = 0, u = 0; u < e.height; u++)
                                for (var c = 0; c < e.width; c++) {
                                    l = 4 * (u * e.width + c);
                                    var d = 4 * ((e.height - 1 - u) * e.width + c),
                                        f = o[l + 0],
                                        g = o[l + 1],
                                        _ = o[l + 2],
                                        b = t[l + 0],
                                        m = t[l + 1],
                                        p = t[l + 2],
                                        x = 0.001,
                                        v = 1 - (b - f + x) / 255,
                                        T = 1 - (m - g + x) / 255,
                                        w = 1 - (p - _ + x) / 255,
                                        y = Math.max(0, Math.min(1, (v + T + w) / 3));
                                    y < 0.05 && (f + g + _) / 3 < 16 && ((f = b), (g = m), (_ = p), (y = 0)),
                                        (h[d + 0] = f),
                                        (h[d + 1] = g),
                                        (h[d + 2] = _),
                                        (h[d + 3] = Math.round(255 * y));
                                }
                            var A = document.createElement("canvas"),
                                E = A.getContext("2d");
                            (A.width = e.width), (A.height = e.height);
                            var C = E.createImageData(e.width, e.height);
                            C.data.set(h),
                                E.putImageData(C, 0, 0),
                                (e.screenshotDataURL = A.toDataURL.apply(A, e.makeDataURL)),
                                e.screenshotCallback && (e.screenshotCallback(), (e.screenshotCallback = null)),
                                (e.clearColor = r),
                                (e.bgTexture = n);
                        }
                        e.makeDataURL = false;
                    }
                    e.draw(s);
                }
            })();
        }
        onDoubleClick(t) {
            WoWModelViewer.isFullscreen() ? WoWModelViewer.exitFullscreen() : WoWModelViewer.requestFullscreen(this.canvas[0]);
        }
        onFullscreen(t) {
            let e = this;
            if (e.viewer.container)
                if ((!e.fullscreen && WoWModelViewer.isFullscreen()) || this.addaptiveMode) {
                    if (
                        ((e.restoreWidth = e.width),
                        (e.restoreHeight = e.height),
                        (e.fullscreen = true),
                        WoWModelViewer.isFullscreen())
                    ) {
                        var i = $(window);
                        let t = window.screen.width || i.width(),
                            e = window.screen.height || i.height();
                        this.onResize(t, e, t / e);
                    } else if (this.addaptiveMode) {
                        var s = e.viewer.container;
                        this.onResize(s.width(), s.height(), s.width() / s.height());
                    }
                } else
                    e.fullscreen &&
                        !WoWModelViewer.isFullscreen() &&
                        ((e.fullscreen = false), this.onResize(e.restoreWidth, e.restoreHeight, e.viewer.aspect));
        }
        onResize(t, e, i) {
            this.resize(t, e), perspective(this.projMatrix, 0.0174532925 * this.fov, i, 0.1, 5e3);
        }
        onMouseDown(t) {
            let e = this;
            3 == t.which || t.ctrlKey ? (e.rightMouseDown = true) : (e.mouseDown = true),
                "touchstart" == t.type
                    ? ((e.mouseX = t.originalEvent.touches[0].clientX), (e.mouseY = t.originalEvent.touches[0].clientY))
                    : ((e.mouseX = t.clientX), (e.mouseY = t.clientY)),
                $("body").addClass("unselectable"),
                t.preventDefault();
        }
        onMouseWheel(t) {
            if (!this.options.wheelEventValidation || this.options.wheelEventValidation.call(this, t))
                return (this.zoom.rateCurrent += t.originalEvent.wheelDelta > 0 ? 1 : -1), t.preventDefault(), false;
        }
        onMouseUp(t) {
            let e = this;
            (e.mouseDown || e.rightMouseDown) &&
                ($("body").removeClass("unselectable"), (e.mouseDown = false), (e.rightMouseDown = false));
        }
        onMouseMove(t) {
            let e = this;
            if ((e.mouseDown || e.rightMouseDown) && undefined !== e.mouseX) {
                var i, s;
                "touchmove" == t.type
                    ? ((i = t.originalEvent.touches[0].clientX), (s = t.originalEvent.touches[0].clientY))
                    : ((i = t.clientX), (s = t.clientY));
                var r = ((i - e.mouseX) / e.width) * Math.PI * 2,
                    n = ((s - e.mouseY) / e.width) * Math.PI * 2;
                if (e.mouseDown) {
                    1 == e.up[2] ? (e.azimuth -= r) : (e.azimuth += r), (e.zenith += n);
                    for (var a = 2 * Math.PI; e.azimuth < 0; ) e.azimuth += a;
                    for (; e.azimuth > a; ) e.azimuth -= a;
                    e.zenith < 1e-4 && (e.zenith = 1e-4), e.zenith >= Math.PI && (e.zenith = Math.PI - 1e-4);
                } else (e.translation[0] += r), (e.translation[1] += n);
                (e.mouseX = i), (e.mouseY = s), t.stopPropagation();
            }
        }
        resize(t, e) {
            var i = this;
            if (i.width !== t || i.height !== e) {
                if (
                    (i.fullscreen || i.viewer.container.css({ height: e + "px", position: "relative" }),
                    (i.width = t),
                    (i.height = e),
                    i.canvas)
                )
                    i.canvas.attr({ width: t, height: e }),
                        i.canvas.css({ width: t + "px", height: e + "px" }),
                        i.context.viewport(0, 0, i.width, i.height);
                else {
                    if (
                        ((i.canvas = $("<canvas/>")),
                        i.canvas.attr({ width: t, height: e }),
                        i.viewer.container.append(i.canvas),
                        (i.context =
                            i.canvas[0].getContext("webgl", { alpha: true, premultipliedAlpha: false }) ||
                            i.canvas[0].getContext("experimental-webgl", { alpha: true, premultipliedAlpha: false })),
                        (i.progressBg = $("<div/>", {
                            css: {
                                display: "none",
                                position: "absolute",
                                bottom: 0,
                                left: 0,
                                right: 0,
                                height: "10px",
                                backgroundColor: "#000",
                            },
                        })),
                        (i.progressBar = $("<div/>", {
                            css: {
                                display: "none",
                                position: "absolute",
                                bottom: 0,
                                left: 0,
                                width: 0,
                                height: "10px",
                                backgroundColor: "#ccc",
                            },
                        })),
                        i.viewer.container.append(i.progressBg),
                        i.viewer.container.append(i.progressBar),
                        !i.context)
                    )
                        return (
                            alert("No WebGL support, sorry! You should totally use Chrome."),
                            i.canvas.detach(),
                            void (i.canvas = null)
                        );
                    const ambientColor = [0.35, 0.35, 0.35, 1],
                        primaryColor = [1, 1, 1, 1],
                        secondaryColor = [0.35, 0.35, 0.35, 1],
                        lightDir1 = vec3Create(),
                        lightDir2 = vec3Create(),
                        lightDir3 = vec3Create();
                    vec3Normalize(lightDir1, [5, -3, 3]), vec3Normalize(lightDir2, [5, 5, 5]), vec3Normalize(lightDir3, [-5, -5, -5]);
                    const sceneState = {
                        uCameraPos: i.eye,
                        uViewMatrix: i.viewMatrix,
                        uProjMatrix: i.projMatrix,
                        uAmbientColor: ambientColor,
                        uPrimaryColor: primaryColor,
                        uSecondaryColor: secondaryColor,
                        uLightDir1: lightDir1,
                        uLightDir2: lightDir2,
                        uLightDir3: lightDir3,
                    };
                    (this.gxDevice = new GXDevice(i.context, sceneState)),
                        (this.renderer = this.gxDevice.e()),
                        i.canvas
                            .off("mousedown.webgl touchstart.webgl")
                            .on("mousedown.webgl touchstart.webgl", i.onMouseDown.bind(i))
                            .off("wheel.webgl")
                            .on("wheel.webgl", i.onMouseWheel.bind(i))
                            .off("dblclick.webgl")
                            .on("dblclick.webgl", i.onDoubleClick.bind(i))
                            .off("contextmenu.webgl")
                            .on("contextmenu.webgl", i.onContextMenu.bind(i));
                    let u = 0,
                        c = 0,
                        d = 0;
                    i.canvas.off("touchend.webgl").on("touchend.webgl", function (t) {
                        const e = t.originalEvent;
                        if (!e || 1 !== e.changedTouches.length) return;
                        const s = e.changedTouches[0],
                            r = new Date().getTime(),
                            n = r - u,
                            a = s.clientX - c,
                            o = s.clientY - d,
                            h = Math.sqrt(a * a + o * o);
                        (u = r),
                            (c = s.clientX),
                            (d = s.clientY),
                            n < 300 && h < 30 && (t.preventDefault(), i.onDoubleClick.call(i, e));
                    }),
                        $(window).off("resize.webgl").on("resize.webgl", i.onFullscreen.bind(i)),
                        $(document)
                            .off("mouseup.webgl touchend.webgl")
                            .on("mouseup.webgl touchend.webgl", i.onMouseUp.bind(i))
                            .off("mousemove.webgl touchmove.webgl")
                            .on("mousemove.webgl touchmove.webgl", i.onMouseMove.bind(i)),
                        i.onFullscreen(null);
                }
                i.options.background && i.loadBackground();
            }
        }
        loadBackground() {
            var t = this,
                e = t.context;
            const i = function () {
                    (t.vb = e.createBuffer()),
                        e.bindBuffer(e.ARRAY_BUFFER, t.vb),
                        e.bufferData(e.ARRAY_BUFFER, new Float32Array(16), e.DYNAMIC_DRAW);
                    var i,
                        s = t.compileShader(
                            e.VERTEX_SHADER,
                            "    attribute vec2 aPosition;    attribute vec2 aTexCoord;        varying vec2 vTexCoord;        void main(void) {        vTexCoord = aTexCoord;        gl_Position = vec4(aPosition, 0, 1);    }    "
                        );
                    i = t.options.backgroundRotatation
                        ? t.compileShader(
                              e.FRAGMENT_SHADER,
                              "\tprecision mediump float;    varying vec2 vTexCoord;    uniform sampler2D uTexture;    uniform vec2 uResolution;    uniform vec4 uBGTransform;    uniform float uRotation;    mat3 getTransform(vec2 pos, vec2 scale, float rotation, vec2 center) {        float c = cos(rotation);        float s = sin(rotation);        return mat3(            scale.x * c, scale.x * s, - scale.x * ( c * center.x + s * center.y ) + center.x + pos.x,            -scale.y * s, scale.y * c, - scale.y * ( - s * center.x + c * center.y ) + center.y + pos.y,            0.0, 0.0, 1.0        );    }    void main(void) {        vec2 uv = gl_FragCoord.xy / uResolution.xy;        mat3 transform = getTransform(uBGTransform.xy, uBGTransform.zw, uRotation, vec2(0.5));        uv = (vec3(uv, 1.0) * transform).xy;        gl_FragColor = texture2D(uTexture, uv);\t}"
                          )
                        : t.compileShader(
                              e.FRAGMENT_SHADER,
                              "    precision mediump float;    varying vec2 vTexCoord;        uniform sampler2D uTexture;    uniform vec4 uBGTransform;        void main(void) {        gl_FragColor = texture2D(uTexture, vTexCoord.xy * uBGTransform.zw + uBGTransform.xy);    }    "
                          );
                    var r = e.createProgram();
                    e.attachShader(r, s),
                        e.attachShader(r, i),
                        e.linkProgram(r),
                        e.getProgramParameter(r, e.LINK_STATUS)
                            ? ((t.vs = s),
                              (t.fs = i),
                              (t.program = r),
                              (t.uTexture = e.getUniformLocation(r, "uTexture")),
                              (t.aPosition = e.getAttribLocation(r, "aPosition")),
                              (t.aTexCoord = e.getAttribLocation(r, "aTexCoord")),
                              (t.uBGTransform = e.getUniformLocation(r, "uBGTransform")),
                              (t.uRotation = e.getUniformLocation(r, "uRotation")),
                              (t.uResolution = e.getUniformLocation(r, "uResolution")))
                            : console.error("Error linking shaders");
                },
                s = function () {
                    var i = t.width / t.bgImg.width,
                        s = t.height / t.bgImg.height;
                    const r = [-1, -1, 0, s, 1, -1, i, s, -1, 1, 0, 0, 1, 1, i, 0];
                    e.bindBuffer(e.ARRAY_BUFFER, t.vb), e.bufferSubData(e.ARRAY_BUFFER, 0, new Float32Array(r));
                };
            t.bgImg
                ? t.bgImg.loaded && (t.vb || i(), s())
                : ((t.bgImg = new Image()),
                  (t.bgImg.crossOrigin = ""),
                  (t.bgImg.onload = function () {
                      var r;
                      null === (r = t.bgImg) || undefined === r || (r.loaded = true),
                          t.bgImg &&
                              ((t.bgTexture = e.createTexture()),
                              e.bindTexture(e.TEXTURE_2D, t.bgTexture),
                              e.texImage2D(e.TEXTURE_2D, 0, e.RGBA, e.RGBA, e.UNSIGNED_BYTE, t.bgImg),
                              e.texParameteri(e.TEXTURE_2D, e.TEXTURE_MIN_FILTER, e.LINEAR),
                              t.vb || i(),
                              s(),
                              (t.bgImgLoaded = true));
                  }),
                  (t.bgImg.onerror = function () {
                      t.bgImg = null;
                  }),
                  (t.bgImg.src = t.options.contentPath + t.options.background),
                  (t.viewer.options.bgPosition = t.options.bgPosition || [0, 0]),
                  (t.viewer.options.bgScale = t.options.bgScale || [1, 1]));
        }
        compileShader(t, e) {
            var i = this.context,
                s = i.createShader(t);
            if ((i.shaderSource(s, e), i.compileShader(s), !i.getShaderParameter(s, i.COMPILE_STATUS)))
                throw "Shader compile error: " + i.getShaderInfoLog(s);
            return s;
        }
        getTexture(t) {
            if (this.textureCache.has(t)) {
                var e = this.textureCache.get(t);
                if (e.g || e.a) return e;
            }
            const i = new Texture(this, t);
            return this.textureCache.set(t, i), i;
        }
    };
    let _l = { Types: Types };
    const ZamModelViewer = Object.assign(WoWModelViewer, {
        Tools: Tools,
        WebGL: WebGL,
        WEBGL: 1,
        WOW: 2,
        FLASH: 2,
        Wow: _l
    });
    window.ZamModelViewer = ZamModelViewer;
})();
