$.fn.extend({
	// AJAX表单提交
	dyAjaxForm: function(form, option){
		if(typeof(option.dataType) == "undefined") option.dataType = "HTML";
		if(typeof(option.onEvent) == "undefined") option.onEvent = {};
		if(typeof(option.onEvent.success) == "undefined") option.onEvent.success = function(){};
		if(typeof(option.onEvent.error) == "undefined") option.onEvent.error = function(){};
		if(typeof(option.onEvent.beforeSend) == "undefined") option.onEvent.beforeSend = function(){};

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
		option.onEvent.beforeSend();
		$.ajax({type: method, dataType: option.dataType, url: action, data: data, async: false, success: function(result){
			option.onEvent.success(result);
		}, error: function(jqXHR, textStatus, errorThrown){
			option.onEvent.error(jqXHR, textStatus, errorThrown);
		}});
	},
	// 时钟
	dyTimeClock: function(){
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
	// 最大z-index
	dyMaxZindex: function(tag){
		var m = 0, z = 0;
		$("body").find(tag).each(function(){
			z = isNaN(z) ? 0 : $(this).css("z-index") * 1;
			if(m < z) m = z;
		});
		return m;
	},
	// 对话框
	dyDialog: function(option){
		// 参数格式化
		if(typeof(option.content) == "undefined") option.content = "";
		if(typeof(option.bgStyle) == "undefined") option.bgStyle = {};
		if(typeof(option.locate.type) == "undefined") option.locate.type = "window";
		if(typeof(option.locate.target) == "undefined") option.locate.target = {};
		if(typeof(option.locate.target.object) == "undefined") option.locate.target.object = false;
		if(typeof(option.locate.target.origin) == "undefined") option.locate.target.origin = "lt";
		if(typeof(option.locate.x) == "undefined") option.locate.x = 0;
		if(typeof(option.locate.y) == "undefined") option.locate.y = 0;
		if(typeof(option.locate.origin) == "undefined") option.locate.origin = "lt";
		if(typeof(option.onEvent) == "undefined") option.onEvent = {};
		if(typeof(option.onEvent.open) == "undefined") option.onEvent.open = function(){};
		if(typeof(option.onEvent.close) == "undefined") option.onEvent.close = function(){};
		if(typeof(option.onEvent.resize) == "undefined") option.onEvent.resize = function(){};
		if(typeof(option.close.bgBtn) == "undefined") option.close.bgBtn = false;
		if(typeof(option.close.className) == "undefined") option.close.className = false;

		// 事件
		var H_Close = function(){
			// 解绑
			$(window).unbind('resize', H_WindowResize);
			option.close.bgBtn && $("#" + id + "-bg").unbind('click', H_Close);
			option.close.className && $("#" + id + "-ft ." + option.close.className).unbind('click', H_Close);
			// 销毁对象
			$("#" + id + "-bg, #" + id + "-ft").fadeOut(300, function(){
				$("#" + id).remove();
			});
			// 关闭回调
			option.onEvent.close();
		}
		var H_WindowResize = function(){
			$("#" + id + "-bg").css({"width": "0px", "height": "0px"});
			$("#" + id + "-ft").css({"top": 0, "left": 0});

			if(option.locate.type == "window"){
				F_LocateWindow();
			}else if(option.locate.type == "page"){
				F_LocatePage();
			}else if(option.locate.type == "tag"){
				F_LocateTag();
			}
			var w = $(document).width(), h = $(document).height();
			$("#" + id + "-bg").css({"width": w + "px", "height": h + "px"});

			option.onEvent.resize();
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
			var t_w = option.locate.target.object.innerWidth(), t_h = option.locate.target.object.innerHeight();
			var t_x = option.locate.target.object.offset().left, t_y = option.locate.target.object.offset().top;
			var x = 0, y = 0;

			if(c_x == "left") x = t_x;
			else if(c_x == "middle") x = t_x + t_w / 2;
			else if(c_x == "right") x = t_x + t_w;
			else switch(option.locate.target.origin){
				case "mt": case "mm": case "mb": x = t_x + t_w / 2 + c_x; break;
				case "rt": case "rm": case "rb": x = t_x + t_w + c_x; break;
				case "lt": case "lm": case "lb": default: x = t_x + c_x; break;
			}

			if(c_y == "top") y = t_y;
			else if(c_y == "middle") y = t_y + t_h / 2;
			else if(c_y == "bottom") y = t_y + t_h;
			else switch(option.locate.target.origin){
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
		var z_index = $.fn.dyMaxZindex("*");
		var id = "dy-dialog-" + parseInt(Math.random() * 1000);
		$("body").append("<div id='" + id + "'><div id='" + id + "-ft'>" + option.content + "</div><div id='" + id + "-bg'></div></div>");
		$("#" + id + "-bg").css({"top":"0", "left":"0", "position":"absolute", "z-index":(z_index + 1), "background-color":"#DDDDDD", "opacity":"0.6"});
		$("#" + id + "-bg").css(option.bgStyle);

		// 定位
		var c_x = option.locate.x, c_y = option.locate.y, c_o = option.locate.origin;
		c_x = isNaN(c_x) ? c_x : (c_x * 1);
		c_y = isNaN(c_y) ? c_y : (c_y * 1);
		$("body").append();
		if(option.locate.type == "window"){
			$("#" + id + "-ft").css({"position":"fixed", "z-index":(z_index + 2)});
		}else if(option.locate.type == "page"){
			$("#" + id + "-ft").css({"position":"absolute", "z-index":(z_index + 2)});
		}else if(option.locate.type == "tag"){
			$("#" + id + "-ft").css({"position":"absolute", "z-index":(z_index + 2)});
		}

		// 绑定
		option.close.bgBtn && $("#" + id + "-bg").bind('click', H_Close);
		option.close.className && $("#" + id + "-ft ." + option.close.className).bind('click', H_Close);
		$(window).bind('resize', H_WindowResize);

		// 展现
		H_WindowResize();
		$("#" + id + "-bg, #" + id + "-ft").css("display", "none").fadeIn(300);
		// 回调
		option.onEvent.open();
	},
	// 文件上传
	dyUploadFile: function(file, option){
		var xhr = new XMLHttpRequest();
		xhr.open('POST', option.url);

		xhr.upload.onprogress = option.onEvent.progress;
		xhr.onreadystatechange = function(){
			if(xhr.readyState == 4 && xhr.status == 200){
				if(option.dataType.toUpperCase() == "JSON"){
					try{
						var r = eval("(" + xhr.responseText + ")");
						option.onEvent.success(r);
					}catch(e){
						option.onEvent.error(e);
					}
				}
			}
		};

		var formData = new FormData();
		formData.append(option.name, file);
		xhr.send(formData);
	}
});
