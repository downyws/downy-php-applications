$(function(){
	var mode_day = System.Gadget.Settings.read("MODE_DAY");
	mode_day = mode_day.split(" ");
	$("#mode_day").val(mode_day[0]);

	System.Gadget.onSettingsClosing = setClosing;

	function setClosing(event){
		if(event.closeAction == event.Action.commit){
			var mode_day = $("#mode_day").val();
			if(mode_day == ""){
				mode_day = "2013-06-26";
			}
			System.Gadget.Settings.write("MODE_DAY", mode_day + " 00:00:00");
		}
	}
});
