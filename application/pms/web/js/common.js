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
					case "text": break;
					case "textarea": break;
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
									}else{
										alert(result.message);
									}
								}, error: function(){
									alert('AJAX请求出错。');
								}});
							}
							parent.removeClass("realtime_edit_current");
							parent.addClass("realtime_edit");
							parent.html(parent.data("value").replace(/./g, "●"));
							parent.data("is_open", false);
						});
						break;
					case "select": break;
					case "checkbox": break;
					case "radio": break;
				}
			}
		});
	}
});

$(function(){
	$(".realtime_edit").each(function(){
		$(this).realtimeEdit();
	});

	$(".index_menu ul li a").click(function(){
		$(".index_menu ul li a").removeClass("current");
		$(this).addClass("current");
	});

	$(".btn").button();
	$(".radiolist").buttonset();

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
});