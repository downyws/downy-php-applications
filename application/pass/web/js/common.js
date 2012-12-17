$(function(){
	// 下拉框
	$(".jq-select").each(function(){
		var name = $(this).attr("name");
		var text = $(this).data("text");
		var type = $(this).data("type");
		var width = $(this).data("width");
		var height = $(this).data("height");
		var id = "jq-select-" + parseInt(Math.random()*1000);

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
	});
	$(".select-box .caption").click(function(){var obj = $(this).parent().find(".list");obj.css("display", "block");obj.focus();return false;});
	$(".select-box .list").blur(function(){$(this).css("display", "none");});

	// 设置首页
	$(".set-homepage").click(function(){
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
	});
});
