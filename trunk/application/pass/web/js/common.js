$(function(){
	// 验证码
	$(".dy_captcha").each(function(){
		$(this).dyCaptcha({url: $(this).data("url"), width: $(this).data("width"), msgerr: $(this).data("msgerr")});
	});
	// 下拉框
	$(".dy_select").each(function(){
		$(this).dySelect({name: $(this).attr("name"), text: $(this).data("text"), type: $(this).data("type"), width: $(this).data("width"), height: $(this).data("height"), defval: $(this).data("defval")});
	});
	$(".dy-text").each(function(){
		$(this).dyText({tips: $(this).data("dy_tips")});
	});
	$(".dy_oncemsg").each(function(){
		$(this).dyOnceMsg({target: $(this).data("target"), event: $(this).data("event")});
	});
});

$.fn.extend({
	// 浏览器
	broswerName: function(){
		if(/firefox/gi.test(navigator.userAgent)){
			return 'firefox';
		}else if(/AppleWebKit/gi.test(navigator.userAgent) && /theworld/gi.test(navigator.userAgent)){
			return 'worldjs';
		}else if(/theworld/gi.test(navigator.userAgent)){
			return 'world';
		}else if((/qqbrowser/gi.test(navigator.userAgent)) || (/chrome/gi.test(navigator.userAgent) && /qqbrowser/gi.test(navigator.userAgent))){
			return 'qq';
		}else if(/360chrome/gi.test(navigator.userAgent) || (/360/gi.test(navigator.userAgent) && /qihu/gi.test(navigator.userAgent))){
			return 's360js';
		}else if(/se /gi.test(navigator.userAgent)){
			return 'sogou';
		}else if(/chrome/gi.test(navigator.userAgent)){
			return 'chrome';
		}else if(/opera/gi.test(navigator.userAgent)){
			return 'opera';
		}else if(/360se/gi.test(navigator.userAgent)){
			return 's360';
		}else if(/greenbrowser/gi.test(navigator.userAgent)){
			return 'green';
		}else if(/tencenttraveler/gi.test(navigator.userAgent)){
			return 'tt';
		}else if(/maxthon/gi.test(navigator.userAgent)){
			return 'maxthon';
		}else if(/safari/gi.test(navigator.userAgent)){
			return 'safari';
		}else if(/krbrowser/gi.test(navigator.userAgent)){
			return 'kr';
		}else{
			return 'ie';
		}
	},
	// 密码强度检测
	assessPassword: function(val){
		var score = 0;
		if(val.length > 0){
			// 长度检测
			if(val.length >= 8){ score += 20; }
			else if(val.length >= 4){ score += 10; }
			// 大小写字母检测
			if(/[a-z]/.test(val) && /[A-Z]/.test(val)){ score += 20; }
			else if(/[a-z]/i.test(val)){ score += 10; }
			// 数字检测
			if(val.split(/[0-9]/g).length > 2){ score += 20; }
			else if(/[0-9]/.test(val)){ score += 10; }
			// 特殊字符检测
			if(val.split(/\W/).length > 2){ score += 20; }
			else if(/\W/.test(val)){ score += 10; }
			// 混合加分
			if(!/[0-9]/.test(val) || !/[a-z]/i.test(val)){ score += 0; }
			else if(!/\W/.test(val)){ score += 5; }
			else if(!/[a-z]/.test(val) || !/[A-Z]/.test(val)){ score += 10; }
			else{ score += 20; }
		}
		return score;
	},
	dyOnceMsg: function(option){
		$(option.target).data("dy-oncemsg", this);
		$(option.target).bind(option.event, function(){
			$($(this).data("dy-oncemsg")).css("display", "none");
		});
	},
	// 提示
	dyTips: function(option){
		if($(this).val() == ""){
			$($(this).data("dy_tips")).css("display", "block");
		}
		$(this).focus(function(){
			$($(this).data("dy_tips")).css("display", "none");
		});
		$(this).blur(function(){
			if($(this).val() == ""){
				$($(this).data("dy_tips")).css("display", "block");
			}
		});
	},
	// 悬浮提示
	dyBubble: function(content, option){

		var id = "dy-bubble-" + parseInt(Math.random() * 1000);
		$(this).data("dy-bubble", "#" + id);
		var height = $(this).innerHeight();
		var top = $(this).offset().top + height / 2 - 20;
		var left = $(this).offset().left - 305;

		// 创建对象
		var html = "<div id='" + id + "' class='dy-bubble' style='top:" + top + "px;left:" + left + "px'>";
		html += "	<div class='content'>" + content + "</div>";
		html += "	<div class='arrow'><div class='arrow-b'></div><div class='arrow-f'></div></div>";
		html += "</div>";
		$("body").append(html);

		// 绑定事件
		$(this).focus(function(){
			var obj = $(this).data("dy-bubble");
			var height = $(this).innerHeight();
			var top = $(this).offset().top + height / 2 - 20;
			var left = $(this).offset().left - 305;
			$(obj).css({"top": top, "left": left});
			$(obj).fadeIn('fast');
		});
		$(this).blur(function(){
			$($(this).data("dy-bubble")).fadeOut('fast');
		});
		if(typeof(option.input) != "undefined"){
			$(this).on("input", option.input);
		}
	},
	dyText: function(option){
		if(typeof(option.tips) != "undefined"){
			$(this).dyTips();
		}
		$(this).focus(function(){
			$(this).removeClass("error");
		});
	},
	dySelect: function(option){
		// 获取配置
		var id = "dy-select-" + parseInt(Math.random() * 1000);

		// 创建对象
		var html = '<div id="' + id + '" class="dy-select"><input type="hidden" name="' + option.name + '" />';
		html += '	<div class="caption" style="width:' + (option.width - 24) + 'px">';
		html += '		<div class="text">' + option.text + '</div><div class="dropdown">&nbsp;</div>';
		html += '	</div>';
		html += '	<div class="list" tabindex="0" style="height:' + option.height + 'px;width:' + option.width + 'px;">';
		$(this).find("option").each(function(){
			html += '<div class="item" data-value="' + $(this).val() + '">' + $(this).html() + '</div>';
		});
		html += '	</div>';
		html += '</div>';
		$(this).after(html);
		if(typeof(option.defval) != "undefined"){
			$("#" + id + " input[type=hidden]").val(option.defval);
			$(this).find("option").each(function(){
				if(option.defval == $(this).val()){
					$("#" + id + " .caption .text").html($(this).html());
				}
			});
		}
		$(this).remove();

		// 绑定事件
		if(option.type == "jump"){
			$("#" + id + ".dy-select .list .item").click(function(){
				window.location.href = $(this).data("value");
			});
		}else{
			$("#" + id + ".dy-select .list .item").click(function(){
				$(this).parent().parent().find("input").val($(this).data("value"));
				$(this).parent().parent().find(".caption .text").html($(this).html());
				$(this).parent().css("display", "none");
			});
		}
		$("#" + id + ".dy-select .caption").click(function(){
			var obj = $(this).parent().find(".list");
			obj.css("display", "block");
			obj.focus();
			return false;
		});
		$("#" + id + ".dy-select .list").blur(function(){
			$(this).css("display", "none");
		});
	},
	// 验证码
	dyCaptcha: function(option){
		// 创建对象
		var html = "<div class='dy-captcha'>";
		html += "<div class='img'><img src='" + option.url + "' alt='captcha'></div>";
		html += "<div class='text'>";
		html += "<label>";
		if(typeof(option.msgerr) == "undefined"){
			html += "<strong><span>输入上图中显示的字符：</span></strong>";
			html += "<input type='text' name='captcha' class='dy-text' style='width:" + option.width + "px' />";
		}else{
			html += "<strong class='msgerr'><span>" + option.msgerr + "</span></strong>";
			html += "<input type='text' name='captcha' class='dy-text error' style='width:" + option.width + "px' />";
		}
		html += "</label>";
		html += "<div class='btns'>";
		html += "<a href='#' title='获取新的验证方式' class='reload'></a>";
		html += "<a href='javascript:;' title='帮助' class='help'></a>";
		html += "</div>";
		html += "</div>";
		html += "</div>";
		$(this).html(html);

		// 绑定事件
		$(this).find(".btns a.reload").click(function(){
			var obj = $(this).parent().parent().parent().find(".img img");
			if(typeof(obj.data("src")) == "undefined"){
				$(this).attr("href", "javascript:;");
				obj.data("src", obj.attr("src") + (obj.attr("src").indexOf("?") ? "&" : "?"));
			}
			obj.attr("src", obj.data("src") + Math.random());
		});
		$(this).find(".btns a.help").click(function(){
			alert("coding...");
		});
		$(this).find("input[name=captcha]").focus(function(){
			var obj = $(this).parent().find(".msgerr");
			obj.find("span").html("&nbsp;");
			obj.removeClass("msgerr");
			$(this).removeClass("error");
		});
	},
	// 对话框
	dyDialog: function(config){
		// 参数格式化
		if(typeof(config.content) == "undefined") config.content = "";
		if(typeof(config.bgStyle) == "undefined") config.bgStyle = {};
		if(typeof(config.locate.type) == "undefined") config.locate.type = "window";
		if(typeof(config.locate.target.object) == "undefined") config.locate.target.object = false;
		if(typeof(config.locate.target.origin) == "undefined") config.locate.target.origin = "lt";
		if(typeof(config.locate.x) == "undefined") config.locate.x = 0;
		if(typeof(config.locate.y) == "undefined") config.locate.y = 0;
		if(typeof(config.locate.origin) == "undefined") config.locate.origin = "lt";
		if(typeof(config.onEvent.open) == "undefined") config.onEvent.open = function(){};
		if(typeof(config.onEvent.close) == "undefined") config.onEvent.close = function(){};
		if(typeof(config.onEvent.resize) == "undefined") config.onEvent.resize = function(){};
		if(typeof(config.close.bgBtn) == "undefined") config.close.bgBtn = false;
		if(typeof(config.close.className) == "undefined") config.close.className = false;

		// 事件
		var H_Close = function(){
			// 解绑
			$(window).unbind('resize', H_WindowResize);
			config.close.bgBtn && $("#" + id + "-bg").unbind('click', H_Close);
			config.close.className && $("#" + id + "-ft ." + config.close.className).unbind('click', H_Close);
			// 销毁对象
			$("#" + id + "-bg, #" + id + "-ft").fadeOut(300, function(){
				$(this).remove();
			});
			// 关闭回调
			config.onEvent.close();
		}
		var H_WindowResize = function(){
			$("#" + id + "-bg").css({"width": "0px", "height": "0px"});
			$("#" + id + "-ft").css({"top": 0, "left": 0});

			if(config.locate.type == "window"){
				F_LocateWindow();
			}else if(config.locate.type == "page"){
				F_LocatePage();
			}else if(config.locate.type == "tag"){
				F_LocateTag();
			}
			var w = $(document).width(), h = $(document).height();
			$("#" + id + "-bg").css({"width": w + "px", "height": h + "px"});

			config.onEvent.resize();
		};
		// 窗口类型定位
		var F_LocateWindow = function(){
			var ft_w = $("#" + id + "-ft").innerWidth(), ft_h = $("#" + id + "-ft").innerHeight();
			var w_w = $(window).width(), w_h = $(window).height();
			var x = 0, y = 0;

			if(c_x == "left") x = 0;
			else if(c_x == "middle") x = w_w / 2;
			else if(c_x == "right") x = w_w;
			else x = c_x;

			if(c_y == "top") y = 0;
			else if(c_y == "middle") y = w_h / 2;
			else if(c_y == "bottom") y = w_h;
			else y = c_y;

			var xy = F_Offset(x, y, c_o, ft_w, ft_h);
			$("#" + id + "-ft").css("left", xy.x);
			$("#" + id + "-ft").css("top", xy.y);
		};
		// 页面类型定位
		var F_LocatePage = function(){
			var ft_w = $("#" + id + "-ft").innerWidth(), ft_h = $("#" + id + "-ft").innerHeight();
			var d_w = $(document).width(), d_h = $(document).height();
			var x = 0, y = 0;

			if(c_x == "left") x = 0;
			else if(c_x == "middle") x = d_w / 2;
			else if(c_x == "right") x = d_w;
			else x = c_x;

			if(c_y == "top") y = 0;
			else if(c_y == "middle") y = d_h / 2;
			else if(c_y == "bottom") y = d_h;
			else y = c_y;

			var xy = F_Offset(x, y, c_o, ft_w, ft_h);
			$("#" + id + "-ft").css("left", xy.x);
			$("#" + id + "-ft").css("top", xy.y);
		};
		// 元素类型定位
		var F_LocateTag = function(){
			var ft_w = $("#" + id + "-ft").innerWidth(), ft_h = $("#" + id + "-ft").innerHeight();
			var t_w = config.locate.target.object.innerWidth(), t_h = config.locate.target.object.innerHeight();
			var t_x = config.locate.target.object.offset().left, t_y = config.locate.target.object.offset().top;
			var x = 0, y = 0;

			if(c_x == "left") x = t_x;
			else if(c_x == "middle") x = t_x + t_w / 2;
			else if(c_x == "right") x = t_x + t_w;
			else switch(config.locate.target.origin){
				case "mt": case "mm": case "mb": x = t_x + t_w / 2 + c_x; break;
				case "rt": case "rm": case "rb": x = t_x + t_w + c_x; break;
				case "lt": case "lm": case "lb": default: x = t_x + c_x; break;
			}

			if(c_y == "top") y = t_y;
			else if(c_y == "middle") y = t_y + t_h / 2;
			else if(c_y == "bottom") y = t_y + t_h;
			else switch(config.locate.target.origin){
				case "lm": case "mm": case "rm": y = t_y + t_h / 2 + c_y; break;
				case "lb": case "mb": case "rb": y = t_y + t_h + c_y; break;
				case "lt": case "mt": case "rt": default: y = t_y + c_y; break;
			}

			var xy = F_Offset(x, y, c_o, ft_w, ft_h);
			$("#" + id + "-ft").css("left", xy.x);
			$("#" + id + "-ft").css("top", xy.y);
		};
		// 对齐点偏移
		var F_Offset = function(left, top, origin, width, height){
			switch(origin){
				case "lm": return {x: left, y: top - height / 2}; break;
				case "lb": return {x: left, y: top - height}; break;
				case "mt": return {x: left - width / 2, y: top}; break;
				case "mm": return {x: left - width / 2, y: top - height / 2}; break;
				case "mb": return {x: left - width / 2, y: top - height}; break;
				case "rt": return {x: left - width, y: top}; break;
				case "rm": return {x: left - width, y: top - height / 2}; break;
				case "rb": return {x: left - width, y: top - height}; break;
				case "lt": default: return {x: left, y: top}; break;
			}
		};

		// 创建对象
		var id = "dy-dialog-" + parseInt(Math.random() * 1000);
		$("body").append("<div id='" + id + "-bg'></div>");
		$("#" + id + "-bg").css({"top":"0", "left":"0", "position":"absolute", "z-index":"999", "background-color":"#DDDDDD", "opacity":"0.6"});
		$("#" + id + "-bg").css(config.bgStyle);

		// 定位
		var c_x = config.locate.x * 1, c_y = config.locate.y * 1, c_o = config.locate.origin;
		$("body").append("<div id='" + id + "-ft'>" + config.content + "</div>");
		if(config.locate.type == "window"){
			$("#" + id + "-ft").css({"position":"fixed", "z-index":"1000"});
		}else if(config.locate.type == "page"){
			$("#" + id + "-ft").css({"position":"absolute", "z-index":"1000"});
		}else if(config.locate.type == "tag"){
			$("#" + id + "-ft").css({"position":"absolute", "z-index":"1000"});
		}

		// 绑定
		config.close.bgBtn && $("#" + id + "-bg").bind('click', H_Close);
		config.close.className && $("#" + id + "-ft ." + config.close.className).bind('click', H_Close);
		$(window).bind('resize', H_WindowResize);

		// 展现
		H_WindowResize();
		$("#" + id + "-bg, #" + id + "-ft").css("display", "none").fadeIn(300);
		// 回调
		config.onEvent.open();
	}
});
