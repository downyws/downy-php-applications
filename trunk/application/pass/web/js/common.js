$(function(){
	// 下拉框
	$(".jq-select").each(function(){$(this).dropDown();});
	// 设置首页
	$(".set-homepage").click(function(){$.fn.setHomePage();});

	// 验证码
	$(".captcha-box .reload").click(function(){
		var obj = $(this).parent().parent().parent().find(".img img");
		if(typeof(obj.data("src")) == "undefined"){
			$(this).attr("href", "javascript:;");
			obj.data("src", obj.attr("src") + (obj.attr("src").indexOf("?") ? "&" : "?"));
		}
		obj.attr("src", obj.data("src") + Math.random());
	});
	$(".captcha-box .help").click(function(){
		$(this).attr("href", "javascript:;");
		alert("coding...");
	});
	$(".captcha-box input").focus(function(){
		var obj = $(this).parent().find(".error");
		obj.find("span").html("&nbsp;");
		obj.removeClass("error");
	});

	// 输入提示符
	$(".placeholder-txt").each(function(){
		if($(this).val() != ""){
			$(".placeholder-txt-" + $(this).attr("name")).css("display", "none");
		}
		$(this).blur(function(){
			if($(this).val() == ""){
				$(".placeholder-txt-" + $(this).attr("name")).css("display", "block");
			}
		});
		$(this).focus(function(){
			$(".placeholder-txt-" + $(this).attr("name")).css("display", "none");
		});
	});

	// 错误提示
	$(".msg-foch").each(function(){
		var obj = $("*[name=" + $(this).data("for") + "]");
		obj.data("msgbox", this);
		obj.one("focus", function(){
			$($(this).data("msgbox")).css("display", "none");
			$(this).removeClass("error");
		});
	});
});

/* ext plugs start */
$.fn.extend({
	// 密码强度检测
	assessPassword: function(val){
		var len = val.length;
		var score = 0;
		if(val.length > 0){

			if(len >= 8){ score += 20; }
			else if(len >= 4){ score += 10; }

			if(/[a-z]/.test(val) && /[A-Z]/.test(val)){ score += 20; }
			else if(/[a-z]/i.test(val)){ score += 10; }

			if(val.split(/[0-9]/g).length > 2){ score += 20; }
			else if(/[0-9]/.test(val)){ score += 10; }

			if(val.split(/\W/).length > 2){ score += 20; }
			else if(/\W/.test(val)){ score += 10; }

			if(!/[0-9]/.test(val) || !/[a-z]/i.test(val)){ score += 0; }
			else if(!/\W/.test(val)){ score += 5; }
			else if(!/[a-z]/.test(val) || !/[A-Z]/.test(val)){ score += 10; }
			else{ score += 20; }
		}
		return score;
	},
	// 悬浮提示
	// For IE
	// if(document.all){$('input[type="text"]').each(function(){var that = this; if(this.attachEvent){this.attachEvent('onpropertychange', function(e){ if(e.propertyName != 'value') return; $(that).trigger('input');});}});}
	jqBubbleTips: function(content, option){
		var id = "jq-bubble-tips-" + parseInt(Math.random() * 1000);

		$(this).data("jq-bubble-tips", "#" + id);
		var height = $(this).innerHeight();
		var top = $(this).offset().top + height / 2 - 20;
		var left = $(this).offset().left - 305;
		var html = "<div id='" + id + "' class='jq-bubble-tips' style='top:" + top + "px;left:" + left + "px'>";
		html += "	<div class='content'>" + content + "</div>";
		html += "	<div class='arrow'>";
		html += "		<div class='arrow-b'></div>";
		html += "		<div class='arrow-f'></div>";
		html += "	</div>";
		html += "</div>";
		$("body").append(html);

		$(this).focus(function(){
			$($(this).data("jq-bubble-tips")).fadeIn('fast');
		});
		$(this).blur(function(){
			$($(this).data("jq-bubble-tips")).fadeOut('fast');
		});
		if(typeof(option.input) != "undefined"){
			$(this).on("input", option.input);
		}
	},
	setHomePage: function(){
		alert('coding...');
		var url = $(this).data("url");
		try{
			this.style.behavior = 'url(#default#homepage)';
			this.setHomePage(url);
		}catch(e){
			if(window.netscape){
				try{
					netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				}catch(e){
					alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。");
				}
				// var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
				// prefs.setCharPref('browser.startup.homepage', url);
			}
		}
	},
	dropDown: function(){
		var name = $(this).attr("name");
		var text = $(this).data("text");
		var type = $(this).data("type");
		var width = $(this).data("width");
		var height = $(this).data("height");
		var id = "jq-select-" + parseInt(Math.random() * 1000);

		var html = '<div id="' + id + '" class="select-box"><input type="hidden" value="" name="' + name + '" />';
		html += '	<div class="caption" style="width:' + (width - 24) + 'px">';
		html += '		<div class="text">' + text + '</div><div class="dropdown">&nbsp;</div>';
		html += '	</div>';
		html += '	<div class="list" tabindex="0" style="height:' + height + 'px;width:' + width + 'px;">';
		$(this).find("option").each(function(){
			html += '<div class="menuitem" data-value="' + $(this).val() + '">' + $(this).html() + '</div>';
		});
		html += '	</div>';
		html += '</div>';
		$(this).after(html);
		if(typeof($(this).data("defval")) != "undefined"){
			var defval = $(this).data("defval");
			$("#" + id + " input[type=hidden]").val(defval);
			$(this).find("option").each(function(){
				if(defval == $(this).val()){
					$("#" + id + " .caption .text").html($(this).html());
				}
			});
		}
		$(this).remove();

		if(type == "jump"){
			$("#" + id + ".select-box .list .menuitem").click(function(){
				window.location.href = $(this).data("value");
			});
		}else{
			$("#" + id + ".select-box .list .menuitem").click(function(){
				$(this).parent().parent().find("input").val($(this).data("value"));
				$(this).parent().parent().find(".caption .text").html($(this).html());
				$(this).parent().css("display", "none");
			});
		}
		$("#" + id + ".select-box .caption").click(function(){
			var obj = $(this).parent().find(".list");
			obj.css("display", "block");
			obj.focus();
			return false;
		});
		$("#" + id + ".select-box .list").blur(function(){
			$(this).css("display", "none");
		});
	}
});