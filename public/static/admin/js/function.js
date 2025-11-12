var lock = {};
var load = {};

var like = {
    ajax: function (json) {
        var load_index = null;
        if (json.beforeSend === undefined) {
            if (lock[json.url.replace("/", "_")] !== undefined) {
                return
            }
            lock[json.url.replace("/", "_")] = true;
            json.beforeSend = function () {
                load[json.url.replace("/", "_")] = setTimeout(function () {
                    load_index = layer.load(1, {shade: [0.1, "#fff"]})
                }, 1500)
            }
        }
        if (json.error === undefined) {
            json.error = function (res) {
                layer.msg("网络错误", {offset: "240px", icon: 2, time: 1500}, function () {
                    return
                })
            }
        }
        if (json.timeout === undefined) {
            json.timeout = 30000
        }
        if (json.type === undefined) {
            json.type = "get"
        }
        json.complete = function (xhr, status) {
            delete lock[json.url.replace("/", "_")];
            if (status == "timeout") {
                layer.msg("请求超时，请重试", {offset: "240px", icon: 2, time: 1500}, function () {
                    if (load_index !== undefined) {
                        layer.close(load_index)
                    }
                    return
                });
                return
            }
            clearTimeout(load[json.url.replace("/", "_")]);
            if (load_index !== undefined) {
                layer.close(load_index)
            }
            res = xhr.responseJSON;
            if (res !== undefined && res.code == -1) {
                window.location.href = window.location.href
            }
            if (res !== undefined && res.code == 0 && res.show == 1) {
                layer.msg(res.msg, {offset: "240px", icon: 2, time: 1500}, function () {
                    return;
                })
            }
        };
        $.ajax(json);
    },
    tableLists: function (elem, url, cols, where,page = true) {
        where = where === undefined ? {} : where;
        page = page === undefined ? true : page;
        layui.use(['table'], function () {
            var table = layui.table;
            table.render({
                elem: elem
                , url: url
                , cols: [cols]
                , where: where
                , text: {none: '暂无相关数据'}
                , response: {
                    statusCode: 1
                }
                , page: page
                , parseData: function (res) {
                    return {
                        "code": res.code,
                        "msg": res.msg,
                        "count": res.data.count,
                        "data": res.data.lists
                    };
                }
                , done: function () {
                    setTimeout(function () {
                        $(".layui-table-main tr").each(function (index, val) {
                            $($(".layui-table-fixed-l .layui-table-body tbody tr")[index]).height($(val).height());
                            $($(".layui-table-fixed-r .layui-table-body tbody tr")[index]).height($(val).height());
                        });
                    }, 200);
                }
            });
        });
    },
    keyUpClick: function (elem, target) {
        $(elem).keyup(function (event) {
            if (event.keyCode === 13) {
                $(target).trigger('click');
            }
        });
    },
    eventClick: function (active) {
        $(document).on("click", ".layEvent", function () {
            var type = $(this).attr("lay-event");
            var obj  = $(this).attr("lay-data");

            active[type] ? active[type].call(this, obj) : "";
        });

        layui.use(["table"], function () {
            layui.table.on("tool(like-table-lists)", function(obj){
                var type = obj.event;
                active[type] ? active[type].call(this, obj) : "";
            });
        });
    },
    setSelect: function (defaultId, data, targetName, optionName) {
        layui.use(['form'], function () {
            var form = layui.form;
            var selectHtml = '<option value="">请选择'+optionName+'</option>';
            for (var i in data) {
                selectHtml += '<option value="' + data[i]['id'] + '">' + data[i]['name'] + '</option>';
            }
            $('select[name=' + targetName + ']').html(selectHtml);
            $('select[name=' + targetName + ']').val(defaultId);
            form.render('select');
        });
    },
    /**
     * 上传图片
     * @author fzr
     * @param options
     */
    imageUpload: function (options) {
        var that  = options.that;
        var limit = options.limit || 1;
        var field = options.field || "image";
        var content = options.content || '/admin/file/lists?type=10&&shop_id=0';
        var isSpecImage = options.isSpecImage || false;

        parent.layer.open({
            type: 2
            ,title: "上传图片"
            ,shadeClose: true
            ,maxmin: true
            ,anim: 1
            ,shade: 0.3
            ,area: ["90%", "90%"]
            ,content: content,
            success: function (layero, index) {
                var iframeNode = $(layero).find("iframe").contents();

                iframeNode.find("#okFile").click(function () {
                    var fileUrls = iframeNode.find("#fileUrl").val();
                    var urls = fileUrls.split(',');

                    if (urls.length <= 0) {
                        parent.layer.msg("请至少选择一张图片");
                        return false;
                    }

                    if (urls.length > limit) {
                        parent.layer.msg("限制只能选"+limit+"张");
                        return false;
                    }

                    if (isSpecImage) {
                        urls.forEach(function (url) {
                            // 商品多规格特殊处理
                            var template  = '<div class="upload-image-div">';
                            template += '<img src="'+url+'" alt="img">';
                            template += '<div class="del-upload-btn">x</div>';
                            that.parent().before(template);
                            that.parent().parent().find(".upload-spec-image").val(url);
                        });
                    } else {
                        urls.forEach(function (url) {
                            var template  = '<div class="upload-image-div">';
                            template += '<img src="'+url+'" alt="img">';
                            template += '<input type="hidden" name="'+field+'" value="'+url+'">';
                            template += '<div class="del-upload-btn">x</div>';
                            that.parent().before(template);
                        });
                    }

                    if (limit === 1) that.parent().hide();
                    parent.layer.close(index);
                });
            }
        });
    },
    /**
     * 上传视频
     * @author fzr
     * @param options
     */
    videoUpload: function (options) {
        var that  = options.that;
        var limit = options.limit || 1;
        var field = options.field || "video";
        var content = options.content || '/admin/file/lists?type=20';

        parent.layer.open({
            type: 2
            ,title: "上传视频"
            ,shadeClose: true
            ,maxmin: true
            ,anim: 1
            ,shade: 0.3
            ,area: ["90%", "90%"]
            ,content: content
            ,success: function (layero, index) {
                var iframeNode = $(layero).find("iframe").contents();
                iframeNode.find("#okFile").click(function () {
                    var urls = [];
                    iframeNode.find(".warehouse li.on").each(function () {
                        urls.push($(this).attr("lay-href"));
                    });

                    if (urls.length <= 0) {
                        parent.layer.msg("请至少选择一个视频");
                        return false;
                    }

                    if (urls.length > limit) {
                        parent.layer.msg("限制只能选"+limit+"个");
                        return false;
                    }

                    urls.forEach(function (url) {
                        var template  = '<div class="upload-video-div">';
                        template += '<video src="'+url+'"></video>';
                        template += '<input type="hidden" name="'+field+'" value="'+url+'">';
                        template += '<div class="del-upload-btn">x</div>';

                        that.parent().parent().prepend(template);
                    });

                    if (limit === 1) that.parent().hide();
                    parent.layer.close(index);
                });
            }
        });
    },
    /**
     * 删除上传文件
     * @author fzr
     */
    delUpload: function () {
        $(document).on("click", ".del-upload-btn", function () {
            $(this).parent().parent().find(".upload-image-elem").show();
            $(this).parent().parent().find(".upload-spec-image").val(''); // 商品多规格特殊处理
            $(this).parent().remove();
        });
    },
    /**
     * 放大图片
     */
    showImg: function (url, xp) {
        function getImageWidth(url, callback) {
            var img = new Image();
            img.src = url;
            if (img.complete) {
                callback(img.width, img.height)
            } else {
                img.onload = function () {
                    callback(img.width, img.height)
                }
            }
        }
        xp === undefined ? 500 : xp;
        getImageWidth(url, function (width, height) {
            if (height > xp) {
                var ratio = width / height;
                height = xp;
                width = height * ratio
            }
            if (width > xp) {
                var ratio = height / width;
                width = xp;
                height = width * ratio
            }
            layer.closeAll();
            layer.open({
                type: 1,
                closeBtn: 1,
                shade: false,
                title: false,
                shadeClose: false,
                area: ["auth", "auth"],
                content: '<img src="' + url + '" width="' + width + 'px" height="' + height + 'px">'
            })
        })
    },
    certUpload:function (element, url, domain) {
        layui.upload.render({
            elem: element
            ,url: url
            ,accept:'file'
            ,exts:'pem|txt|doc'
            ,done: function(res, index, upload) {
                var html = '<div class="pay-li">\n' +
                    '<img class="pay-img" ' +
                    'src="/static/common/image/default/upload.png">' +
                    '<a class="pay-img-del-x" style="display: none">x</a>' +
                    '</div>';
                $(element).prev().val(res.data[1].uri);
                $(element).after(html);
                $(element).css('display','none');
            }
        });
    }
};



