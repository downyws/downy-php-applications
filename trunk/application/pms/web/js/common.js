$(function(){
	$(".timeclock").each(function(){
		$(this).timeClock();
	});

	$(".realtime_edit").each(function(){
		$(this).realtimeEdit();
	});

	$(".index_menu ul li a").click(function(){
		$(".index_menu ul li a").removeClass("current");
		$(this).addClass("current");
	});

	$(".ajaxlink").ajaxLink();

	$(".wordstata").each(function(){
		$(this).wordStata();
	});

	$(".btn").button();
	$(".radiolist").buttonset();
	$(".checkboxlist").buttonset();

	$(".rowtabs").rowTabs();

	$(".datepicker").each(function(){
		$(this).datepicker({dateFormat: "yy-mm-dd"});
	});
	$(".datepicker_from").each(function(){
		$(this).datepicker({
			dateFormat: "yy-mm-dd",
			numberOfMonths: 2,
			onSelect: function(selectedDate){
				$(".datepicker_to").datepicker("option", "minDate", selectedDate);
			}
		});
	});
	$(".datepicker_to").each(function(){
		$(this).datepicker({
			dateFormat: "yy-mm-dd",
			numberOfMonths: 2,
			onSelect: function(selectedDate){
				$(".datepicker_from").datepicker("option", "maxDate", selectedDate);
			}
		});
	});
	$(".datetimepicker").each(function(){
		$(this).datetimepicker({dateFormat: "yy-mm-dd", timeFormat: "hh:mm:ss", showSecond: true});
	});
});



$.fn.extend({
	/* 框架网页检查 */
	frameCheck:function(){
		if(top.location !== self.location){
			top.location = self.location;
		}
	},

	/* AJAX即时提交控件 */
	realtimeEdit:function(){
		var type = $(this).data("type");
		$(this).addClass("realtime_edit");
		$(this).click(function(){
			var is_open = $(this).data("is_open");
			if(!is_open){
				$(this).removeClass("realtime_edit");
				$(this).addClass("realtime_edit_current");
				$(this).data("is_open", true);
				switch(type){
					case "text":
						$(this).html("<input type='text' value='" + $(this).data("value") + "' />");
						$(this).find("input").focus();
						$(this).find("input").blur(function(){
							var parent = $(this).parent();
							var new_val = $(this).val();
							var old_val = parent.data("value");
							if(new_val != old_val){
								var url = parent.data("url");
								$.ajax({type: "POST", dataType: "JSON", url: url, data: {val: new_val}, async: false, success: function(result){
									if(result.state){
										parent.data("value", new_val);
										$.fn.msgbox('success', '保存成功。');
									}else{
										$.fn.msgbox('failed', result.message);
									}
								}, error: function(){
									$.fn.msgbox('failed', 'AJAX请求出错。');
								}});
							}
							parent.removeClass("realtime_edit_current");
							parent.addClass("realtime_edit");
							parent.html(parent.data("value"));
							parent.data("is_open", false);
						});
						break;
					case "password":
						$(this).html("<input type='password' value='" + $(this).data("value") + "' />");
						$(this).find("input").focus();
						$(this).find("input").blur(function(){
							var parent = $(this).parent();
							var new_val = $(this).val();
							var old_val = parent.data("value");
							if(new_val != old_val){
								var url = parent.data("url");
								$.ajax({type: "POST", dataType: "JSON", url: url, data: {val: new_val}, async: false, success: function(result){
									if(result.state){
										parent.data("value", new_val);
										$.fn.msgbox('success', '保存成功。');
									}else{
										$.fn.msgbox('failed', result.message);
									}
								}, error: function(){
									$.fn.msgbox('failed', 'AJAX请求出错。');
								}});
							}
							parent.removeClass("realtime_edit_current");
							parent.addClass("realtime_edit");
							parent.html(parent.data("value").replace(/./g, "●"));
							parent.data("is_open", false);
						});
						break;
					case "textarea": break;
					case "select": break;
					case "checkbox": break;
					case "radio": break;
				}
			}
		});
	},

	/* 选项表单 */
	rowTabs: function(){
		var list = new Array();
		$(this).find("input").each(function(){
			list.push($(this).data("tab"));
			if($(this).attr("checked") != "checked"){
				$($(this).data("tab")).css("display", "none");
			}
			$(this).change(function(){
				for(var i = 0; i < list.length; i++){
					$(list[i]).css("display", "none");
				}
				$($(this).data("tab")).css("display", "");
			});
		});
	},

	/* 时钟 */
	timeClock: function(){
		var that = this;
		var run = function(){
			var timestamp = $(that).data("timestamp");
			$(that).data("timestamp", timestamp + 1000);
			var now = new Date(timestamp);
			$(that).html(now.toDateString() + " " + now.toTimeString());			
		}
		run();
		setInterval(run, 1000);
	},

	/* 文本框字数统计 */
	wordStata: function(){
		var that = this;
		var type = $(this).data("type");
		var output = $(this).data("output");
		var stata = function(){
			if(type == "iso"){
				$(output).html($(that).val().length);
			}
		};
		$(this).keyup(stata);
		stata();
	},

	/* 提示信息框 */
	msgbox: function(state, message){
		$(".msgbox").remove();
		$("body").append("<div class='msgbox'><span class='" + state + "'>" + message + "</span></div>");
		$(".msgbox").fadeIn(800).delay(2000).fadeOut(800);
	},

	/* AJAX表单提交 */
	ajaxForm: function(form){
		var data = {};
		var list = $(form).serializeArray();
		var method = $(form).attr('method');
		var action = $(form).attr('action');
		$.each(list, function(){
			if(typeof(data[this.name]) !== "undefined"){
				if(!data[this.name].push){
					data[this.name] = [data[this.name]];
				}
				data[this.name].push(this.value || "");
			}else{
				data[this.name] = this.value || "";
			}
		});
		$.ajax({type: method, dataType: "JSON", url: action, data: data, async: true, success: function(result){
			if(result.state){
				$.fn.msgbox('success', result.message);
				result.script && eval(result.script);
			}else{
				$.fn.msgbox('failed', result.message);
			}
		}, error: function(){
			$.fn.msgbox('failed', 'AJAX请求出错。');
		}});
		return false;
	},

	/* AJAX链接 */
	ajaxLink: function(){
		$(this).click(function(){
			$.ajax({type: "GET", dataType: "JSON", url: $(this).data("url"), async: true, success: function(result){
				if(result.state){
					$.fn.msgbox('success', result.message);
					result.script && eval(result.script);
				}else{
					$.fn.msgbox('failed', result.message);
				}
			}, error: function(){
				$.fn.msgbox('failed', 'AJAX请求出错。');
			}});
		});
	}
});
