/*
 * PAKO 2.1.0 https://github.com/nodeca/pako @license (MIT AND Zlib)
 * https://app.unpkg.com/pako@2.1.0/files/dist/pako.js
 */
export function pako()
{
  function zero$1(buf) { let len = buf.length; while (--len >= 0) { buf[len] = 0; } }

  const LITERALS$1 = 256,
        L_CODES$1  = 286,
        D_CODES$1  = 30,
        MAX_BITS$1 = 15,
        extra_lbits  = new Uint8Array([0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 0]),
        extra_dbits  = new Uint8Array([0, 0, 0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 11, 12, 12, 13, 13]),
        extra_blbits = new Uint8Array([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 3, 7]),
        bl_order     = new Uint8Array([16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15]);

    const static_ltree = new Array(576);
    zero$1(static_ltree);

    const static_dtree = new Array(60);
    zero$1(static_dtree);

    const _dist_code = new Array(512);
    zero$1(_dist_code);

    const _length_code = new Array(256);
    zero$1(_length_code);

    const base_length = new Array(29);
    zero$1(base_length);

    const base_dist = new Array(D_CODES$1);
    zero$1(base_dist);


    function StaticTreeDesc(static_tree, extra_bits, extra_base, elems, max_length) {

        this.static_tree  = static_tree;
        this.extra_bits   = extra_bits;
        this.extra_base   = extra_base;
        this.elems        = elems;
        this.max_length   = max_length;
        this.has_stree    = static_tree && static_tree.length;
    }

    let static_l_desc, static_d_desc, static_bl_desc;

    function TreeDesc(dyn_tree, stat_desc) {
        this.dyn_tree = dyn_tree;
        this.max_code = 0;
        this.stat_desc = stat_desc;
    }

    const d_code = (dist) => {
        return dist < 256 ? _dist_code[dist] : _dist_code[256 + (dist >>> 7)];
    };

    const put_short = (s, w) => {
        s.pending_buf[s.pending++] = (w) & 0xff;
        s.pending_buf[s.pending++] = (w >>> 8) & 0xff;
    };

    const send_bits = (s, value, length) => {
        if (s.bi_valid > (Buf_size - length)) {
            s.bi_buf |= (value << s.bi_valid) & 0xffff;
            put_short(s, s.bi_buf);
            s.bi_buf = value >> (Buf_size - s.bi_valid);
            s.bi_valid += length - Buf_size;
        } else {
            s.bi_buf |= (value << s.bi_valid) & 0xffff;
            s.bi_valid += length;
        }
    };

    const send_code = (s, c, tree) => {
        send_bits(s, tree[c * 2], tree[c * 2 + 1]);
    };

    const bi_reverse = (code, len) => {
        let res = 0;
        do {
            res |= code & 1;
            code >>>= 1;
            res <<= 1;
        } while (--len > 0);
        return res >>> 1;
    };

    const bi_flush = (s) => {
        if (s.bi_valid === 16) {
            put_short(s, s.bi_buf);
            s.bi_buf = 0;
            s.bi_valid = 0;
        } else if (s.bi_valid >= 8) {
            s.pending_buf[s.pending++] = s.bi_buf & 0xff;
            s.bi_buf >>= 8;
            s.bi_valid -= 8;
        }
    };

    const gen_codes = (tree, max_code, bl_count) => {
        const next_code = new Array(MAX_BITS$1 + 1);
        let code = 0;
        let bits;
        let n;

        for (bits = 1; bits <= MAX_BITS$1; bits++) {
            code = (code + bl_count[bits - 1]) << 1;
            next_code[bits] = code;
        }

        for (n = 0;  n <= max_code; n++) {
            let len = tree[n * 2 + 1];
            if (len === 0) { continue; }

            tree[n * 2] = bi_reverse(next_code[len]++, len);
        }
    };

    const init_block = (t) => {
        let e;
        for (e = 0; e < L_CODES$1; e++) t.dyn_ltree[2 * e] = 0;
        for (e = 0; e < D_CODES$1; e++) t.dyn_dtree[2 * e] = 0;
        for (e = 0; e < 19; e++) t.bl_tree[2 * e] = 0;
        (t.dyn_ltree[512] = 1), (t.opt_len = t.static_len = 0), (t.sym_next = t.matches = 0);
    };

    const bi_windup = (s) =>
    {
        if (s.bi_valid > 8) {
            put_short(s, s.bi_buf);
        } else if (s.bi_valid > 0) {
            s.pending_buf[s.pending++] = s.bi_buf;
        }
        s.bi_buf = 0;
        s.bi_valid = 0;
    };

    const smaller = (tree, n, m, depth) => {
        const _n2 = n * 2;
        const _m2 = m * 2;
        return (tree[_n2] < tree[_m2] ||
            (tree[_n2] === tree[_m2] && depth[n] <= depth[m]));
    };

    const pqdownheap = (s, tree, k) => {
        const v = s.heap[k];
        let j = k << 1;
        while (j <= s.heap_len) {
            if (j < s.heap_len &&
                smaller(tree, s.heap[j + 1], s.heap[j], s.depth)) {
                j++;
            }

            if (smaller(tree, v, s.heap[j], s.depth)) { break; }

            s.heap[k] = s.heap[j];
            k = j;

            j <<= 1;
        }
        s.heap[k] = v;
    };

    const compress_block = (s, ltree, dtree) => {
        let dist;
        let lc;
        let sx = 0;
        let code;
        let extra;

        if (s.sym_next !== 0) {
            do {
                dist = s.pending_buf[s.sym_buf + sx++] & 0xff;
                dist += (s.pending_buf[s.sym_buf + sx++] & 0xff) << 8;
                lc = s.pending_buf[s.sym_buf + sx++];
                if (dist === 0) {
                    send_code(s, lc, ltree);
                } else {
                    code = _length_code[lc];
                    send_code(s, code + LITERALS$1 + 1, ltree);
                    extra = extra_lbits[code];
                    if (extra !== 0) {
                        lc -= base_length[code];
                        send_bits(s, lc, extra);
                    }
                    dist--;
                    code = d_code(dist);

                    send_code(s, code, dtree);
                    extra = extra_dbits[code];
                    if (extra !== 0) {
                        dist -= base_dist[code];
                        send_bits(s, dist, extra);
                    }
                }

            } while (sx < s.sym_next);
        }

        send_code(s, END_BLOCK, ltree);
    };

    const build_tree = (s, desc) => {
        const tree     = desc.dyn_tree;
        const stree    = desc.stat_desc.static_tree;
        const has_stree = desc.stat_desc.has_stree;
        const elems    = desc.stat_desc.elems;
        let n, m;
        let max_code = -1;
        let node;

        s.heap_len = 0;
        s.heap_max = HEAP_SIZE$1;

        for (n = 0; n < elems; n++) {
            if (tree[n * 2] !== 0) {
                s.heap[++s.heap_len] = max_code = n;
                s.depth[n] = 0;

            } else {
                tree[n * 2 + 1] = 0;
            }
        }

        while (s.heap_len < 2) {
            node = s.heap[++s.heap_len] = (max_code < 2 ? ++max_code : 0);
            tree[node * 2] = 1;
            s.depth[node] = 0;
            s.opt_len--;

            if (has_stree) {
                s.static_len -= stree[node * 2 + 1];
            }
        }
        desc.max_code = max_code;

        for (n = (s.heap_len >> 1); n >= 1; n--) { pqdownheap(s, tree, n); }

        node = elems;
        do {
            n = s.heap[1];
            s.heap[1] = s.heap[s.heap_len--];
            pqdownheap(s, tree, 1);

            m = s.heap[1];

            s.heap[--s.heap_max] = n;
            s.heap[--s.heap_max] = m;

            tree[node * 2] = tree[n * 2] + tree[m * 2];
            s.depth[node] = (s.depth[n] >= s.depth[m] ? s.depth[n] : s.depth[m]) + 1;
            tree[n * 2 + 1] = tree[m * 2 + 1] = node;

            s.heap[1] = node++;
            pqdownheap(s, tree, 1);
        } while (s.heap_len >= 2);

        s.heap[--s.heap_max] = s.heap[1];

        gen_bitlen(s, desc);

        gen_codes(tree, max_code, s.bl_count);
    };

    const scan_tree = (s, tree, max_code) => {
        let n;
        let prevlen = -1;
        let curlen;

        let nextlen = tree[0 * 2 + 1];

        let count = 0;
        let max_count = 7;
        let min_count = 4;

        if (nextlen === 0) {
            max_count = 138;
            min_count = 3;
        }
        tree[(max_code + 1) * 2 + 1] = 0xffff;

        for (n = 0; n <= max_code; n++) {
            curlen = nextlen;
            nextlen = tree[(n + 1) * 2 + 1];

            if (++count < max_count && curlen === nextlen) {
                continue;

            } else if (count < min_count) {
                s.bl_tree[curlen * 2] += count;

            } else if (curlen !== 0) {

                if (curlen !== prevlen) { s.bl_tree[curlen * 2]++; }
                s.bl_tree[REP_3_6 * 2]++;

            } else if (count <= 10) {
                s.bl_tree[REPZ_3_10 * 2]++;

            } else {
                s.bl_tree[REPZ_11_138 * 2]++;
            }

            count = 0;
            prevlen = curlen;

            if (nextlen === 0) {
                max_count = 138;
                min_count = 3;

            } else if (curlen === nextlen) {
                max_count = 6;
                min_count = 3;

            } else {
                max_count = 7;
                min_count = 4;
            }
        }
    };

    const send_tree = (s, tree, max_code) => {
        let n;
        let prevlen = -1;
        let curlen;

        let nextlen = tree[0 * 2 + 1];

        let count = 0;
        let max_count = 7;
        let min_count = 4;

        if (nextlen === 0) {
            max_count = 138;
            min_count = 3;
        }

        for (n = 0; n <= max_code; n++) {
            curlen = nextlen;
            nextlen = tree[(n + 1) * 2 + 1];

            if (++count < max_count && curlen === nextlen) {
                continue;

            } else if (count < min_count) {
                do { send_code(s, curlen, s.bl_tree); } while (--count !== 0);

            } else if (curlen !== 0) {
                if (curlen !== prevlen) {
                    send_code(s, curlen, s.bl_tree);
                    count--;
                }

                send_code(s, REP_3_6, s.bl_tree);
                send_bits(s, count - 3, 2);

            } else if (count <= 10) {
                send_code(s, REPZ_3_10, s.bl_tree);
                send_bits(s, count - 3, 3);

            } else {
                send_code(s, REPZ_11_138, s.bl_tree);
                send_bits(s, count - 11, 7);
            }

            count = 0;
            prevlen = curlen;
            if (nextlen === 0) {
                max_count = 138;
                min_count = 3;

            } else if (curlen === nextlen) {
                max_count = 6;
                min_count = 3;

            } else {
                max_count = 7;
                min_count = 4;
            }
        }
    };

    const build_bl_tree = (s) => {
        let max_blindex;

        scan_tree(s, s.dyn_ltree, s.l_desc.max_code);
        scan_tree(s, s.dyn_dtree, s.d_desc.max_code);

        build_tree(s, s.bl_desc);

        for (max_blindex = 18; max_blindex >= 3; max_blindex--) {
            if (s.bl_tree[bl_order[max_blindex] * 2 + 1] !== 0) {
                break;
            }
        }

        s.opt_len += 3 * (max_blindex + 1) + 5 + 5 + 4;

        return max_blindex;
    };

    const send_all_trees = (s, lcodes, dcodes, blcodes) => {
        let rank;

        send_bits(s, lcodes  - 257, 5);
        send_bits(s, dcodes  - 1,   5);
        send_bits(s, blcodes - 4,   4);

        for (rank = 0; rank < blcodes; rank++)
            send_bits(s, s.bl_tree[2 * bl_order[rank] + 1], 3);

        send_tree(s, s.dyn_ltree, lcodes - 1);
        send_tree(s, s.dyn_dtree, dcodes - 1);
    }

    const detect_data_type = (s) => {
        let block_mask = 0xf3ffc07f;
        let n;

        for (n = 0; n <= 31; n++, block_mask >>>= 1) {
            if ((block_mask & 1) && (s.dyn_ltree[n * 2] !== 0)) {
                return 0;
            }
        }

        if (s.dyn_ltree[9 * 2] !== 0 || s.dyn_ltree[10 * 2] !== 0 ||
            s.dyn_ltree[13 * 2] !== 0) {
            return 1;
        }
        for (n = 32; n < LITERALS$1; n++) {
            if (s.dyn_ltree[n * 2] !== 0) {
                return 1;
            }
        }

        return 0;
    };

    let static_init_done = false;

    const _tr_stored_block$1 = (s, buf, stored_len, last) => {
        send_bits(s, (STORED_BLOCK << 1) + (last ? 1 : 0), 3);
        bi_windup(s);
        put_short(s, stored_len);
        put_short(s, ~stored_len);
        if (stored_len) {
            s.pending_buf.set(s.window.subarray(buf, buf + stored_len), s.pending);
        }
        s.pending += stored_len;
    };

    const tr_static_init = () => {
        let n;
        let bits;
        let length;
        let code;
        let dist;
        const bl_count = new Array(MAX_BITS$1 + 1);

        length = 0;
        for (code = 0; code < LENGTH_CODES$1 - 1; code++) {
            base_length[code] = length;
            for (n = 0; n < (1 << extra_lbits[code]); n++) {
                _length_code[length++] = code;
            }
        }

        _length_code[length - 1] = code;

        dist = 0;
        for (code = 0; code < 16; code++) {
            base_dist[code] = dist;
            for (n = 0; n < (1 << extra_dbits[code]); n++) {
                _dist_code[dist++] = code;
            }
        }

        dist >>= 7;
        for (; code < D_CODES$1; code++) {
            base_dist[code] = dist << 7;
            for (n = 0; n < (1 << (extra_dbits[code] - 7)); n++) {
                _dist_code[256 + dist++] = code;
            }
        }

        for (bits = 0; bits <= MAX_BITS$1; bits++) {
            bl_count[bits] = 0;
        }

        n = 0;
        while (n <= 143) {
            static_ltree[n * 2 + 1] = 8;
            n++;
            bl_count[8]++;
        }
        while (n <= 255) {
            static_ltree[n * 2 + 1] = 9;
            n++;
            bl_count[9]++;
        }
        while (n <= 279) {
            static_ltree[n * 2 + 1] = 7;
            n++;
            bl_count[7]++;
        }
        while (n <= 287) {
            static_ltree[n * 2 + 1] = 8;
            n++;
            bl_count[8]++;
        }

        gen_codes(static_ltree, L_CODES$1 + 1, bl_count);

        for (n = 0; n < D_CODES$1; n++) {
            static_dtree[n * 2 + 1] = 5;
            static_dtree[n * 2] = bi_reverse(n, 5);
        }

        static_l_desc  = new StaticTreeDesc(static_ltree, extra_lbits,  LITERALS$1 + 1, L_CODES$1,  MAX_BITS$1);
        static_d_desc  = new StaticTreeDesc(static_dtree, extra_dbits,  0,              D_CODES$1,  MAX_BITS$1);
        static_bl_desc = new StaticTreeDesc(new Array(0), extra_blbits, 0,              19,         7);
    };

    const _tr_init$1 = (s) => {
        if (!static_init_done) {
            tr_static_init();
            static_init_done = true;
        }

        s.l_desc  = new TreeDesc(s.dyn_ltree, static_l_desc);
        s.d_desc  = new TreeDesc(s.dyn_dtree, static_d_desc);
        s.bl_desc = new TreeDesc(s.bl_tree,   static_bl_desc);

        s.bi_buf = 0;
        s.bi_valid = 0;

        init_block(s);
    };

    const _tr_align$1 = (s) => {
        send_bits(s, 2, 3);
        send_code(s, 256, static_ltree);
        bi_flush(s);
    };

    const _tr_flush_block$1 = (s, buf, stored_len, last) => {
        let opt_lenb,
            static_lenb,
            max_blindex  = 0;

        if (s.level > 0)
        {
            if (s.strm.data_type === 2) {
                s.strm.data_type = detect_data_type(s);
            }

            build_tree(s, s.l_desc);
            build_tree(s, s.d_desc);

            max_blindex  = build_bl_tree(s);

            opt_lenb = (s.opt_len + 3 + 7) >>> 3;
            static_lenb = (s.static_len + 3 + 7) >>> 3;

            if (static_lenb <= opt_lenb) { opt_lenb = static_lenb; }
        } else {
            opt_lenb = static_lenb = stored_len + 5;
        }

        if (stored_len + 4 <= opt_lenb && buf !== -1) {
            _tr_stored_block$1(s, buf, stored_len, last);
        } else if (s.strategy === 4 || static_lenb === opt_lenb) {
            send_bits(s, 2 + (last ? 1 : 0), 3);
            compress_block(s, static_ltree, static_dtree);
        } else {
            send_bits(s, 4 + (last ? 1 : 0), 3);
            send_all_trees(s, s.l_desc.max_code + 1, s.d_desc.max_code + 1, max_blindex  + 1);
            compress_block(s, s.dyn_ltree, s.dyn_dtree);
        }

        init_block(s);

        if (last) {
            bi_windup(s);
        }
    };

    const _tr_tally$1 = (s, dist, lc) => {
        s.pending_buf[s.sym_buf + s.sym_next++] = dist;
        s.pending_buf[s.sym_buf + s.sym_next++] = dist >> 8;
        s.pending_buf[s.sym_buf + s.sym_next++] = lc;
        if (dist === 0) {
            s.dyn_ltree[2 * lc]++;
        } else {
            s.matches++, dist--, s.dyn_ltree[2 * (_length_code[lc] + LITERALS$1 + 1)]++;
            s.dyn_dtree[2 * d_code(dist)]++;
        }

        return s.sym_next === s.sym_end;
    };

    const trees = {
        _tr_init: _tr_init$1,
        _tr_stored_block: _tr_stored_block$1,
        _tr_flush_block: _tr_flush_block$1,
        _tr_tally: _tr_tally$1,
        _tr_align: _tr_align$1
    };

    var adler32 = (adler, buf, len, pos) => {
        let s1 = (adler & 0xffff) | 0,
            s2 = ((adler >>> 16) & 0xffff) | 0,
            n = 0;
        while (len !== 0) {
            n = len > 2000 ? 2000 : len;
            len -= n;
            do {
                s1 = (s1 + buf[pos++]) | 0;
                s2 = (s2 + s1) | 0;
            } while (--n);

            s1 %= 65521;
            s2 %= 65521;
        }

        return (s1 | (s2 << 16)) | 0;
    };

    const makeTable = () => {
        let c,
            table = [];

        for (var n = 0; n < 256; n++) {
            c = n;
            for (var s = 0; s < 8; s++) {
                c = ((1 & c) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1));
            }
            table[n] = c;
        }

        return table;
    };

    const crcTable = new Uint32Array(makeTable());

    var crc32 = (crc, buf, len, pos) => {
        const t = crcTable,
              end = pos + len;

        crc ^= -1;

        for (let i = pos; i < end; i++) {
            crc = (crc >>> 8) ^ t[(crc ^ buf[i]) & 0xFF];
        }

        return -1 ^ crc;
    };

    var messages = {
        2:    "need dictionary",
        1:    "stream end",
        0:    "",
        "-1": "file error",
        "-2": "stream error",
        "-3": "data error",
        "-4": "insufficient memory",
        "-5": "buffer error",
        "-6": "incompatible version"
    };

    const constants$2 = {
        Z_NO_FLUSH:      0,
        Z_PARTIAL_FLUSH: 1,
        Z_SYNC_FLUSH:    2,
        Z_FULL_FLUSH:    3,
        Z_FINISH:        4,
        Z_BLOCK:         5,
        Z_TREES:         6,

        Z_OK:            0,
        Z_STREAM_END:    1,
        Z_NEED_DICT:     2,
        Z_ERRNO:        -1,
        Z_STREAM_ERROR: -2,
        Z_DATA_ERROR:   -3,
        Z_MEM_ERROR:    -4,
        Z_BUF_ERROR:    -5,

        Z_NO_COMPRESSION:       0,
        Z_BEST_SPEED:           1,
        Z_BEST_COMPRESSION:     9,
        Z_DEFAULT_COMPRESSION: -1,

        Z_FILTERED:             1,
        Z_HUFFMAN_ONLY:         2,
        Z_RLE:                  3,
        Z_FIXED:                4,
        Z_DEFAULT_STRATEGY:     0,

        Z_BINARY:               0,
        Z_TEXT:                 1,
        Z_UNKNOWN:              2,

        Z_DEFLATED:             8,
    };

    const { _tr_init, _tr_stored_block, _tr_flush_block, _tr_tally, _tr_align } = trees;
    const {
        Z_NO_FLUSH: Z_NO_FLUSH$2, Z_PARTIAL_FLUSH: ia, Z_FULL_FLUSH: Z_FULL_FLUSH$1, Z_FINISH: Z_FINISH$3, Z_BLOCK: Z_BLOCK$1,
        Z_OK: Z_OK$3, Z_STREAM_END: Z_STREAM_END$3, Z_STREAM_ERROR: Z_STREAM_ERROR$2, Z_DATA_ERROR: Z_DATA_ERROR$2, Z_BUF_ERROR: Z_BUF_ERROR$1,
        Z_DEFAULT_COMPRESSION: Z_DEFAULT_COMPRESSION$1,
        Z_FILTERED: da, Z_HUFFMAN_ONLY: fa, Z_RLE: ga, Z_FIXED: _a, Z_DEFAULT_STRATEGY: Z_DEFAULT_STRATEGY$1,
        Z_UNKNOWN: ma,
        Z_DEFLATED: Z_DEFLATED$2,
    } = constants$2;

    const MAX_MATCH     = 258,
          MIN_LOOKAHEAD = 262,

          INIT_STATE   = 42,
          BUSY_STATE   = 113,
          FINISH_STATE = 666;

    const err = (strm, errCode) => {
        strm.msg = messages[errCode];
        return errCode;
    };

    const rank = (f) => {
        return ((f) * 2) - ((f) > 4 ? 9 : 0);
    };

    const zero = (buf) => {
        let len = buf.length; while (--len >= 0) { buf[len] = 0; }
    };

    const slide_hash = (s) => {
        let n,
            m,
            p,
            wsize = s.w_size;

        n = s.hash_size;
        p = n;

        do {
            m = s.head[--p];
            s.head[p] = (m >= wsize ? m - wsize : 0);
        } while (--n);

        n = wsize;
        p = n;

        do {
            m = s.prev[--p];
            s.prev[p] = m >= wsize ? m - wsize : 0;
        } while (--n);
    };

    let HASH_ZLIB = (s, prev, data) => ((prev << s.hash_shift) ^ data) & s.hash_mask;

    const flush_pending = (strm) => {
        const s = strm.state;
        let len = s.pending;

        if (len > strm.avail_out) {
            len = strm.avail_out;
        }
        if (len === 0) { return; }

        strm.output.set(s.pending_buf.subarray(s.pending_out, s.pending_out + len), strm.next_out);
        strm.next_out  += len;
        s.pending_out  += len;
        strm.total_out += len;
        strm.avail_out -= len;
        s.pending      -= len;
        if (s.pending === 0) {
            s.pending_out = 0;
        }
    };

    const flush_block_only = (s, last) => {
        _tr_flush_block(s, (s.block_start >= 0 ? s.block_start : -1), s.strstart - s.block_start, last);
        s.block_start = s.strstart;
        flush_pending(s.strm);
    },

    const put_byte = (s, b) => {
        s.pending_buf[s.pending++] = b;
    };

    const putShortMSB = (s, b) => {
        s.pending_buf[s.pending++] = (b >>> 8) & 0xFF;
        s.pending_buf[s.pending++] = 0xFF & b;
    };

    const read_buf = (strm, buf, start, size) => {
        let len = strm.avail_in;

        if (len > size) { len = size; }
        if (len === 0) { return 0; }

        strm.avail_in -= len;

        buf.set(strm.input.subarray(strm.next_in, strm.next_in + len), start);
        if (strm.state.wrap === 1) {
            strm.adler = adler32(strm.adler, buf, len, start);
        }
        else if (strm.state.wrap === 2) {
            strm.adler = crc32(strm.adler, buf, len, start);
        }

        strm.next_in += len;
        strm.total_in += len;

        return len;
    };

    const longest_match = (s, cur_match) => {
        let match,
            len,
            chain_length = s.max_chain_length,
            scan = s.strstart,
            best_len = s.prev_length,
            nice_match = s.nice_match;
        const limit = s.strstart > s.w_size - MIN_LOOKAHEAD ?
            s.strstart - (s.w_size - MIN_LOOKAHEAD) : 0,

        const _win = s.window;

        const wmast = s.w_mask,
              prev = s.prev;

        const strend = s.strstart + MAX_MATCH;

        let scan_end1 = _win[scan + best_len - 1],
            scan_end  = _win[scan + best_len];

        if (s.prev_length >= s.good_match) {
            chain_length >>= 2;
        }

        if (nice_match > s.lookahead) { nice_match = s.lookahead; }

        do {
            match = cur_match;
            if (_win[match + best_len]     !== scan_end  ||
                _win[match + best_len - 1] !== scan_end1 ||
                _win[match]                !== _win[scan] ||
                _win[++match]              !== _win[scan + 1]) {
                continue;
            }

            scan += 2;
            match++;

            do {} while (_win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
                         _win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
                         _win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
                         _win[++scan] === _win[++match] && _win[++scan] === _win[++match] &&
                        scan < strend);

            len = MAX_MATCH - (strend - scan);
            scan = strend - MAX_MATCH;

            if (len > best_len) {
                s.match_start = cur_match;
                best_len = len;
                if (len >= nice_match) {
                    break;
                }
                scan_end1 = _win[scan + best_len - 1];
                scan_end = _win[scan + best_len];
            }
        } while ((cur_match = prev[cur_match & wmast]) > limit && --chain_length !== 0);

        if (best_len <= s.lookahead) {
            return best_len;
        }
        return s.lookahead;
    };

    const fill_window = (s) => {
        const _w_size = s.w_size;
        let n, more, str;
        do {
            more = s.window_size - s.lookahead - s.strstart;

            if (s.strstart >= _w_size + (_w_size - MIN_LOOKAHEAD)) {
                s.window.set(s.window.subarray(_w_size, _w_size + _w_size - more), 0);
                s.match_start -= _w_size;
                s.strstart -= _w_size;
                s.block_start -= _w_size;
                if (s.insert > s.strstart) {
                    s.insert = s.strstart;
                }
                slide_hash(s),
                more += _w_size;
            }
            if (s.strm.avail_in === 0) {
                break;
            }

            n = read_buf(s.strm, s.window, s.strstart + s.lookahead, more);
            s.lookahead += n;

            if (s.lookahead + s.insert >= 3) {
                str = s.strstart - s.insert;
                s.ins_h = s.window[str];

                s.ins_h = HASH_ZLIB(s, s.ins_h, s.window[str + 1]);

                while (s.insert) {
                    s.ins_h = HASH_ZLIB(s, s.ins_h, s.window[str + 3 - 1]);

                    s.prev[str & s.w_mask] = s.head[s.ins_h];
                    s.head[s.ins_h] = str;
                    str++;
                    s.insert--;
                    if (s.lookahead + s.insert < 3) {
                        break;
                    }
                }
            }
        } while (s.lookahead < MIN_LOOKAHEAD && s.strm.avail_in !== 0);
    };

    const deflate_stored = (t, flush) => {
        let min_block = t.pending_buf_size - 5 > t.w_size ? t.w_size : t.pending_buf_size - 5;

        let len, left, have, last = 0,
            used = t.strm.avail_in;

        do {
            len = 65535;
            have = (t.bi_valid + 42) >> 3;
            if (t.strm.avail_out < have) {
                break;
            }

            have = t.strm.avail_out - have;
            left = t.strstart - t.block_start;
            if (len > left + t.strm.avail_in) {
                len = left + t.strm.avail_in;
            }
            if (len > have) {
                len = have;
            }

            if (len < min_block && ((len === 0 && flush !== Z_FINISH$3) ||
                                flush === Z_NO_FLUSH$2 ||
                                len !== left + t.strm.avail_in)) {
                break;
            }

            last = flush === Z_FINISH$3 && len === left + t.strm.avail_in ? 1 : 0;
            _tr_stored_block$1(t, 0, 0, last);

            t.pending_buf[t.pending - 4] = len;
            t.pending_buf[t.pending - 3] = len >> 8;
            t.pending_buf[t.pending - 2] = ~len;
            t.pending_buf[t.pending - 1] = ~len >> 8;

            flush_pending(t.strm);

            if (left) {
                if (left > len) {
                     left = len;
                }

                t.strm.output.set(t.window.subarray(t.block_start, t.block_start + left), t.strm.next_out);
                t.strm.next_out += left;
                t.strm.avail_out -= left;
                t.strm.total_out += left;
                t.block_start += left;
                len -= left;
            }

            if (len) {
                read_buf(t.strm, t.strm.output, t.strm.next_out, len);
                t.strm.next_out += len;
                t.strm.avail_out -= len;
                t.strm.total_out += len;
            }
        } while (0 === last);

        used -= t.strm.avail_in;
        if (used) {
            if (used >= t.w_size) {
                t.matches = 2;
                t.window.set(t.strm.input.subarray(t.strm.next_in - t.w_size, t.strm.next_in), 0);
                t.strstart = t.w_size;
                t.insert = t.strstart;
            }
            else {
                if (t.window_size - t.strstart <= used) {
                    t.strstart -= t.w_size;
                    t.window.set(t.window.subarray(t.w_size, t.w_size + t.strstart), 0);
                    if (t.matches < 2) {
                        t.matches++;
                    }
                    if (t.insert > t.strstart) {
                        t.insert = t.strstart;
                    }
                }
                t.window.set(t.strm.input.subarray(t.strm.next_in - used, t.strm.next_in), t.strstart);
                t.strstart += used;
                t.insert += used > t.w_size - t.insert ? t.w_size - t.insert : used;
            }
            t.block_start = t.strstart;
        }
        if (t.high_water < t.strstart) {
            t.high_water = t.strstart;
        }
        if (last) {
            return 4;
        }

        if (flush !== Z_NO_FLUSH$2 && flush !== Z_FINISH$3 && 0 === t.strm.avail_in && t.strstart === t.block_start) {
            return 2;
        }

        have = t.window_size - t.strstart;
        if (t.strm.avail_in > have && t.block_start >= t.w_size) {
            t.block_start -= t.w_size;
            t.strstart -= t.w_size;
            t.window.set(t.window.subarray(t.w_size, t.w_size + t.strstart), 0);
            if (t.matches < 2) {
                t.matches++;
            }
            have += t.w_size;
            if (t.insert > t.strstart) {
                t.insert = t.strstart;
            }
        }
        if (have > t.strm.avail_in) {
            have = t.strm.avail_in;
        }
        if (have) {
            read_buf(t.strm, t.window, t.strstart, have);
            t.strstart += have;
            t.insert += have > t.w_size - t.insert ? t.w_size - t.insert : have;
        }
        if (t.high_water < t.strstart) {
            t.high_water = t.strstart;
        }

        have = (t.bi_valid + 42) >> 3;
        have = t.pending_buf_size - have > 65535 ? 65535 : t.pending_buf_size - have;
        min_block = have > t.w_size ? t.w_size : have;
        left = t.strstart - t.block_start;
        if (left >= min_block || ((left || flush === Z_FINISH$3) && flush !== Z_NO_FLUSH$2 && 0 === t.strm.avail_in && left <= have)) {
            len = left > have ? have : left;
            last = flush === Z_FINISH$3 && 0 === t.strm.avail_in && len === left ? 1 : 0;
            _tr_stored_block$1(t, t.block_start, len, last);
            t.block_start += len;
            flush_pending(t.strm);
        }

        return last ? 3 : 1
    };

    // https://unpkg.com/pako@2.1.0/dist/pako.js
    // const deflate_fast = (s, flush) => {

    const Pa = (t, e) => {
            let i, s;
            for (;;) {
                if (t.lookahead < MIN_LOOKAHEAD) {
                    if ((fill_window(t), t.lookahead < MIN_LOOKAHEAD && e === Z_NO_FLUSH$2)) return 1;
                    if (0 === t.lookahead) break;
                }
                if (
                    ((i = 0),
                    t.lookahead >= 3 &&
                        ((t.ins_h = HASH_ZLIB(t, t.ins_h, t.window[t.strstart + 3 - 1])),
                        (i = t.prev[t.strstart & t.w_mask] = t.head[t.ins_h]),
                        (t.head[t.ins_h] = t.strstart)),
                    0 !== i && t.strstart - i <= t.w_size - MIN_LOOKAHEAD && (t.match_length = longest_match(t, i)),
                    t.match_length >= 3)
                )
                    if (
                        ((s = Qn(t, t.strstart - t.match_start, t.match_length - 3)),
                        (t.lookahead -= t.match_length),
                        t.match_length <= t.max_lazy_match && t.lookahead >= 3)
                    ) {
                        t.match_length--;
                        do {
                            t.strstart++,
                                (t.ins_h = HASH_ZLIB(t, t.ins_h, t.window[t.strstart + 3 - 1])),
                                (i = t.prev[t.strstart & t.w_mask] = t.head[t.ins_h]),
                                (t.head[t.ins_h] = t.strstart);
                        } while (0 !== --t.match_length);
                        t.strstart++;
                    } else
                        (t.strstart += t.match_length),
                            (t.match_length = 0),
                            (t.ins_h = t.window[t.strstart]),
                            (t.ins_h = HASH_ZLIB(t, t.ins_h, t.window[t.strstart + 1]));
                else (s = Qn(t, 0, t.window[t.strstart])), t.lookahead--, t.strstart++;
                if (s && (flush_block_only(t, false), 0 === t.strm.avail_out)) return 1;
            }
            return (
                (t.insert = t.strstart < 2 ? t.strstart : 2),
                e === Z_FINISH$3
                    ? (flush_block_only(t, true), 0 === t.strm.avail_out ? 3 : 4)
                    : t.sym_next && (flush_block_only(t, false), 0 === t.strm.avail_out)
                      ? 1
                      : 2
            );
        },
        za = (t, e) => {
            let i, s, r;
            for (;;) {
                if (t.lookahead < MIN_LOOKAHEAD) {
                    if ((fill_window(t), t.lookahead < MIN_LOOKAHEAD && e === Z_NO_FLUSH$2)) return 1;
                    if (0 === t.lookahead) break;
                }
                if (
                    ((i = 0),
                    t.lookahead >= 3 &&
                        ((t.ins_h = HASH_ZLIB(t, t.ins_h, t.window[t.strstart + 3 - 1])),
                        (i = t.prev[t.strstart & t.w_mask] = t.head[t.ins_h]),
                        (t.head[t.ins_h] = t.strstart)),
                    (t.prev_length = t.match_length),
                    (t.prev_match = t.match_start),
                    (t.match_length = 2),
                    0 !== i &&
                        t.prev_length < t.max_lazy_match &&
                        t.strstart - i <= t.w_size - MIN_LOOKAHEAD &&
                        ((t.match_length = longest_match(t, i)),
                        t.match_length <= 5 &&
                            (t.strategy === da || (3 === t.match_length && t.strstart - t.match_start > 4096)) &&
                            (t.match_length = 2)),
                    t.prev_length >= 3 && t.match_length <= t.prev_length)
                ) {
                    (r = t.strstart + t.lookahead - 3),
                        (s = Qn(t, t.strstart - 1 - t.prev_match, t.prev_length - 3)),
                        (t.lookahead -= t.prev_length - 1),
                        (t.prev_length -= 2);
                    do {
                        ++t.strstart <= r &&
                            ((t.ins_h = HASH_ZLIB(t, t.ins_h, t.window[t.strstart + 3 - 1])),
                            (i = t.prev[t.strstart & t.w_mask] = t.head[t.ins_h]),
                            (t.head[t.ins_h] = t.strstart));
                    } while (0 !== --t.prev_length);
                    if (
                        ((t.match_available = 0),
                        (t.match_length = 2),
                        t.strstart++,
                        s && (flush_block_only(t, false), 0 === t.strm.avail_out))
                    )
                        return 1;
                } else if (t.match_available) {
                    if (
                        ((s = Qn(t, 0, t.window[t.strstart - 1])),
                        s && flush_block_only(t, false),
                        t.strstart++,
                        t.lookahead--,
                        0 === t.strm.avail_out)
                    )
                        return 1;
                } else (t.match_available = 1), t.strstart++, t.lookahead--;
            }
            return (
                t.match_available && ((s = Qn(t, 0, t.window[t.strstart - 1])), (t.match_available = 0)),
                (t.insert = t.strstart < 2 ? t.strstart : 2),
                e === Z_FINISH$3
                    ? (flush_block_only(t, true), 0 === t.strm.avail_out ? 3 : 4)
                    : t.sym_next && (flush_block_only(t, false), 0 === t.strm.avail_out)
                      ? 1
                      : 2
            );
        };
    function Ha(t, e, i, s, r) {
        (this.good_length = t), (this.max_lazy = e), (this.nice_length = i), (this.max_chain = s), (this.func = r);
    }
    const Na = [
        new Ha(0, 0, 0, 0, deflate_stored),
        new Ha(4, 4, 8, 4, Pa),
        new Ha(4, 5, 16, 8, Pa),
        new Ha(4, 6, 32, 32, Pa),
        new Ha(4, 4, 16, 16, za),
        new Ha(8, 16, 32, 32, za),
        new Ha(8, 16, 128, 128, za),
        new Ha(8, 32, 128, 256, za),
        new Ha(32, 128, 258, 1024, za),
        new Ha(32, 258, 258, 4096, za),
    ];
    function Ga() {
        (this.strm = null),
            (this.status = 0),
            (this.pending_buf = null),
            (this.pending_buf_size = 0),
            (this.pending_out = 0),
            (this.pending = 0),
            (this.wrap = 0),
            (this.gzhead = null),
            (this.gzindex = 0),
            (this.method = Z_DEFLATED$2),
            (this.last_flush = -1),
            (this.w_size = 0),
            (this.w_bits = 0),
            (this.w_mask = 0),
            (this.window = null),
            (this.window_size = 0),
            (this.prev = null),
            (this.head = null),
            (this.ins_h = 0),
            (this.hash_size = 0),
            (this.hash_bits = 0),
            (this.hash_mask = 0),
            (this.hash_shift = 0),
            (this.block_start = 0),
            (this.match_length = 0),
            (this.prev_match = 0),
            (this.match_available = 0),
            (this.strstart = 0),
            (this.match_start = 0),
            (this.lookahead = 0),
            (this.prev_length = 0),
            (this.max_chain_length = 0),
            (this.max_lazy_match = 0),
            (this.level = 0),
            (this.strategy = 0),
            (this.good_match = 0),
            (this.nice_match = 0),
            (this.dyn_ltree = new Uint16Array(1146)),
            (this.dyn_dtree = new Uint16Array(122)),
            (this.bl_tree = new Uint16Array(78)),
            zero(this.dyn_ltree),
            zero(this.dyn_dtree),
            zero(this.bl_tree),
            (this.l_desc = null),
            (this.d_desc = null),
            (this.bl_desc = null),
            (this.bl_count = new Uint16Array(16)),
            (this.heap = new Uint16Array(573)),
            zero(this.heap),
            (this.heap_len = 0),
            (this.heap_max = 0),
            (this.depth = new Uint16Array(573)),
            zero(this.depth),
            (this.sym_buf = 0),
            (this.lit_bufsize = 0),
            (this.sym_next = 0),
            (this.sym_end = 0),
            (this.opt_len = 0),
            (this.static_len = 0),
            (this.matches = 0),
            (this.insert = 0),
            (this.bi_buf = 0),
            (this.bi_valid = 0);
    }
    const La = (t) => {
            if (!t) return 1;
            const e = t.state;
            return !e ||
                e.strm !== t ||
                (e.status !== INIT_STATE &&
                    57 !== e.status &&
                    69 !== e.status &&
                    73 !== e.status &&
                    91 !== e.status &&
                    103 !== e.status &&
                    e.status !== BUSY_STATE &&
                    e.status !== FINISH_STATE)
                ? 1
                : 0;
        },
        ja = (t) => {
            if (La(t)) return err(t, Z_STREAM_ERROR$2);
            (t.total_in = t.total_out = 0), (t.data_type = ma);
            const e = t.state;
            return (
                (e.pending = 0),
                (e.pending_out = 0),
                e.wrap < 0 && (e.wrap = -e.wrap),
                (e.status = 2 === e.wrap ? 57 : e.wrap ? INIT_STATE : BUSY_STATE),
                (t.adler = 2 === e.wrap ? 0 : 1),
                (e.last_flush = -2),
                Kn(e),
                Z_OK$3
            );
        },
        qa = (t) => {
            const e = ja(t);
            var i;
            return (
                e === Z_OK$3 &&
                    (((i = t.state).window_size = 2 * i.w_size),
                    zero(i.head),
                    (i.max_lazy_match = Na[i.level].max_lazy),
                    (i.good_match = Na[i.level].good_length),
                    (i.nice_match = Na[i.level].nice_length),
                    (i.max_chain_length = Na[i.level].max_chain),
                    (i.strstart = 0),
                    (i.block_start = 0),
                    (i.lookahead = 0),
                    (i.insert = 0),
                    (i.match_length = i.prev_length = 2),
                    (i.match_available = 0),
                    (i.ins_h = 0)),
                e
            );
        },
        Va = (t, e, i, s, r, n) => {
            if (!t) return Z_STREAM_ERROR$2;
            let a = 1;
            if (
                (e === Z_DEFAULT_COMPRESSION$1 && (e = 6),
                s < 0 ? ((a = 0), (s = -s)) : s > 15 && ((a = 2), (s -= 16)),
                r < 1 ||
                    r > 9 ||
                    i !== Z_DEFLATED$2 ||
                    s < 8 ||
                    s > 15 ||
                    e < 0 ||
                    e > 9 ||
                    n < 0 ||
                    n > _a ||
                    (8 === s && 1 !== a))
            )
                return err(t, Z_STREAM_ERROR$2);
            8 === s && (s = 9);
            const o = new Ga();
            return (
                (t.state = o),
                (o.strm = t),
                (o.status = INIT_STATE),
                (o.wrap = a),
                (o.gzhead = null),
                (o.w_bits = s),
                (o.w_size = 1 << o.w_bits),
                (o.w_mask = o.w_size - 1),
                (o.hash_bits = r + 7),
                (o.hash_size = 1 << o.hash_bits),
                (o.hash_mask = o.hash_size - 1),
                (o.hash_shift = ~~((o.hash_bits + 3 - 1) / 3)),
                (o.window = new Uint8Array(2 * o.w_size)),
                (o.head = new Uint16Array(o.hash_size)),
                (o.prev = new Uint16Array(o.w_size)),
                (o.lit_bufsize = 1 << (r + 6)),
                (o.pending_buf_size = 4 * o.lit_bufsize),
                (o.pending_buf = new Uint8Array(o.pending_buf_size)),
                (o.sym_buf = o.lit_bufsize),
                (o.sym_end = 3 * (o.lit_bufsize - 1)),
                (o.level = e),
                (o.strategy = n),
                (o.method = i),
                qa(t)
            );
        };
    var Wa = (t, e) => {
            if (La(t) || e > Z_BLOCK$1 || e < 0) return t ? err(t, Z_STREAM_ERROR$2) : Z_STREAM_ERROR$2;
            const i = t.state;
            if (!t.output || (0 !== t.avail_in && !t.input) || (i.status === FINISH_STATE && e !== Z_FINISH$3))
                return err(t, 0 === t.avail_out ? Z_BUF_ERROR$1 : Z_STREAM_ERROR$2);
            const s = i.last_flush;
            if (((i.last_flush = e), 0 !== i.pending)) {
                if ((flush_pending(t), 0 === t.avail_out)) return (i.last_flush = -1), Z_OK$3;
            } else if (0 === t.avail_in && rank(e) <= rank(s) && e !== Z_FINISH$3) return err(t, Z_BUF_ERROR$1);
            if (i.status === FINISH_STATE && 0 !== t.avail_in) return err(t, Z_BUF_ERROR$1);
            if ((i.status === INIT_STATE && 0 === i.wrap && (i.status = BUSY_STATE), i.status === INIT_STATE)) {
                let e = (Z_DEFLATED$2 + ((i.w_bits - 8) << 4)) << 8,
                    s = -1;
                if (
                    ((s = i.strategy >= fa || i.level < 2 ? 0 : i.level < 6 ? 1 : 6 === i.level ? 2 : 3),
                    (e |= s << 6),
                    0 !== i.strstart && (e |= 32),
                    (e += 31 - (e % 31)),
                    putShortMSB(i, e),
                    0 !== i.strstart && (putShortMSB(i, t.adler >>> 16), putShortMSB(i, 65535 & t.adler)),
                    (t.adler = 1),
                    (i.status = BUSY_STATE),
                    flush_pending(t),
                    0 !== i.pending)
                )
                    return (i.last_flush = -1), Z_OK$3;
            }
            if (57 === i.status)
                if (((t.adler = 0), put_byte(i, 31), put_byte(i, 139), put_byte(i, 8), i.gzhead))
                    put_byte(
                        i,
                        (i.gzhead.text ? 1 : 0) +
                            (i.gzhead.hcrc ? 2 : 0) +
                            (i.gzhead.extra ? 4 : 0) +
                            (i.gzhead.name ? 8 : 0) +
                            (i.gzhead.comment ? 16 : 0)
                    ),
                        put_byte(i, 255 & i.gzhead.time),
                        put_byte(i, (i.gzhead.time >> 8) & 255),
                        put_byte(i, (i.gzhead.time >> 16) & 255),
                        put_byte(i, (i.gzhead.time >> 24) & 255),
                        put_byte(i, 9 === i.level ? 2 : i.strategy >= fa || i.level < 2 ? 4 : 0),
                        put_byte(i, 255 & i.gzhead.os),
                        i.gzhead.extra &&
                            i.gzhead.extra.length &&
                            (put_byte(i, 255 & i.gzhead.extra.length), put_byte(i, (i.gzhead.extra.length >> 8) & 255)),
                        i.gzhead.hcrc && (t.adler = crc32(t.adler, i.pending_buf, i.pending, 0)),
                        (i.gzindex = 0),
                        (i.status = 69);
                else if (
                    (put_byte(i, 0),
                    put_byte(i, 0),
                    put_byte(i, 0),
                    put_byte(i, 0),
                    put_byte(i, 0),
                    put_byte(i, 9 === i.level ? 2 : i.strategy >= fa || i.level < 2 ? 4 : 0),
                    put_byte(i, 3),
                    (i.status = BUSY_STATE),
                    flush_pending(t),
                    0 !== i.pending)
                )
                    return (i.last_flush = -1), Z_OK$3;
            if (69 === i.status) {
                if (i.gzhead.extra) {
                    let e = i.pending,
                        s = (65535 & i.gzhead.extra.length) - i.gzindex;
                    for (; i.pending + s > i.pending_buf_size; ) {
                        let r = i.pending_buf_size - i.pending;
                        if (
                            (i.pending_buf.set(i.gzhead.extra.subarray(i.gzindex, i.gzindex + r), i.pending),
                            (i.pending = i.pending_buf_size),
                            i.gzhead.hcrc && i.pending > e && (t.adler = crc32(t.adler, i.pending_buf, i.pending - e, e)),
                            (i.gzindex += r),
                            flush_pending(t),
                            0 !== i.pending)
                        )
                            return (i.last_flush = -1), Z_OK$3;
                        (e = 0), (s -= r);
                    }
                    let r = new Uint8Array(i.gzhead.extra);
                    i.pending_buf.set(r.subarray(i.gzindex, i.gzindex + s), i.pending),
                        (i.pending += s),
                        i.gzhead.hcrc && i.pending > e && (t.adler = crc32(t.adler, i.pending_buf, i.pending - e, e)),
                        (i.gzindex = 0);
                }
                i.status = 73;
            }
            if (73 === i.status) {
                if (i.gzhead.name) {
                    let e,
                        s = i.pending;
                    do {
                        if (i.pending === i.pending_buf_size) {
                            if (
                                (i.gzhead.hcrc &&
                                    i.pending > s &&
                                    (t.adler = crc32(t.adler, i.pending_buf, i.pending - s, s)),
                                flush_pending(t),
                                0 !== i.pending)
                            )
                                return (i.last_flush = -1), Z_OK$3;
                            s = 0;
                        }
                        (e = i.gzindex < i.gzhead.name.length ? 255 & i.gzhead.name.charCodeAt(i.gzindex++) : 0),
                            put_byte(i, e);
                    } while (0 !== e);
                    i.gzhead.hcrc && i.pending > s && (t.adler = crc32(t.adler, i.pending_buf, i.pending - s, s)),
                        (i.gzindex = 0);
                }
                i.status = 91;
            }
            if (91 === i.status) {
                if (i.gzhead.comment) {
                    let e,
                        s = i.pending;
                    do {
                        if (i.pending === i.pending_buf_size) {
                            if (
                                (i.gzhead.hcrc &&
                                    i.pending > s &&
                                    (t.adler = crc32(t.adler, i.pending_buf, i.pending - s, s)),
                                flush_pending(t),
                                0 !== i.pending)
                            )
                                return (i.last_flush = -1), Z_OK$3;
                            s = 0;
                        }
                        (e = i.gzindex < i.gzhead.comment.length ? 255 & i.gzhead.comment.charCodeAt(i.gzindex++) : 0),
                            put_byte(i, e);
                    } while (0 !== e);
                    i.gzhead.hcrc && i.pending > s && (t.adler = crc32(t.adler, i.pending_buf, i.pending - s, s));
                }
                i.status = 103;
            }
            if (103 === i.status) {
                if (i.gzhead.hcrc) {
                    if (i.pending + 2 > i.pending_buf_size && (flush_pending(t), 0 !== i.pending)) return (i.last_flush = -1), Z_OK$3;
                    put_byte(i, 255 & t.adler), put_byte(i, (t.adler >> 8) & 255), (t.adler = 0);
                }
                if (((i.status = BUSY_STATE), flush_pending(t), 0 !== i.pending)) return (i.last_flush = -1), Z_OK$3;
            }
            if (0 !== t.avail_in || 0 !== i.lookahead || (e !== Z_NO_FLUSH$2 && i.status !== FINISH_STATE)) {
                let s =
                    0 === i.level
                        ? deflate_stored(i, e)
                        : i.strategy === fa
                          ? ((t, e) => {
                                let i;
                                for (;;) {
                                    if (0 === t.lookahead && (fill_window(t), 0 === t.lookahead)) {
                                        if (e === Z_NO_FLUSH$2) return 1;
                                        break;
                                    }
                                    if (
                                        ((t.match_length = 0),
                                        (i = Qn(t, 0, t.window[t.strstart])),
                                        t.lookahead--,
                                        t.strstart++,
                                        i && (flush_block_only(t, false), 0 === t.strm.avail_out))
                                    )
                                        return 1;
                                }
                                return (
                                    (t.insert = 0),
                                    e === Z_FINISH$3
                                        ? (flush_block_only(t, true), 0 === t.strm.avail_out ? 3 : 4)
                                        : t.sym_next && (flush_block_only(t, false), 0 === t.strm.avail_out)
                                          ? 1
                                          : 2
                                );
                            })(i, e)
                          : i.strategy === ga
                            ? ((t, e) => {
                                  let i, s, r, n;
                                  const a = t.window;
                                  for (;;) {
                                      if (t.lookahead <= MAX_MATCH) {
                                          if ((fill_window(t), t.lookahead <= MAX_MATCH && e === Z_NO_FLUSH$2)) return 1;
                                          if (0 === t.lookahead) break;
                                      }
                                      if (
                                          ((t.match_length = 0),
                                          t.lookahead >= 3 &&
                                              t.strstart > 0 &&
                                              ((r = t.strstart - 1),
                                              (s = a[r]),
                                              s === a[++r] && s === a[++r] && s === a[++r]))
                                      ) {
                                          n = t.strstart + MAX_MATCH;
                                          do {} while (
                                              s === a[++r] &&
                                              s === a[++r] &&
                                              s === a[++r] &&
                                              s === a[++r] &&
                                              s === a[++r] &&
                                              s === a[++r] &&
                                              s === a[++r] &&
                                              s === a[++r] &&
                                              r < n
                                          );
                                          (t.match_length = MAX_MATCH - (n - r)),
                                              t.match_length > t.lookahead && (t.match_length = t.lookahead);
                                      }
                                      if (
                                          (t.match_length >= 3
                                              ? ((i = Qn(t, 1, t.match_length - 3)),
                                                (t.lookahead -= t.match_length),
                                                (t.strstart += t.match_length),
                                                (t.match_length = 0))
                                              : ((i = Qn(t, 0, t.window[t.strstart])), t.lookahead--, t.strstart++),
                                          i && (flush_block_only(t, false), 0 === t.strm.avail_out))
                                      )
                                          return 1;
                                  }
                                  return (
                                      (t.insert = 0),
                                      e === Z_FINISH$3
                                          ? (flush_block_only(t, true), 0 === t.strm.avail_out ? 3 : 4)
                                          : t.sym_next && (flush_block_only(t, false), 0 === t.strm.avail_out)
                                            ? 1
                                            : 2
                                  );
                              })(i, e)
                            : Na[i.level].func(i, e);
                if (((3 !== s && 4 !== s) || (i.status = FINISH_STATE), 1 === s || 3 === s))
                    return 0 === t.avail_out && (i.last_flush = -1), Z_OK$3;
                if (
                    2 === s &&
                    (e === ia
                        ? ta(i)
                        : e !== Z_BLOCK$1 &&
                          ($n(i, 0, 0, false),
                          e === Z_FULL_FLUSH$1 &&
                              (zero(i.head),
                              0 === i.lookahead && ((i.strstart = 0), (i.block_start = 0), (i.insert = 0)))),
                    flush_pending(t),
                    0 === t.avail_out)
                )
                    return (i.last_flush = -1), Z_OK$3;
            }
            return e !== Z_FINISH$3
                ? Z_OK$3
                : i.wrap <= 0
                  ? Z_STREAM_END$3
                  : (2 === i.wrap
                        ? (put_byte(i, 255 & t.adler),
                          put_byte(i, (t.adler >> 8) & 255),
                          put_byte(i, (t.adler >> 16) & 255),
                          put_byte(i, (t.adler >> 24) & 255),
                          put_byte(i, 255 & t.total_in),
                          put_byte(i, (t.total_in >> 8) & 255),
                          put_byte(i, (t.total_in >> 16) & 255),
                          put_byte(i, (t.total_in >> 24) & 255))
                        : (putShortMSB(i, t.adler >>> 16), putShortMSB(i, 65535 & t.adler)),
                    flush_pending(t),
                    i.wrap > 0 && (i.wrap = -i.wrap),
                    0 !== i.pending ? Z_OK$3 : Z_STREAM_END$3);
        },
        Xa = (t, e) => {
            let i = e.length;
            if (La(t)) return Z_STREAM_ERROR$2;
            const s = t.state,
                r = s.wrap;
            if (2 === r || (1 === r && s.status !== INIT_STATE) || s.lookahead) return Z_STREAM_ERROR$2;
            if ((1 === r && (t.adler = adler32(t.adler, e, i, 0)), (s.wrap = 0), i >= s.w_size)) {
                0 === r && (zero(s.head), (s.strstart = 0), (s.block_start = 0), (s.insert = 0));
                let t = new Uint8Array(s.w_size);
                t.set(e.subarray(i - s.w_size, i), 0), (e = t), (i = s.w_size);
            }
            const n = t.avail_in,
                a = t.next_in,
                o = t.input;
            for (t.avail_in = i, t.next_in = 0, t.input = e, fill_window(s); s.lookahead >= 3; ) {
                let t = s.strstart,
                    e = s.lookahead - 2;
                do {
                    (s.ins_h = HASH_ZLIB(s, s.ins_h, s.window[t + 3 - 1])),
                        (s.prev[t & s.w_mask] = s.head[s.ins_h]),
                        (s.head[s.ins_h] = t),
                        t++;
                } while (--e);
                (s.strstart = t), (s.lookahead = 2), fill_window(s);
            }
            return (
                (s.strstart += s.lookahead),
                (s.block_start = s.strstart),
                (s.insert = s.lookahead),
                (s.lookahead = 0),
                (s.match_length = s.prev_length = 2),
                (s.match_available = 0),
                (t.next_in = a),
                (t.input = o),
                (t.avail_in = n),
                (s.wrap = r),
                Z_OK$3
            );
        },
        Ya = {
            deflateInit: (t, e) => Va(t, e, Z_DEFLATED$2, 15, 8, Z_DEFAULT_STRATEGY$1),
            deflateInit2: Va,
            deflateReset: qa,
            deflateResetKeep: ja,
            deflateSetHeader: (t, e) => (La(t) || 2 !== t.state.wrap ? Z_STREAM_ERROR$2 : ((t.state.gzhead = e), Z_OK$3)),
            deflate: Wa,
            deflateEnd: (t) => {
                if (La(t)) return Z_STREAM_ERROR$2;
                const e = t.state.status;
                return (t.state = null), e === BUSY_STATE ? err(t, Z_DATA_ERROR$2) : Z_OK$3;
            },
            deflateSetDictionary: Xa,
            deflateInfo: "pako deflate (from Nodeca project)",
        };
    const Za = (t, e) => Object.prototype.hasOwnProperty.call(t, e);
    var Ka = function (t) {
            const e = Array.prototype.slice.call(arguments, 1);
            for (; e.length; ) {
                const i = e.shift();
                if (i) {
                    if ("object" != typeof i) throw new TypeError(i + "must be non-object");
                    for (const e in i) Za(i, e) && (t[e] = i[e]);
                }
            }
            return t;
        },
        $a = (t) => {
            let e = 0;
            for (let i = 0, s = t.length; i < s; i++) e += t[i].length;
            const i = new Uint8Array(e);
            for (let e = 0, s = 0, r = t.length; e < r; e++) {
                let r = t[e];
                i.set(r, s), (s += r.length);
            }
            return i;
        };
    let Ja = true;
    try {
        String.fromCharCode.apply(null, new Uint8Array(1));
    } catch (t) {
        Ja = false;
    }
    const Qa = new Uint8Array(256);
    for (let t = 0; t < 256; t++) Qa[t] = t >= 252 ? 6 : t >= 248 ? 5 : t >= 240 ? 4 : t >= 224 ? 3 : t >= 192 ? 2 : 1;
    Qa[254] = Qa[254] = 1;
    var to = (t) => {
            if ("function" == typeof TextEncoder && TextEncoder.prototype.encode) return new TextEncoder().encode(t);
            let e,
                i,
                s,
                r,
                n,
                a = t.length,
                o = 0;
            for (r = 0; r < a; r++)
                (i = t.charCodeAt(r)),
                    55296 == (64512 & i) &&
                        r + 1 < a &&
                        ((s = t.charCodeAt(r + 1)),
                        56320 == (64512 & s) && ((i = 65536 + ((i - 55296) << 10) + (s - 56320)), r++)),
                    (o += i < 128 ? 1 : i < 2048 ? 2 : i < 65536 ? 3 : 4);
            for (e = new Uint8Array(o), n = 0, r = 0; n < o; r++)
                (i = t.charCodeAt(r)),
                    55296 == (64512 & i) &&
                        r + 1 < a &&
                        ((s = t.charCodeAt(r + 1)),
                        56320 == (64512 & s) && ((i = 65536 + ((i - 55296) << 10) + (s - 56320)), r++)),
                    i < 128
                        ? (e[n++] = i)
                        : i < 2048
                          ? ((e[n++] = 192 | (i >>> 6)), (e[n++] = 128 | (63 & i)))
                          : i < 65536
                            ? ((e[n++] = 224 | (i >>> 12)),
                              (e[n++] = 128 | ((i >>> 6) & 63)),
                              (e[n++] = 128 | (63 & i)))
                            : ((e[n++] = 240 | (i >>> 18)),
                              (e[n++] = 128 | ((i >>> 12) & 63)),
                              (e[n++] = 128 | ((i >>> 6) & 63)),
                              (e[n++] = 128 | (63 & i)));
            return e;
        },
        eo = (t, e) => {
            const i = e || t.length;
            if ("function" == typeof TextDecoder && TextDecoder.prototype.decode)
                return new TextDecoder().decode(t.subarray(0, e));
            let s, r;
            const n = new Array(2 * i);
            for (r = 0, s = 0; s < i; ) {
                let e = t[s++];
                if (e < 128) {
                    n[r++] = e;
                    continue;
                }
                let a = Qa[e];
                if (a > 4) (n[r++] = 65533), (s += a - 1);
                else {
                    for (e &= 2 === a ? 31 : 3 === a ? 15 : 7; a > 1 && s < i; ) (e = (e << 6) | (63 & t[s++])), a--;
                    a > 1
                        ? (n[r++] = 65533)
                        : e < 65536
                          ? (n[r++] = e)
                          : ((e -= 65536), (n[r++] = 55296 | ((e >> 10) & 1023)), (n[r++] = 56320 | (1023 & e)));
                }
            }
            return ((t, e) => {
                if (e < 65534 && t.subarray && Ja)
                    return String.fromCharCode.apply(null, t.length === e ? t : t.subarray(0, e));
                let i = "";
                for (let s = 0; s < e; s++) i += String.fromCharCode(t[s]);
                return i;
            })(n, r);
        },
        io = (t, e) => {
            (e = e || t.length) > t.length && (e = t.length);
            let i = e - 1;
            for (; i >= 0 && 128 == (192 & t[i]); ) i--;
            return i < 0 || 0 === i ? e : i + Qa[t[i]] > e ? i : e;
        };
    var so = function () {
        (this.input = null),
            (this.next_in = 0),
            (this.avail_in = 0),
            (this.total_in = 0),
            (this.output = null),
            (this.next_out = 0),
            (this.avail_out = 0),
            (this.total_out = 0),
            (this.msg = ""),
            (this.state = null),
            (this.data_type = 2),
            (this.adler = 0);
    };
    const ro = Object.prototype.toString,
        {
            Z_NO_FLUSH: no,
            Z_SYNC_FLUSH: ao,
            Z_FULL_FLUSH: oo,
            Z_FINISH: ho,
            Z_OK: lo,
            Z_STREAM_END: uo,
            Z_DEFAULT_COMPRESSION: co,
            Z_DEFAULT_STRATEGY: fo,
            Z_DEFLATED: go,
        } = constants$2;
    function _o(t) {
        this.options = Ka(
            { level: co, method: go, chunkSize: 16384, windowBits: 15, memLevel: 8, strategy: fo },
            t || {}
        );
        let e = this.options;
        e.raw && e.windowBits > 0
            ? (e.windowBits = -e.windowBits)
            : e.gzip && e.windowBits > 0 && e.windowBits < 16 && (e.windowBits += 16),
            (this.err = 0),
            (this.msg = ""),
            (this.ended = false),
            (this.chunks = []),
            (this.strm = new so()),
            (this.strm.avail_out = 0);
        let i = Ya.deflateInit2(this.strm, e.level, e.method, e.windowBits, e.memLevel, e.strategy);
        if (i !== lo) throw new Error(messages[i]);
        if ((e.header && Ya.deflateSetHeader(this.strm, e.header), e.dictionary)) {
            let t;
            if (
                ((t =
                    "string" == typeof e.dictionary
                        ? to(e.dictionary)
                        : "[object ArrayBuffer]" === ro.call(e.dictionary)
                          ? new Uint8Array(e.dictionary)
                          : e.dictionary),
                (i = Ya.deflateSetDictionary(this.strm, t)),
                i !== lo)
            )
                throw new Error(messages[i]);
            this._dict_set = true;
        }
    }
    function bo(t, e) {
        const i = new _o(e);
        if ((i.push(t, true), i.err)) throw i.msg || messages[i.err];
        return i.result;
    }
    (_o.prototype.push = function (t, e) {
        const i = this.strm,
            s = this.options.chunkSize;
        let r, n;
        if (this.ended) return false;
        for (
            n = e === ~~e ? e : true === e ? ho : no,
                "string" == typeof t
                    ? (i.input = to(t))
                    : "[object ArrayBuffer]" === ro.call(t)
                      ? (i.input = new Uint8Array(t))
                      : (i.input = t),
                i.next_in = 0,
                i.avail_in = i.input.length;
            ;

        )
            if (
                (0 === i.avail_out && ((i.output = new Uint8Array(s)), (i.next_out = 0), (i.avail_out = s)),
                (n === ao || n === oo) && i.avail_out <= 6)
            )
                this.onData(i.output.subarray(0, i.next_out)), (i.avail_out = 0);
            else {
                if (((r = Ya.deflate(i, n)), r === uo))
                    return (
                        i.next_out > 0 && this.onData(i.output.subarray(0, i.next_out)),
                        (r = Ya.deflateEnd(this.strm)),
                        this.onEnd(r),
                        (this.ended = true),
                        r === lo
                    );
                if (0 !== i.avail_out) {
                    if (n > 0 && i.next_out > 0) this.onData(i.output.subarray(0, i.next_out)), (i.avail_out = 0);
                    else if (0 === i.avail_in) break;
                } else this.onData(i.output);
            }
        return true;
    }),
        (_o.prototype.onData = function (t) {
            this.chunks.push(t);
        }),
        (_o.prototype.onEnd = function (t) {
            t === lo && (this.result = $a(this.chunks)), (this.chunks = []), (this.err = t), (this.msg = this.strm.msg);
        });
    var mo = {
        Deflate: _o,
        deflate: bo,
        deflateRaw: function (t, e) {
            return ((e = e || {}).raw = true), bo(t, e);
        },
        gzip: function (t, e) {
            return ((e = e || {}).gzip = true), bo(t, e);
        },
        constants: constants$2,
    };
    const po = 16209;
    var xo = function (t, e) {
        let i, s, r, n, a, o, h, l, u, c, d, f, g, _, b, m, p, x, v, T, w, y, A, E;
        const C = t.state;
        (i = t.next_in),
            (A = t.input),
            (s = i + (t.avail_in - 5)),
            (r = t.next_out),
            (E = t.output),
            (n = r - (e - t.avail_out)),
            (a = r + (t.avail_out - 257)),
            (o = C.dmax),
            (h = C.wsize),
            (l = C.whave),
            (u = C.wnext),
            (c = C.window),
            (d = C.hold),
            (f = C.bits),
            (g = C.lencode),
            (_ = C.distcode),
            (b = (1 << C.lenbits) - 1),
            (m = (1 << C.distbits) - 1);
        t: do {
            f < 15 && ((d += A[i++] << f), (f += 8), (d += A[i++] << f), (f += 8)), (p = g[d & b]);
            e: for (;;) {
                if (((x = p >>> 24), (d >>>= x), (f -= x), (x = (p >>> 16) & 255), 0 === x)) E[r++] = 65535 & p;
                else {
                    if (!(16 & x)) {
                        if (64 & x) {
                            if (32 & x) {
                                C.mode = 16191;
                                break t;
                            }
                            (t.msg = "invalid literal/length code"), (C.mode = po);
                            break t;
                        }
                        p = g[(65535 & p) + (d & ((1 << x) - 1))];
                        continue e;
                    }
                    for (
                        v = 65535 & p,
                            x &= 15,
                            x &&
                                (f < x && ((d += A[i++] << f), (f += 8)),
                                (v += d & ((1 << x) - 1)),
                                (d >>>= x),
                                (f -= x)),
                            f < 15 && ((d += A[i++] << f), (f += 8), (d += A[i++] << f), (f += 8)),
                            p = _[d & m];
                        ;

                    ) {
                        if (((x = p >>> 24), (d >>>= x), (f -= x), (x = (p >>> 16) & 255), 16 & x)) {
                            if (
                                ((T = 65535 & p),
                                (x &= 15),
                                f < x && ((d += A[i++] << f), (f += 8), f < x && ((d += A[i++] << f), (f += 8))),
                                (T += d & ((1 << x) - 1)),
                                T > o)
                            ) {
                                (t.msg = "invalid distance too far back"), (C.mode = po);
                                break t;
                            }
                            if (((d >>>= x), (f -= x), (x = r - n), T > x)) {
                                if (((x = T - x), x > l && C.sane)) {
                                    (t.msg = "invalid distance too far back"), (C.mode = po);
                                    break t;
                                }
                                if (((w = 0), (y = c), 0 === u)) {
                                    if (((w += h - x), x < v)) {
                                        v -= x;
                                        do {
                                            E[r++] = c[w++];
                                        } while (--x);
                                        (w = r - T), (y = E);
                                    }
                                } else if (u < x) {
                                    if (((w += h + u - x), (x -= u), x < v)) {
                                        v -= x;
                                        do {
                                            E[r++] = c[w++];
                                        } while (--x);
                                        if (((w = 0), u < v)) {
                                            (x = u), (v -= x);
                                            do {
                                                E[r++] = c[w++];
                                            } while (--x);
                                            (w = r - T), (y = E);
                                        }
                                    }
                                } else if (((w += u - x), x < v)) {
                                    v -= x;
                                    do {
                                        E[r++] = c[w++];
                                    } while (--x);
                                    (w = r - T), (y = E);
                                }
                                for (; v > 2; ) (E[r++] = y[w++]), (E[r++] = y[w++]), (E[r++] = y[w++]), (v -= 3);
                                v && ((E[r++] = y[w++]), v > 1 && (E[r++] = y[w++]));
                            } else {
                                w = r - T;
                                do {
                                    (E[r++] = E[w++]), (E[r++] = E[w++]), (E[r++] = E[w++]), (v -= 3);
                                } while (v > 2);
                                v && ((E[r++] = E[w++]), v > 1 && (E[r++] = E[w++]));
                            }
                            break;
                        }
                        if (64 & x) {
                            (t.msg = "invalid distance code"), (C.mode = po);
                            break t;
                        }
                        p = _[(65535 & p) + (d & ((1 << x) - 1))];
                    }
                }
                break;
            }
        } while (i < s && r < a);
        (v = f >> 3),
            (i -= v),
            (f -= v << 3),
            (d &= (1 << f) - 1),
            (t.next_in = i),
            (t.next_out = r),
            (t.avail_in = i < s ? s - i + 5 : 5 - (i - s)),
            (t.avail_out = r < a ? a - r + 257 : 257 - (r - a)),
            (C.hold = d),
            (C.bits = f);
    };
    const vo = 15,
        To = new Uint16Array([
            3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 15, 17, 19, 23, 27, 31, 35, 43, 51, 59, 67, 83, 99, 115, 131, 163, 195,
            227, 258, 0, 0,
        ]),
        wo = new Uint8Array([
            16, 16, 16, 16, 16, 16, 16, 16, 17, 17, 17, 17, 18, 18, 18, 18, 19, 19, 19, 19, 20, 20, 20, 20, 21, 21, 21,
            21, 16, 72, 78,
        ]),
        yo = new Uint16Array([
            1, 2, 3, 4, 5, 7, 9, 13, 17, 25, 33, 49, 65, 97, 129, 193, 257, 385, 513, 769, 1025, 1537, 2049, 3073, 4097,
            6145, 8193, 12289, 16385, 24577, 0, 0,
        ]),
        Ao = new Uint8Array([
            16, 16, 16, 16, 17, 17, 18, 18, 19, 19, 20, 20, 21, 21, 22, 22, 23, 23, 24, 24, 25, 25, 26, 26, 27, 27, 28,
            28, 29, 29, 64, 64,
        ]);
    var Eo = (t, e, i, s, r, n, a, o) => {
        const h = o.bits;
        let l,
            u,
            c,
            d,
            f,
            g,
            _ = 0,
            b = 0,
            m = 0,
            p = 0,
            x = 0,
            v = 0,
            T = 0,
            w = 0,
            y = 0,
            A = 0,
            E = null;
        const C = new Uint16Array(16),
            M = new Uint16Array(16);
        let k,
            S,
            F,
            I = null;
        for (_ = 0; _ <= vo; _++) C[_] = 0;
        for (b = 0; b < s; b++) C[e[i + b]]++;
        for (x = h, p = vo; p >= 1 && 0 === C[p]; p--);
        if ((x > p && (x = p), 0 === p)) return (r[n++] = 20971520), (r[n++] = 20971520), (o.bits = 1), 0;
        for (m = 1; m < p && 0 === C[m]; m++);
        for (x < m && (x = m), w = 1, _ = 1; _ <= vo; _++) if (((w <<= 1), (w -= C[_]), w < 0)) return -1;
        if (w > 0 && (0 === t || 1 !== p)) return -1;
        for (M[1] = 0, _ = 1; _ < vo; _++) M[_ + 1] = M[_] + C[_];
        for (b = 0; b < s; b++) 0 !== e[i + b] && (a[M[e[i + b]]++] = b);
        if (
            (0 === t
                ? ((E = I = a), (g = 20))
                : 1 === t
                  ? ((E = To), (I = wo), (g = 257))
                  : ((E = yo), (I = Ao), (g = 0)),
            (A = 0),
            (b = 0),
            (_ = m),
            (f = n),
            (v = x),
            (T = 0),
            (c = -1),
            (y = 1 << x),
            (d = y - 1),
            (1 === t && y > 852) || (2 === t && y > 592))
        )
            return 1;
        for (;;) {
            (k = _ - T),
                a[b] + 1 < g
                    ? ((S = 0), (F = a[b]))
                    : a[b] >= g
                      ? ((S = I[a[b] - g]), (F = E[a[b] - g]))
                      : ((S = 96), (F = 0)),
                (l = 1 << (_ - T)),
                (u = 1 << v),
                (m = u);
            do {
                (u -= l), (r[f + (A >> T) + u] = (k << 24) | (S << 16) | F);
            } while (0 !== u);
            for (l = 1 << (_ - 1); A & l; ) l >>= 1;
            if ((0 !== l ? ((A &= l - 1), (A += l)) : (A = 0), b++, 0 === --C[_])) {
                if (_ === p) break;
                _ = e[i + a[b]];
            }
            if (_ > x && (A & d) !== c) {
                for (0 === T && (T = x), f += m, v = _ - T, w = 1 << v; v + T < p && ((w -= C[v + T]), !(w <= 0)); )
                    v++, (w <<= 1);
                if (((y += 1 << v), (1 === t && y > 852) || (2 === t && y > 592))) return 1;
                (c = A & d), (r[c] = (x << 24) | (v << 16) | (f - n));
            }
        }
        return 0 !== A && (r[f + A] = ((_ - T) << 24) | (64 << 16)), (o.bits = x), 0;
    };
    const {
            Z_FINISH: Co,
            Z_BLOCK: Mo,
            Z_TREES: ko,
            Z_OK: So,
            Z_STREAM_END: Fo,
            Z_NEED_DICT: Io,
            Z_STREAM_ERROR: Do,
            Z_DATA_ERROR: Ro,
            Z_MEM_ERROR: Uo,
            Z_BUF_ERROR: Bo,
            Z_DEFLATED: Oo,
        } = constants$2,
        Po = 16180,
        zo = 16190,
        Ho = 16191,
        No = 16192,
        Go = 16194,
        Lo = 16199,
        jo = 16200,
        qo = 16206,
        Vo = 16209,
        Wo = (t) => ((t >>> 24) & 255) + ((t >>> 8) & 65280) + ((65280 & t) << 8) + ((255 & t) << 24);
    function Xo() {
        (this.strm = null),
            (this.mode = 0),
            (this.last = false),
            (this.wrap = 0),
            (this.havedict = false),
            (this.flags = 0),
            (this.dmax = 0),
            (this.check = 0),
            (this.total = 0),
            (this.head = null),
            (this.wbits = 0),
            (this.wsize = 0),
            (this.whave = 0),
            (this.wnext = 0),
            (this.window = null),
            (this.hold = 0),
            (this.bits = 0),
            (this.length = 0),
            (this.offset = 0),
            (this.extra = 0),
            (this.lencode = null),
            (this.distcode = null),
            (this.lenbits = 0),
            (this.distbits = 0),
            (this.ncode = 0),
            (this.nlen = 0),
            (this.ndist = 0),
            (this.have = 0),
            (this.next = null),
            (this.lens = new Uint16Array(320)),
            (this.work = new Uint16Array(288)),
            (this.lendyn = null),
            (this.distdyn = null),
            (this.sane = 0),
            (this.back = 0),
            (this.was = 0);
    }
    const Yo = (t) => {
            if (!t) return 1;
            const e = t.state;
            return !e || e.strm !== t || e.mode < Po || e.mode > 16211 ? 1 : 0;
        },
        Zo = (t) => {
            if (Yo(t)) return Do;
            const e = t.state;
            return (
                (t.total_in = t.total_out = e.total = 0),
                (t.msg = ""),
                e.wrap && (t.adler = 1 & e.wrap),
                (e.mode = Po),
                (e.last = 0),
                (e.havedict = 0),
                (e.flags = -1),
                (e.dmax = 32768),
                (e.head = null),
                (e.hold = 0),
                (e.bits = 0),
                (e.lencode = e.lendyn = new Int32Array(852)),
                (e.distcode = e.distdyn = new Int32Array(592)),
                (e.sane = 1),
                (e.back = -1),
                So
            );
        },
        Ko = (t) => {
            if (Yo(t)) return Do;
            const e = t.state;
            return (e.wsize = 0), (e.whave = 0), (e.wnext = 0), Zo(t);
        },
        $o = (t, e) => {
            let i;
            if (Yo(t)) return Do;
            const s = t.state;
            return (
                e < 0 ? ((i = 0), (e = -e)) : ((i = 5 + (e >> 4)), e < 48 && (e &= 15)),
                e && (e < 8 || e > 15)
                    ? Do
                    : (null !== s.window && s.wbits !== e && (s.window = null), (s.wrap = i), (s.wbits = e), Ko(t))
            );
        },
        Jo = (t, e) => {
            if (!t) return Do;
            const i = new Xo();
            (t.state = i), (i.strm = t), (i.window = null), (i.mode = Po);
            const s = $o(t, e);
            return s !== So && (t.state = null), s;
        };
    let Qo,
        th,
        eh = true;
    const ih = (t) => {
            if (eh) {
                (Qo = new Int32Array(512)), (th = new Int32Array(32));
                let e = 0;
                for (; e < 144; ) t.lens[e++] = 8;
                for (; e < 256; ) t.lens[e++] = 9;
                for (; e < 280; ) t.lens[e++] = 7;
                for (; e < 288; ) t.lens[e++] = 8;
                for (Eo(1, t.lens, 0, 288, Qo, 0, t.work, { bits: 9 }), e = 0; e < 32; ) t.lens[e++] = 5;
                Eo(2, t.lens, 0, 32, th, 0, t.work, { bits: 5 }), (eh = false);
            }
            (t.lencode = Qo), (t.lenbits = 9), (t.distcode = th), (t.distbits = 5);
        },
        sh = (t, e, i, s) => {
            let r;
            const n = t.state;
            return (
                null === n.window &&
                    ((n.wsize = 1 << n.wbits), (n.wnext = 0), (n.whave = 0), (n.window = new Uint8Array(n.wsize))),
                s >= n.wsize
                    ? (n.window.set(e.subarray(i - n.wsize, i), 0), (n.wnext = 0), (n.whave = n.wsize))
                    : ((r = n.wsize - n.wnext),
                      r > s && (r = s),
                      n.window.set(e.subarray(i - s, i - s + r), n.wnext),
                      (s -= r)
                          ? (n.window.set(e.subarray(i - s, i), 0), (n.wnext = s), (n.whave = n.wsize))
                          : ((n.wnext += r),
                            n.wnext === n.wsize && (n.wnext = 0),
                            n.whave < n.wsize && (n.whave += r))),
                0
            );
        };
    var rh = (t, e) => {
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
                A = 0;
            const E = new Uint8Array(4);
            let C, M;
            const k = new Uint8Array([16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15]);
            if (Yo(t) || !t.output || (!t.input && 0 !== t.avail_in)) return Do;
            (i = t.state),
                i.mode === Ho && (i.mode = No),
                (a = t.next_out),
                (r = t.output),
                (h = t.avail_out),
                (n = t.next_in),
                (s = t.input),
                (o = t.avail_in),
                (l = i.hold),
                (u = i.bits),
                (c = o),
                (d = h),
                (y = So);
            t: for (;;)
                switch (i.mode) {
                    case Po:
                        if (0 === i.wrap) {
                            i.mode = No;
                            break;
                        }
                        for (; u < 16; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        if (2 & i.wrap && 35615 === l) {
                            0 === i.wbits && (i.wbits = 15),
                                (i.check = 0),
                                (E[0] = 255 & l),
                                (E[1] = (l >>> 8) & 255),
                                (i.check = crc32(i.check, E, 2, 0)),
                                (l = 0),
                                (u = 0),
                                (i.mode = 16181);
                            break;
                        }
                        if ((i.head && (i.head.done = false), !(1 & i.wrap) || (((255 & l) << 8) + (l >> 8)) % 31)) {
                            (t.msg = "incorrect header check"), (i.mode = Vo);
                            break;
                        }
                        if ((15 & l) !== Oo) {
                            (t.msg = "unknown compression method"), (i.mode = Vo);
                            break;
                        }
                        if (
                            ((l >>>= 4),
                            (u -= 4),
                            (w = 8 + (15 & l)),
                            0 === i.wbits && (i.wbits = w),
                            w > 15 || w > i.wbits)
                        ) {
                            (t.msg = "invalid window size"), (i.mode = Vo);
                            break;
                        }
                        (i.dmax = 1 << i.wbits),
                            (i.flags = 0),
                            (t.adler = i.check = 1),
                            (i.mode = 512 & l ? 16189 : Ho),
                            (l = 0),
                            (u = 0);
                        break;
                    case 16181:
                        for (; u < 16; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        if (((i.flags = l), (255 & i.flags) !== Oo)) {
                            (t.msg = "unknown compression method"), (i.mode = Vo);
                            break;
                        }
                        if (57344 & i.flags) {
                            (t.msg = "unknown header flags set"), (i.mode = Vo);
                            break;
                        }
                        i.head && (i.head.text = (l >> 8) & 1),
                            512 & i.flags &&
                                4 & i.wrap &&
                                ((E[0] = 255 & l), (E[1] = (l >>> 8) & 255), (i.check = crc32(i.check, E, 2, 0))),
                            (l = 0),
                            (u = 0),
                            (i.mode = 16182);
                    case 16182:
                        for (; u < 32; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        i.head && (i.head.time = l),
                            512 & i.flags &&
                                4 & i.wrap &&
                                ((E[0] = 255 & l),
                                (E[1] = (l >>> 8) & 255),
                                (E[2] = (l >>> 16) & 255),
                                (E[3] = (l >>> 24) & 255),
                                (i.check = crc32(i.check, E, 4, 0))),
                            (l = 0),
                            (u = 0),
                            (i.mode = 16183);
                    case 16183:
                        for (; u < 16; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        i.head && ((i.head.xflags = 255 & l), (i.head.os = l >> 8)),
                            512 & i.flags &&
                                4 & i.wrap &&
                                ((E[0] = 255 & l), (E[1] = (l >>> 8) & 255), (i.check = crc32(i.check, E, 2, 0))),
                            (l = 0),
                            (u = 0),
                            (i.mode = 16184);
                    case 16184:
                        if (1024 & i.flags) {
                            for (; u < 16; ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            (i.length = l),
                                i.head && (i.head.extra_len = l),
                                512 & i.flags &&
                                    4 & i.wrap &&
                                    ((E[0] = 255 & l), (E[1] = (l >>> 8) & 255), (i.check = crc32(i.check, E, 2, 0))),
                                (l = 0),
                                (u = 0);
                        } else i.head && (i.head.extra = null);
                        i.mode = 16185;
                    case 16185:
                        if (
                            1024 & i.flags &&
                            ((f = i.length),
                            f > o && (f = o),
                            f &&
                                (i.head &&
                                    ((w = i.head.extra_len - i.length),
                                    i.head.extra || (i.head.extra = new Uint8Array(i.head.extra_len)),
                                    i.head.extra.set(s.subarray(n, n + f), w)),
                                512 & i.flags && 4 & i.wrap && (i.check = crc32(i.check, s, f, n)),
                                (o -= f),
                                (n += f),
                                (i.length -= f)),
                            i.length)
                        )
                            break t;
                        (i.length = 0), (i.mode = 16186);
                    case 16186:
                        if (2048 & i.flags) {
                            if (0 === o) break t;
                            f = 0;
                            do {
                                (w = s[n + f++]),
                                    i.head && w && i.length < 65536 && (i.head.name += String.fromCharCode(w));
                            } while (w && f < o);
                            if (
                                (512 & i.flags && 4 & i.wrap && (i.check = crc32(i.check, s, f, n)), (o -= f), (n += f), w)
                            )
                                break t;
                        } else i.head && (i.head.name = null);
                        (i.length = 0), (i.mode = 16187);
                    case 16187:
                        if (4096 & i.flags) {
                            if (0 === o) break t;
                            f = 0;
                            do {
                                (w = s[n + f++]),
                                    i.head && w && i.length < 65536 && (i.head.comment += String.fromCharCode(w));
                            } while (w && f < o);
                            if (
                                (512 & i.flags && 4 & i.wrap && (i.check = crc32(i.check, s, f, n)), (o -= f), (n += f), w)
                            )
                                break t;
                        } else i.head && (i.head.comment = null);
                        i.mode = 16188;
                    case 16188:
                        if (512 & i.flags) {
                            for (; u < 16; ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            if (4 & i.wrap && l !== (65535 & i.check)) {
                                (t.msg = "header crc mismatch"), (i.mode = Vo);
                                break;
                            }
                            (l = 0), (u = 0);
                        }
                        i.head && ((i.head.hcrc = (i.flags >> 9) & 1), (i.head.done = true)),
                            (t.adler = i.check = 0),
                            (i.mode = Ho);
                        break;
                    case 16189:
                        for (; u < 32; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        (t.adler = i.check = Wo(l)), (l = 0), (u = 0), (i.mode = zo);
                    case zo:
                        if (0 === i.havedict)
                            return (
                                (t.next_out = a),
                                (t.avail_out = h),
                                (t.next_in = n),
                                (t.avail_in = o),
                                (i.hold = l),
                                (i.bits = u),
                                Io
                            );
                        (t.adler = i.check = 1), (i.mode = Ho);
                    case Ho:
                        if (e === Mo || e === ko) break t;
                    case No:
                        if (i.last) {
                            (l >>>= 7 & u), (u -= 7 & u), (i.mode = qo);
                            break;
                        }
                        for (; u < 3; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        switch (((i.last = 1 & l), (l >>>= 1), (u -= 1), 3 & l)) {
                            case 0:
                                i.mode = 16193;
                                break;
                            case 1:
                                if ((ih(i), (i.mode = Lo), e === ko)) {
                                    (l >>>= 2), (u -= 2);
                                    break t;
                                }
                                break;
                            case 2:
                                i.mode = 16196;
                                break;
                            case 3:
                                (t.msg = "invalid block type"), (i.mode = Vo);
                        }
                        (l >>>= 2), (u -= 2);
                        break;
                    case 16193:
                        for (l >>>= 7 & u, u -= 7 & u; u < 32; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        if ((65535 & l) != ((l >>> 16) ^ 65535)) {
                            (t.msg = "invalid stored block lengths"), (i.mode = Vo);
                            break;
                        }
                        if (((i.length = 65535 & l), (l = 0), (u = 0), (i.mode = Go), e === ko)) break t;
                    case Go:
                        i.mode = 16195;
                    case 16195:
                        if (((f = i.length), f)) {
                            if ((f > o && (f = o), f > h && (f = h), 0 === f)) break t;
                            r.set(s.subarray(n, n + f), a), (o -= f), (n += f), (h -= f), (a += f), (i.length -= f);
                            break;
                        }
                        i.mode = Ho;
                        break;
                    case 16196:
                        for (; u < 14; ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        if (
                            ((i.nlen = 257 + (31 & l)),
                            (l >>>= 5),
                            (u -= 5),
                            (i.ndist = 1 + (31 & l)),
                            (l >>>= 5),
                            (u -= 5),
                            (i.ncode = 4 + (15 & l)),
                            (l >>>= 4),
                            (u -= 4),
                            i.nlen > 286 || i.ndist > 30)
                        ) {
                            (t.msg = "too many length or distance symbols"), (i.mode = Vo);
                            break;
                        }
                        (i.have = 0), (i.mode = 16197);
                    case 16197:
                        for (; i.have < i.ncode; ) {
                            for (; u < 3; ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            (i.lens[k[i.have++]] = 7 & l), (l >>>= 3), (u -= 3);
                        }
                        for (; i.have < 19; ) i.lens[k[i.have++]] = 0;
                        if (
                            ((i.lencode = i.lendyn),
                            (i.lenbits = 7),
                            (C = { bits: i.lenbits }),
                            (y = Eo(0, i.lens, 0, 19, i.lencode, 0, i.work, C)),
                            (i.lenbits = C.bits),
                            y)
                        ) {
                            (t.msg = "invalid code lengths set"), (i.mode = Vo);
                            break;
                        }
                        (i.have = 0), (i.mode = 16198);
                    case 16198:
                        for (; i.have < i.nlen + i.ndist; ) {
                            for (
                                ;
                                (A = i.lencode[l & ((1 << i.lenbits) - 1)]),
                                    (b = A >>> 24),
                                    (m = (A >>> 16) & 255),
                                    (p = 65535 & A),
                                    !(b <= u);

                            ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            if (p < 16) (l >>>= b), (u -= b), (i.lens[i.have++] = p);
                            else {
                                if (16 === p) {
                                    for (M = b + 2; u < M; ) {
                                        if (0 === o) break t;
                                        o--, (l += s[n++] << u), (u += 8);
                                    }
                                    if (((l >>>= b), (u -= b), 0 === i.have)) {
                                        (t.msg = "invalid bit length repeat"), (i.mode = Vo);
                                        break;
                                    }
                                    (w = i.lens[i.have - 1]), (f = 3 + (3 & l)), (l >>>= 2), (u -= 2);
                                } else if (17 === p) {
                                    for (M = b + 3; u < M; ) {
                                        if (0 === o) break t;
                                        o--, (l += s[n++] << u), (u += 8);
                                    }
                                    (l >>>= b), (u -= b), (w = 0), (f = 3 + (7 & l)), (l >>>= 3), (u -= 3);
                                } else {
                                    for (M = b + 7; u < M; ) {
                                        if (0 === o) break t;
                                        o--, (l += s[n++] << u), (u += 8);
                                    }
                                    (l >>>= b), (u -= b), (w = 0), (f = 11 + (127 & l)), (l >>>= 7), (u -= 7);
                                }
                                if (i.have + f > i.nlen + i.ndist) {
                                    (t.msg = "invalid bit length repeat"), (i.mode = Vo);
                                    break;
                                }
                                for (; f--; ) i.lens[i.have++] = w;
                            }
                        }
                        if (i.mode === Vo) break;
                        if (0 === i.lens[256]) {
                            (t.msg = "invalid code -- missing end-of-block"), (i.mode = Vo);
                            break;
                        }
                        if (
                            ((i.lenbits = 9),
                            (C = { bits: i.lenbits }),
                            (y = Eo(1, i.lens, 0, i.nlen, i.lencode, 0, i.work, C)),
                            (i.lenbits = C.bits),
                            y)
                        ) {
                            (t.msg = "invalid literal/lengths set"), (i.mode = Vo);
                            break;
                        }
                        if (
                            ((i.distbits = 6),
                            (i.distcode = i.distdyn),
                            (C = { bits: i.distbits }),
                            (y = Eo(2, i.lens, i.nlen, i.ndist, i.distcode, 0, i.work, C)),
                            (i.distbits = C.bits),
                            y)
                        ) {
                            (t.msg = "invalid distances set"), (i.mode = Vo);
                            break;
                        }
                        if (((i.mode = Lo), e === ko)) break t;
                    case Lo:
                        i.mode = jo;
                    case jo:
                        if (o >= 6 && h >= 258) {
                            (t.next_out = a),
                                (t.avail_out = h),
                                (t.next_in = n),
                                (t.avail_in = o),
                                (i.hold = l),
                                (i.bits = u),
                                xo(t, d),
                                (a = t.next_out),
                                (r = t.output),
                                (h = t.avail_out),
                                (n = t.next_in),
                                (s = t.input),
                                (o = t.avail_in),
                                (l = i.hold),
                                (u = i.bits),
                                i.mode === Ho && (i.back = -1);
                            break;
                        }
                        for (
                            i.back = 0;
                            (A = i.lencode[l & ((1 << i.lenbits) - 1)]),
                                (b = A >>> 24),
                                (m = (A >>> 16) & 255),
                                (p = 65535 & A),
                                !(b <= u);

                        ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        if (m && !(240 & m)) {
                            for (
                                x = b, v = m, T = p;
                                (A = i.lencode[T + ((l & ((1 << (x + v)) - 1)) >> x)]),
                                    (b = A >>> 24),
                                    (m = (A >>> 16) & 255),
                                    (p = 65535 & A),
                                    !(x + b <= u);

                            ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            (l >>>= x), (u -= x), (i.back += x);
                        }
                        if (((l >>>= b), (u -= b), (i.back += b), (i.length = p), 0 === m)) {
                            i.mode = 16205;
                            break;
                        }
                        if (32 & m) {
                            (i.back = -1), (i.mode = Ho);
                            break;
                        }
                        if (64 & m) {
                            (t.msg = "invalid literal/length code"), (i.mode = Vo);
                            break;
                        }
                        (i.extra = 15 & m), (i.mode = 16201);
                    case 16201:
                        if (i.extra) {
                            for (M = i.extra; u < M; ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            (i.length += l & ((1 << i.extra) - 1)),
                                (l >>>= i.extra),
                                (u -= i.extra),
                                (i.back += i.extra);
                        }
                        (i.was = i.length), (i.mode = 16202);
                    case 16202:
                        for (
                            ;
                            (A = i.distcode[l & ((1 << i.distbits) - 1)]),
                                (b = A >>> 24),
                                (m = (A >>> 16) & 255),
                                (p = 65535 & A),
                                !(b <= u);

                        ) {
                            if (0 === o) break t;
                            o--, (l += s[n++] << u), (u += 8);
                        }
                        if (!(240 & m)) {
                            for (
                                x = b, v = m, T = p;
                                (A = i.distcode[T + ((l & ((1 << (x + v)) - 1)) >> x)]),
                                    (b = A >>> 24),
                                    (m = (A >>> 16) & 255),
                                    (p = 65535 & A),
                                    !(x + b <= u);

                            ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            (l >>>= x), (u -= x), (i.back += x);
                        }
                        if (((l >>>= b), (u -= b), (i.back += b), 64 & m)) {
                            (t.msg = "invalid distance code"), (i.mode = Vo);
                            break;
                        }
                        (i.offset = p), (i.extra = 15 & m), (i.mode = 16203);
                    case 16203:
                        if (i.extra) {
                            for (M = i.extra; u < M; ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            (i.offset += l & ((1 << i.extra) - 1)),
                                (l >>>= i.extra),
                                (u -= i.extra),
                                (i.back += i.extra);
                        }
                        if (i.offset > i.dmax) {
                            (t.msg = "invalid distance too far back"), (i.mode = Vo);
                            break;
                        }
                        i.mode = 16204;
                    case 16204:
                        if (0 === h) break t;
                        if (((f = d - h), i.offset > f)) {
                            if (((f = i.offset - f), f > i.whave && i.sane)) {
                                (t.msg = "invalid distance too far back"), (i.mode = Vo);
                                break;
                            }
                            f > i.wnext ? ((f -= i.wnext), (g = i.wsize - f)) : (g = i.wnext - f),
                                f > i.length && (f = i.length),
                                (_ = i.window);
                        } else (_ = r), (g = a - i.offset), (f = i.length);
                        f > h && (f = h), (h -= f), (i.length -= f);
                        do {
                            r[a++] = _[g++];
                        } while (--f);
                        0 === i.length && (i.mode = jo);
                        break;
                    case 16205:
                        if (0 === h) break t;
                        (r[a++] = i.length), h--, (i.mode = jo);
                        break;
                    case qo:
                        if (i.wrap) {
                            for (; u < 32; ) {
                                if (0 === o) break t;
                                o--, (l |= s[n++] << u), (u += 8);
                            }
                            if (
                                ((d -= h),
                                (t.total_out += d),
                                (i.total += d),
                                4 & i.wrap &&
                                    d &&
                                    (t.adler = i.check = i.flags ? crc32(i.check, r, d, a - d) : adler32(i.check, r, d, a - d)),
                                (d = h),
                                4 & i.wrap && (i.flags ? l : Wo(l)) !== i.check)
                            ) {
                                (t.msg = "incorrect data check"), (i.mode = Vo);
                                break;
                            }
                            (l = 0), (u = 0);
                        }
                        i.mode = 16207;
                    case 16207:
                        if (i.wrap && i.flags) {
                            for (; u < 32; ) {
                                if (0 === o) break t;
                                o--, (l += s[n++] << u), (u += 8);
                            }
                            if (4 & i.wrap && l !== (4294967295 & i.total)) {
                                (t.msg = "incorrect length check"), (i.mode = Vo);
                                break;
                            }
                            (l = 0), (u = 0);
                        }
                        i.mode = 16208;
                    case 16208:
                        y = Fo;
                        break t;
                    case Vo:
                        y = Ro;
                        break t;
                    case 16210:
                        return Uo;
                    default:
                        return Do;
                }
            return (
                (t.next_out = a),
                (t.avail_out = h),
                (t.next_in = n),
                (t.avail_in = o),
                (i.hold = l),
                (i.bits = u),
                (i.wsize || (d !== t.avail_out && i.mode < Vo && (i.mode < qo || e !== Co))) &&
                    sh(t, t.output, t.next_out, d - t.avail_out),
                (c -= t.avail_in),
                (d -= t.avail_out),
                (t.total_in += c),
                (t.total_out += d),
                (i.total += d),
                4 & i.wrap &&
                    d &&
                    (t.adler = i.check =
                        i.flags ? crc32(i.check, r, d, t.next_out - d) : adler32(i.check, r, d, t.next_out - d)),
                (t.data_type =
                    i.bits +
                    (i.last ? 64 : 0) +
                    (i.mode === Ho ? 128 : 0) +
                    (i.mode === Lo || i.mode === Go ? 256 : 0)),
                ((0 === c && 0 === d) || e === Co) && y === So && (y = Bo),
                y
            );
        },
        nh = {
            inflateReset: Ko,
            inflateReset2: $o,
            inflateResetKeep: Zo,
            inflateInit: (t) => Jo(t, 15),
            inflateInit2: Jo,
            inflate: rh,
            inflateEnd: (t) => {
                if (Yo(t)) return Do;
                let e = t.state;
                return e.window && (e.window = null), (t.state = null), So;
            },
            inflateGetHeader: (t, e) => {
                if (Yo(t)) return Do;
                const i = t.state;
                return 2 & i.wrap ? ((i.head = e), (e.done = false), So) : Do;
            },
            inflateSetDictionary: (t, e) => {
                const i = e.length;
                let s, r, n;
                return Yo(t)
                    ? Do
                    : ((s = t.state),
                      0 !== s.wrap && s.mode !== zo
                          ? Do
                          : s.mode === zo && ((r = 1), (r = adler32(r, e, i, 0)), r !== s.check)
                            ? Ro
                            : ((n = sh(t, e, i, i)), n ? ((s.mode = 16210), Uo) : ((s.havedict = 1), So)));
            },
            inflateInfo: "pako inflate (from Nodeca project)",
        };
    var ah = function () {
        (this.text = 0),
            (this.time = 0),
            (this.xflags = 0),
            (this.os = 0),
            (this.extra = null),
            (this.extra_len = 0),
            (this.name = ""),
            (this.comment = ""),
            (this.hcrc = 0),
            (this.done = false);
    };
    const oh = Object.prototype.toString,
        {
            Z_NO_FLUSH: hh,
            Z_FINISH: lh,
            Z_OK: uh,
            Z_STREAM_END: ch,
            Z_NEED_DICT: dh,
            Z_STREAM_ERROR: fh,
            Z_DATA_ERROR: gh,
            Z_MEM_ERROR: _h,
        } = constants$2;
    function bh(t) {
        this.options = Ka({ chunkSize: 65536, windowBits: 15, to: "" }, t || {});
        const e = this.options;
        e.raw &&
            e.windowBits >= 0 &&
            e.windowBits < 16 &&
            ((e.windowBits = -e.windowBits), 0 === e.windowBits && (e.windowBits = -15)),
            !(e.windowBits >= 0 && e.windowBits < 16) || (t && t.windowBits) || (e.windowBits += 32),
            e.windowBits > 15 && e.windowBits < 48 && (15 & e.windowBits || (e.windowBits |= 15)),
            (this.err = 0),
            (this.msg = ""),
            (this.ended = false),
            (this.chunks = []),
            (this.strm = new so()),
            (this.strm.avail_out = 0);
        let i = nh.inflateInit2(this.strm, e.windowBits);
        if (i !== uh) throw new Error(messages[i]);
        if (
            ((this.header = new ah()),
            nh.inflateGetHeader(this.strm, this.header),
            e.dictionary &&
                ("string" == typeof e.dictionary
                    ? (e.dictionary = to(e.dictionary))
                    : "[object ArrayBuffer]" === oh.call(e.dictionary) && (e.dictionary = new Uint8Array(e.dictionary)),
                e.raw && ((i = nh.inflateSetDictionary(this.strm, e.dictionary)), i !== uh)))
        )
            throw new Error(messages[i]);
    }
    function mh(t, e) {
        const i = new bh(e);
        if ((i.push(t), i.err)) throw i.msg || messages[i.err];
        return i.result;
    }
    (bh.prototype.push = function (t, e) {
        const i = this.strm,
            s = this.options.chunkSize,
            r = this.options.dictionary;
        let n, a, o;
        if (this.ended) return false;
        for (
            a = e === ~~e ? e : true === e ? lh : hh,
                "[object ArrayBuffer]" === oh.call(t) ? (i.input = new Uint8Array(t)) : (i.input = t),
                i.next_in = 0,
                i.avail_in = i.input.length;
            ;

        ) {
            for (
                0 === i.avail_out && ((i.output = new Uint8Array(s)), (i.next_out = 0), (i.avail_out = s)),
                    n = nh.inflate(i, a),
                    n === dh &&
                        r &&
                        ((n = nh.inflateSetDictionary(i, r)), n === uh ? (n = nh.inflate(i, a)) : n === gh && (n = dh));
                i.avail_in > 0 && n === ch && i.state.wrap > 0 && 0 !== t[i.next_in];

            )
                nh.inflateReset(i), (n = nh.inflate(i, a));
            switch (n) {
                case fh:
                case gh:
                case dh:
                case _h:
                    return this.onEnd(n), (this.ended = true), false;
            }
            if (((o = i.avail_out), i.next_out && (0 === i.avail_out || n === ch)))
                if ("string" === this.options.to) {
                    let t = io(i.output, i.next_out),
                        e = i.next_out - t,
                        r = eo(i.output, t);
                    (i.next_out = e),
                        (i.avail_out = s - e),
                        e && i.output.set(i.output.subarray(t, t + e), 0),
                        this.onData(r);
                } else this.onData(i.output.length === i.next_out ? i.output : i.output.subarray(0, i.next_out));
            if (n !== uh || 0 !== o) {
                if (n === ch) return (n = nh.inflateEnd(this.strm)), this.onEnd(n), (this.ended = true), true;
                if (0 === i.avail_in) break;
            }
        }
        return true;
    }),
        (bh.prototype.onData = function (t) {
            this.chunks.push(t);
        }),
        (bh.prototype.onEnd = function (t) {
            t === uh &&
                ("string" === this.options.to ? (this.result = this.chunks.join("")) : (this.result = $a(this.chunks))),
                (this.chunks = []),
                (this.err = t),
                (this.msg = this.strm.msg);
        });
    var ph = {
        Inflate: bh,
        inflate: mh,
        inflateRaw: function (t, e) {
            return ((e = e || {}).raw = true), mh(t, e);
        },
        ungzip: mh,
        constants: constants$2,
    };
    const { Deflate: xh, deflate: vh, deflateRaw: Th, gzip: wh } = mo,
        { Inflate: yh, inflate: inflate, inflateRaw: Eh, ungzip: Ch } = ph;

}
