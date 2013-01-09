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
	}
});
