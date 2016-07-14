$(function(){
	var tipTask = function(url) {
		$.getJSON(url, function(tip) {
			if(tip){
				if(!tip.flag) return;
				playVoice('/skin/sound/backcash.wav', 'cash-voice');
				
				var buttons=[];
				tip.buttons.split('|').forEach(function(button){
					button=button.split(':');
					buttons.push({text:button[0], click:window[button[1]]});
				});
				
				$('<div>').append(tip.message).dialog({
					position:['right','bottom'],
					minHeight:40,
					title:'系统提示',
					buttons:buttons
				});

			}
		});
	};
	if(typeof(TIP)!='undefined' && TIP) {
		setInterval(tipTask, 10000, '/index.php/business/getTip'); //系统提示
		setInterval(tipTask, 10000, '/index.php/business/getRecharge'); //充值提示
		setInterval(tipTask, 10000, '/index.php/business/getCash'); //提现提示
	}
});