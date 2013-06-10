$(function(){
	// 全局变量
	var TIMER_POINT;
	var INDEX_MIN = 1;
	var INDEX_MAX = 80;
	var PPINT_M = -1;
	var POINT_D = -1;
	var INDEX_TODAY = -1;
	var TEST_DAY = $.fn.Time_Strtotime("2013-09-14 00:00:00");
	var MODE_DAY = System.Gadget.Settings.read("MODE_DAY");
	if(MODE_DAY == ""){
		MODE_DAY = $.fn.Time_Strtotime("2013-06-26 00:00:00");
	}else{
		MODE_DAY = $.fn.Time_Strtotime(MODE_DAY);
	}



	// 初始化
	System.Gadget.settingsUI = "settings.html";
	System.Gadget.onSettingsClosed = setClosed;	
	var html = "";
	for(var i = 0; i < 8; i++){
		for(var j = 0; j < 10; j++){
			html += "<a style='background-position:-" + (j * 2) + "00px -" + (i * 2) + "00px;'></a>";
		}
	}
	$(".m-block div").html(html);
	if($.fn.Time_Time() >= TEST_DAY){
		$(".m-title").html("2013年司法考试80天通关计划。");
	}else{
		$(".m-title").html("2013年司法考试80天通关计划，离考试只剩下<span></span>天！");
		refresh();
	}



	// 修改设置
	function setClosed(){
		clearTimeout(TIMER_POINT);
		MODE_DAY = $.fn.Time_Strtotime(System.Gadget.Settings.read("MODE_DAY"));
		refresh();
	}
	// 刷新
	function refresh(){
		var now = $.fn.Time_Time();
		if(now < TEST_DAY){
			var day;
			var sleep = (300 - now % 300) * 1000;
			TIMER_POINT = setTimeout(refresh, sleep);
			
			// 剩余天数计算
			day = parseInt((TEST_DAY - now) / 86400) + 1;
			$(".m-title span").html(day);

			// 计划计算
			day = parseInt((now - MODE_DAY) / 86400) + 1;
			if(INDEX_TODAY != day){
				INDEX_TODAY = day;
				var ml = scopeCheck((INDEX_TODAY - 2) * -200);
				$(".m-block div").animate({"margin-left": ml + "px"}, 200);
			}
		}else{
			$(".m-title").html("今天考试，早点休息~");
		}
	}
	// 边界处理
	function scopeCheck(x){
		if(x > 0){
			x = 0;
		}
		if(x < (INDEX_MAX - 3) * -200){
			x = (INDEX_MAX - 3) * -200;
		}
		return x;
	}



	// 鼠标双击
	$(".m-block").dblclick(function(){
		PPINT_M = -1;
		POINT_D = -1;
		var ml = scopeCheck((INDEX_TODAY - 2) * -200);
		$(".m-block div").animate({"margin-left": ml + "px"}, 200);
	});
	// 鼠标拖动
	$(".m-block").mousedown(function(eventObject){
		POINT_D = eventObject.screenX;
		PPINT_M = parseInt($(".m-block div").css("margin-left").replace(/px/g, ""));
	});
	$(".m-block").mousemove(function(eventObject){
		if(POINT_D > 0){
			var x = scopeCheck(PPINT_M + eventObject.screenX - POINT_D);
			$(".m-block div").css("margin-left", x + "px");
		}
	});
	$(".m-block").bind("mouseenter", function(eventObject){
		PPINT_M = -1;
		POINT_D = -1;
	});
	$(".m-block").bind("mouseup mouseleave", function(eventObject){
		PPINT_M = -1;
		POINT_D = -1;
		var x = parseInt($(".m-block div").css("margin-left").replace(/px/g, ""));
		if(x < 0){
			var v = (-1 * x) % 200;
			if(v > 100){
				x = x - (200 - v);
			}else if(v > 0){
				x = x + v;
			}
			x = scopeCheck(x);
			$(".m-block div").animate({"margin-left": x + "px"}, 200);
		}
	});
});



$.fn.extend({
	Time_Time: function(){
		return parseInt(Date.parse(new Date()) / 1000);
	},
	Time_Strtotime: function(time){
		time = time.replace(/:/g, "-");
		time = time.replace(/ /g, "-");
		time = time.split("-");
		time = new Date(Date.UTC(time[0], time[1] - 1, time[2], time[3] - 8, time[4], time[5]));
		return parseInt(time.getTime() / 1000);
	},
	Time_Data: function(format, timestamp){
		if(timestamp == ""){
			timestamp = $.fn.Time_Time();
		}
		// 时间戳转换
		var timestamp = new Date(timestamp * 1000);
		timestamp.toLocaleString();
		// 格式化
		var o = {
			"M+": timestamp.getMonth() + 1,
			"d+": timestamp.getDate(),
			"h+": timestamp.getHours(),
			"m+": timestamp.getMinutes(),
			"s+": timestamp.getSeconds(),
			"q+": Math.floor((timestamp.getMonth() + 3) / 3),
			"S": timestamp.getMilliseconds()
		}
		if(/(y+)/.test(format)) {
			format = format.replace(RegExp.$1, (timestamp.getFullYear() + "").substr(4 - RegExp.$1.length));
		}
		for(var k in o) {
			if(new RegExp("("+ k +")").test(format)) {
				format = format.replace(RegExp.$1, RegExp.$1.length==1 ? o[k] : ("00"+ o[k]).substr((""+ o[k]).length));
			}
		}
		// 返回
		return format;
	}
});
