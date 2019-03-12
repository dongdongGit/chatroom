var Scroll = {};
(function (win, doc, $) {
    function CusScrollBar(options) {
        this._init(options);
    }
    $.extend(CusScrollBar.prototype, {
        _init: function (options) {
            var self = this;
            self.options = {
                scrollDir: "y", //滚动的方向
                sizeSelector: "", //滚动内容区以及滚动条的容器的选择器
                contSelector: "", //滚动内容区的选择器
                barSelector: "", //滚动条选择器
                sliderSelector: "", //滚动滑块选择器
                btnUpSelector: "", //向上按钮选则器
                btnDownSelector: "", //向下按钮选择器
                txtSelector: "", //文本框输入选择器
                btnSendSelector: "", //发送按钮选择器
                formSelector: "", //表单选择器
                errorMsgSelector: "", //错误信息提示选择器
                wheelStep: 10, //滚轮步长
            }
            $.extend(true, self.options, options || {});
            //			console.log(self.options.contSelector);
            self._initDomEvent();
            //			console.profile();
            return self;
        },

		/**
		 * 初始化DOM引用
		 * @method _initDomEvent
		 * @return {CusScrollBar}
		 */

        _initDomEvent: function () {
            var options = this.options;
            this.$size = $(options.sizeSelector);
            this.$cont = $(options.contSelector); //滚动内容区对象，必填项
            this.$slider = $(options.sliderSelector); //滚动条滑块对象，必填项
            this.$bar = options.barSelector ? $(options.barSelector) : self.$slider.parent(); //滚动条对象，如果有值就赋值，没有就找到滚动滑块的父级
            this.$up = $(options.btnUpSelector);
            this.$down = $(options.btnDownSelector);
            this.$txt = $(options.txtSelector);
            this.$send = $(options.btnSendSelector);
            this.$form = $(options.formSelector);
            this.$error = $(options.errorMsgSelector);
            this.$doc = $(doc); //获取文档对象
            this._initScrollBarDragEvent()
                ._initSliderDragEvent()
                ._initContDragEvent()
                ._bindTxtHeightEvent()
                ._bindTxtSend()
                ._bindContScroll()
                ._bindMousewheel()
                ._bindBtnUp()
                ._bindBtnDown();
        },
		/**
		 * 初始化点击滚动条滚动$cont的高度 
		 * @return {Object} [this]
		 */
        _initScrollBarDragEvent: function () {
            var self = this,
                bar = self.$bar,
                cont = self.$cont,
                barElement = bar[0],
                contElement = cont[0],
                scrollClickPosition = null,
                scrollMousedownPosition = null;
            if (barElement) {
                var dragStartPagePosition,
                    dragStartEventPosition;
                bar.on("mousedown", function (e) {
                    e.preventDefault();
                    dragStartPagePosition = e.pageY;
                    //判断event是否是scroll-bar,不是则返回
                    if (e.target.className != "scroll-bar") return;
                    //通过鼠标相对位置减去向上按钮的高度从而获得在滚动条的相对位置,相减小于就赋值0,大于0赋值相减结果
                    e.offsetY - self.$up.height() > 0 ? dragStartEventPosition = e.offsetY - self.$up.height() : dragStartEventPosition = 0;
                    var sub = dragStartEventPosition - self.getSliderPosition();
                    startScroll(sub, dragStartEventPosition);
                    scrollClickPosition = window.setTimeout(function () {
                        scrollMousedownPosition = window.setInterval(function () {
                            startScroll(sub, dragStartEventPosition);
                        }, 50);
                    }, 500);
                }).on("mouseup", function () {
                    endScroll();
                });
            }

            function startScroll(direction, dragStartEventPosition) {
                if (direction > 0) {
                    if (self.getSliderPosition() < dragStartEventPosition) {
                        self.scrollMode('down', 0.1);
                    }
                } else if (direction < 0) {
                    if (self.getSliderPosition() + self.$slider.height() > dragStartEventPosition) {
                        self.scrollMode('up', 0.1);
                    }
                }
            }

            function endScroll() {
                scrollClickPosition = window.clearTimeout(scrollClickPosition);
                scrollMousedonwPosition = window.clearInterval(scrollMousedownPosition);
            }
            return self;
        },
		/**
		 * 初始化滑块拖动功能
		 * @return {[Object]}[this]
		 * 
		 */

        _initSliderDragEvent: function () {
            var self = this,
                slider = self.$slider,
                sliderElement = slider[0];
            if (sliderElement) {
                var doc = self.$doc,
                    dragStartPagePosition,
                    dragStartPageScrollPosition,
                    dragContBarRate;
                slider.on("mousedown", function (e) {
                    e.preventDefault();
                    dragStartPagePosition = e.pageY; //记录鼠标开始的Y轴的坐标
                    dragStartPageScrollPosition = self.$cont[0].scrollTop; //记录$cont[0]的scrollTop的位置
                    dragContBarRate = self.getMaxScrollPosition() / self.getMaxSliderPosition();
                    doc.on("mousemove.scroll", mousemoveHandler).on("mouseup.scroll", function (e) {
                        doc.off(".scroll");
                    });
                });
            }
            //滑块移动的位置
            function mousemoveHandler(e) {
                e.preventDefault();
                if (dragStartPageScrollPosition == null) return;
                self.scrollTo(dragStartPageScrollPosition + (e.pageY - dragStartPagePosition) * dragContBarRate);
            }
            return self;
        },
        _initContDragEvent: function () {
            var self = this,
                cont = self.$cont;
            get("init");
            setInterval(function () {
                get();
            }, 3000);

            function get(msg) {
                if (msg == undefined) msg = '';
                $.ajax({
                    url: window.location.href + "/chatRoom/Get/get",
                    type: "get",
                    dataType: "json",
                    data: {
                        msg: msg
                    },
                    success: function (result) {
                        var length = result.msg.length;
                        if (result.status == '2') {
                            if (length == undefined) {
                                addInitOneMsg(result);
                            } else {
                                for (var i = length - 1; i >= 0; i--) {
                                    addInitMsg(result, i);
                                }
                            }
                        } else if (result.status == '1') {
                            if (length == undefined) {
                                addOneMsg(result);
                            } else {
                                for (var i = length - 1; i >= 0; i--) {
                                    addMsg(result, i);
                                }
                            }
                        }
                        if (result.status == '0') {
                            self.$cont.append(result.msg);
                        }
                        self.scrollTo(self.getMaxScrollPosition());
                    }
                });
            }

            function addInitMsg(msgArray, i) {
                if (msgArray.msg[i]['flag'] === 1) {
                    cont.append('<div class="media"><div class="media-body"><div class="media-heading text-right"><span class="h4">' +
                        msgArray.msg[i]['username'] + '</span><span class="time">' +
                        msgArray.msg[i]['pubTime'] + '</span></div><div class="bubble rightPush pull-right"><div class="bubbleStyle bubble-right"></div><p class="label label-success send-right">' +
                        msgArray.msg[i]['content'] + '</p></div></div><div class="media-right"><a href="#"><img class="media-object" src="/public/chatRoom/img/head03.gif"></a></div></div>');
                } else {
                    cont.append('<div class="media"><div class="media-left"><a href="#"><img class="media-object" src="/public/chatRoom/img/head01.jpg" alt="..."></a></div><div class="media-body"><div class="media-heading"><span class="h4">' +
                        msgArray.msg[i]['username'] + '</span><span class="time">' +
                        msgArray.msg[i]['pubTime'] + '</span></div><div class="bubble leftPush"><div class="bubbleStyle bubble-left"></div><p class="label label-success send-left">' +
                        msgArray.msg[i]['content'] + '</p></div></div></div>');
                }
            }

            function addInitOneMsg(msgArray) {
                if (msgArray.msg['flag'] === 1) {
                    cont.append('<div class="media"><div class="media-body"><div class="media-heading text-right"><span class="h4">' +
                        msgArray.msg['username'] + '</span><span class="time">' +
                        msgArray.msg['pubTime'] + '</span></div><div class="bubble rightPush pull-right"><div class="bubbleStyle bubble-right"></div><p class="label label-success send-right">' +
                        msgArray.msg['content'] + '</p></div></div><div class="media-right"><a href="#"><img class="media-object" src="/public/chatRoom/img/head03.gif"></a></div></div>');
                } else {
                    cont.append('<div class="media"><div class="media-left"><a href="#"><img class="media-object" src="/public/chatRoom/img/head01.jpg" alt="..."></a></div><div class="media-body"><div class="media-heading"><span class="h4">' +
                        msgArray.msg['username'] + '</span><span class="time">' +
                        msgArray.msg['pubTime'] + '</span></div><div class="bubble leftPush"><div class="bubbleStyle bubble-left"></div><p class="label label-success send-left">' +
                        msgArray.msg['content'] + '</p></div></div></div>');
                }
            }

            function addMsg(msgArray, i) {
                cont.append('<div class="media"><div class="media-left"><a href="#"><img class="media-object" src="/public/chatRoom/img/head01.jpg" alt="..."></a></div><div class="media-body"><div class="media-heading"><span class="h4">' +
                    msgArray.msg[i]['username'] + '</span><span class="time">' +
                    msgArray.msg[i]['pubTime'] + '</span></div><div class="bubble leftPush"><div class="bubbleStyle bubble-left"></div><p class="label label-success send-left">' +
                    msgArray.msg[i]['content'] + '</p></div></div></div>');
            }

            function addOneMsg(msgArray) {
                cont.append('<div class="media"><div class="media-left"><a href="#"><img class="media-object" src="/public/chatRoom/img/head01.jpg" alt="..."></a></div><div class="media-body"><div class="media-heading"><span class="h4">' +
                    msgArray.msg['username'] + '</span><span class="time">' +
                    msgArray.msg['pubTime'] + '</span></div><div class="bubble leftPush"><div class="bubbleStyle bubble-left"></div><p class="label label-success send-left">' +
                    msgArray.msg['content'] + '</p></div></div></div>');
            }
            return self;
        },

		/**
		 * 绑定文本框高度变化
		 */
        _bindTxtHeightEvent: function () {
            var self = this,
                txt = self.$txt,
                txtElement = txt[0];
            if (txtElement) {
                var btnSendHeight,
                    contentHeight,
                    barHeight;
                txt.on("resize", function () {
                    btnSendHeight = txt.height();
                    contentHeight = self.$size.height() - txt.outerHeight();
                    barHeight = self.$size.outerHeight() - txt.outerHeight();
                    self.setBtnSendHeight(btnSendHeight);
                    self.setBarHeight(barHeight);
                    self.setContentHeight(contentHeight);
                    self.scrollTo(self.getMaxScrollPosition());
                });
            }
            return self;
        },
		/**
		 * 绑定内容滚动高度
		 * @return {[Object]}[this]
		 */
        _bindContScroll: function () {
            var self = this;
            self.$cont.on("scroll", function () {
                var sliderElement = self.$slider && self.$slider[0];
                if (sliderElement) {
                    //修正高度为16px的$up,以免滑块覆盖住按钮块
                    sliderElement.style.top = self.getSliderPosition() + self.$up.height() + "px";
                }
            });
            return self;
        },
		/**
		 * 绑定滚轮滚动，来使内容以及滑块滚动
		 * @return {[Object]}[this]
		 */
        _bindMousewheel: function () {
            var self = this;
            //parent()是为了让鼠标悬停在滚动条上也能触发鼠标滚轮事件  因为html结构的
            self.$cont.parent().on("mousewheel DOMMouseScroll", function (e) {
                e.preventDefault();
                var oEvent = e.originalEvent, //指向原生事件
                    //判断原生事件中有没有wheelDelta属性，有就赋值，没有就赋值oEvent.detail属性
                    wheelRange = oEvent.wheelDelta ? -oEvent.wheelDelta / 120 : (oEvent.detail || 0) / 3;
                //				self.scrollTo(self.$cont.scrollTop() + wheelRange * self.getMaxScrollPosition().toFixed(2) * 0.1);
                self.scrollMode('', wheelRange * 0.1);
            });
            return self;
        },
		/**
		 * 绑定向上按钮事件
		 * @return {[Object]}[this]
		 */
        _bindBtnUp: function () {
            var self = this,
                up = self.$up,
                cont = self.$cont,
                scrollFlag = null;
            up.on("mousedown", function () {
                scrollFlag = window.setInterval(function () {
                    if (self.getSliderPosition() < self.$up.height()) scrollFlag = window.clearInterval(scrollFlag);
                    startScroll();
                }, 50);
            }).on("mouseup", function () {
                endScroll();
            });

            function startScroll() {
                self.scrollMode('up', 0.01);
            }

            function endScroll() {
                scrollFlag = window.clearInterval(scrollFlag);
            }
            return self;
        },
		/**
		 * 绑定向下按钮事件
		 * @return {[Object]}[this]
		 */
        _bindBtnDown: function () {
            var self = this,
                down = self.$down,
                cont = self.$cont,
                scrollFlag = null;
            down.on("mousedown.btnDownScroll", function () {
                scrollFlag = window.setInterval(function () {
                    if (self.getSliderPosition() == self.getMaxSliderPosition()) scrollFlag = window.clearInterval(scrollFlag);
                    startScroll();
                }, 50);
            }).on("mouseup.btnDownScroll", function () {
                endScroll();
                down.off("btnDownScroll");
            });

            function startScroll() {
                self.scrollMode('down', 0.01);
            }

            function endScroll() {
                scrollFlag = window.clearInterval(scrollFlag);
            }
            return self;
        },
        _bindTxtSend: function () {
            var self = this,
                form = self.$form,
                send = self.$send,
                txtBox = $(".txt-box"),
                errorMsg = $("#warningMsg"),
                time = new Date(),
                i = 0;
            time = time.getTime();
            flag = false;
            send.on("mousedown.send", function (e) {
                var subTime = new Date().getTime();
                i++;
                e.preventDefault();
                var msg = "请填写信息再发送",
                    sendData = txtBox.html();
                if (sendData === "" || sendData === "<br>") {
                    errorMessage(msg);
                    return;
                } else if (sendData.length > 260) {
                    errorMessage("字数不能超过260个");
                    return;
                } else if (i > 10) {
                    subTime = subTime - time;
                    if (subTime > 10000) {
                        i = 0;
                        time = new Date().getTime();
                    } else {
                        errorMessage("别发送这么快！");
                        return;
                    }
                }
                //				else if (){
                //					
                //				}
                if (flag) return;
                flag = true;
                $.ajax({
                    url: window.location.href + "/chatRoom/Send/send",
                    type: "post",
                    dataType: "json",
                    timeout: 8000,
                    data: {
                        comment: sendData
                    },
                    success: function (result) {
                        var self = this;
                        flag = false;
                        if (result.status == '0') {
                            $.each(result, function () {
                                errorMessage(result.errors);
                            });
                        } else if (result.status == '1') {
                            errorMessage(result.msg);
                        } else if (result.status == '2') {
                            addMsgEvent(result.msg);
                            txtBox.html("");
                        }
                    },
                    error: function () {
                        console.log("服务器错误");
                    }
                });
                return self;
            }).on("mouseup.send", function () {
                send.off("send");
                //发送数据成功将滑块定位最底部
                self.$cont.on("DOMNodeInserted", function (e) {
                    self.scrollTo(self.getMaxScrollPosition());
                });
            });
            //显示错误信息
            function errorMessage(msg) {
                errorMsg.text(msg);
                self.$error.css({
                    "margin-left": -self.$error.width() / 2
                }).fadeIn();
                setTimeout(function () {
                    self.$error.fadeOut();
                }, 3000);
                txtBox.focus();
            }
            //将用户发送的聊天添加到html代码块，并在scroll-content输出
            function addMsgEvent(msgArray) {
                var self = this,
                    last = $(".scroll-content")[0],
                    newLast = $(last);
                newLast.append('<div class="media"><div class="media-body"><div class="media-heading text-right"><span class="h4">' +
                    msgArray['username'] + '</span><span class="time">' +
                    msgArray['pubTime'] + '</span></div><div class="bubble rightPush pull-right"><div class="bubbleStyle bubble-right"></div><p class="label label-success send-right">' +
                    msgArray['content'] + '</p></div></div><div class="media-right"><a href="#"><img class="media-object" src="/public/chatRoom/img/head03.gif"></a></div></div>');
            }
            return self;
        },
        //计算滑块当前位置
        getSliderPosition: function () {
            var self = this,
                maxSliderPosition = self.getMaxSliderPosition();
            return Math.round(Math.min(maxSliderPosition, maxSliderPosition * self.$cont[0].scrollTop / self.getMaxScrollPosition()));
        },
        //内容可滚动高度
        getMaxScrollPosition: function () {
            var self = this;
            return Math.max(self.$cont.height(), self.$cont[0].scrollHeight) - self.$cont.height();
        },
        //滑块可移动距离
        getMaxSliderPosition: function () {
            var self = this;
            return self.$bar.height() - self.$slider.height() - self.$up.height() - self.$down.height();
        },
        //内容滚动高度
        scrollTo: function (positionValue) {
            var self = this;
            self.$cont.scrollTop(positionValue);
        },
        scrollMode: function (direction, range) {
            var self = this;
            if (range == "") {
                range = 0.1;
            }
            if (direction == 'up') {
                self.scrollTo(self.$cont.scrollTop() - self.getMaxScrollPosition().toFixed(2) * range);
            } else if (direction == 'down') {
                self.scrollTo(self.$cont.scrollTop() + self.getMaxScrollPosition().toFixed(2) * range);
            } else if (direction == '') {
                self.scrollTo(self.$cont.scrollTop() + self.getMaxScrollPosition().toFixed(2) * range);
            }
        },
        //设置内容高度
        setContentHeight: function (height) {
            var self = this;
            return self.$cont.height(height);
        },
        //设置发送按钮高度
        setBtnSendHeight: function (height) {
            var self = this;
            return self.$send.height(height);
        },
        //设置滚到条高度
        setBarHeight: function (height) {
            var self = this;
            return self.$bar.height(height);
        }

    });
    //	CusScrollBar.prototype._init = function() {
    //		console.log("test1");
    //	}
    Scroll.CusScrollBar = CusScrollBar;
})(window, document, jQuery);
$(function () {
    var scrollObj = new Scroll.CusScrollBar({
        sizeSelector: ".viewSize",
        contSelector: ".scroll-content",
        barSelector: ".scroll-bar",
        sliderSelector: ".scroll-slider",
        btnUpSelector: ".scroll-btnUp",
        btnDownSelector: ".scroll-btnDown",
        txtSelector: ".txt-box",
        btnSendSelector: "#sendTxt",
        formSelector: "#chatRoomForm",
        errorMsgSelector: ".error"
    });
});
