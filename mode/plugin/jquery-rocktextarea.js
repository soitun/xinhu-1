/**
	edittable 单击选择插件
	caratename：chenxihu
	caratetime：2026-04-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.rockoa.com
*/

(function ($) {
	rocktextareadata = {};
	$.rocktextarea	= function(cans){
		
		let obj = cans.obj;
		let evt = cans.evt;
		let len = cans.len;
		let val = obj.value;
		let objt= $(obj);
		let temp= objt.attr('temp');
		if(!temp){
			temp = 's'+js.getrand();
			objt.attr('temp', temp);
		}
		if(!rocktextareadata[temp])rocktextareadata[temp]=cans;
		if(!get(temp)){
			var s = '<div id="'+temp+'" style="font-size:12px;color:gray">字数：<span id="zshu'+temp+'" style="font-size:12px">'+val.length+'</span>';
			if(len && len>0)s+='/'+len+'';
			s+='&nbsp;<a href="javascript:;" onclick="$.rocktextareamax(\''+temp+'\')" style="font-size:12px">※</a></div>';
			$(obj).after(s);
		}else{
			$('#zshu'+temp+'').html(''+val.length+'');
		}
	};
	
	$.rocktextareamax = function(tmp){
		let cans = rocktextareadata[tmp];
		let val  = cans.obj.value;
		let len = cans.len;
		var wid = winWb()-52;if(wid>1000)wid=1000;
		js.tanbody('maxinput','文本输入最大化', wid, 300, {
			html:'<div style="padding:10px" class="flex"><textarea onkeyup="$(\'#zshutempmaxinput\').html(this.value.length)" id="amaxinput" temp="tempmaxinput" style="flex:1;height:'+(winHb()-200)+'px;" class="textarea"></textarea></div>',
			btn:[{text:'确定'}]
		});
		var s = '<span id="tempmaxinput"  style="color:gray" align="left">字数：<span id="zshutempmaxinput">'+val.length+'</span>';
		if(len && len>0)s+='/'+len+'';
		s+='</span>';
		$('#msgview_maxinput').html(s);
		get('amaxinput').value = val;
		$('#maxinput_btn0').click(function(){
			let sval = get('amaxinput').value;
			cans.obj.value = sval;
			$('#zshu'+tmp+'').html(''+sval.length+'');
			js.tanclose('maxinput');
		});
	}
	
})(jQuery);