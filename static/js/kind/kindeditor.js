(function(f) {
    var d = {};
    d.version = "3.5.2 (2010-12-02)";
    d.scriptPath = function() {
        for (var a = document.getElementsByTagName("script"), b = 0, c = a.length; b < c; b++) if (a[b].src && a[b].src.match(/kindeditor[\w\-\.]*\.js/)) return a[b].src.substring(0, a[b].src.lastIndexOf("/") + 1);
        return ""
    } ();
    d.browser = function() {
        var a = navigator.userAgent.toLowerCase();
        return {
            VERSION: a.match(/(msie|firefox|webkit|opera)[\/:\s](\d+)/) ? RegExp.$2: "0",
            IE: a.indexOf("msie") > -1 && a.indexOf("opera") == -1,
            GECKO: a.indexOf("gecko") > -1 && a.indexOf("khtml") == 
            -1,
            WEBKIT: a.indexOf("applewebkit") > -1,
            OPERA: a.indexOf("opera") > -1
        }
    } ();
    d.setting = {
        wyswygMode: true,
        loadStyleMode: true,
        resizeMode: 2,
        filterMode: false,
        autoSetDataMode: true,
        shadowMode: true,
        urlType: "",
        skinType: "default",
        newlineTag: "p",
        dialogAlignType: "page",
        cssPath: "",
        skinsPath: d.scriptPath + "skins/",
        pluginsPath: d.scriptPath + "plugins/",
        emoticonsPath: d.scriptPath + "../../images/",
        minWidth: 200,
        minHeight: 100,
        minChangeSize: 5,
        toolbarLineHeight: 24,
        statusbarHeight: 11,
        items: ["source", "|", "fullscreen", "undo", "redo", "print", "cut", "copy", "paste", "plainpaste", 
        "wordpaste", "|", "justifyleft", "justifycenter", "justifyright", "justifyfull", "insertorderedlist", "insertunorderedlist", "indent", "outdent", "subscript", "superscript", "|", "selectall", "-", "title", "fontname", "fontsize", "|", "textcolor", "bgcolor", "bold", "italic", "underline", "strikethrough", "removeformat", "|", "image", "flash", "media", "advtable", "hr", "emoticons", "link", "unlink", "|", "about"],
        colorTable: [["#E53333", "#E56600", "#FF9900", "#64451D", "#DFC5A4", "#FFE500"], ["#009900", "#006600", "#99BB00", "#B8D100", "#60D978", 
        "#00D5FF"], ["#337FE5", "#003399", "#4C33E5", "#9933E5", "#CC33E5", "#EE33EE"], ["#FFFFFF", "#CCCCCC", "#999999", "#666666", "#333333", "#000000"]],
        noEndTags: ["br", "hr", "img", "area", "col", "embed", "input", "param"],
        inlineTags: ["b", "del", "em", "font", "i", "span", "strike", "strong", "sub", "sup", "u"],
        endlineTags: ["br", "hr", "table", "tbody", "td", "tr", "th", "div", "p", "ol", "ul", "li", "blockquote", "h1", "h2", "h3", "h4", "h5", "h6", "script", "style", "marquee"],
        htmlTags: {
            font: ["color", "size", "face", ".background-color"],
            span: [".color", 
            ".background-color", ".font-size", ".font-family", ".background", ".font-weight", ".font-style", ".text-decoration", ".vertical-align"],
            div: ["align", ".border", ".margin", ".padding", ".text-align", ".color", ".background-color", ".font-size", ".font-family", ".font-weight", ".background", ".font-style", ".text-decoration", ".vertical-align", ".margin-left"],
            table: ["border", "cellspacing", "cellpadding", "width", "height", "align", "bordercolor", ".padding", ".margin", ".border", "bgcolor", ".text-align", ".color", ".background-color", 
            ".font-size", ".font-family", ".font-weight", ".font-style", ".text-decoration", ".background", ".width", ".height"],
            "td,th": ["align", "valign", "width", "height", "colspan", "rowspan", "bgcolor", ".text-align", ".color", ".background-color", ".font-size", ".font-family", ".font-weight", ".font-style", ".text-decoration", ".vertical-align", ".background"],
            a: ["href", "target", "name"],
            embed: ["src", "width", "height", "type", "loop", "autostart", "quality", ".width", ".height", "align", "allowscriptaccess", "/"],
            img: ["src", "width", "height", 
            "border", "alt", "title", ".width", ".height", "/"],
            hr: ["/"],
            br: ["/"],
            "p,ol,ul,li,blockquote,h1,h2,h3,h4,h5,h6": ["align", ".text-align", ".color", ".background-color", ".font-size", ".font-family", ".background", ".font-weight", ".font-style", ".text-decoration", ".vertical-align", ".text-indent", ".margin-left"],
            "tbody,tr,strong,b,sub,sup,em,i,u,strike": []
        },
        mediaTypes: {
            rm: "audio/x-pn-realaudio-plugin",
            flash: "application/x-shockwave-flash",
            media: "video/x-ms-asf-plugin"
        }
    };
    d.g = {};
    d.plugin = {};
    d.$ = function(a, b) {
        return (b || 
        document).getElementById(a)
    };
    d.$$ = function(a, b) {
        return (b || document).createElement(a)
    };
    d.event = {
        add: function(a, b, c, e) {
            if (a.addEventListener) a.addEventListener(b, c, false);
            else a.attachEvent && a.attachEvent("on" + b, c);
            e !== f && d.g[e].eventStack.push({
                el: a,
                type: b,
                fn: c
            })
        },
        remove: function(a, b, c, e) {
            if (a.removeEventListener) a.removeEventListener(b, c, false);
            else a.detachEvent && a.detachEvent("on" + b, c);
            if (e !== f) {
                e = d.g[e].eventStack;
                for (var g = 0, h = e.length; g < h; g++) {
                    var i = e[g];
                    i && a === i.el && b === i.type && c === i.fn && delete e[g]
                }
            }
        },
        stop: function(a) {
            a.preventDefault && a.preventDefault();
            a.stopPropagation && a.stopPropagation();
            if (a.cancelBubble !== f) a.cancelBubble = true;
            if (a.returnValue !== f) a.returnValue = false
        },
        bind: function(a, b, c, e) {
            this.add(a, b, 
            function(g) {
                c(g);
                d.event.stop(g);
                return false
            },
            e)
        },
        input: function(a, b, c) {
            function e(g) {
                window.setTimeout(function() {
                    b(g)
                },
                1)
            }
            this.add(a, "keyup", 
            function(g) {
                if (!g.ctrlKey && !g.altKey && (g.keyCode < 16 || g.keyCode > 18) && g.keyCode != 116) {
                    b(g);
                    d.event.stop(g);
                    return false
                }
            },
            c);
            a = a.nodeName == "#document" ? 
            a.body: a;
            this.add(a, "paste", e, c);
            this.add(a, "cut", e, c)
        },
        ctrl: function(a, b, c, e) {
            b = b.toString().match(/^\d{2,}$/) ? b: b.toUpperCase().charCodeAt(0);
            this.add(a, "keydown", 
            function(g) {
                if (g.ctrlKey && g.keyCode == b && !g.shiftKey && !g.altKey) {
                    c(g);
                    d.event.stop(g);
                    return false
                }
            },
            e)
        },
        ready: function(a, b, c, e) {
            b = b || window;
            c = c || document;
            var g = false,
            h = function() {
                if (!g) {
                    g = true;
                    a()
                }
            };
            if (c.addEventListener) this.add(c, "DOMContentLoaded", h, e);
            else if (c.attachEvent) {
                this.add(c, "readystatechange", 
                function() {
                    c.readyState == "complete" && 
                    h()
                },
                e);
                if (c.documentElement.doScroll && typeof b.frameElement === "undefined") {
                    var i = function() {
                        if (!g) {
                            try {
                                c.documentElement.doScroll("left")
                            } catch(j) {
                                window.setTimeout(i, 0);
                                return
                            }
                            h()
                        }
                    };
                    i()
                }
            }
            this.add(b, "load", h, e)
        }
    };
    d.each = function(a, b) {
        for (var c in a) a.hasOwnProperty(c) && b(c, a[c])
    };
    d.eachNode = function(a, b) {
        var c = function(e) {
            if (d.util.getNodeType(e) != 1) return true;
            for (e = e.firstChild; e;) {
                var g = e.nextSibling;
                if (!b(e)) return false;
                if (!c(e)) return false;
                e = g
            }
            return true
        };
        c(a)
    };
    d.selection = function(a) {
        this.keRange = 
        this.range = this.sel = null;
        this.isControl = false;
        var b = a.parentWindow || a.defaultView;
        this.init = function() {
            var c = b.getSelection ? b.getSelection() : a.selection,
            e;
            try {
                e = c.rangeCount > 0 ? c.getRangeAt(0) : c.createRange()
            } catch(g) {}
            e || (e = d.util.createRange(a));
            this.sel = c;
            this.range = e;
            var h,
            i,
            j;
            if (d.browser.IE) if (e.item) {
                this.isControl = true;
                c = i = e.item(0);
                h = j = 0
            } else {
                this.isControl = false;
                c = function(l) {
                    var m = e.duplicate();
                    m.collapse(l);
                    var p = m.parentElement(),
                    o = p.childNodes;
                    if (o.length == 0) return {
                        node: p,
                        pos: 0
                    };
                    var q,
                    r = 0,
                    u = false,
                    n = e.duplicate();
                    d.util.moveToElementText(n, p);
                    for (var v = 0, t = o.length; v < t; v++) {
                        l = o[v];
                        var x = n.compareEndPoints("StartToStart", m);
                        if (x > 0) u = true;
                        else if (x == 0) if (l.nodeType == 1) {
                            m = new d.range(a);
                            m.selectTextNode(l);
                            return {
                                node: m.startNode,
                                pos: 0
                            }
                        } else return {
                            node: l,
                            pos: 0
                        };
                        if (l.nodeType == 1) {
                            x = e.duplicate();
                            d.util.moveToElementText(x, l);
                            n.setEndPoint("StartToEnd", x);
                            if (u) r += x.text.replace(/\r\n|\n|\r/g, "").length;
                            else r = 0
                        } else if (l.nodeType == 3) if (typeof l.nodeValue === "string") {
                            n.moveStart("character", 
                            l.nodeValue.length);
                            r += l.nodeValue.length
                        }
                        u || (q = l)
                    }
                    if (!u && q.nodeType == 1) {
                        q = p.lastChild;
                        return {
                            node: q,
                            pos: q.nodeType == 1 ? 1: q.nodeValue.length
                        }
                    }
                    n = e.duplicate();
                    d.util.moveToElementText(n, p);
                    n.setEndPoint("StartToEnd", m);
                    r -= n.text.replace(/\r\n|\n|\r/g, "").length;
                    return {
                        node: q,
                        pos: r
                    }
                };
                h = c(true);
                j = c(false);
                c = h.node;
                h = h.pos;
                i = j.node;
                j = j.pos
            } else {
                c = e.startContainer;
                h = e.startOffset;
                i = e.endContainer;
                j = e.endOffset;
                if (c.nodeType == 1 && typeof c.childNodes[h] != "undefined") {
                    c = c.childNodes[h];
                    h = 0
                }
                if (i.nodeType == 1) {
                    j = 
                    j == 0 ? 1: j;
                    if (typeof i.childNodes[j - 1] != "undefined") {
                        i = i.childNodes[j - 1];
                        j = i.nodeType == 1 ? 0: i.nodeValue.length
                    }
                }
                this.isControl = c.nodeType == 1 && c === i && e.startOffset + 1 == e.endOffset;
                if (c.nodeType == 1 && i.nodeType == 3 && j == 0 && i.previousSibling) for (var k = i.previousSibling; k;) {
                    if (k === c) {
                        i = c;
                        break
                    }
                    if (k.childNodes.length != 1) break;
                    k = k.childNodes[0]
                }
                if (e.collapsed) {
                    k = new d.range(a);
                    k.setTextStart(c, h);
                    i = k.startNode;
                    j = k.startPos
                }
            }
            k = new d.range(a);
            k.setTextStart(c, h);
            k.setTextEnd(i, j);
            this.keRange = k
        };
        this.init();
        this.addRange = 
        function(c) {
            if (! (d.browser.GECKO && d.browser.VERSION < 3)) {
                this.keRange = c;
                if (d.browser.IE) {
                    var e = function(j) {
                        var k = d.util.createRange(a),
                        l = j ? c.startNode: c.endNode;
                        if (l.nodeType == 1) {
                            d.util.moveToElementText(k, l);
                            k.collapse(j)
                        } else if (l.nodeType == 3) {
                            k = d.util.getNodeStartRange(a, l);
                            k.moveStart("character", j ? c.startPos: c.endPos)
                        }
                        return k
                    };
                    if (!this.range.item) {
                        var g = c.startNode;
                        if (g == c.endNode && d.util.getNodeType(g) == 1 && d.util.getNodeTextLength(g) == 0) {
                            e = a.createTextNode(" ");
                            g.appendChild(e);
                            d.util.moveToElementText(this.range, 
                            g);
                            this.range.collapse(false);
                            this.range.select();
                            g.removeChild(e)
                        } else {
                            if (g.nodeType == 3 && c.collapsed()) {
                                this.range = e(true);
                                this.range.collapse(true)
                            } else {
                                this.range.setEndPoint("StartToStart", e(true));
                                this.range.setEndPoint("EndToStart", e(false))
                            }
                            this.range.select()
                        }
                    }
                } else {
                    g = function(j) {
                        for (var k = 0; j;) {
                            j = j.previousSibling;
                            k++
                        }
                        return--k
                    };
                    e = new d.range(a);
                    e.setTextStart(c.startNode, c.startPos);
                    e.setTextEnd(c.endNode, c.endPos);
                    var h = e.startNode,
                    i = e.endNode;
                    d.util.getNodeType(h) == 88 ? this.range.setStart(h.parentNode, 
                    g(e.startNode)) : this.range.setStart(h, e.startPos);
                    d.util.getNodeType(i) == 88 ? this.range.setEnd(i.parentNode, g(e.endNode) + 1) : this.range.setEnd(i, e.endPos);
                    this.sel.removeAllRanges();
                    this.sel.addRange(this.range)
                }
            }
        };
        this.focus = function() {
            d.browser.IE && this.range != null && this.range.select()
        }
    };
    d.range = function(a) {
        this.endPos = this.endNode = this.startPos = this.startNode = null;
        this.getParentElement = function() {
            var b = function(g, h) {
                for (; g && (!g.tagName || g.tagName.toLowerCase() != "body");) {
                    g = g.parentNode;
                    if (h(g)) return
                }
            },
            c = [];
            b(this.startNode, 
            function(g) {
                c.push(g)
            });
            var e;
            b(this.endNode, 
            function(g) {
                if (d.util.inArray(g, c)) {
                    e = g;
                    return true
                }
            });
            return e ? e: a.body
        };
        this.getNodeList = function() {
            var b = this,
            c = this.getParentElement(),
            e = [],
            g = false;
            if (c == b.startNode) g = true;
            g && e.push(c);
            d.eachNode(c, 
            function(h) {
                if (h == b.startNode) g = true;
                var i = new d.range(a);
                i.selectTextNode(h);
                var j = i.comparePoints("START_TO_END", b);
                if (j > 0) return false;
                else if (j == 0) if (i.startNode !== i.endNode || i.startPos !== i.endPos) return false;
                g && e.push(h);
                return true
            });
            return e
        };
        this.comparePoints = function(b, c) {
            var e = function(g, h, i, j) {
                if (d.browser.IE) {
                    var k = function(m, p, o) {
                        var q = d.util.createRange(a),
                        r = d.util.getNodeType(m);
                        if (r == 1) {
                            d.util.moveToElementText(q, m);
                            q.collapse(o)
                        } else if (r == 3) {
                            q = d.util.getNodeStartRange(a, m);
                            q.moveStart("character", p);
                            q.collapse(true)
                        }
                        return q
                    },
                    l;
                    l = b == "START_TO_START" || b == "START_TO_END" ? k(g, h, true) : k(g, h, false);
                    g = b == "START_TO_START" || b == "END_TO_START" ? k(i, j, true) : k(i, j, false);
                    return l.compareEndPoints("StartToStart", g)
                } else {
                    l = d.util.createRange(a);
                    l.selectNode(g);
                    b == "START_TO_START" || b == "START_TO_END" ? l.collapse(true) : l.collapse(false);
                    g = d.util.createRange(a);
                    g.selectNode(i);
                    b == "START_TO_START" || b == "END_TO_START" ? g.collapse(true) : g.collapse(false);
                    h = l.compareBoundaryPoints(Range.START_TO_START, g) > 0 ? 1: l.compareBoundaryPoints(Range.START_TO_START, g) == 0 ? h > j ? 1: h == j ? 0: -1: -1
                }
                return h
            };
            if (b == "START_TO_START") return e(this.startNode, this.startPos, c.startNode, c.startPos);
            if (b == "START_TO_END") return e(this.startNode, this.startPos, c.endNode, c.endPos);
            if (b == "END_TO_START") return e(this.endNode, this.endPos, c.startNode, c.startPos);
            if (b == "END_TO_END") return e(this.endNode, this.endPos, c.endNode, c.endPos)
        };
        this.collapsed = function() {
            return this.startNode === this.endNode && this.startPos === this.endPos
        };
        this.collapse = function(b) {
            b ? this.setEnd(this.startNode, this.startPos) : this.setStart(this.endNode, this.endPos)
        };
        this.setTextStart = function(b, c) {
            var e = b;
            d.eachNode(b, 
            function(g) {
                if (d.util.getNodeType(g) == 3 && g.nodeValue.length > 0 || d.util.getNodeType(g) == 88) {
                    e = 
                    g;
                    c = 0;
                    return false
                }
                return true
            });
            this.setStart(e, c)
        };
        this.setStart = function(b, c) {
            this.startNode = b;
            this.startPos = c;
            if (this.endNode === null) {
                this.endNode = b;
                this.endPos = c
            }
        };
        this.setTextEnd = function(b, c) {
            var e = b;
            d.eachNode(b, 
            function(g) {
                if (d.util.getNodeType(g) == 3 && g.nodeValue.length > 0 || d.util.getNodeType(g) == 88) {
                    e = g;
                    c = d.util.getNodeType(g) == 3 ? g.nodeValue.length: 0
                }
                return true
            });
            this.setEnd(e, c)
        };
        this.setEnd = function(b, c) {
            this.endNode = b;
            this.endPos = c;
            if (this.startNode === null) {
                this.startNode = b;
                this.startPos = 
                c
            }
        };
        this.selectNode = function(b) {
            this.setStart(b, 0);
            this.setEnd(b, b.nodeType == 1 ? 0: b.nodeValue.length)
        };
        this.selectTextNode = function(b) {
            this.setTextStart(b, 0);
            this.setTextEnd(b, b.nodeType == 1 ? 0: b.nodeValue.length)
        };
        this.extractContents = function(b) {
            b = b === f ? true: b;
            var c = this,
            e = this.startNode,
            g = this.startPos,
            h = this.endNode,
            i = this.endPos,
            j = function(r, u, n) {
                var v = r.nodeValue.length,
                t = r.cloneNode(true).splitText(u);
                t.splitText(n - u);
                if (b) {
                    var x = r;
                    if (u > 0) x = r.splitText(u);
                    n < v && x.splitText(n - u);
                    x.parentNode.removeChild(x)
                }
                return t
            },
            k = d.util.arrayToHash(d.setting.noEndTags),
            l = false,
            m = false,
            p = function(r, u) {
                if (d.util.getNodeType(r) != 1) return true;
                for (var n = r.firstChild; n;) {
                    if (n == e) l = true;
                    if (n == h) m = true;
                    var v = n.nextSibling,
                    t = n.nodeType;
                    if (t == 1) {
                        t = new d.range(a);
                        t.selectNode(n);
                        t = t.comparePoints("END_TO_END", c);
                        if (l && (t < 0 || t == 0 && k[n.nodeName.toLowerCase()] !== f)) {
                            t = n.cloneNode(true);
                            u.appendChild(t);
                            b && n.parentNode.removeChild(n)
                        } else {
                            t = n.cloneNode(false);
                            if (k[t.nodeName.toLowerCase()] === f) {
                                u.appendChild(t);
                                if (!p(n, t)) return false
                            }
                        }
                    } else if (t == 
                    3) if (l) if (n == e && n == h) {
                        n = j(n, g, i);
                        u.appendChild(n);
                        return false
                    } else if (n == e) {
                        n = j(n, g, n.nodeValue.length);
                        u.appendChild(n)
                    } else if (n == h) {
                        n = j(n, 0, i);
                        u.appendChild(n);
                        return false
                    } else {
                        n = j(n, 0, n.nodeValue.length);
                        u.appendChild(n)
                    }
                    n = v;
                    if (m) return false
                }
                u.innerHTML.replace(/<.*?>/g, "") === "" && u.parentNode && u.parentNode.removeChild(u);
                return true
            },
            o = this.getParentElement(),
            q = o.cloneNode(false);
            p(o, q);
            return q
        };
        this.cloneContents = function() {
            return this.extractContents(false)
        };
        this.getText = function() {
            return this.cloneContents().innerHTML.replace(/<.*?>/g, 
            "")
        }
    };
    d.cmd = function(a) {
        this.doc = d.g[a].iframeDoc;
        this.keSel = d.g[a].keSel;
        this.keRange = d.g[a].keRange;
        this.mergeAttributes = function(b, c) {
            for (var e = 0, g = c.length; e < g; e++) d.each(c[e], 
            function(h, i) {
                if (h.charAt(0) == ".") {
                    var j = d.util.getJsKey(h.substr(1));
                    b.style[j] = i
                } else {
                    if (d.browser.IE && d.browser.VERSION < 8 && h == "class") h = "className";
                    b.setAttribute(h, i)
                }
            });
            return b
        };
        this.wrapTextNode = function(b, c, e, g, h) {
            var i = b.nodeValue.length,
            j = c == 0 && e == i,
            k = new d.range(this.doc);
            k.selectTextNode(b.parentNode);
            if (j && 
            b.parentNode.tagName == g.tagName && k.comparePoints("END_TO_END", this.keRange) <= 0 && k.comparePoints("START_TO_START", this.keRange) >= 0) {
                this.mergeAttributes(b.parentNode, h);
                return b
            } else {
                g = g.cloneNode(true);
                if (j) {
                    c = b.cloneNode(true);
                    g.appendChild(c);
                    b.parentNode.replaceChild(g, b);
                    return c
                } else {
                    j = b;
                    if (c < e) {
                        if (c > 0) j = b.splitText(c);
                        e < i && j.splitText(e - c);
                        c = j.cloneNode(true);
                        g.appendChild(c);
                        j.parentNode.replaceChild(g, j);
                        return c
                    } else {
                        if (c < i) {
                            j = b.splitText(c);
                            j.parentNode.insertBefore(g, j)
                        } else j.nextSibling ? 
                        j.parentNode.insertBefore(g, j.nextSibling) : j.parentNode.appendChild(g);
                        return g
                    }
                }
            }
        };
        this.wrap = function(b, c) {
            c = c || [];
            var e = this;
            this.keSel.focus();
            var g = d.$$(b, this.doc);
            this.mergeAttributes(g, c);
            var h = this.keRange,
            i = h.startNode,
            j = h.startPos,
            k = h.endNode,
            l = h.endPos,
            m = h.getParentElement();
            if (!d.util.inMarquee(m)) {
                var p = false;
                d.eachNode(m, 
                function(o) {
                    if (o == i) p = true;
                    if (o.nodeType == 1) if (o == i && o == k) {
                        if (d.util.inArray(o.tagName.toLowerCase(), d.g[a].noEndTags)) j > 0 ? o.parentNode.appendChild(g) : o.parentNode.insertBefore(g, 
                        o);
                        else o.appendChild(g);
                        h.selectNode(g);
                        return false
                    } else if (o == i) h.setStart(o, 0);
                    else {
                        if (o == k) {
                            h.setEnd(o, 0);
                            return false
                        }
                    } else if (o.nodeType == 3) if (p) if (o == i && o == k) {
                        o = e.wrapTextNode(o, j, l, g, c);
                        h.selectNode(o);
                        return false
                    } else if (o == i) {
                        o = e.wrapTextNode(o, j, o.nodeValue.length, g, c);
                        h.setStart(o, 0)
                    } else if (o == k) {
                        o = e.wrapTextNode(o, 0, l, g, c);
                        h.setEnd(o, o.nodeType == 1 ? 0: o.nodeValue.length);
                        return false
                    } else e.wrapTextNode(o, 0, o.nodeValue.length, g, c);
                    return true
                });
                this.keSel.addRange(h)
            }
        };
        this.getTopParent = 
        function(b, c) {
            for (var e = null; c;) {
                c = c.parentNode;
                if (d.util.inArray(c.tagName.toLowerCase(), b)) e = c;
                else break
            }
            return e
        };
        this.splitNodeParent = function(b, c, e) {
            var g = new d.range(this.doc);
            g.selectNode(b.firstChild);
            g.setEnd(c, e);
            c = g.extractContents();
            b.parentNode.insertBefore(c, b);
            return {
                left: c,
                right: b
            }
        };
        this.remove = function(b) {
            var c = this.keRange,
            e = c.startNode,
            g = c.startPos,
            h = c.endNode,
            i = c.endPos;
            this.keSel.focus();
            if (!d.util.inMarquee(c.getParentElement())) {
                var j = c.getText().replace(/\s+/g, "") === "";
                if (! (j && 
                !d.browser.IE)) {
                    var k = [];
                    d.each(b, 
                    function(r) {
                        r != "*" && k.push(r)
                    });
                    var l = this.getTopParent(k, e),
                    m = this.getTopParent(k, h);
                    if (l) {
                        var p = this.splitNodeParent(l, e, g);
                        c.setStart(p.right, 0);
                        if (e == h && d.util.getNodeTextLength(p.right) > 0) {
                            c.selectNode(p.right);
                            e = new d.range(this.doc);
                            e.selectTextNode(p.left);
                            if (g > 0) i -= e.endNode.nodeValue.length;
                            e.selectTextNode(p.right);
                            h = e.startNode
                        }
                    }
                    if (j) {
                        l = c.startNode;
                        if (l.nodeType == 1) {
                            if (l.nodeName.toLowerCase() == "br") return;
                            c.selectNode(l)
                        } else return
                    } else if (m) {
                        g = this.splitNodeParent(m, 
                        h, i);
                        c.setEnd(g.left, 0);
                        l == m && c.setStart(g.left, 0)
                    }
                    m = function(r, u) {
                        if (u.charAt(0) == ".") {
                            var n = d.util.getJsKey(u.substr(1));
                            r.style[n] = ""
                        } else {
                            if (d.browser.IE && d.browser.VERSION < 8 && u == "class") u = "className";
                            r.removeAttribute(u)
                        }
                    };
                    g = c.getNodeList();
                    c.setTextStart(c.startNode, c.startPos);
                    c.setTextEnd(c.endNode, c.endPos);
                    i = 0;
                    for (j = g.length; i < j; i++) {
                        l = g[i];
                        if (l.nodeType == 1) {
                            p = l.tagName.toLowerCase();
                            if (b[p]) {
                                p = b[p];
                                e = 0;
                                for (h = p.length; e < h; e++) if (p[e] == "*") {
                                    d.util.removeParent(l);
                                    break
                                } else {
                                    m(l, p[e]);
                                    var o = 
                                    [];
                                    if (l.outerHTML) {
                                        attrHash = d.util.getAttrList(l.outerHTML);
                                        d.each(attrHash, 
                                        function(r, u) {
                                            o.push({
                                                name: r,
                                                value: u
                                            })
                                        })
                                    } else o = l.attributes;
                                    if (o.length == 0) {
                                        d.util.removeParent(l);
                                        break
                                    } else if (o[0].name == "style" && o[0].value === "") {
                                        d.util.removeParent(l);
                                        break
                                    }
                                }
                            }
                            if (b["*"]) {
                                p = b["*"];
                                e = 0;
                                for (h = p.length; e < h; e++) m(l, p[e])
                            }
                        }
                    }
                    try {
                        this.keSel.addRange(c)
                    } catch(q) {}
                }
            }
        }
    };
    d.format = {
        getUrl: function(a, b, c, e) {
            if (!b) return a;
            b = b.toLowerCase();
            if (!d.util.inArray(b, ["absolute", "relative", "domain"])) return a;
            c = c || location.protocol + 
            "//" + location.host;
            if (e === f) {
                var g = location.pathname.match(/^(\/.*)\//);
                e = g ? g[1] : ""
            }
            if (g = a.match(/^(\w+:\/\/[^\/]*)/)) {
                if (g[1] !== c) return a
            } else if (a.match(/^\w+:/)) return a;
            g = function(i) {
                i = i.split("/");
                paths = [];
                for (var j = 0, k = i.length; j < k; j++) {
                    var l = i[j];
                    if (l == "..") paths.length > 0 && paths.pop();
                    else l !== "" && l != "." && paths.push(l)
                }
                return "/" + paths.join("/")
            };
            if (a.match(/^\//)) a = c + g(a.substr(1));
            else a.match(/^\w+:\/\//) || (a = c + g(e + "/" + a));
            if (b == "relative") {
                var h = function(i, j) {
                    if (a.substr(0, i.length) === 
                    i) {
                        for (var k = [], l = 0; l < j; l++) k.push("..");
                        l = ".";
                        if (k.length > 0) l += "/" + k.join("/");
                        if (e == "/") l += "/";
                        return l + a.substr(i.length)
                    } else if (k = i.match(/^(.*)\//)) return h(k[1], ++j)
                };
                a = h(c + e, 0).substr(2)
            } else if (b == "absolute") if (a.substr(0, c.length) === c) a = a.substr(c.length);
            return a
        },
        getHtml: function(a, b, c) {
            var e = b ? true: false;
            a = a.replace(/(<pre[^>]*>)([\s\S]*?)(<\/pre>)/ig, 
            function(m, p, o, q) {
                return p + o.replace(/<br[^>]*>/ig, "\n") + q
            });
            var g = {},
            h = ["xx-small", "x-small", "small", "medium", "large", "x-large", "xx-large"];
            e && d.each(b, 
            function(m, p) {
                for (var o = m.split(","), q = 0, r = o.length; q < r; q++) g[o[q]] = d.util.arrayToHash(p)
            });
            var i = d.util.arrayToHash(d.setting.noEndTags);
            d.util.arrayToHash(d.setting.inlineTags);
            var j = d.util.arrayToHash(d.setting.endlineTags);
            a = a.replace(/((?:\r\n|\n|\r)*)<(\/)?([\w-:]+)((?:\s+|(?:\s+[\w-:]+)|(?:\s+[\w-:]+=[^\s"'<>]+)|(?:\s+[\w-:]+="[^"]*")|(?:\s+[\w-:]+='[^']*'))*)(\/)?>((?:\r\n|\n|\r)*)/g, 
            function(m, p, o, q, r, u, n) {
                m = p || "";
                o = o || "";
                var v = q.toLowerCase();
                q = r || "";
                u = u ? " " + u: "";
                n = n || "";
                if (e && 
                typeof g[v] == "undefined") return "";
                if (u === "" && typeof i[v] != "undefined") u = " /";
                if (v in j) {
                    if (o || u) n = "\n"
                } else if (n) n = " ";
                if (v !== "script" && v !== "style") m = "";
                if (v === "font") {
                    var t = {},
                    x = "";
                    q = q.replace(/\s*([\w-:]+)=([^\s"'<>]+|"[^"]*"|'[^']*')/g, 
                    function(w, y, s) {
                        y = y.toLowerCase();
                        s = s || "";
                        s = s.replace(/^["']|["']$/g, "");
                        if (y === "color") {
                            t.color = s;
                            return " "
                        }
                        if (y === "size") {
                            t["font-size"] = h[parseInt(s) - 1] || "";
                            return " "
                        }
                        if (y === "face") {
                            t["font-family"] = s;
                            return " "
                        }
                        if (y === "style") {
                            x = s;
                            return " "
                        }
                        return w
                    });
                    if (x && !/;$/.test(x)) x += 
                    ";";
                    d.each(t, 
                    function(w, y) {
                        if (y !== "") {
                            if (/\s/.test(y)) y = "'" + y + "'";
                            x += w + ":" + y + ";"
                        }
                    });
                    if (x) q += ' style="' + x + '"';
                    v = "span"
                }
                if (q !== "") {
                    q = q.replace(/\s*([\w-:]+)=([^\s"'<>]+|"[^"]*"|'[^']*')/g, 
                    function(w, y, s) {
                        w = y.toLowerCase();
                        s = s || "";
                        if (e) if (w.charAt(0) === "." || w !== "style" && typeof g[v][w] == "undefined") return " ";
                        if (s === "") s = '""';
                        else {
                            if (w === "style") {
                                s = s.substr(1, s.length - 2);
                                s = s.replace(/\s*([^\s]+?)\s*:(.*?)(;|$)/g, 
                                function(z, A, B) {
                                    z = A.toLowerCase();
                                    if (e) if (typeof g[v].style == "undefined" && typeof g[v]["." + 
                                    z] == "undefined") return "";
                                    B = d.util.trim(B);
                                    B = d.util.rgbToHex(B);
                                    return z + ":" + B + ";"
                                });
                                s = d.util.trim(s);
                                if (s === "") return "";
                                s = '"' + s + '"'
                            }
                            if (d.util.inArray(w, ["src", "href"])) {
                                if (s.charAt(0) === '"') s = s.substr(1, s.length - 2);
                                s = d.format.getUrl(s, c)
                            }
                            if (s.charAt(0) !== '"') s = '"' + s + '"'
                        }
                        return " " + w + "=" + s + " "
                    });
                    q = q.replace(/\s+(checked|selected|disabled|readonly)(\s+|$)/ig, 
                    function(w, y) {
                        var s = y.toLowerCase();
                        if (e) if (s.charAt(0) === "." || typeof g[v][s] == "undefined") return " ";
                        return " " + s + '="' + s + '" '
                    });
                    q = d.util.trim(q);
                    if (q = q.replace(/\s+/g, " ")) q = " " + q;
                    return m + "<" + o + v + q + u + ">" + n
                } else return m + "<" + o + v + u + ">" + n
            });
            if (!d.browser.IE) {
                a = a.replace(/<p><br\s+\/>\n<\/p>/ig, "<p>&nbsp;</p>");
                a = a.replace(/<br\s+\/>\n<\/p>/ig, "</p>")
            }
            var k = d.setting.inlineTags.join("|"),
            l = function(m) {
                var p = m.replace(new RegExp("<(" + k + ")[^>]*><\\/(" + k + ")>", "ig"), 
                function(o, q, r) {
                    return q == r ? "": o
                });
                if (m !== p) p = l(p);
                return p
            };
            return d.util.trim(l(a))
        }
    };
    d.addClass = function(a, b) {
        if (typeof a == "object") {
            var c = a.className;
            if (c) {
                if ((" " + c + " ").indexOf(" " + 
                b + " ") < 0) a.className = c + " " + b
            } else a.className = b
        } else if (typeof a == "string") a = /\s+class\s*=/.test(a) ? a.replace(/(\s+class=["']?)([^"']*)(["']?[\s>])/, 
        function(e, g, h, i) {
            return (" " + h + " ").indexOf(" " + b + " ") < 0 ? h === "" ? g + b + i: g + h + " " + b + i: e
        }) : a.substr(0, a.length - 1) + ' class="' + b + '">';
        return a
    };
    d.removeClass = function(a, b) {
        var c = a.className || "";
        c = " " + c + " ";
        b = " " + b + " ";
        if (c.indexOf(b) >= 0) {
            c = d.util.trim(c.replace(new RegExp(b, "ig"), ""));
            if (c === "") {
                c = a.getAttribute("class") ? "class": "className";
                a.removeAttribute(c)
            } else a.className = 
            c
        }
        return a
    };
    d.getComputedStyle = function(a, b) {
        var c = a.ownerDocument,
        e = c.parentWindow || c.defaultView;
        c = d.util.getJsKey(b);
        var g = "";
        if (e.getComputedStyle) {
            e = e.getComputedStyle(a, null);
            g = e[c] || e.getPropertyValue(b) || a.style[c]
        } else if (a.currentStyle) g = a.currentStyle[c] || a.style[c];
        return g
    };
    d.getCommonAncestor = function(a, b) {
        function c(i) {
            for (; i;) {
                if (i.nodeType == 1) if (i.tagName.toLowerCase() === b) return i;
                i = i.parentNode
            }
            return null
        }
        var e = a.range,
        g = a.keRange,
        h = g.startNode;
        g = g.endNode;
        if (d.util.inArray(b, 
        ["table", "td", "tr"])) if (d.browser.IE) if (e.item) {
            if (e.item(0).nodeName.toLowerCase() === b) h = g = e.item(0)
        } else {
            h = e.duplicate();
            h.collapse(true);
            e = e.duplicate();
            e.collapse(false);
            h = h.parentElement();
            g = e.parentElement()
        } else {
            h = e.cloneRange();
            h.collapse(true);
            e = e.cloneRange();
            e.collapse(false);
            h = h.startContainer;
            g = e.startContainer
        }
        e = c(h);
        h = c(g);
        if (e && h && e === h) return e;
        return null
    };
    d.queryCommandValue = function(a, b) {
        function c() {
            var i = a.queryCommandValue(b);
            if (typeof i !== "string") i = "";
            return i
        }
        b = b.toLowerCase();
        var e = "";
        if (b === "fontname") {
            e = c();
            e = e.replace(/['"]/g, "")
        } else if (b === "formatblock") {
            e = c();
            if (e === "") {
                var g = new d.selection(a),
                h = d.getCommonAncestor(g, "h1");
                h || (h = d.getCommonAncestor(g, "h2"));
                h || (h = d.getCommonAncestor(g, "h3"));
                h || (h = d.getCommonAncestor(g, "h4"));
                h || (h = d.getCommonAncestor(g, "p"));
                if (h) e = h.nodeName
            }
            if (e === "Normal") e = "p"
        } else if (b === "fontsize") {
            g = new d.selection(a);
            if (h = d.getCommonAncestor(g, "span")) e = d.getComputedStyle(h, "font-size")
        } else if (b === "textcolor") {
            g = new d.selection(a);
            if (h = 
            d.getCommonAncestor(g, "span")) e = d.getComputedStyle(h, "color");
            e = d.util.rgbToHex(e);
            if (e === "") e = "default"
        } else if (b === "bgcolor") {
            g = new d.selection(a);
            if (h = d.getCommonAncestor(g, "span")) e = d.getComputedStyle(h, "background-color");
            e = d.util.rgbToHex(e);
            if (e === "") e = "default"
        }
        return e.toLowerCase()
    };
    d.util = {
        getDocumentElement: function(a) {
            a = a || document;
            return a.compatMode != "CSS1Compat" ? a.body: a.documentElement
        },
        getDocumentHeight: function(a) {
            a = this.getDocumentElement(a);
            return Math.max(a.scrollHeight, a.clientHeight)
        },
        getDocumentWidth: function(a) {
            a = this.getDocumentElement(a);
            return Math.max(a.scrollWidth, a.clientWidth)
        },
        createTable: function(a) {
            a = d.$$("table", a);
            a.cellPadding = 0;
            a.cellSpacing = 0;
            a.border = 0;
            return {
                table: a,
                cell: a.insertRow(0).insertCell(0)
            }
        },
        loadStyle: function(a) {
            var b = d.$$("link");
            b.setAttribute("type", "text/css");
            b.setAttribute("rel", "stylesheet");
            b.setAttribute("href", a);
            document.getElementsByTagName("head")[0].appendChild(b)
        },
        getAttrList: function(a) {
            for (var b = /\s+(?:([\w-:]+)|(?:([\w-:]+)=([\w-:]+))|(?:([\w-:]+)="([^"]*)")|(?:([\w-:]+)='([^']*)'))(?=(?:\s|\/|>)+)/g, 
            c, e, g = {};c = b.exec(a);) {
                e = c[1] || c[2] || c[4] || c[6];
                c = c[1] || (c[2] ? c[3] : c[4] ? c[5] : c[7]);
                g[e] = c
            }
            return g
        },
        inArray: function(a, b) {
            for (var c = 0; c < b.length; c++) if (a == b[c]) return true;
            return false
        },
        trim: function(a) {
            return a.replace(/^\s+|\s+$/g, "")
        },
        getJsKey: function(a) {
            var b = a.split("-");
            a = "";
            for (var c = 0, e = b.length; c < e; c++) a += c > 0 ? b[c].charAt(0).toUpperCase() + b[c].substr(1) : b[c];
            return a
        },
        arrayToHash: function(a) {
            for (var b = {},
            c = 0, e = a.length; c < e; c++) b[a[c]] = 1;
            return b
        },
        escape: function(a) {
            a = a.replace(/&/g, "&amp;");
            a = a.replace(/</g, "&lt;");
            a = a.replace(/>/g, "&gt;");
            return a = a.replace(/"/g, "&quot;")
        },
        unescape: function(a) {
            a = a.replace(/&lt;/g, "<");
            a = a.replace(/&gt;/g, ">");
            a = a.replace(/&quot;/g, '"');
            return a = a.replace(/&amp;/g, "&")
        },
        getScrollPos: function() {
            var a,
            b;
            if (d.browser.IE || d.browser.OPERA) {
                b = this.getDocumentElement();
                a = b.scrollLeft;
                b = b.scrollTop
            } else {
                a = window.scrollX;
                b = window.scrollY
            }
            return {
                x: a,
                y: b
            }
        },
        getElementPos: function(a) {
            var b = 0,
            c = 0;
            if (a.getBoundingClientRect) {
                c = a.getBoundingClientRect();
                a = this.getScrollPos();
                b = c.left + a.x;
                c = c.top + a.y
            } else {
                b = a.offsetLeft;
                c = a.offsetTop;
                for (a = a.offsetParent; a;) {
                    b += a.offsetLeft;
                    c += a.offsetTop;
                    a = a.offsetParent
                }
            }
            return {
                x: b,
                y: c
            }
        },
        getCoords: function(a) {
            a = a || window.event;
            return {
                x: a.clientX,
                y: a.clientY
            }
        },
        setOpacity: function(a, b) {
            if (typeof a.style.opacity == "undefined") a.style.filter = b == 100 ? "": "alpha(opacity=" + b + ")";
            else a.style.opacity = b == 100 ? "": b / 100
        },
        getIframeDoc: function(a) {
            return a.contentDocument || a.contentWindow.document
        },
        rgbToHex: function(a) {
            function b(c) {
                c = parseInt(c).toString(16);
                return c.length > 1 ? c: "0" + c
            }
            return a.replace(/rgb\s*?\(\s*?(\d+)\s*?,\s*?(\d+)\s*?,\s*?(\d+)\s*?\)/ig, 
            function(c, e, g, h) {
                return "#" + b(e) + b(g) + b(h)
            })
        },
        parseJson: function(a) {
            var b;
            if (b = /\{[\s\S]*\}|\[[\s\S]*\]/.exec(a)) a = b[0];
            b = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
            b.lastIndex = 0;
            if (b.test(a)) a = a.replace(b, 
            function(c) {
                return "\\u" + ("0000" + c.charCodeAt(0).toString(16)).slice( - 4)
            });
            if (/^[\],:{}\s]*$/.test(a.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, 
            "@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]").replace(/(?:^|:|,)(?:\s*\[)+/g, ""))) return eval("(" + a + ")");
            throw "JSON parse error";
        },
        createRange: function(a) {
            return a.createRange ? a.createRange() : a.body.createTextRange()
        },
        getNodeType: function(a) {
            return a.nodeType == 1 && d.util.inArray(a.tagName.toLowerCase(), d.setting.noEndTags) ? 88: a.nodeType
        },
        inMarquee: function(a) {
            for (a = a; a;) {
                if (a.nodeName.toLowerCase() === "marquee") return true;
                a = a.parentNode
            }
            return false
        },
        moveToElementText: function(a, 
        b) {
            this.inMarquee(b) || a.moveToElementText(b)
        },
        getNodeTextLength: function(a) {
            var b = d.util.getNodeType(a);
            if (b == 1) return a.innerHTML.replace(/<.*?>/ig, "").length;
            else if (b == 3) return a.nodeValue.length
        },
        getNodeStartRange: function(a, b) {
            var c = d.util.createRange(a),
            e = b.nodeType;
            if (e == 1) {
                d.util.moveToElementText(c, b);
                return c
            } else if (e == 3) {
                e = 0;
                for (var g = b.previousSibling; g;) {
                    if (g.nodeType == 1) {
                        var h = d.util.createRange(a);
                        d.util.moveToElementText(h, g);
                        c.setEndPoint("StartToEnd", h);
                        c.moveStart("character", 
                        e);
                        return c
                    } else if (g.nodeType == 3) e += g.nodeValue.length;
                    g = g.previousSibling
                }
                d.util.moveToElementText(c, b.parentNode);
                c.moveStart("character", e);
                return c
            }
        },
        removeParent: function(a) {
            if (a.hasChildNodes) for (var b = a.firstChild; b;) {
                var c = b.nextSibling;
                a.parentNode.insertBefore(b, a);
                b = c
            }
            a.parentNode.removeChild(a)
        },
        pluginLang: function(a, b) {
            d.each(d.lang.plugins[a], 
            function(c, e) {
                var g = d.$("lang." + c, b);
                if (g) {
                    g.parentNode.insertBefore(b.createTextNode(e), g);
                    g.parentNode.removeChild(g)
                }
            })
        },
        drag: function(a, b, 
        c, e) {
            var g = d.g[a];
            b.onmousedown = function(h) {
                function i(s) {
                    if (x) {
                        var z = d.util.getCoords(s),
                        A = d.util.getScrollPos();
                        s = parseInt(z.y - u - v + A.y);
                        z = parseInt(z.x - n - t + A.x);
                        e(p, o, q, r, s, z)
                    }
                }
                function j(s) {
                    if (x) {
                        var z = d.util.getCoords(s, g.iframeDoc);
                        s = parseInt(w.y + z.y - u - v);
                        z = parseInt(w.x + z.x - n - t);
                        e(p, o, q, r, s, z)
                    }
                }
                function k(s) {
                    x = false;
                    l.releaseCapture && l.releaseCapture();
                    d.event.remove(document, "mousemove", i);
                    d.event.remove(document, "mouseup", k);
                    d.event.remove(g.iframeDoc, "mousemove", j);
                    d.event.remove(g.iframeDoc, 
                    "mouseup", k);
                    d.event.remove(document, "selectstart", y);
                    d.event.stop(s);
                    return false
                }
                var l = this;
                h = h || window.event;
                var m = d.util.getCoords(h),
                p = parseInt(c.style.top),
                o = parseInt(c.style.left),
                q = c.style.width,
                r = c.style.height;
                if (q.match(/%$/)) q = c.offsetWidth + "px";
                if (r.match(/%$/)) r = c.offsetHeight + "px";
                q = parseInt(q);
                r = parseInt(r);
                var u = m.y,
                n = m.x;
                m = d.util.getScrollPos();
                var v = m.y,
                t = m.x,
                x = true,
                w = d.util.getElementPos(g.iframe),
                y = function() {
                    return false
                };
                d.event.add(document, "mousemove", i);
                d.event.add(document, 
                "mouseup", k);
                d.event.add(g.iframeDoc, "mousemove", j);
                d.event.add(g.iframeDoc, "mouseup", k);
                d.event.add(document, "selectstart", y);
                l.setCapture && l.setCapture();
                d.event.stop(h);
                return false
            }
        },
        resize: function(a, b, c, e, g) {
            g = typeof g == "undefined" ? true: g;
            a = d.g[a];
            if (a.container) if (! (e && (parseInt(b) <= a.minWidth || parseInt(c) <= a.minHeight))) {
                if (g) a.container.style.width = b;
                a.container.style.height = c;
                b = parseInt(c) - a.toolbarHeight - a.statusbarHeight;
                if (b >= 0) {
                    a.iframe.style.height = b + "px";
                    a.newTextarea.style.height = 
                    ((d.browser.IE && d.browser.VERSION < 8 || document.compatMode != "CSS1Compat") && b >= 2 ? b - 2: b) + "px"
                }
            }
        },
        hideLoadingPage: function(a) {
            a = d.g[a].dialogStack;
            a = a[a.length - 1];
            a.loading.style.display = "none";
            a.iframe.style.display = ""
        },
        showLoadingPage: function(a) {
            a = d.g[a].dialogStack;
            a = a[a.length - 1];
            a.loading.style.display = "";
            a.iframe.style.display = "none"
        },
        setDefaultPlugin: function() {
            for (var a = ["selectall", "justifyleft", "justifycenter", "justifyright", "justifyfull", "insertorderedlist", "insertunorderedlist", "indent", "outdent", 
            "subscript", "superscript", "bold", "italic", "underline", "strikethrough"], b = {
                bold: "B",
                italic: "I",
                underline: "U"
            },
            c = 0; c < a.length; c++) {
                var e = a[c],
                g = {};
                if (e in b) g.init = function(h) {
                    return function(i) {
                        d.event.ctrl(d.g[i].iframeDoc, b[h], 
                        function() {
                            d.plugin[h].click(i);
                            d.util.focus(i)
                        },
                        i)
                    }
                } (e);
                g.click = function(h) {
                    return function(i) {
                        d.util.execCommand(i, h, null)
                    }
                } (e);
                d.plugin[e] = g
            }
        },
        getFullHtml: function(a) {
            var b = "<html>";
            b += "<head>";
            b += '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            b += 
            "<title>jsg</title>";
            b += '<link href="' + d.g[a].skinsPath + "common/editor.css?ver=" + escape(d.version) + '" rel="stylesheet" type="text/css" />';
            a = d.g[a].cssPath;
            if (typeof a == "string") a = [a];
            for (var c = 0, e = a.length; c < e; c++) if (a[c] !== "") b += '<link href="' + a[c] + '" rel="stylesheet" type="text/css" />';
            b += "</head>";
            b += '<body class="ke-content"></body>';
            b += "</html>";
            return b
        },
        getMediaType: function(a) {
            return a.match(/\.(rm|rmvb)(\?|$)/i) ? "rm": a.match(/\.(swf|flv)(\?|$)/i) ? "flash": "media"
        },
        getMediaImage: function(a, 
        b, c) {
            var e = c.width,
            g = c.height;
            b = b || this.getMediaType(c.src);
            c = this.getMediaEmbed(c);
            var h = "";
            if (e > 0) h += "width:" + e + "px;";
            if (g > 0) h += "height:" + g + "px;";
            a = '<img class="' + ("ke-" + b) + '" src="' + d.g[a].skinsPath + 'common/blank.gif" ';
            if (h !== "") a += 'style="' + h + '" ';
            a += 'kesrctag="' + escape(c) + '" alt="" />';
            return a
        },
        getMediaEmbed: function(a) {
            var b = "<embed ";
            d.each(a, 
            function(c, e) {
                b += c + '="' + e + '" '
            });
            b += "/>";
            return b
        },
        execGetHtmlHooks: function(a, b) {
            for (var c = d.g[a].getHtmlHooks, e = 0, g = c.length; e < g; e++) b = c[e](b);
            return b
        },
        execSetHtmlHooks: function(a, b) {
            for (var c = d.g[a].setHtmlHooks, e = 0, g = c.length; e < g; e++) b = c[e](b);
            return b
        },
        execOnchangeHandler: function(a) {
            a = d.g[a].onchangeHandlerStack;
            for (var b = 0, c = a.length; b < c; b++) a[b]()
        },
        toData: function(a, b) {
            var c = d.g[a],
            e = this.execGetHtmlHooks(a, b);
            e = e.replace(/^\s*<br[^>]*>\s*$/ig, "");
            e = e.replace(/^\s*<p>\s*&nbsp;\s*<\/p>\s*$/ig, "");
            return c.filterMode ? d.format.getHtml(e, c.htmlTags, c.urlType) : d.format.getHtml(e, null, c.urlType)
        },
        getData: function(a, b) {
            var c = d.g[a];(b = b === f ? c.wyswygMode: 
            b) || this.innerHtml(c.iframeDoc.body, d.util.execSetHtmlHooks(a, c.newTextarea.value));
            return this.toData(a, c.iframeDoc.body.innerHTML)
        },
        getSrcData: function(a) {
            var b = d.g[a];
            b.wyswygMode || this.innerHtml(b.iframeDoc.body, d.util.execSetHtmlHooks(a, b.newTextarea.value));
            return b.iframeDoc.body.innerHTML
        },
        getPureData: function(a) {
            return this.extractText(this.getData(a))
        },
        extractText: function(a) {
            a = a.replace(/<(?!img|embed).*?>/ig, "");
            return a = a.replace(/&nbsp;/ig, " ")
        },
        isEmpty: function(a) {
            return this.getPureData(a).replace(/\r\n|\n|\r/, 
            "").replace(/^\s+|\s+$/, "") === ""
        },
        setData: function(a) {
            var b = d.g[a];
            if (b.srcTextarea) b.srcTextarea.value = this.getData(a)
        },
        focus: function(a) {
            a = d.g[a];
            a.wyswygMode ? a.iframeWin.focus() : a.newTextarea.focus()
        },
        click: function(a, b) {
            this.focus(a);
            d.hideMenu(a);
            d.plugin[b].click(a)
        },
        selection: function(a) {
            if (!d.browser.IE || !d.g[a].keRange) this.setSelection(a)
        },
        setSelection: function(a) {
            a = d.g[a];
            var b = new d.selection(a.iframeDoc);
            if (!d.browser.IE || b.range.item || b.range.parentElement().ownerDocument === a.iframeDoc) {
                a.keSel = 
                b;
                a.keRange = a.keSel.keRange;
                a.sel = a.keSel.sel;
                a.range = a.keSel.range
            }
        },
        select: function(a) {
            d.browser.IE && d.g[a].wyswygMode && d.g[a].range && d.g[a].range.select()
        },
        execCommand: function(a, b, c) {
            d.util.focus(a);
            d.util.select(a);
            try {
                d.g[a].iframeDoc.execCommand(b, false, c)
            } catch(e) {}
            d.toolbar.updateState(a);
            d.util.execOnchangeHandler(a)
        },
        innerHtml: function(a, b) {
            if (d.browser.IE) {
                a.innerHTML = '<img id="__ke_temp_tag__" width="0" height="0" />' + b;
                var c = d.$("__ke_temp_tag__", a.ownerDocument);
                c && c.parentNode.removeChild(c)
            } else a.innerHTML = 
            b
        },
        pasteHtml: function(a, b, c) {
            a = d.g[a];
            if (c) b = '<img id="__ke_temp_tag__" width="0" height="0" />' + b;
            else b += '<img id="__ke_temp_tag__" width="0" height="0" />';
            if (d.browser.IE) if (a.range.item) a.range.item(0).outerHTML = b;
            else a.range.pasteHTML(b);
            else {
                a.range.deleteContents();
                b = a.range.createContextualFragment(b);
                a.range.insertNode(b)
            }
            b = d.$("__ke_temp_tag__", a.iframeDoc);
            c = a.iframeDoc.createTextNode("");
            b.parentNode.replaceChild(c, b);
            a.keRange.selectNode(c);
            a.keSel.addRange(a.keRange)
        },
        insertHtml: function(a, 
        b) {
            if (b !== "") {
                var c = d.g[a];
                if (c.wyswygMode) if (c.range) {
                    b = this.execSetHtmlHooks(a, b);
                    if (d.browser.IE) {
                        this.select(a);
                        if (c.range.item) try {
                            c.range.item(0).outerHTML = b
                        } catch(e) {
                            c = c.range.item(0);
                            var g = c.parentNode;
                            g.removeChild(c);
                            if (g.nodeName.toLowerCase() != "body") g = g.parentNode;
                            this.innerHtml(g, b + g.innerHTML)
                        } else c.range.pasteHTML(b)
                    } else if (d.browser.GECKO && d.browser.VERSION < 3) {
                        this.execCommand(a, "inserthtml", b);
                        return
                    } else this.pasteHtml(a, b);
                    d.util.execOnchangeHandler(a)
                }
            }
        },
        setFullHtml: function(a, 
        b) {
            var c = d.g[a];
            if (!d.browser.IE && b === "") b = "<br />";
            b = d.util.execSetHtmlHooks(a, b);
            this.innerHtml(c.iframeDoc.body, b);
            if (!c.wyswygMode) c.newTextarea.value = d.util.getData(a, true);
            d.util.execOnchangeHandler(a)
        },
        selectImageWebkit: function(a, b, c) {
            if (d.browser.WEBKIT) {
                b = b.srcElement || b.target;
                if (b.tagName.toLowerCase() == "img") {
                    c && d.util.selection(a);
                    c = d.g[a].keRange;
                    c.selectNode(b);
                    d.g[a].keSel.addRange(c)
                }
            }
        },
        addTabEvent: function(a) {
            d.event.add(d.g[a].iframeDoc, "keydown", 
            function(b) {
                if (b.keyCode == 9) {
                    d.util.setSelection(a);
                    d.util.insertHtml(a, "&nbsp;&nbsp;&nbsp;&nbsp;");
                    d.event.stop(b);
                    return false
                }
            },
            a)
        },
        addContextmenuEvent: function(a) {
            var b = d.g[a];
            b.contextmenuItems.length != 0 && d.event.add(b.iframeDoc, "contextmenu", 
            function(c) {
                d.hideMenu(a);
                d.util.setSelection(a);
                d.util.selectImageWebkit(a, c, false);
                for (var e = 0, g = [], h = 0, i = b.contextmenuItems.length; h < i; h++) {
                    var j = b.contextmenuItems[h];
                    if (j === "-") g.push(j);
                    else if (j.cond && j.cond(a)) {
                        g.push(j);
                        if (j.options) {
                            var k = parseInt(j.options.width) || 0;
                            if (k > e) e = k
                        }
                    }
                    j = j
                }
                for (; g.length > 
                0 && g[0] === "-";) g.shift();
                for (; g.length > 0 && g[g.length - 1] === "-";) g.pop();
                j = null;
                h = 0;
                for (i = g.length; h < i; h++) {
                    g[h] === "-" && j === "-" && delete g[h];
                    j = g[h] || null
                }
                if (g.length > 0) {
                    var l = new d.menu({
                        id: a,
                        event: c,
                        type: "contextmenu",
                        width: e
                    });
                    h = 0;
                    for (i = g.length; h < i; h++) if (j = g[h]) if (j === "-") h < i - 1 && l.addSeparator();
                    else l.add(j.text, 
                    function(m) {
                        return function() {
                            m.click(a, l)
                        }
                    } (j), j.options);
                    l.show();
                    d.event.stop(c);
                    return false
                }
                return true
            },
            a)
        },
        addNewlineEvent: function(a) {
            var b = d.g[a];
            d.browser.IE && b.newlineTag.toLowerCase() != 
            "br" || d.browser.GECKO && d.browser.VERSION < 3 && b.newlineTag.toLowerCase() != "p" || d.browser.OPERA || d.event.add(b.iframeDoc, "keydown", 
            function(c) {
                if (c.keyCode != 13 || c.shiftKey || c.ctrlKey || c.altKey) return true;
                d.util.setSelection(a);
                var e = b.keRange.getParentElement();
                if (!d.util.inMarquee(e)) {
                    e = e.tagName.toLowerCase();
                    if (b.newlineTag.toLowerCase() == "br") {
                        if (!d.util.inArray(e, ["h1", "h2", "h3", "h4", "h5", "h6", "li"])) {
                            d.util.pasteHtml(a, "<br />");
                            e = b.keRange.startNode.nextSibling;
                            if (d.browser.IE) e || d.util.pasteHtml(a, 
                            "<br />", true);
                            else if (d.browser.WEBKIT) if (e) {
                                var g = new d.range(b.iframeDoc);
                                g.selectNode(e.parentNode);
                                g.setStart(e, 0);
                                g.cloneContents().innerHTML.replace(/<(?!img|embed).*?>/ig, "") === "" && d.util.pasteHtml(a, "<br />", true)
                            } else d.util.pasteHtml(a, "<br />", true);
                            d.event.stop(c);
                            return false
                        }
                    } else d.util.inArray(e, ["p", "h1", "h2", "h3", "h4", "h5", "h6", "pre", "div", "li"]) || d.util.execCommand(a, "formatblock", "<P>");
                    return true
                }
            },
            a)
        }
    };
    d.layout = {
        hide: function(a) {
            var b = d.g[a];
            d.hideMenu(a);
            for (a = b.dialogStack; a.length > 
            0;) a[a.length - 1].hide();
            b.maskDiv.style.display = "none"
        }
    };
    d.hideMenu = function(a) {
        a = d.g[a];
        a.hideDiv.innerHTML = "";
        a.hideDiv.style.display = "none"
    };
    d.colorpicker = function(a) {
        var b,
        c = a.x || 0,
        e = a.y || 0,
        g = a.z || 0,
        h = a.colors || d.setting.colorTable,
        i = a.doc || document,
        j = a.onclick,
        k = (a.selectedColor || "").toLowerCase();
        b = d.$$("div");
        b.className = "ke-colorpicker";
        b.style.top = e + "px";
        b.style.left = c + "px";
        b.style.zIndex = g;
        this.remove = function() {
            i.body.removeChild(b)
        };
        this.getElement = function() {
            function l(n, v, t) {
                if (k === 
                v.toLowerCase()) t += " ke-colorpicker-cell-selected";
                n.className = t;
                n.title = v || d.lang.noColor;
                n.onmouseover = function() {
                    this.className = t + " ke-colorpicker-cell-on"
                };
                n.onmouseout = function() {
                    this.className = t
                };
                n.onclick = function() {
                    j(v)
                };
                if (v) {
                    var x = d.$$("div");
                    x.className = "ke-colorpicker-cell-color";
                    x.style.backgroundColor = v;
                    n.appendChild(x)
                } else n.innerHTML = d.lang.noColor
            }
            var m = d.$$("table");
            m.className = "ke-colorpicker-table";
            m.cellPadding = 0;
            m.cellSpacing = 0;
            m.border = 0;
            var p = m.insertRow(0),
            o = p.insertCell(0);
            o.colSpan = h[0].length;
            l(o, "", "ke-colorpicker-cell-top");
            for (var q = 0; q < h.length; q++) {
                p = m.insertRow(q + 1);
                for (var r = 0; r < h[q].length; r++) {
                    var u = h[q][r];
                    o = p.insertCell(r);
                    l(o, u, "ke-colorpicker-cell")
                }
            }
            return m
        };
        this.create = function() {
            b.appendChild(this.getElement());
            d.event.bind(b, "click", 
            function() {});
            d.event.bind(b, "mousedown", 
            function() {});
            i.body.appendChild(b)
        }
    };
    d.menu = function(a) {
        function b(c, e) {
            var g = a.id,
            h = 0,
            i = 0;
            if (this.type == "menu") {
                g = d.g[g].toolbarIcon[a.cmd];
                i = d.util.getElementPos(g[0]);
                h = i.x;
                i = i.y + g[0].offsetHeight
            } else {
                i = d.util.getCoords(a.event);
                g = d.util.getElementPos(d.g[g].iframe);
                h = i.x + g.x;
                i = i.y + g.y + 5
            }
            if (c > 0 || e > 0) {
                g = d.util.getScrollPos();
                var j = d.util.getDocumentElement();
                g = g.x + j.clientWidth - c - 2;
                if (h > g) h = g
            }
            return {
                x: h,
                y: i
            }
        } (function() {
            var c = a.width;
            this.type = a.type && a.type == "contextmenu" ? a.type: "menu";
            var e = d.$$("div");
            e.className = "ke-" + this.type;
            e.setAttribute("name", a.cmd);
            var g = b.call(this, 0, 0);
            e.style.top = g.y + "px";
            e.style.left = g.x + "px";
            if (a.width) e.style.width = /^\d+$/.test(c) ? 
            c + "px": c;
            d.event.bind(e, "click", 
            function() {},
            a.id);
            d.event.bind(e, "mousedown", 
            function() {},
            a.id);
            this.div = e
        }).call(this);
        this.add = function(c, e, g) {
            var h,
            i,
            j = false;
            if (g !== f) {
                h = g.height;
                i = g.iconHtml;
                j = g.checked
            }
            var k = this;
            g = d.$$("div");
            g.className = "ke-" + k.type + "-item";
            if (h) g.style.height = h;
            var l = d.$$("div");
            l.className = "ke-" + this.type + "-left";
            var m = d.$$("div");
            m.className = "ke-" + k.type + "-center";
            if (h) m.style.height = h;
            var p = d.$$("div");
            p.className = "ke-" + this.type + "-right";
            if (h) p.style.lineHeight = h;
            g.onmouseover = 
            function() {
                this.className = "ke-" + k.type + "-item ke-" + k.type + "-item-on";
                m.className = "ke-" + k.type + "-center ke-" + k.type + "-center-on"
            };
            g.onmouseout = function() {
                this.className = "ke-" + k.type + "-item";
                m.className = "ke-" + k.type + "-center"
            };
            g.onclick = e;
            g.appendChild(l);
            g.appendChild(m);
            g.appendChild(p);
            if (j) d.util.innerHtml(l, '<span class="ke-common-icon ke-common-icon-url ke-icon-checked"></span>');
            else i && d.util.innerHtml(l, i);
            d.util.innerHtml(p, c);
            this.append(g)
        };
        this.addSeparator = function() {
            var c = d.$$("div");
            c.className = "ke-" + this.type + "-separator";
            this.append(c)
        };
        this.append = function(c) {
            this.div.appendChild(c)
        };
        this.insert = function(c) {
            d.util.innerHtml(this.div, c)
        };
        this.hide = function() {
            d.hideMenu(a.id)
        };
        this.show = function() {
            this.hide();
            var c = a.id;
            d.g[c].hideDiv.style.display = "";
            d.g[c].hideDiv.appendChild(this.div);
            c = b.call(this, this.div.clientWidth, this.div.clientHeight);
            this.div.style.top = c.y + "px";
            this.div.style.left = c.x + "px"
        };
        this.picker = function(c) {
            this.append((new d.colorpicker({
                colors: d.g[a.id].colorTable,
                onclick: function(e) {
                    d.plugin[a.cmd].exec(a.id, e)
                },
                selectedColor: c
            })).getElement());
            this.show()
        }
    };
    d.dialog = function(a) {
        function b() {
            d.util.getDocumentElement();
            var h = d.util.getScrollPos();
            e = h.y;
            g = h.x
        }
        function c() {
            var h = this.width + this.widthMargin,
            i = this.height + this.heightMargin,
            j = a.id,
            k = d.g[j],
            l = 0,
            m = 0;
            if (k.dialogAlignType == "page") {
                k = d.util.getDocumentElement();
                j = d.util.getScrollPos();
                l = Math.round(j.x + (k.clientWidth - h) / 2);
                m = Math.round(j.y + (k.clientHeight - i) / 2)
            } else {
                j = d.util.getElementPos(d.g[j].container);
                k = k.container;
                l = Math.round(k.clientWidth / 2) - Math.round(h / 2);
                i = Math.round(k.clientHeight / 2) - Math.round(i / 2);
                l = l < 0 ? j.x: j.x + l;
                m = i < 0 ? j.y: j.y + i
            }
            return {
                x: l < 0 ? 0: l,
                y: m < 0 ? 0: m
            }
        }
        this.widthMargin = 30;
        this.heightMargin = 100;
        this.zIndex = 19811214;
        this.width = a.width;
        this.height = a.height;
        var e,
        g;
        this.beforeHide = a.beforeHide;
        this.afterHide = a.afterHide;
        this.beforeShow = a.beforeShow;
        this.afterShow = a.afterShow;
        this.ondrag = a.ondrag;
        this.resize = function(h, i) {
            if (h) this.width = h;
            if (i) this.height = i;
            this.hide();
            this.show()
        };
        this.hide = 
        function() {
            this.beforeHide && this.beforeHide(h);
            var h = a.id,
            i = d.g[h].dialogStack;
            if (i[i.length - 1] == this) {
                var j = i.pop().iframe;
                j.src = "javascript:false";
                j.parentNode.removeChild(j);
                document.body.removeChild(this.div);
                if (i.length < 1) d.g[h].maskDiv.style.display = "none";
                d.event.remove(window, "resize", b);
                d.event.remove(window, "scroll", b);
                this.afterHide && this.afterHide(h);
                d.util.focus(h)
            }
        };
        this.show = function() {
            this.beforeShow && this.beforeShow(i);
            var h = this,
            i = a.id,
            j = d.$$("div");
            j.className = "ke-dialog";
            d.event.bind(j, 
            "click", 
            function() {},
            i);
            d.event.bind(j, "mousedown", 
            function() {},
            i);
            var k = d.g[i].dialogStack;
            if (k.length > 0) this.zIndex = k[k.length - 1].zIndex + 1;
            j.style.zIndex = this.zIndex;
            k = c.call(this);
            j.style.top = k.y + "px";
            j.style.left = k.x + "px";
            var l;
            if (d.g[i].shadowMode) {
                k = d.$$("table");
                k.className = "ke-dialog-table";
                k.cellPadding = 0;
                k.cellSpacing = 0;
                k.border = 0;
                for (var m = ["t", "m", "b"], p = ["l", "c", "r"], o = 0; o < 3; o++) for (var q = k.insertRow(o), r = 0; r < 3; r++) {
                    var u = q.insertCell(r);
                    u.className = "ke-" + m[o] + p[r];
                    if (o == 1 && r == 1) l = u;
                    else u.innerHTML = '<span class="ke-dialog-empty"></span>'
                }
                j.appendChild(k)
            } else {
                d.addClass(j, "ke-dialog-no-shadow");
                l = j
            }
            k = d.$$("div");
            k.className = "ke-dialog-title";
            k.innerHTML = a.title;
            m = d.$$("span");
            m.className = "ke-dialog-close";
            d.g[i].shadowMode ? d.addClass(m, "ke-dialog-close-shadow") : d.addClass(m, "ke-dialog-close-no-shadow");
            m.alt = d.lang.close;
            m.title = d.lang.close;
            m.onclick = function() {
                h.hide();
                d.util.select(i)
            };
            k.appendChild(m);
            b();
            d.event.add(window, "resize", b);
            d.event.add(window, "scroll", b);
            d.util.drag(i, 
            k, j, 
            function(n, v, t, x, w, y) {
                h.ondrag && h.ondrag(i);
                b();
                w = n + w;
                y = v + y;
                if (w < e) w = e;
                if (y < g) y = g;
                j.style.top = w + "px";
                j.style.left = y + "px"
            });
            l.appendChild(k);
            p = d.$$("div");
            p.className = "ke-dialog-body";
            k = d.util.createTable();
            k.table.className = "ke-loading-table";
            k.table.style.width = this.width + "px";
            k.table.style.height = this.height + "px";
            m = d.$$("span");
            m.className = "ke-loading-img";
            k.cell.appendChild(m);
            m = d.g[i].dialogStack.length == 0 && d.g[i].dialog ? d.g[i].dialog: d.$$("iframe");
            m.className = a.useFrameCSS ? "ke-dialog-iframe ke-dialog-iframe-border": 
            "ke-dialog-iframe";
            m.setAttribute("frameBorder", "0");
            m.style.width = this.width + "px";
            m.style.height = this.height + "px";
            m.style.display = "none";
            p.appendChild(m);
            p.appendChild(k.table);
            l.appendChild(p);
            r = d.$$("div");
            r.className = "ke-dialog-bottom";
            q = o = p = null;
            if (a.previewButton) {
                q = d.$$("input");
                q.className = "ke-button ke-dialog-preview";
                q.type = "button";
                q.name = "previewButton";
                q.value = a.previewButton;
                q.onclick = function() {
                    var n = d.g[i].dialogStack;
                    n[n.length - 1] == h && d.plugin[a.cmd].preview(i)
                };
                r.appendChild(q)
            }
            if (a.yesButton) {
                o = 
                d.$$("input");
                o.className = "ke-button ke-dialog-yes";
                o.type = "button";
                o.name = "yesButton";
                o.value = a.yesButton;
                o.onclick = function() {
                    var n = d.g[i].dialogStack;
                    n[n.length - 1] == h && d.plugin[a.cmd].exec(i)
                };
                r.appendChild(o)
            }
            if (a.noButton) {
                p = d.$$("input");
                p.className = "ke-button ke-dialog-no";
                p.type = "button";
                p.name = "noButton";
                p.value = a.noButton;
                p.onclick = function() {
                    h.hide();
                    d.util.select(i)
                };
                r.appendChild(p)
            }
            if (a.yesButton || a.noButton || a.previewButton) l.appendChild(r);
            document.body.appendChild(j);
            window.focus();
            if (o) o.focus();
            else p && p.focus();
            if (a.html !== f) {
                l = d.util.getIframeDoc(m);
                r = d.util.getFullHtml(i);
                l.open();
                l.write(r);
                l.close();
                d.util.innerHtml(l.body, a.html)
            } else if (a.url !== f) m.src = a.url;
            else {
                l = "id=" + escape(i) + "&ver=" + escape(d.version);
                if (a.file === f) m.src = d.g[i].pluginsPath + a.cmd + ".html?" + l;
                else {
                    l = (/\?/.test(a.file) ? "&": "?") + l;
                    m.src = d.g[i].pluginsPath + a.file + l
                }
            }
            d.g[i].maskDiv.style.width = d.util.getDocumentWidth() + "px";
            d.g[i].maskDiv.style.height = d.util.getDocumentHeight() + "px";
            d.g[i].maskDiv.style.display = 
            "block";
            this.iframe = m;
            this.loading = k.table;
            this.noButton = p;
            this.yesButton = o;
            this.previewButton = q;
            this.div = j;
            d.g[i].dialogStack.push(this);
            d.g[i].dialog = m;
            d.g[i].yesButton = o;
            d.g[i].noButton = p;
            d.g[i].previewButton = q;
            a.loadingMode || d.util.hideLoadingPage(i);
            this.afterShow && this.afterShow(i);
            d.g[i].afterDialogCreate && d.g[i].afterDialogCreate(i)
        }
    };
    d.toolbar = {
        updateState: function(a) {
            for (var b = ["justifyleft", "justifycenter", "justifyright", "justifyfull", "insertorderedlist", "insertunorderedlist", "indent", 
            "outdent", "subscript", "superscript", "bold", "italic", "underline", "strikethrough"], c = 0; c < b.length; c++) {
                var e = b[c],
                g = false;
                try {
                    g = d.g[a].iframeDoc.queryCommandState(e)
                } catch(h) {}
                g ? d.toolbar.select(a, e) : d.toolbar.unselect(a, e)
            }
        },
        isSelected: function(a, b) {
            return d.plugin[b] && d.plugin[b].isSelected ? true: false
        },
        select: function(a, b) {
            if (d.g[a].toolbarIcon[b]) {
                var c = d.g[a].toolbarIcon[b][0];
                c.className = "ke-icon ke-icon-selected";
                c.onmouseover = null;
                c.onmouseout = null
            }
        },
        unselect: function(a, b) {
            if (d.g[a].toolbarIcon[b]) {
                var c = 
                d.g[a].toolbarIcon[b][0];
                c.className = "ke-icon";
                c.onmouseover = function() {
                    this.className = "ke-icon ke-icon-on"
                };
                c.onmouseout = function() {
                    this.className = "ke-icon"
                }
            }
        },
        _setAttr: function(a, b, c) {
            b.className = "ke-icon";
            b.href = "javascript:;";
            b.onclick = function(e) {
                e = e || window.event;
                var g = d.g[a].hideDiv.firstChild;
                g && g.getAttribute("name") == c ? d.hideMenu(a) : d.util.click(a, c);
                e.preventDefault && e.preventDefault();
                e.stopPropagation && e.stopPropagation();
                if (e.cancelBubble !== f) e.cancelBubble = true;
                return false
            };
            b.onmouseover = 
            function() {
                this.className = "ke-icon ke-icon-on"
            };
            b.onmouseout = function() {
                this.className = "ke-icon"
            };
            b.hidefocus = true;
            b.title = d.lang[c]
        },
        able: function(a, b) {
            var c = this;
            d.each(d.g[a].toolbarIcon, 
            function(e, g) {
                if (!d.util.inArray(e, b)) {
                    var h = g[1];
                    c._setAttr(a, g[0], e);
                    d.util.setOpacity(h, 100)
                }
            })
        },
        disable: function(a, b) {
            d.each(d.g[a].toolbarIcon, 
            function(c, e) {
                if (!d.util.inArray(c, b)) {
                    var g = e[0],
                    h = e[1];
                    g.className = "ke-icon ke-icon-disabled";
                    d.util.setOpacity(h, 50);
                    g.onclick = null;
                    g.onmouseover = null;
                    g.onmouseout = 
                    null
                }
            })
        },
        create: function(a) {
            var b = d.util.arrayToHash(d.setting.items);
            d.g[a].toolbarIcon = [];
            var c = d.util.createTable(),
            e = c.table;
            e.className = "ke-toolbar";
            e.oncontextmenu = function() {
                return false
            };
            e.onmousedown = function() {
                return false
            };
            e.onmousemove = function() {
                return false
            };
            c = c.cell;
            var g = d.g[a].items.length,
            h = 0,
            i;
            d.g[a].toolbarHeight = d.g[a].toolbarLineHeight;
            for (var j = 0; j < g; j++) {
                var k = d.g[a].items[j];
                if (j == 0 || k == "-") {
                    var l = d.util.createTable().table;
                    l.deleteRow(0);
                    l.className = "ke-toolbar-table";
                    i = l.insertRow(0);
                    h = 0;
                    c.appendChild(l);
                    if (k == "-") {
                        d.g[a].toolbarHeight += d.g[a].toolbarLineHeight;
                        continue
                    }
                }
                l = i.insertCell(h);
                l.hideforcus = true;
                h++;
                if (k == "|") {
                    k = d.$$("div");
                    k.className = "ke-toolbar-separator";
                    l.appendChild(k)
                } else {
                    var m = d.$$("a");
                    this._setAttr(a, m, k);
                    var p = d.$$("span");
                    p.className = typeof b[k] == "undefined" ? "ke-common-icon ke-icon-" + k: "ke-common-icon ke-common-icon-url ke-icon-" + k;
                    m.appendChild(p);
                    l.appendChild(m);
                    d.g[a].toolbarIcon[k] = [m, p];
                    d.toolbar.isSelected(a, k) && d.toolbar.select(a, k)
                }
            }
            return e
        }
    };
    d.history = {
        addStackData: function(a, b) {
            var c = "";
            if (a.length > 0) c = a[a.length - 1];
            if (a.length == 0 || b !== c) a.push(b)
        },
        add: function(a, b) {
            var c = d.g[a],
            e = d.util.getSrcData(a);
            if (c.undoStack.length > 0) if (Math.abs(e.length - c.undoStack[c.undoStack.length - 1].length) < b) return;
            this.addStackData(c.undoStack, e)
        },
        undo: function(a) {
            var b = d.g[a];
            if (b.undoStack.length != 0) {
                var c = d.util.getSrcData(a);
                this.addStackData(b.redoStack, c);
                var e = b.undoStack.pop();
                if (c === e && b.undoStack.length > 0) e = b.undoStack.pop();
                e = d.util.toData(a, 
                e);
                if (b.wyswygMode) d.util.innerHtml(b.iframeDoc.body, d.util.execSetHtmlHooks(a, e));
                else b.newTextarea.value = e
            }
        },
        redo: function(a) {
            var b = d.g[a];
            if (b.redoStack.length != 0) {
                var c = d.util.getSrcData(a);
                this.addStackData(b.undoStack, c);
                c = b.redoStack.pop();
                c = d.util.toData(a, c);
                if (b.wyswygMode) d.util.innerHtml(b.iframeDoc.body, d.util.execSetHtmlHooks(a, c));
                else b.newTextarea.value = c
            }
        }
    };
    d.readonly = function(a, b) {
        b = b == f ? true: b;
        var c = d.g[a];
        if (d.browser.IE) c.iframeDoc.body.contentEditable = b ? "false": "true";
        else c.iframeDoc.designMode = 
        b ? "off": "on"
    };
    d.focus = function(a, b) {
        b = (b || "").toLowerCase();
        if (d.g[a].container) {
            d.util.focus(a);
            if (b === "end") {
                d.util.setSelection(a);
                if (d.g[a].sel) {
                    var c = d.g[a].keSel,
                    e = d.g[a].keRange;
                    e.selectTextNode(d.g[a].iframeDoc.body);
                    e.collapse(false);
                    c.addRange(e)
                }
            }
        }
    };
    d.html = function(a, b) {
        if (b === f) return d.util.getData(a);
        else if (d.g[a].container) {
            d.util.setFullHtml(a, b);
            d.focus(a, "end")
        }
    };
    d.text = function(a, b) {
        if (b === f) {
            b = d.html(a);
            b = b.replace(/<.*?>/ig, "");
            b = b.replace(/&nbsp;/ig, " ");
            return b = d.util.trim(b)
        } else d.html(a, 
        d.util.escape(b))
    };
    d.insertHtml = function(a, b) {
        if (d.g[a].container) if (d.g[a].range) {
            d.focus(a);
            d.util.selection(a);
            d.util.insertHtml(a, b)
        } else d.appendHtml(a, b)
    };
    d.appendHtml = function(a, b) {
        d.html(a, d.html(a) + b);
        d.focus(a, "end")
    };
    d.isEmpty = function(a) {
        return d.util.isEmpty(a)
    };
    d.selectedHtml = function(a) {
        var b = d.g[a].range;
        if (!b) return "";
        var c = "";
        if (d.browser.IE) c = b.item ? b.item(0).outerHTML: b.htmlText;
        else {
            c = d.$$("div", d.g[a].iframeDoc);
            c.appendChild(b.cloneContents());
            c = c.innerHTML
        }
        return d.util.toData(a, 
        c)
    };
    d.count = function(a, b) {
        b = (b || "html").toLowerCase();
        if (b === "html") return d.html(a).length;
        else if (b === "text") {
            var c = d.util.getPureData(a);
            c = c.replace(/<(?:img|embed).*?>/ig, "K");
            c = c.replace(/\r\n|\n|\r/g, "");
            c = d.util.trim(c);
            return c.length
        }
        return 0
    };
    d.remove = function(a, b) {
        var c = d.g[a];
        if (!c.container) return false;
        b = typeof b == "undefined" ? 0: b;
        d.util.setData(a);
        for (var e = c.container, g = c.eventStack, h = 0, i = g.length; h < i; h++) {
            var j = g[h];
            j && d.event.remove(j.el, j.type, j.fn, a)
        }
        c.iframeDoc.src = "javascript:false";
        c.iframe.parentNode.removeChild(c.iframe);
        if (b == 1) document.body.removeChild(e);
        else {
            g = c.srcTextarea;
            g.parentNode.removeChild(e);
            if (b == 0) g.style.display = ""
        }
        document.body.removeChild(c.hideDiv);
        document.body.removeChild(c.maskDiv);
        c.container = null;
        c.dialogStack = [];
        c.contextmenuItems = [];
        c.getHtmlHooks = [];
        c.setHtmlHooks = [];
        c.onchangeHandlerStack = [];
        c.eventStack = []
    };
    d.create = function(a, b) {
        function c() {
            d.hideMenu(a)
        }
        function e() {
            d.toolbar.updateState(a)
        }
        function g() {
            d.util.setSelection(a)
        }
        d.g[a].beforeCreate && 
        d.g[a].beforeCreate(a);
        if (d.browser.IE && d.browser.VERSION < 7) try {
            document.execCommand("BackgroundImageCache", false, true)
        } catch(h) {}
        var i = d.$(a) || document.getElementsByName(a)[0];
        b = typeof b == "undefined" ? 0: b;
        if (! (b == 0 && d.g[a].container)) {
            var j = d.g[a].width || i.style.width || i.offsetWidth + "px",
            k = d.g[a].height || i.style.height || i.offsetHeight + "px",
            l = d.util.createTable(),
            m = l.table;
            m.className = "ke-container";
            m.style.width = j;
            m.style.height = k;
            var p = l.cell;
            p.className = "ke-toolbar-outer";
            var o = m.insertRow(0).insertCell(0);
            o.className = "ke-textarea-outer";
            l = d.util.createTable();
            var q = l.table;
            q.className = "ke-textarea-table";
            var r = l.cell;
            o.appendChild(q);
            var u = m.insertRow(0).insertCell(0);
            u.className = "ke-bottom-outer";
            i.style.display = "none";
            b == 1 ? document.body.appendChild(m) : i.parentNode.insertBefore(m, i);
            o = d.toolbar.create(a);
            o.style.height = d.g[a].toolbarHeight + "px";
            p.appendChild(o);
            p = d.g[a].iframe || d.$$("iframe");
            p.className = "ke-iframe";
            p.setAttribute("frameBorder", "0");
            l = d.$$("textarea");
            l.className = "ke-textarea";
            l.style.display = 
            "none";
            d.g[a].container = m;
            d.g[a].iframe = p;
            d.g[a].newTextarea = l;
            d.util.resize(a, j, k);
            r.appendChild(p);
            r.appendChild(l);
            r = d.$$("table");
            r.className = "ke-bottom";
            r.cellPadding = 0;
            r.cellSpacing = 0;
            r.border = 0;
            r.style.height = d.g[a].statusbarHeight + "px";
            var n = r.insertRow(0),
            v = n.insertCell(0);
            v.className = "ke-bottom-left";
            var t = d.$$("span");
            t.className = "ke-bottom-left-img";
            if (d.g[a].config.resizeMode == 0 || b == 1) {
               v.style.cursor = "default";
                t.style.visibility = "hidden"
            }
            v.appendChild(t);
            n = n.insertCell(1);
            n.className = 
            "ke-bottom-right";
            t = d.$$("span");
            t.className = "ke-bottom-right-img";
            if (d.g[a].config.resizeMode == 0 || b == 1) {
                n.style.cursor = "default";
                t.style.visibility = "hidden"
            } else if (d.g[a].config.resizeMode == 1) {
                n.style.cursor = "s-resize";
                t.style.visibility = "hidden"
            }
            n.appendChild(t);
            u.appendChild(r);
            u = d.$$("div");
            u.className = "ke-reset";
            u.style.display = "none";
            t = d.$$("div");
            t.className = "ke-mask";
            d.util.setOpacity(t, 50);
            d.event.bind(t, "click", 
            function() {},
            a);
            d.event.bind(t, "mousedown", 
            function() {},
            a);
            document.body.appendChild(u);
            document.body.appendChild(t);
            d.util.setDefaultPlugin(a);
            var x = p.contentWindow,
            w = d.util.getIframeDoc(p);
            if (!d.browser.IE || d.browser.VERSION < 8) w.designMode = "on";
            var y = d.util.getFullHtml(a);
            w.open();
            w.write(y);
            w.close();
            if (!d.g[a].wyswygMode) {
                l.value = d.util.execSetHtmlHooks(a, i.value);
                l.style.display = "block";
                p.style.display = "none";
                d.toolbar.disable(a, ["source", "fullscreen"]);
                d.toolbar.select(a, "source")
            }
            d.browser.WEBKIT && d.event.add(w, "click", 
            function(s) {
                d.util.selectImageWebkit(a, s, true)
            },
            a);
            d.browser.IE && 
            d.event.add(w, "keydown", 
            function(s) {
                if (s.keyCode == 8) {
                    s = d.g[a].range;
                    if (s.item) {
                        s = s.item(0);
                        s.parentNode.removeChild(s);
                        d.util.execOnchangeHandler(a);
                        d.event.stop(a);
                        return false
                    }
                }
            },
            a);
            d.event.add(w, "click", c, a);
            d.event.add(w, "click", e, a);
            d.event.input(w, e, a);
            d.event.bind(l, "click", c, a);
            d.event.add(document, "click", c, a);
            d.g[a].toolbarTable = o;
            d.g[a].textareaTable = q;
            d.g[a].srcTextarea = i;
            d.g[a].bottom = r;
            d.g[a].hideDiv = u;
            d.g[a].maskDiv = t;
            d.g[a].iframeWin = x;
            d.g[a].iframeDoc = w;
            d.g[a].width = j;
            d.g[a].height = 
            k;
            d.util.drag(a, n, m, 
            function(s, z, A, B, C, D) {
                if (d.g[a].resizeMode == 2) d.util.resize(a, A + D + "px", B + C + "px", true);
                else d.g[a].resizeMode == 1 && d.util.resize(a, A + "px", B + C + "px", true, false)
            });
            d.util.drag(a, v, m, 
            function(s, z, A, B, C) {
                d.g[a].resizeMode > 0 && d.util.resize(a, A + "px", B + C + "px", true, false)
            });
            d.each(d.plugin, 
            function(s, z) {
                z.init && z.init(a)
            });
            d.g[a].getHtmlHooks.push(function(s) {
                return s.replace(/(<[^>]*)kesrc="([^"]+)"([^>]*>)/ig, 
                function(z, A, B) {
                    z = z.replace(/(\s+(?:href|src)=")[^"]+(")/i, "$1" + B + "$2");
                    return z = 
                    z.replace(/\s+kesrc="[^"]+"/i, "")
                })
            });
            d.g[a].setHtmlHooks.push(function(s) {
                return s.replace(/(<[^>]*)(href|src)="([^"]+)"([^>]*>)/ig, 
                function(z, A, B, C, D) {
                    if (z.match(/\skesrc="[^"]+"/i)) return z;
                    return A + B + '="' + C + '" kesrc="' + C + '"' + D
                })
            });
            d.util.addContextmenuEvent(a);
            d.util.addNewlineEvent(a);
            d.util.addTabEvent(a);
            d.event.input(w, g, a);
            d.event.add(w, "mouseup", g, a);
            d.event.add(document, "mousedown", g, a);
            d.onchange(a, 
            function(s) {
                if (d.g[s].autoSetDataMode) {
                    d.util.setData(s);
                    d.g[s].afterSetData && d.g[s].afterSetData(s)
                }
                d.history.add(s, 
                d.g[s].minChangeSize)
            });
            d.browser.IE && d.browser.VERSION > 7 && d.readonly(a, false);
            d.util.setFullHtml(a, i.value);
            d.history.add(a, 0);
            b > 0 && d.util.focus(a);
            d.g[a].afterCreate && d.g[a].afterCreate(a)
        }
    };
    d.onchange = function(a, b) {
        function c() {
            b(a)
        }
        var e = d.g[a];
        e.onchangeHandlerStack.push(c);
        d.event.input(e.iframeDoc, c, a);
        d.event.input(e.newTextarea, c, a);
        d.event.add(e.iframeDoc, "mouseup", 
        function() {
            window.setTimeout(function() {
                b(a)
            },
            0)
        },
        a)
    };
    d.init = function(a) {
        var b = d.g[a.id] = a;
        b.config = {};
        b.undoStack = [];
        b.redoStack = 
        [];
        b.dialogStack = [];
        b.contextmenuItems = [];
        b.getHtmlHooks = [];
        b.setHtmlHooks = [];
        b.onchangeHandlerStack = [];
        b.eventStack = [];
        d.each(d.setting, 
        function(c, e) {
            b[c] = typeof a[c] == "undefined" ? e: a[c];
            b.config[c] = b[c]
        });
        b.loadStyleMode && d.util.loadStyle(b.skinsPath + b.skinType + ".css")
    };
    d.show = function(a) {
        d.init(a);
        d.event.ready(function() {
            d.create(a.id)
        })
    };
    if (window.KE === f) window.KE = d;
    window.KindEditor = d
})();
 (function(f) {
    f.langType = "zh_CN";
    f.lang = {
        source: "HTML\u4ee3\u7801",
        undo: "\u540e\u9000(Ctrl+Z)",
        redo: "\u524d\u8fdb(Ctrl+Y)",
        cut: "\u526a\u5207(Ctrl+X)",
        copy: "\u590d\u5236(Ctrl+C)",
        paste: "\u7c98\u8d34(Ctrl+V)",
        plainpaste: "\u7c98\u8d34\u4e3a\u65e0\u683c\u5f0f\u6587\u672c",
        wordpaste: "\u4eceWord\u7c98\u8d34",
        selectall: "\u5168\u9009",
        justifyleft: "\u5de6\u5bf9\u9f50",
        justifycenter: "\u5c45\u4e2d",
        justifyright: "\u53f3\u5bf9\u9f50",
        justifyfull: "\u4e24\u7aef\u5bf9\u9f50",
        insertorderedlist: "\u7f16\u53f7",
        insertunorderedlist: "\u9879\u76ee\u7b26\u53f7",
        indent: "\u589e\u52a0\u7f29\u8fdb",
        outdent: "\u51cf\u5c11\u7f29\u8fdb",
        subscript: "\u4e0b\u6807",
        superscript: "\u4e0a\u6807",
        title: "\u6807\u9898",
        fontname: "\u5b57\u4f53",
        fontsize: "\u6587\u5b57\u5927\u5c0f",
        textcolor: "\u6587\u5b57\u989c\u8272",
        bgcolor: "\u6587\u5b57\u80cc\u666f",
        bold: "\u7c97\u4f53(Ctrl+B)",
        italic: "\u659c\u4f53(Ctrl+I)",
        underline: "\u4e0b\u5212\u7ebf(Ctrl+U)",
        strikethrough: "\u5220\u9664\u7ebf",
        removeformat: "\u5220\u9664\u683c\u5f0f",
        image: "\u56fe\u7247",
        flash: "\u63d2\u5165Flash",
        media: "\u63d2\u5165\u591a\u5a92\u4f53",
        table: "\u63d2\u5165\u8868\u683c",
        hr: "\u63d2\u5165\u6a2a\u7ebf",
        emoticons: "\u63d2\u5165\u8868\u60c5",
        link: "\u8d85\u7ea7\u94fe\u63a5",
        unlink: "\u53d6\u6d88\u8d85\u7ea7\u94fe\u63a5",
        fullscreen: "\u5168\u5c4f\u663e\u793a",
        about: "\u5173\u4e8e",
        print: "\u6253\u5370",
        fileManager: "\u6d4f\u89c8\u670d\u52a1\u5668",
        advtable: "\u8868\u683c",
        yes: "\u786e\u5b9a",
        no: "\u53d6\u6d88",
        close: "\u5173\u95ed",
        editImage: "\u56fe\u7247\u5c5e\u6027",
        deleteImage: "\u5220\u9664\u56fe\u7247",
        editLink: "\u8d85\u7ea7\u94fe\u63a5\u5c5e\u6027",
        deleteLink: "\u53d6\u6d88\u8d85\u7ea7\u94fe\u63a5",
        tableprop: "\u8868\u683c\u5c5e\u6027",
        tableinsert: "\u63d2\u5165\u8868\u683c",
        tabledelete: "\u5220\u9664\u8868\u683c",
        tablecolinsertleft: "\u5de6\u4fa7\u63d2\u5165\u5217",
        tablecolinsertright: "\u53f3\u4fa7\u63d2\u5165\u5217",
        tablerowinsertabove: "\u4e0a\u65b9\u63d2\u5165\u884c",
        tablerowinsertbelow: "\u4e0b\u65b9\u63d2\u5165\u884c",
        tablecoldelete: "\u5220\u9664\u5217",
        tablerowdelete: "\u5220\u9664\u884c",
        noColor: "\u65e0\u989c\u8272",
        invalidImg: "\u8bf7\u8f93\u5165\u6709\u6548\u7684URL\u5730\u5740\u3002\n\u53ea\u5141\u8bb8jpg,gif,bmp,png\u683c\u5f0f\u3002",
        invalidMedia: "\u8bf7\u8f93\u5165\u6709\u6548\u7684URL\u5730\u5740\u3002\n\u53ea\u5141\u8bb8swf,flv,mp3,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb\u683c\u5f0f\u3002",
        invalidWidth: "\u5bbd\u5ea6\u5fc5\u987b\u4e3a\u6570\u5b57\u3002",
        invalidHeight: "\u9ad8\u5ea6\u5fc5\u987b\u4e3a\u6570\u5b57\u3002",
        invalidBorder: "\u8fb9\u6846\u5fc5\u987b\u4e3a\u6570\u5b57\u3002",
        invalidUrl: "\u8bf7\u8f93\u5165\u6709\u6548\u7684URL\u5730\u5740\u3002",
        invalidRows: "\u884c\u6570\u4e3a\u5fc5\u9009\u9879\uff0c\u53ea\u5141\u8bb8\u8f93\u5165\u5927\u4e8e0\u7684\u6570\u5b57\u3002",
        invalidCols: "\u5217\u6570\u4e3a\u5fc5\u9009\u9879\uff0c\u53ea\u5141\u8bb8\u8f93\u5165\u5927\u4e8e0\u7684\u6570\u5b57\u3002",
        invalidPadding: "\u8fb9\u8ddd\u5fc5\u987b\u4e3a\u6570\u5b57\u3002",
        invalidSpacing: "\u95f4\u8ddd\u5fc5\u987b\u4e3a\u6570\u5b57\u3002",
        invalidBorder: "\u8fb9\u6846\u5fc5\u987b\u4e3a\u6570\u5b57\u3002",
        pleaseInput: "\u8bf7\u8f93\u5165\u5185\u5bb9\u3002",
        invalidJson: "\u670d\u52a1\u5668\u53d1\u751f\u6545\u969c\u3002",
        cutError: "\u60a8\u7684\u6d4f\u89c8\u5668\u5b89\u5168\u8bbe\u7f6e\u4e0d\u5141\u8bb8\u4f7f\u7528\u526a\u5207\u64cd\u4f5c\uff0c\u8bf7\u4f7f\u7528\u5feb\u6377\u952e(Ctrl+X)\u6765\u5b8c\u6210\u3002",
        copyError: "\u60a8\u7684\u6d4f\u89c8\u5668\u5b89\u5168\u8bbe\u7f6e\u4e0d\u5141\u8bb8\u4f7f\u7528\u590d\u5236\u64cd\u4f5c\uff0c\u8bf7\u4f7f\u7528\u5feb\u6377\u952e(Ctrl+C)\u6765\u5b8c\u6210\u3002",
        pasteError: "\u60a8\u7684\u6d4f\u89c8\u5668\u5b89\u5168\u8bbe\u7f6e\u4e0d\u5141\u8bb8\u4f7f\u7528\u7c98\u8d34\u64cd\u4f5c\uff0c\u8bf7\u4f7f\u7528\u5feb\u6377\u952e(Ctrl+V)\u6765\u5b8c\u6210\u3002"
    };
    var d = f.lang.plugins = {};
    d.about = {
        version: f.version,
        title: "HTML\u53ef\u89c6\u5316\u7f16\u8f91\u5668"
    };
    d.plainpaste = 
    {
        comment: "\u8bf7\u4f7f\u7528\u5feb\u6377\u952e(Ctrl+V)\u628a\u5185\u5bb9\u7c98\u8d34\u5230\u4e0b\u9762\u7684\u65b9\u6846\u91cc\u3002"
    };
    d.wordpaste = {
        comment: "\u8bf7\u4f7f\u7528\u5feb\u6377\u952e(Ctrl+V)\u628a\u5185\u5bb9\u7c98\u8d34\u5230\u4e0b\u9762\u7684\u65b9\u6846\u91cc\u3002"
    };
    d.link = {
        url: "URL\u5730\u5740",
        linkType: "\u6253\u5f00\u7c7b\u578b",
        newWindow: "\u65b0\u7a97\u53e3",
        selfWindow: "\u5f53\u524d\u7a97\u53e3"
    };
    d.flash = {
        url: "Flash\u5730\u5740",
        width: "\u5bbd\u5ea6",
        height: "\u9ad8\u5ea6"
    };
    d.media = 
    {
        url: "\u5a92\u4f53\u6587\u4ef6\u5730\u5740",
        width: "\u5bbd\u5ea6",
        height: "\u9ad8\u5ea6",
        autostart: "\u81ea\u52a8\u64ad\u653e"
    };
    d.image = {
        remoteImage: "\u8fdc\u7a0b\u56fe\u7247",
        localImage: "\u672c\u5730\u4e0a\u4f20",
        remoteUrl: "\u56fe\u7247\u5730\u5740",
        localUrl: "\u56fe\u7247\u5730\u5740",
        size: "\u56fe\u7247\u5927\u5c0f",
        width: "\u5bbd",
        height: "\u9ad8",
        resetSize: "\u91cd\u7f6e\u5927\u5c0f",
        align: "\u5bf9\u9f50\u65b9\u5f0f",
        defaultAlign: "\u9ed8\u8ba4\u65b9\u5f0f",
        leftAlign: "\u5de6\u5bf9\u9f50",
        rightAlign: "\u53f3\u5bf9\u9f50",
        imgTitle: "\u56fe\u7247\u8bf4\u660e",
        viewServer: "\u6d4f\u89c8..."
    };
    d.file_manager = {
        emptyFolder: "\u7a7a\u6587\u4ef6\u5939",
        moveup: "\u79fb\u5230\u4e0a\u4e00\u7ea7\u6587\u4ef6\u5939",
        viewType: "\u663e\u793a\u65b9\u5f0f\uff1a",
        viewImage: "\u7f29\u7565\u56fe",
        listImage: "\u8be6\u7ec6\u4fe1\u606f",
        orderType: "\u6392\u5e8f\u65b9\u5f0f\uff1a",
        fileName: "\u540d\u79f0",
        fileSize: "\u5927\u5c0f",
        fileType: "\u7c7b\u578b"
    };
    d.advtable = {
        cells: "\u5355\u5143\u683c\u6570",
        rows: "\u884c\u6570",
        cols: "\u5217\u6570",
        size: "\u8868\u683c\u5927\u5c0f",
        width: "\u5bbd\u5ea6",
        height: "\u9ad8\u5ea6",
        percent: "%",
        px: "px",
        space: "\u8fb9\u8ddd\u95f4\u8ddd",
        padding: "\u8fb9\u8ddd",
        spacing: "\u95f4\u8ddd",
        align: "\u5bf9\u9f50\u65b9\u5f0f",
        alignDefault: "\u9ed8\u8ba4",
        alignLeft: "\u5de6\u5bf9\u9f50",
        alignCenter: "\u5c45\u4e2d",
        alignRight: "\u53f3\u5bf9\u9f50",
        border: "\u8868\u683c\u8fb9\u6846",
        borderWidth: "\u8fb9\u6846",
        borderColor: "\u989c\u8272",
        backgroundColor: "\u80cc\u666f\u989c\u8272"
    };
    d.title = {
        h1: "\u6807\u9898 1",
        h2: "\u6807\u9898 2",
        h3: "\u6807\u9898 3",
        h4: "\u6807\u9898 4",
        p: "\u6b63 \u6587"
    };
    d.fontname = {
        fontName: {
            SimSun: "\u5b8b\u4f53",
            NSimSun: "\u65b0\u5b8b\u4f53",
            FangSong_GB2312: "\u4eff\u5b8b_GB2312",
            KaiTi_GB2312: "\u6977\u4f53_GB2312",
            SimHei: "\u9ed1\u4f53",
            "Microsoft YaHei": "\u5fae\u8f6f\u96c5\u9ed1",
            Arial: "Arial",
            "Arial Black": "Arial Black",
            "Times New Roman": "Times New Roman",
            "Courier New": "Courier New",
            Tahoma: "Tahoma",
            Verdana: "Verdana"
        }
    }
})(KindEditor);
 (function(f, d) {
    f.plugin.about = {
        click: function(a) {
            f.util.selection(a);(new f.dialog({
                id: a,
                cmd: "about",
                file: "about.html",
                width: 300,
                height: 70,
                loadingMode: true,
                title: f.lang.about,
                noButton: f.lang.close
            })).show()
        }
    };
    f.plugin.undo = {
        init: function(a) {
            f.event.ctrl(f.g[a].iframeDoc, "Z", 
            function() {
                f.plugin.undo.click(a);
                f.util.focus(a)
            },
            a);
            f.event.ctrl(f.g[a].newTextarea, "Z", 
            function() {
                f.plugin.undo.click(a);
                f.util.focus(a)
            },
            a)
        },
        click: function(a) {
            f.history.undo(a);
            f.util.execOnchangeHandler(a)
        }
    };
    f.plugin.redo = 
    {
        init: function(a) {
            f.event.ctrl(f.g[a].iframeDoc, "Y", 
            function() {
                f.plugin.redo.click(a);
                f.util.focus(a)
            },
            a);
            f.event.ctrl(f.g[a].newTextarea, "Y", 
            function() {
                f.plugin.redo.click(a);
                f.util.focus(a)
            },
            a)
        },
        click: function(a) {
            f.history.redo(a);
            f.util.execOnchangeHandler(a)
        }
    };
    f.plugin.cut = {
        click: function(a) {
            try {
                if (!f.g[a].iframeDoc.queryCommandSupported("cut")) throw "e";
            } catch(b) {
                alert(f.lang.cutError);
                return
            }
            f.util.execCommand(a, "cut", null)
        }
    };
    f.plugin.copy = {
        click: function(a) {
            try {
                if (!f.g[a].iframeDoc.queryCommandSupported("copy")) throw "e";

            } catch(b) {
                alert(f.lang.copyError);
                return
            }
            f.util.execCommand(a, "copy", null)
        }
    };
    f.plugin.paste = {
        click: function(a) {
            try {
                if (!f.g[a].iframeDoc.queryCommandSupported("paste")) throw "e";
            } catch(b) {
                alert(f.lang.pasteError);
                return
            }
            f.util.execCommand(a, "paste", null)
        }
    };
    f.plugin.plainpaste = {
        click: function(a) {
            f.util.selection(a);
            this.dialog = new f.dialog({
                id: a,
                cmd: "plainpaste",
                file: "plainpaste.html",
                width: 450,
                height: 300,
                loadingMode: true,
                title: f.lang.plainpaste,
                yesButton: f.lang.yes,
                noButton: f.lang.no
            });
            this.dialog.show()
        },
        exec: function(a) {
            var b = f.util.getIframeDoc(this.dialog.iframe);
            b = f.$("textArea", b).value;
            b = f.util.escape(b);
            b = b.replace(/ /g, "&nbsp;");
            b = b.replace(/\r\n|\n|\r/g, "<br />$&");
            f.util.insertHtml(a, b);
            this.dialog.hide();
            f.util.focus(a)
        }
    };
    f.plugin.wordpaste = {
        click: function(a) {
            f.util.selection(a);
            this.dialog = new f.dialog({
                id: a,
                cmd: "wordpaste",
                file: "wordpaste.html",
                width: 450,
                height: 300,
                loadingMode: true,
                title: f.lang.wordpaste,
                yesButton: f.lang.yes,
                noButton: f.lang.no
            });
            this.dialog.show()
        },
        exec: function(a) {
            var b = 
            f.util.getIframeDoc(this.dialog.iframe);
            b = f.$("wordIframe", b);
            b = f.util.getIframeDoc(b).body.innerHTML;
            b = b.replace(/<meta(\n|.)*?>/ig, "");
            b = b.replace(/<!(\n|.)*?>/ig, "");
            b = b.replace(/<style[^>]*>(\n|.)*?<\/style>/ig, "");
            b = b.replace(/<script[^>]*>(\n|.)*?<\/script>/ig, "");
            b = b.replace(/<w:[^>]+>(\n|.)*?<\/w:[^>]+>/ig, "");
            b = b.replace(/<xml>(\n|.)*?<\/xml>/ig, "");
            b = b.replace(/\r\n|\n|\r/ig, "");
            b = f.util.execGetHtmlHooks(a, b);
            b = f.format.getHtml(b, f.g[a].htmlTags, f.g[a].urlType);
            f.util.insertHtml(a, b);
            this.dialog.hide();
            f.util.focus(a)
        }
    };
    f.plugin.fullscreen = {
        click: function(a) {
            var b = f.g[a],
            c = this,
            e = function() {
                var k = f.util.getDocumentElement();
                b.width = k.clientWidth + "px";
                b.height = k.clientHeight + "px"
            },
            g = "",
            h = function() {
                if (c.isSelected) {
                    var k = f.util.getDocumentElement();
                    k = [k.clientWidth, k.clientHeight].join("");
                    if (g != k) {
                        g = k;
                        e();
                        f.util.resize(a, b.width, b.height)
                    }
                }
            };
            if (this.isSelected) {
                this.isSelected = false;
                f.util.setData(a);
                f.remove(a, 1);
                b.width = this.width;
                b.height = this.height;
                f.create(a, 2);
                document.body.parentNode.style.overflow = 
                "auto";
                f.event.remove(window, "resize", h);
                b.resizeMode = b.config.resizeMode;
                f.toolbar.unselect(a, "fullscreen")
            } else {
                this.isSelected = true;
                this.width = b.container.style.width;
                this.height = b.container.style.height;
                f.util.setData(a);
                f.remove(a, 2);
                document.body.parentNode.style.overflow = "hidden";
                e();
                f.create(a, 1);
                var i = f.util.getScrollPos(),
                j = b.container;
                j.style.position = "absolute";
                j.style.left = i.x + "px";
                j.style.top = i.y + "px";
                j.style.zIndex = 19811211;
                f.event.add(window, "resize", h);
                b.resizeMode = 0;
                f.toolbar.select(a, 
                "fullscreen")
            }
        }
    };
    f.plugin.bgcolor = {
        click: function(a) {
            f.util.selection(a);
            var b = f.queryCommandValue(f.g[a].iframeDoc, "bgcolor");
            this.menu = new f.menu({
                id: a,
                cmd: "bgcolor"
            });
            this.menu.picker(b)
        },
        exec: function(a, b) {
            var c = new f.cmd(a);
            b == "" ? c.remove({
                span: [".background-color"]
            }) : c.wrap("span", [{
                ".background-color": b
            }]);
            f.util.execOnchangeHandler(a);
            this.menu.hide();
            f.util.focus(a)
        }
    };
    f.plugin.fontname = {
        click: function(a) {
            var b = f.lang.plugins.fontname.fontName;
            f.util.selection(a);
            var c = new f.menu({
                id: a,
                cmd: "fontname",
                width: 150
            }),
            e = f.queryCommandValue(f.g[a].iframeDoc, "fontname");
            f.each(b, 
            function(g, h) {
                c.add('<span class="ke-reset" style="font-family: ' + g + ';">' + h + "</span>", 
                function() {
                    f.plugin.fontname.exec(a, g)
                },
                {
                    checked: e === g.toLowerCase() || e === h.toLowerCase()
                })
            });
            c.show();
            this.menu = c
        },
        exec: function(a, b) {(new f.cmd(a)).wrap("span", [{
                ".font-family": b
            }]);
            f.util.execOnchangeHandler(a);
            this.menu.hide();
            f.util.focus(a)
        }
    };
    f.plugin.fontsize = {
        click: function(a) {
            var b = ["9px", "10px", "12px", "14px", "16px", "18px", "24px", "32px"];
            f.util.selection(a);
            for (var c = f.queryCommandValue(f.g[a].iframeDoc, "fontsize"), e = new f.menu({
                id: a,
                cmd: "fontsize",
                width: 120
            }), g = 0, h = b.length; g < h; g++) {
                var i = b[g];
                e.add('<span class="ke-reset" style="font-size: ' + i + ';">' + i + "</span>", 
                function(j) {
                    return function() {
                        f.plugin.fontsize.exec(a, j)
                    }
                } (i), {
                    height: parseInt(i) + 12 + "px",
                    checked: c === i
                })
            }
            e.show();
            this.menu = e
        },
        exec: function(a, b) {(new f.cmd(a)).wrap("span", [{
                ".font-size": b
            }]);
            f.util.execOnchangeHandler(a);
            this.menu.hide();
            f.util.focus(a)
        }
    };
    f.plugin.hr = 
    {
        click: function(a) {
            f.util.selection(a);
            f.util.insertHtml(a, "<hr />");
            f.util.focus(a)
        }
    };
    f.plugin.print = {
        click: function(a) {
            f.util.selection(a);
            f.g[a].iframeWin.print()
        }
    };
    f.plugin.removeformat = {
        click: function(a) {
            f.util.selection(a);
            for (var b = new f.cmd(a), c = {
                "*": ["class", "style"]
            },
            e = 0, g = f.g[a].inlineTags.length; e < g; e++) c[f.g[a].inlineTags[e]] = ["*"];
            b.remove(c);
            f.util.execOnchangeHandler(a);
            f.toolbar.updateState(a);
            f.util.focus(a)
        }
    };
    f.plugin.source = {
        click: function(a) {
            var b = f.g[a];
            if (b.wyswygMode) {
                f.hideMenu(a);
                b.newTextarea.value = f.util.getData(a);
                b.iframe.style.display = "none";
                b.newTextarea.style.display = "block";
                f.toolbar.disable(a, ["source", "fullscreen"]);
                b.wyswygMode = false;
                this.isSelected = true;
                f.toolbar.select(a, "source")
            } else {
                f.util.setFullHtml(a, b.newTextarea.value);
                b.iframe.style.display = "block";
                b.newTextarea.style.display = "none";
                f.toolbar.able(a, ["source", "fullscreen"]);
                b.wyswygMode = true;
                this.isSelected = false;
                f.toolbar.unselect(a, "source")
            }
            f.util.focus(a)
        }
    };
    f.plugin.textcolor = {
        click: function(a) {
            f.util.selection(a);
            var b = f.queryCommandValue(f.g[a].iframeDoc, "textcolor");
            this.menu = new f.menu({
                id: a,
                cmd: "textcolor"
            });
            this.menu.picker(b)
        },
        exec: function(a, b) {
            var c = new f.cmd(a);
            b == "" ? c.remove({
                span: [".color"],
                font: ["color"]
            }) : c.wrap("span", [{
                ".color": b
            }]);
            f.util.execOnchangeHandler(a);
            this.menu.hide();
            f.util.focus(a)
        }
    };
    f.plugin.title = {
        click: function(a) {
            var b = f.lang.plugins.title;
            b = {
                H1: b.h1,
                H2: b.h2,
                H3: b.h3,
                H4: b.h4,
                P: b.p
            };
            var c = {
                H1: 28,
                H2: 24,
                H3: 18,
                H4: 14,
                P: 12
            };
            f.util.selection(a);
            var e = f.queryCommandValue(f.g[a].iframeDoc, 
            "formatblock"),
            g = new f.menu({
                id: a,
                cmd: "title",
                width: f.langType == "en" ? 200: 150
            });
            f.each(b, 
            function(h, i) {
                var j = "font-size:" + c[h] + "px;";
                if (h !== "P") j += "font-weight:bold;";
                g.add('<span class="ke-reset" style="' + j + '">' + i + "</span>", 
                function() {
                    f.plugin.title.exec(a, "<" + h + ">")
                },
                {
                    height: c[h] + 12 + "px",
                    checked: e === h.toLowerCase() || e === i.toLowerCase()
                })
            });
            g.show();
            this.menu = g
        },
        exec: function(a, b) {
            f.util.select(a);
            f.util.execCommand(a, "formatblock", b);
            this.menu.hide();
            f.util.focus(a)
        }
    };
    f.plugin.emoticons = {
        click: function(a) {
            function b(v) {
                var t = 
                f.$$("table");
                if (q) {
                    t.onmouseover = function() {
                        q.style.display = "block"
                    };
                    t.onmouseout = function() {
                        q.style.display = "none"
                    }
                }
                t.className = "ke-plugin-emoticons-table";
                t.cellPadding = 0;
                t.cellSpacing = 0;
                t.border = 0;
                v = (v - 1) * j + i;
                var totle = list['tusiji_face'].length;
                for (var x = 0; x < g; x++) for (var w = t.insertRow(x), y = 0; y < h && v < totle; y++) {
                    var s = w.insertCell(y);
                    s.className = "ke-plugin-emoticons-cell";
                    s.onmouseover = q ? 
                    function(A, B) {
                        return function() {
                            if (A > l) {
                                q.style.left = 0;
                                q.style.right = ""
                            } else {
                                q.style.left = "";
                                q.style.right = 0
                            }
                            r.src = p + list['tusiji_face'][B] + ".gif";
                            this.className = "ke-plugin-emoticons-cell ke-plugin-emoticons-cell-on"
                        }
                    } (y, 
                    v) : function() {
                        this.className = "ke-plugin-emoticons-cell ke-plugin-emoticons-cell-on"
                    };
                    s.onmouseout = function() {
                        this.className = "ke-plugin-emoticons-cell"
                    };
                    s.onclick = function(A) {
                        return function() {
                            e.exec(a, A,list);
                            return false
                        }
                    } (v);
                    var z = f.$$("span");
                    z.className = "ke-plugin-emoticons-img";
                    z.style.backgroundPosition = "-" + 24 * v + "px 0px";
                    s.appendChild(z);
                    v++
                }
                return t
            }
            function c(v) {
                for (var t = 1; t <= k; t++) {
                    if (v !== t) {
                        var x = f.$$("a");
                        x.href = "javascript:;";
                        x.innerHTML = "[" + t + "]";
                        x.onclick = function(w) {
                            return function() {
                                o.removeChild(u);
                                var y = b(w);
                                o.insertBefore(y, n);
                                u = y;
                                n.innerHTML = "";
                                c(w);
                                return false
                            }
                        } (t);
                        n.appendChild(x)
                    } else n.appendChild(document.createTextNode("[" + t + "]"));
                    n.appendChild(document.createTextNode(" "))
                }
            }
            var list = 
                {'tusiji_face' : 
                    ['weixiao','pizui','se','fadai','deyi','liulei',
                     'haixiu','bizui','shuijiao','daku','gangga',
                     'danu','tiaopi','ciya','jingya','nanguo','ku',
                     'lenghan','zhuakuang','tu','touxiao','keai',
                     'baiyan','aoman','er','kun','jingkong',
                     'liuhan','haha','dabing','fendou','ma','wen',
                     'xu','yun','zhemo','shuai','kulou','da','zaijian',
                     'cahan','wabi','guzhang','qioudale','huaixiao',
                     'zuohengheng','youhengheng','haqian','bishi','weiqu',
                     'kuaikule','yinxian','qinqin','xia','kelian','caidao',
                     'xigua','pijiu','lanqiu','pingpang','kafei','fan',
                     'zhutou','hua','diaoxie','kiss','love','xinsui','dangao',
                     'shandian','zhadan','dao','qiu','chong','dabian','yueliang',
                     'taiyang','liwu','yongbao','qiang','ruo','woshou','shengli',
                     'peifu','gouyin','quantou','chajin','geili','no','ok','cheer',
                     'feiwen','tiao','fadou','dajiao','zhuanquan','ketou','huitou',
                     'tiaosheng','huishou','jidong','tiaowu',
                     'xianwen','zuotaiji','youtaiji'],
                 'tusiji':
                    ['xingxing','hunmi','yeah','aa','niubei','ding',
                     'douxiong','baibai','huihan','wuliao','lula',
                     'paizhuan','roulian','shengri','tanshou',
                     'xixishui','tanzuo','heng','shanshan','xuanzhuan',
                     'buxing','yumen','music','zhuaqiang','zhuangqiang',
                     'waitou','chuoyan','piaoguo','hupai','za','anshuang',
                     'shaolinsi','deyi','','naiping','woti','yaohuang',
                     'yunjue','longzi','zhendang']
                };
            var e = this,
            g = 8,
            h = 15,
            i = 0,
            j = g * h,
            
            k = Math.ceil(120 / j),
            l = Math.floor(h / 2),
            m = f.g[a],
            p = m.emoticonsPath + "face/";
            m = m.allowPreviewEmoticons === d ? true: m.allowPreviewEmoticons;
            f.util.selection(a);
            var o = f.$$("div");
            o.className = "ke-plugin-emoticons-wrapper";
            var q,
            r;
            if (m) {
                q = f.$$("div");
                q.className = "ke-plugin-emoticons-preview";
                q.style.right = 0;
                r = f.$$("img");
                r.className = "ke-reset";
                r.src = p + "aoman.gif";
                r.border = 0;
                q.appendChild(r);
                o.appendChild(q)
            }
            var u = b(1);
            o.appendChild(u);
            var n = f.$$("div");
            n.className = "ke-plugin-emoticons-page";
            o.appendChild(n);
            c(1);
            m = new f.menu({
                id: a,
                cmd: "emoticons"
            });
            m.append(o);
            m.show();
            this.menu = m
        },
        exec: function(a, b,list) {
            var c = f.g[a].emoticonsPath + "face/" + list['tusiji_face'][b] + ".gif";
            f.util.insertHtml(a, '<img src="' + c + '" kesrc="' + c + '" border="0" alt="" />');
            this.menu.hide();
            f.util.focus(a)
        }
    };
    f.plugin.flash = {
        init: function(a) {
            f.g[a].getHtmlHooks.push(function(b) {
                return b.replace(/<img[^>]*class="?ke-flash"?[^>]*>/ig, 
                function(c) {
                    var e = c.match(/style="[^"]*;?\s*width:\s*(\d+)/i) ? RegExp.$1: 0,
                    g = c.match(/style="[^"]*;?\s*height:\s*(\d+)/i) ? RegExp.$1: 0;
                    e = e || (c.match(/width="([^"]+)"/i) ? RegExp.$1: 0);
                    g = g || (c.match(/height="([^"]+)"/i) ? RegExp.$1: 0);
                    if (c.match(/kesrctag="([^"]+)"/i)) {
                        c = f.util.getAttrList(unescape(RegExp.$1));
                        c.width = e || c.width || 0;
                        c.height = g || c.height || 0;
                        c.kesrc = c.src;
                        return f.util.getMediaEmbed(c)
                    }
                })
            });
            f.g[a].setHtmlHooks.push(function(b) {
                return b.replace(/<embed[^>]*type="application\/x-shockwave-flash"[^>]*>(?:<\/embed>)?/ig, 
                function(c) {
                    var e = c.match(/\s+src="([^"]+)"/i) ? RegExp.$1: "";
                    if (c.match(/\s+kesrc="([^"]+)"/i)) e = RegExp.$1;
                    var g = c.match(/\s+width="([^"]+)"/i) ? RegExp.$1: 0,
                    h = c.match(/\s+height="([^"]+)"/i) ? RegExp.$1: 0;
                    c = f.util.getAttrList(c);
                    c.src = e;
                    c.width = g;
                    c.height = h;
                    return f.util.getMediaImage(a, "flash", c)
                })
            })
        },
        click: function(a) {
            f.util.selection(a);
            this.dialog = new f.dialog({
                id: a,
                cmd: "flash",
                file: "flash.html",
                width: 400,
                height: 140,
                loadingMode: true,
                title: f.lang.flash,
                yesButton: f.lang.yes,
                noButton: f.lang.no
            });
            this.dialog.show()
        },
        check: function(a, b, c, e) {
            a = f.util.getIframeDoc(this.dialog.iframe);
            if (!b.match(/^.{3,}$/)) {
                alert(f.lang.invalidUrl);
                f.$("url", a).focus();
                return false
            }
            if (!c.match(/^\d*$/)) {
                alert(f.lang.invalidWidth);
                f.$("width", a).focus();
                return false
            }
            if (!e.match(/^\d*$/)) {
                alert(f.lang.invalidHeight);
                f.$("height", a).focus();
                return false
            }
            return true
        },
        exec: function(a) {
            var b = f.util.getIframeDoc(this.dialog.iframe),
            c = f.$("url", b).value,
            e = f.$("width", b).value;
            b = f.$("height", b).value;
            if (!this.check(a, c, e, b)) return false;
            c = f.util.getMediaImage(a, "flash", {
                src: c,
                type: f.g[a].mediaTypes.flash,
                width: e,
                height: b,
                quality: "high"
            });
            f.util.insertHtml(a, c);
            this.dialog.hide();
            f.util.focus(a)
        }
    };

   f.plugin.image = {
        getSelectedNode: function(a) {
            a = f.g[a];
            var b = a.keRange.startNode,
            c = a.keRange.endNode;
            if (f.browser.WEBKIT || a.keSel.isControl) if (b.nodeType == 1) if (b.tagName.toLowerCase() == "img") if (b == c) if (!b.className.match(/^ke-\w+/i)) return b
        },
        init: function(a) {
            var b = this;
            a = f.g[a];
            a.contextmenuItems.push({
                text: f.lang.editImage,
                click: function(c, 
                e) {
                    f.util.select(c);
                    e.hide();
                    b.click(c)
                },
                cond: function(c) {
                    return b.getSelectedNode(c)
                },
                options: {
                    width: "150px",
                    iconHtml: '<span class="ke-common-icon ke-common-icon-url ke-icon-image"></span>'
                }
            });
            a.contextmenuItems.push({
                text: f.lang.deleteImage,
                click: function(c, e) {
                    f.util.select(c);
                    e.hide();
                    var g = b.getSelectedNode(c);
                    g.parentNode.removeChild(g);
                    f.util.execOnchangeHandler(c)
                },
                cond: function(c) {
                    return b.getSelectedNode(c)
                },
                options: {
                    width: "150px"
                }
            });
            a.contextmenuItems.push("-")
        },
        click: function(a) {
            f.util.selection(a);
            this.dialog = new f.dialog({
                id: a,
                cmd: "image",
                file: "image/image.html",
                width: 400,
                height: 220,
                loadingMode: true,
                title: f.lang.image,
                yesButton: f.lang.yes,
                noButton: f.lang.no
            });
            this.dialog.show()
        },
        check: function() {
            var a = f.util.getIframeDoc(this.dialog.iframe),
            b = f.$("type", a).value,
            c = f.$("imgWidth", a).value,
            e = f.$("imgHeight", a).value;
            f.$("imgTitle", a);
            b = b == 2 ? f.$("imgFile", a) : f.$("url", a);
            if (!b.value.match(/\.(jpg|jpeg|gif|bmp|png)(\s|\?|$)/i)) {
                alert(f.lang.invalidImg);
                b.focus();
                return false
            }
            if (!c.match(/^\d*$/)) {
                alert(f.lang.invalidWidth);
                f.$("imgWidth", a).focus();
                return false
            }
            if (!e.match(/^\d*$/)) {
                alert(f.lang.invalidHeight);
                f.$("imgHeight", a).focus();
                return false
            }
            return true
        },
        exec: function(a) {
            for (var b = this, c = f.util.getIframeDoc(this.dialog.iframe), e = f.$("type", c).value, g = f.$("imgWidth", c).value, h = f.$("imgHeight", c).value, i = f.$("imgTitle", c).value, j = c.getElementsByName("align"), k = "", l = 0, m = j.length; l < m; l++) if (j[l].checked) {
                k = j[l].value;
                break
            }
            if (!this.check(a)) return false;
            if (e == 2) {
                f.$("editorId", c).value = a;
                var p = f.$("uploadIframe", 
                c);
                f.util.showLoadingPage(a);
                var o = function() {
                    f.event.remove(p, "load", o);
                    f.util.hideLoadingPage(a);
                    var q = f.util.getIframeDoc(p),
                    r = "";
                    try {
                        r = f.util.parseJson(q.body.innerHTML)
                    } catch(u) {
                        alert(f.lang.invalidJson)
                    }
                    if (typeof r === "object" && "error" in r) if (r.error === 0) b.insert(a, r.url, i, g, h, 0, k);
                    else {
                        alert(r.message);
                        return false
                    }
                };
                f.event.add(p, "load", o);
                c.uploadForm.submit()
            } else {
                c = f.$("url", c).value;
                this.insert(a, c, i, g, h, 0, k)
            }
        },
        insert: function(a, b, c, e, g, h, i) {
            b = '<img src="' + b + '" kesrc="' + b + '" ';
            if (e > 0) b += 
            'width="' + e + '" ';
            if (g > 0) b += 'height="' + g + '" ';
            if (c) b += 'title="' + c + '" ';
            if (i) b += 'align="' + i + '" ';
            b += 'alt="' + c + '" ';
            b += 'border="' + h + '" />';
            f.util.insertHtml(a, b);
            this.dialog.hide();
            f.util.focus(a)
        }
    };
    f.plugin.link = {
        getSelectedNode: function(a) {
            return f.getCommonAncestor(f.g[a].keSel, "a")
        },
        init: function(a) {
            var b = this;
            f.g[a].contextmenuItems.push({
                text: f.lang.editLink,
                click: function(c, e) {
                    f.util.select(c);
                    e.hide();
                    b.click(c)
                },
                cond: function(c) {
                    return b.getSelectedNode(c)
                },
                options: {
                    width: "150px",
                    iconHtml: '<span class="ke-common-icon ke-common-icon-url ke-icon-link"></span>'
                }
            })
        },
        click: function(a) {
            f.util.selection(a);
            this.dialog = new f.dialog({
                id: a,
                cmd: "link",
                file: "link/link.html",
                width: 400,
                height: 90,
                loadingMode: true,
                title: f.lang.link,
                yesButton: f.lang.yes,
                noButton: f.lang.no
            });
            this.dialog.show()
        },
        exec: function(a) {
            var b = f.g[a];
            f.util.select(a);
            var c = b.keRange,
            e = c.startNode,
            g = c.endNode,
            h = b.iframeDoc,
            i = f.util.getIframeDoc(this.dialog.iframe),
            j = f.$("hyperLink", i).value,
            k = f.$("linkType", i).value;
            if (!j.match(/.+/) || j.match(/^\w+:\/\/\/?$/)) {
                alert(f.lang.invalidUrl);
                f.$("hyperLink", 
                i).focus();
                return false
            }
            for (i = c.getParentElement(); i;) {
                if (i.tagName.toLowerCase() == "a" || i.tagName.toLowerCase() == "body") break;
                i = i.parentNode
            }
            i = i.parentNode;
            g = f.browser.IE ? !!b.range.item: e.nodeType == 1 && e === g && e.nodeName.toLowerCase() != "br";
            var l = !g;
            g || (l = f.browser.IE ? b.range.text === "": b.range.toString() === "");
            if (l || f.util.isEmpty(a)) {
                b = '<a href="' + j + '"';
                if (k) b += ' target="' + k + '"';
                b += ">" + j + "</a>";
                f.util.insertHtml(a, b)
            } else {
                h.execCommand("createlink", false, "__ke_temp_url__");
                i = i.getElementsByTagName("a");
                l = 0;
                for (var m = i.length; l < m; l++) if (i[l].href.match(/\/?__ke_temp_url__$/)) {
                    i[l].href = j;
                    i[l].setAttribute("kesrc", j);
                    if (k) i[l].target = k;
                    else i[l].removeAttribute("target")
                }
                if (f.browser.WEBKIT && g && e.tagName.toLowerCase() == "img") {
                    g = e.parentNode;
                    if (g.tagName.toLowerCase() != "a") {
                        h = f.$$("a", h);
                        g.insertBefore(h, e);
                        h.appendChild(e);
                        g = h
                    }
                    g.href = j;
                    g.setAttribute("kesrc", j);
                    if (k) g.target = k;
                    else g.removeAttribute("target");
                    b.keSel.addRange(c)
                }
            }
            f.util.execOnchangeHandler(a);
            this.dialog.hide();
            f.util.focus(a)
        }
    };
    f.plugin.unlink = {
        init: function(a) {
            var b = this;
            f.g[a].contextmenuItems.push({
                text: f.lang.deleteLink,
                click: function(c, e) {
                    f.util.select(c);
                    e.hide();
                    b.click(c)
                },
                cond: function(c) {
                    return f.plugin.link.getSelectedNode(c)
                },
                options: {
                    width: "150px",
                    iconHtml: '<span class="ke-common-icon ke-common-icon-url ke-icon-unlink"></span>'
                }
            });
            f.g[a].contextmenuItems.push("-")
        },
        click: function(a) {
            var b = f.g[a],
            c = b.iframeDoc;
            f.util.selection(a);
            var e = b.keRange,
            g = e.startNode;
            e = e.endNode;
            e = g.nodeType == 1 && g === e;
            var h = !e;
            e || (h = 
            f.browser.IE ? b.range.text === "": b.range.toString() === "");
            if (h) {
                h = f.plugin.link.getSelectedNode(a);
                if (!h) return;
                e = b.keRange;
                e.selectTextNode(h);
                b.keSel.addRange(e);
                f.util.select(a);
                c.execCommand("unlink", false, null);
                if (f.browser.WEBKIT && g.tagName.toLowerCase() == "img") {
                    c = g.parentNode;
                    if (c.tagName.toLowerCase() == "a") {
                        f.util.removeParent(c);
                        b.keSel.addRange(e)
                    }
                }
            } else c.execCommand("unlink", false, null);
            f.util.execOnchangeHandler(a);
            f.toolbar.updateState(a);
            f.util.focus(a)
        }
    };
    f.plugin.media = {
        init: function(a) {
            var b = 
            {};
            f.each(f.g[a].mediaTypes, 
            function(c, e) {
                b[e] = c
            });
            f.g[a].getHtmlHooks.push(function(c) {
                return c.replace(/<img[^>]*class="?ke-\w+"?[^>]*>/ig, 
                function(e) {
                    var g = e.match(/style="[^"]*;?\s*width:\s*(\d+)/i) ? RegExp.$1: 0,
                    h = e.match(/style="[^"]*;?\s*height:\s*(\d+)/i) ? RegExp.$1: 0;
                    g = g || (e.match(/width="([^"]+)"/i) ? RegExp.$1: 0);
                    h = h || (e.match(/height="([^"]+)"/i) ? RegExp.$1: 0);
                    if (e.match(/\s+kesrctag="([^"]+)"/i)) {
                        e = f.util.getAttrList(unescape(RegExp.$1));
                        e.width = g || e.width || 0;
                        e.height = h || e.height || 0;
                        e.kesrc = 
                        e.src;
                        return f.util.getMediaEmbed(e)
                    }
                })
            });
            f.g[a].setHtmlHooks.push(function(c) {
                return c.replace(/<embed[^>]*type="([^"]+)"[^>]*>(?:<\/embed>)?/ig, 
                function(e, g) {
                    if (typeof b[g] == "undefined") return e;
                    var h = e.match(/\s+src="([^"]+)"/i) ? RegExp.$1: "";
                    if (e.match(/\s+kesrc="([^"]+)"/i)) h = RegExp.$1;
                    var i = e.match(/\s+width="([^"]+)"/i) ? RegExp.$1: 0,
                    j = e.match(/\s+height="([^"]+)"/i) ? RegExp.$1: 0,
                    k = f.util.getAttrList(e);
                    k.src = h;
                    k.width = i;
                    k.height = j;
                    return f.util.getMediaImage(a, "", k)
                })
            })
        },
        click: function(a) {
            f.util.selection(a);
            this.dialog = new f.dialog({
                id: a,
                cmd: "media",
                file: "media.html",
                width: 400,
                height: 170,
                loadingMode: true,
                title: f.lang.media,
                yesButton: f.lang.yes,
                noButton: f.lang.no
            });
            this.dialog.show()
        },
        check: function(a, b, c, e) {
            a = f.util.getIframeDoc(this.dialog.iframe);
            if (!b.match(/^.{3,}\.(swf|flv|mp3|wav|wma|wmv|mid|avi|mpg|mpeg|asf|rm|rmvb)(\?|$)/i)) {
                alert(f.lang.invalidMedia);
                f.$("url", a).focus();
                return false
            }
            if (!c.match(/^\d*$/)) {
                alert(f.lang.invalidWidth);
                f.$("width", a).focus();
                return false
            }
            if (!e.match(/^\d*$/)) {
                alert(f.lang.invalidHeight);
                f.$("height", a).focus();
                return false
            }
            return true
        },
        exec: function(a) {
            var b = f.util.getIframeDoc(this.dialog.iframe),
            c = f.$("url", b).value,
            e = f.$("width", b).value,
            g = f.$("height", b).value;
            if (!this.check(a, c, e, g)) return false;
            b = f.$("autostart", b).checked ? "true": "false";
            c = f.util.getMediaImage(a, "", {
                src: c,
                type: f.g[a].mediaTypes[f.util.getMediaType(c)],
                width: e,
                height: g,
                autostart: b,
                loop: "true"
            });
            f.util.insertHtml(a, c);
            this.dialog.hide();
            f.util.focus(a)
        }
    };
    f.plugin.advtable = {
        getSelectedTable: function(a) {
            return f.getCommonAncestor(f.g[a].keSel, 
            "table")
        },
        getSelectedRow: function(a) {
            return f.getCommonAncestor(f.g[a].keSel, "tr")
        },
        getSelectedCell: function(a) {
            return f.getCommonAncestor(f.g[a].keSel, "td")
        },
        tableprop: function(a) {
            this.click(a)
        },
        tableinsert: function(a) {
            this.click(a, "insert")
        },
        tabledelete: function(a) {
            a = this.getSelectedTable(a);
            a.parentNode.removeChild(a)
        },
        tablecolinsert: function(a, b) {
            for (var c = this.getSelectedTable(a), e = this.getSelectedCell(a).cellIndex + b, g = 0, h = c.rows.length; g < h; g++) c.rows[g].insertCell(e).innerHTML = "&nbsp;"
        },
        tablecolinsertleft: function(a) {
            this.tablecolinsert(a, 
            0)
        },
        tablecolinsertright: function(a) {
            this.tablecolinsert(a, 1)
        },
        tablerowinsert: function(a, b) {
            var c = this.getSelectedTable(a),
            e = this.getSelectedRow(a);
            c = c.insertRow(e.rowIndex + b);
            var g = 0;
            for (e = e.cells.length; g < e; g++) c.insertCell(g).innerHTML = "&nbsp;"
        },
        tablerowinsertabove: function(a) {
            this.tablerowinsert(a, 0)
        },
        tablerowinsertbelow: function(a) {
            this.tablerowinsert(a, 1)
        },
        tablecoldelete: function(a) {
            var b = this.getSelectedTable(a);
            a = this.getSelectedCell(a);
            for (var c = 0, e = b.rows.length; c < e; c++) b.rows[c].deleteCell(a.cellIndex)
        },
        tablerowdelete: function(a) {
            var b = this.getSelectedTable(a);
            a = this.getSelectedRow(a);
            b.deleteRow(a.rowIndex)
        },
        init: function(a) {
            for (var b = this, c = "prop,colinsertleft,colinsertright,rowinsertabove,rowinsertbelow,coldelete,rowdelete,insert,delete".split(","), e = 0, g = c.length; e < g; e++) {
                var h = "table" + c[e];
                f.g[a].contextmenuItems.push({
                    text: f.lang[h],
                    click: function(i) {
                        return function(j, k) {
                            f.util.select(j);
                            k.hide();
                            b[i] !== d && b[i](j);
                            /prop/.test(i) || f.util.execOnchangeHandler(j)
                        }
                    } (h),
                    cond: function(i) {
                        return f.util.inArray(i, 
                        ["tableprop", "tabledelete"]) ? 
                        function(j) {
                            return b.getSelectedTable(j)
                        }: function(j) {
                            return b.getSelectedCell(j)
                        }
                    } (h),
                    options: {
                        width: "170px",
                        iconHtml: '<span class="ke-common-icon ke-common-icon-url ke-icon-' + h + '"></span>'
                    }
                })
            }
            f.g[a].contextmenuItems.push("-");
            f.g[a].setHtmlHooks.push(function(i) {
                return i.replace(/<table([^>]*)>/ig, 
                function(j, k) {
                    if (k.match(/\s+border=["']?(\d*)["']?/ig)) {
                        var l = RegExp.$1;
                        return k.indexOf("ke-zeroborder") < 0 && (l === "" || l === "0") ? f.addClass(j, "ke-zeroborder") : j
                    } else return f.addClass(j, 
                    "ke-zeroborder")
                })
            })
        },
        click: function(a, b) {
            b = b || "default";
            f.util.selection(a);
            this.dialog = new f.dialog({
                id: a,
                cmd: "advtable",
                file: "advtable/advtable.html?mode=" + b,
                width: 420,
                height: 220,
                loadingMode: true,
                title: f.lang.advtable,
                yesButton: f.lang.yes,
                noButton: f.lang.no
            });
            this.dialog.show()
        },
        exec: function(a) {
            var b = f.util.getIframeDoc(this.dialog.iframe),
            c = f.$("mode", b),
            e = f.$("rows", b),
            g = f.$("cols", b),
            h = f.$("width", b),
            i = f.$("height", b),
            j = f.$("widthType", b),
            k = f.$("heightType", b),
            l = f.$("padding", b),
            m = f.$("spacing", 
            b),
            p = f.$("align", b),
            o = f.$("border", b),
            q = f.$("borderColor", b),
            r = f.$("backgroundColor", b);
            b = e.value;
            var u = g.value,
            n = h.value,
            v = i.value;
            j = j.value;
            var t = k.value;
            k = l.value;
            var x = m.value;
            p = p.value;
            var w = o.value;
            q = q.innerHTML;
            r = r.innerHTML;
            if (b == "" || b == 0 || !b.match(/^\d*$/)) {
                alert(f.lang.invalidRows);
                e.focus();
                return false
            }
            if (u == "" || u == 0 || !u.match(/^\d*$/)) {
                alert(f.lang.invalidCols);
                g.focus();
                return false
            }
            if (!n.match(/^\d*$/)) {
                alert(f.lang.invalidWidth);
                h.focus();
                return false
            }
            if (!v.match(/^\d*$/)) {
                alert(f.lang.invalidHeight);
                i.focus();
                return false
            }
            if (!k.match(/^\d*$/)) {
                alert(f.lang.invalidPadding);
                l.focus();
                return false
            }
            if (!x.match(/^\d*$/)) {
                alert(f.lang.invalidSpacing);
                m.focus();
                return false
            }
            if (!w.match(/^\d*$/)) {
                alert(f.lang.invalidBorder);
                o.focus();
                return false
            }
            if (c.value === "update") {
                b = this.getSelectedTable(a);
                if (n !== "") b.style.width = n + j;
                else if (b.style.width) b.style.width = "";
                b.width !== d && b.removeAttribute("width");
                if (v !== "") b.style.height = v + t;
                else if (b.style.height) b.style.height = "";
                b.height !== d && b.removeAttribute("height");
                if (r !== "") b.style.backgroundColor = r;
                else if (b.style.backgroundColor) b.style.backgroundColor = "";
                b.bgColor !== d && b.removeAttribute("bgColor");
                if (k !== "") b.cellPadding = k;
                else b.removeAttribute("cellPadding");
                if (x !== "") b.cellSpacing = x;
                else b.removeAttribute("cellSpacing");
                if (p !== "") b.align = p;
                else b.removeAttribute("align");
                w === "" || w === "0" ? f.addClass(b, "ke-zeroborder") : f.removeClass(b, "ke-zeroborder");
                w !== "" ? b.setAttribute("border", w) : b.removeAttribute("border");
                q !== "" ? b.setAttribute("borderColor", q) : b.removeAttribute("borderColor");
                f.util.execOnchangeHandler(a)
            } else {
                c = "";
                if (n !== "") c += "width:" + n + j + ";";
                if (v !== "") c += "height:" + v + t + ";";
                if (r !== "") c += "background-color:" + r + ";";
                n = "<table";
                if (c !== "") n += ' style="' + c + '"';
                if (k !== "") n += ' cellpadding="' + k + '"';
                if (x !== "") n += ' cellspacing="' + x + '"';
                if (p !== "") n += ' align="' + p + '"';
                if (w === "" || w === "0") n += ' class="ke-zeroborder"';
                if (w !== "") n += ' border="' + w + '"';
                if (q !== "") n += ' bordercolor="' + q + '"';
                n += ">";
                for (v = 0; v < b; v++) {
                    n += "<tr>";
                    for (c = 0; c < u; c++) n += "<td>&nbsp;</td>";
                    n += "</tr>"
                }
                n += "</table>";
                f.util.insertHtml(a, 
                n)
            }
            this.dialog.hide();
            f.util.focus(a)
        }
    }
})(KindEditor);