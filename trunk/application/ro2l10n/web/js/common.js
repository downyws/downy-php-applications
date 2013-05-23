$(function(){
	// ��ʾ��
	$("body").on("click", ".notification .close", function(){
		$(this).parent().slideUp(300, function(){
			$(this).remove();
		});
	});
	// ɾ����ʾ
	$(".delConfirm").each(function(){
		$(this).data("href", $(this).attr("href"));
		$(this).attr("href", "javascript:;");
		$(this).click(function(){
			var that = this;
			$(".msgdialog").html($(this).data("confirm")).dialog({
				modal: true, buttons: {
					Ok: function(){
						window.location.href = $(that).data("href");
					},
					Cancel: function(){
						$(this).dialog("close");
					}
				}
			});
		});
	});
	// ʱ��
	$(".jq-time").each(function(){
		$(this).datetimepicker({
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm:ss"
		});
	});
});

$.fn.extend({
	// ��ʾ��
	notificationShow: function(object, message, type){
		var html = "<div class='notification " + type + "'><a class='close' href='javascript:;'><img src='/images/cross_grey_small.png' /></a><div>" + message + "</div></div>";
		object.append(html);
	}
});
