/**
 * NOTE: 核心框架
 * author: FZR
 * desc: 大鹏一日同风起, 扶摇直上九万里
 */
layui.use(["form", "element"], function () {
    var $ = layui.jquery;
    var element = layui.element;
    var $body = $("body");
    var APP_BODY = "#LAY_app_body";
    var TABS_HEADER = "#LAY_app_tabsheader>li";
    var FILTER_TAB_TABS = "lay-layout-tabs";
    var TABS_BODY = 'lay-tabsbody-item'
    var SHOW = 'layui-show'
    //同步路由

    var admin = {
        tabsPage: {},
        //关闭当前 pageTabs
        closeThisTabs: function () {
            if (!admin.tabsPage.index) return;
            $(TABS_HEADER).eq(admin.tabsPage.index).find('.layui-tab-close').trigger('click');
            $(TABS_HEADER).eq(admin.tabsPage.index).trigger('click');
        },
        //获取页面标签主体元素
        tabsBody: function (index) {
            return $(APP_BODY).find('.' + TABS_BODY).eq(index || 0);
        }
        //resize事件管理
        , resize: function (fn) {
            var router = layui.router()
                , key = router.path.join('-');

            if (admin.resizeFn[key]) {
                $win.off('resize', admin.resizeFn[key]);
                delete admin.resizeFn[key];
            }

            if (fn === 'off') return; //如果是清除 resize 事件，则终止往下执行

            fn(), admin.resizeFn[key] = fn;
            $win.on('resize', admin.resizeFn[key]);
        }
        , resizeFn: {}
        , runResize: function () {
            var router = layui.router()
                , key = router.path.join('-');
            admin.resizeFn[key] && admin.resizeFn[key]();
        }
        , delResize: function () {
            this.resize('off');
        }
    };
    /**
     * 操作事件
     */
    let events = {
        // 左右滚动页面标签
        rollPage: function (type, index) {
            var tabsHeader = $('#LAY_app_tabsheader')
                , liItem = tabsHeader.children('li')
                , scrollWidth = tabsHeader.prop('scrollWidth')
                , outerWidth = tabsHeader.outerWidth()
                , tabsLeft = parseFloat(tabsHeader.css('left'));

            //右左往右
            if (type === 'left') {
                if (!tabsLeft && tabsLeft <= 0) return;

                //当前的left减去可视宽度，用于与上一轮的页标比较
                var prefLeft = -tabsLeft - outerWidth;

                liItem.each(function (index, item) {
                    var li = $(item)
                        , left = li.position().left;

                    if (left >= prefLeft) {
                        tabsHeader.css('left', -left);
                        return false;
                    }
                });
            } else if (type === 'auto') { //自动滚动
                (function () {
                    var thisLi = liItem.eq(index), thisLeft;

                    if (!thisLi[0]) return;
                    thisLeft = thisLi.position().left;

                    //当目标标签在可视区域左侧时
                    if (thisLeft < -tabsLeft) {
                        return tabsHeader.css('left', -thisLeft);
                    }

                    //当目标标签在可视区域右侧时
                    if (thisLeft + thisLi.outerWidth() >= outerWidth - tabsLeft) {
                        var subLeft = thisLeft + thisLi.outerWidth() - (outerWidth - tabsLeft);
                        liItem.each(function (i, item) {
                            var li = $(item)
                                , left = li.position().left;

                            //从当前可视区域的最左第二个节点遍历，如果减去最左节点的差 > 目标在右侧不可见的宽度，则将该节点放置可视区域最左
                            if (left + tabsLeft > 0) {
                                if (left - tabsLeft > subLeft) {
                                    tabsHeader.css('left', -left);
                                    return false;
                                }
                            }
                        });
                    }
                }());
            } else {
                //默认向左滚动
                liItem.each(function (i, item) {
                    var li = $(item)
                        , left = li.position().left;

                    if (left + li.outerWidth() >= outerWidth - tabsLeft) {
                        tabsHeader.css('left', -left);
                        return false;
                    }
                });
            }
        }
        // 向右滚动页面标签
        , leftPage: function () {
            events.rollPage('left');
        }
        // 向左滚动页面标签
        , rightPage: function () {
            events.rollPage();
        }
        //关闭当前标签页
        , closeThisTabs: function () {
            events.rollPage('auto', admin.tabsPage.index);
            var topAdmin = parent === self ? admin : parent.layui.admin;
            topAdmin.closeThisTabs();
        }
        //关闭其它标签页
        , closeOtherTabs: function (type) {
            events.rollPage('auto', 0);
            var TABS_REMOVE = 'LAY-system-pagetabs-remove';
            if (type === 'all') {
                $(TABS_HEADER + ':gt(0)').remove();
                $(APP_BODY).find('.' + TABS_BODY + ':gt(0)').remove();

                $(TABS_HEADER).eq(0).trigger('click');
            } else {
                $(TABS_HEADER).each(function (index, item) {
                    if (index && index != admin.tabsPage.index) {
                        $(item).addClass(TABS_REMOVE);
                        admin.tabsBody(index).addClass(TABS_REMOVE);
                    }
                });
                $('.' + TABS_REMOVE).remove();
                admin.tabsPage.index = 1
            }
        }
        //关闭全部标签页
        , closeAllTabs: function () {
            events.closeOtherTabs('all');
            // 需要删除 layui-body下面的容器, 保留工作台
            // 菜单切换到控制台的, 可以模拟点击标签, 看看能不能自动切换
        }
    };

    /**
     * 打开标签(用于侧边菜单点击)
     * @author FZR
     * @param url (要打开的URL)
     * @param text (标签文本)
     * @param menus_id (数据库中的菜单ID)
     */
    var openTabsPage = function (url, text, menus_id) {
        var matchTo;
        var tabs = $("#LAY_app_tabsheader > li");

        // 循环查找是否已存在标签栏中
        tabs.each(function () {
            var lay_id = $(this).attr('lay-id');
            var lay_url = $(this).attr("lay-attr");
            if (lay_url === url && lay_id === menus_id) {
                matchTo = true;
            }
        });

        // 如果标签不存在则创建
        if (!matchTo) {
            setTimeout(function () {
                $(APP_BODY).find(".lay-tabsbody-item").removeClass("layui-show");
                $(APP_BODY).append([
                    '<div lay-id="' + menus_id + '" class="lay-tabsbody-item layui-show">'
                    , '<iframe src="' + url + '" class="lay-iframe"></iframe>'
                    , '</div>'
                ].join(''));
            }, 10);

            element.tabAdd(FILTER_TAB_TABS, {
                title: text || '新打开标签'
                , id: menus_id
                , attr: url
            });

        } else {
            //页面已存在,直接切换到页面就行,而不是重新加载新页面
            $(APP_BODY).find(".lay-tabsbody-item").removeClass("layui-show");
            $(APP_BODY).find(".lay-tabsbody-item[lay-id=" + menus_id + "]").addClass("layui-show");
        }

        // 定位到当前Tabs
        element.tabChange(FILTER_TAB_TABS, menus_id);
    };

    /**
     * 标签栏切换点击
     * @author FZR
     */
    $body.on("click", TABS_HEADER, function () {
        var url = $(this).attr('lay-attr');
        var text = $(this).children("span").html();
        var lay_id = $(this).attr('lay-id');
        var elem = $(".layui-sidebar .layui-side-menu li a[lay-id=" + lay_id + "]");

        // 切换到指定一级菜单
        elem.parents("li").siblings().find("a").removeClass("active");
        elem.parents("li").children("a").addClass("active");
        // 是否存在二级菜单
        if (elem.parents("li").has("dl").length) {
            $(".layui-sidebar > .layui-side-menu li dl").css("display", "");
            elem.parents("li").children("dl").css("display", "block");

            elem.parents("li").children("dl").find("a").removeClass("active");
            elem.parents("li").children("dl").find("a[lay-id=" + lay_id + "]").addClass("active");
            $(APP_BODY).css({ "left": "232px" });
        } else {
            $(APP_BODY).css({ "left": "110px" });
            $(".layui-sidebar > .layui-side-menu li > dl").css("display", "");
        }

        //页面已存在,直接切换到页面就行,而不是重新加载新页面
        $(APP_BODY).find(".lay-tabsbody-item").removeClass("layui-show");
        $(APP_BODY).find(".lay-tabsbody-item[lay-id=" + lay_id + "]").addClass("layui-show");
    });

    /**
     * 侧边菜单切换(一二级)
     * @author FZR
     */
    $body.on("click", ".layui-sidebar .layui-side-menu li", function () {
        if (!$(this).children("a").hasClass("active")) {
            // 一级菜单样式切换
            $(this).siblings().find("a").removeClass("active");
            $(this).children("a").addClass("active");
            // 二级菜单显示或隐藏
            if ($(this).has("dl").length) {
                $(".layui-sidebar > .layui-side-menu li dl").css("display", "");
                $(this).children("dl").css("display", "block");
                var towSubMenu = $(this).children("dl").find("dd > a");
                var Node = towSubMenu.eq(0).children("i").length ? towSubMenu.eq(1) : towSubMenu.eq(0);
                Node.addClass("active");
                $(APP_BODY).css({ "left": "232px" })

                var id = Node.attr("lay-id");
                var url = Node.attr("lay-href");
                var text = Node.html();
                openTabsPage(url, text, id)
            } else {
                $(".layui-sidebar > .layui-side-menu li > dl").css("display", "");
                $(APP_BODY).css({ "left": "110px" });

                var id = $(this).children("a").attr("lay-id");
                var url = $(this).children("a").attr("lay-href");
                var text = $(this).find("cite").html();
                openTabsPage(url, text, id)
                
            }
        }
    });

    /**
     * 三级菜单(显示/隐藏)
     * @author FZR
     */
    $body.on("click", ".layui-sidebar > .layui-side-menu .child-menu-title", function () {
        if ($(this).children("i").hasClass("layui-icon-triangle-d")) {
            $(this).children("i").removeClass("layui-icon-triangle-d");
            $(this).children("i").addClass("layui-icon-triangle-r");
            $(this).next().stop().slideUp();
        } else {
            $(this).children("i").removeClass("layui-icon-triangle-r");
            $(this).children("i").addClass("layui-icon-triangle-d");
            $(this).next().stop().slideDown();
        }
    });

    /**
     * 三级菜(样式切换)
     * @author FZR
     */
    $body.on("click", ".layui-sidebar > .layui-side-menu .child-menu dd", function () {
        if (!$(this).children("a").hasClass("child-menu-title")) {
            $(".layui-sidebar > .layui-side-menu .child-menu dd a").removeClass("active");
            $(this).children("a").addClass("active");
            // 打开标签
            var id = $(this).children("a").attr("lay-id");
            var url = $(this).children("a").attr("lay-href");
            var text = $(this).children("a").html();
            openTabsPage(url, text, id)
        }
    });

    /**
     * 快捷方式切换
     */
    $body.on("click", ".shortcut-list .shop-item > a", function () {
        var url = $(this).attr('lay-href');
        var id = $(this).attr('data-id');
        var text = $(this).attr('data-txt');
        console.log('aaaa')
        openTabsPage(url, text, id)
    });

    /**
     * 刷新页面
     */
    $body.on("click", ".layui-header .refresh", function () {
        var uri = $("#LAY_app_tabsheader li.layui-this").attr("lay-attr");
        $("#LAY_app_body .lay-tabsbody-item.layui-show .lay-iframe").attr("src", uri);
    });

    /**
     * 按键盘全屏或退出全屏
     * @author FZR
     */
    $body.on("keydown", "", function (event) {
        event = event || window.event || arguments.callee.caller.arguments[0];
        //按Esc
        if (event && event.keyCode === 27) {
            $(".fullscreen").children("i").eq(0).removeClass("layui-icon-screen-restore");
        }
        //按F11
        if (event && event.keyCode === 122) {
            $(".fullscreen").children("i").eq(0).addClass("layui-icon-screen-restore");
        }
    });

    /**
     * 点击按钮全屏或退出全屏
     * @author FZR
     */
    $body.on("click", ".fullscreen", function () {
        var docElm = document.documentElement;
        if ($(this).children("i").hasClass("layui-icon-screen-restore")) {
            document.exitFullscreen();
            $(this).children("i").eq(0).removeClass("layui-icon-screen-restore");
        } else {
            docElm.requestFullscreen();
            $(this).children("i").eq(0).addClass("layui-icon-screen-restore");
        }
    });
    //监听 tabspage 删除
    element.on('tabDelete(' + FILTER_TAB_TABS + ')', function (obj) {
        var othis = $(TABS_HEADER + '.layui-this');

        obj.index && admin.tabsBody(obj.index).remove();
        admin.tabsBody(obj.index).addClass(SHOW).siblings().removeClass(SHOW);
        events.rollPage('auto', obj.index);
        if(admin.tabsPage.index == obj.index + 1) return
        if(othis.index() + 1 == obj.index  ) { 
            $(TABS_HEADER).eq(obj.index - 1).trigger('click');
        }
        if(othis.index() - 1 == obj.index  ) {
            $(TABS_HEADER).eq(obj.index + 1).trigger('click');
        }
    });

    /**
     * 点击事件
     */
    $body.on('click', '*[lay-event]', function () {
        var that = $(this);
        var attrEvent = that.attr('lay-event');
        events[attrEvent] && events[attrEvent].call(this, that);
    });
    element.on('tab(' + FILTER_TAB_TABS + ')', function (data) {
        admin.tabsPage.index = data.index;
        events.rollPage('auto', data.index);
    });
});