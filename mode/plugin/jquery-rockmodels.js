/**
*	rockmodelmsg 模式窗口
*	caratename：rainrock
*	caratetime：2014-05-13 21:40:00
*	email:admin@rockoa.com
*	homepage:www.rockoa.com
*/

(function ($) {
	
	//模式提示
	$.rockmodelmsg  = function(lx, txt, sj,fun){
		clearTimeout($.rockmodelmsgtime);
		$('#rockmodelmsg').remove();
		js.msg('none');
		if(!fun)fun=function(){};
		if(lx=='none')return;
		var s = '<div id="rockmodelmsg" onclick="$(this).remove()" align="center" style="position:fixed;left:45%;top:30%;z-index:9999;border-radius:10px; background:rgba(0,0,0,0.5);color:white;min-width:80px"><div style="padding:30px;">';
		if(lx=='wait'){
			if(!txt)txt='处理中...';
			s+='<div><i style="height:35px;width:35px" class="rock-loading"></i></div>';
			s+='<div style="padding-top:5px">'+txt+'</div>';
			if(!sj)sj= 60;
		}
		if(lx=='ok'){
			if(!txt)txt='处理成功';
			s+='<div style="font-size:40px">✔</div>';
			s+='<div>'+txt+'</div>';
		}
		if(lx=='msg' || !lx){
			if(!txt)txt='提示';
			s+='<div style="font-size:40px;color:red">☹</div>';
			s+='<div style="color:red">'+txt+'</div>';
		}
		s+='</div></div>';
		$('body').append(s);
		if(!sj)sj = 3;
		var le = (winWb()-$('#rockmodelmsg').width())*0.5;
		var te = (winHb()-$('#rockmodelmsg').height())*0.5-10;
		$('#rockmodelmsg').css({'left':''+le+'px','top':''+te+'px'});
		$.rockmodelmsgtime = setTimeout(function(){
			$('#rockmodelmsg').remove();
			fun();
		}, sj*1000);
	}
	js.msgok	= function(msg,fun,sj){
		$.rockmodelmsg('ok', msg,sj, fun);
	};
	js.msgerror	= function(msg,fun,sj){
		$.rockmodelmsg('msg', msg,sj, fun);
	};
	js.loading	= function(msg,sj){
		$.rockmodelmsg('wait', msg,sj);
	};
	js.unloading= function(){
		$.rockmodelmsg('none');
	};
	
	
	js.showtodo = {
		todo:function(cans){
			var can = js.applyIf(cans,{
				body:'',
				icon:'images/logo.png',
				title:'提醒',
				tagid:0,
				width:0,
				now:'',
				click:function(){return true;}
			});
			if(can.tagid == 0)can.tagid = js.getrand();
			var sty = '';
			if(can.width>0)sty+='width:'+can.width+'px;';
			var sid = 'todoid_'+can.tagid+'';
			var s = '<div id="'+sid+'" onclick="" style="border-radius:10px;background:white;margin-top:15px;max-width:400px;margin-right:5px;position:relative" ><div style="border-radius:10px;background:var(--main-hgcolor);display:inline-block;'+sty+'" class="box cursor"><table><tr>';
			s+='<td align="center" valign="top"><div style="padding:10px;height:24px;overflow:hidden"><img src="'+can.icon+'" width="24" height="24"></div></td>';
			s+='<td><div style="padding:10px;padding-left:0">';
			s+='<div><b>'+can.title+'</b></div>';
			s+='<div style="font-size:14px;color:#888888;margin-top:5px">'+can.body.replace(/\n/gi,'<br>')+'</div>';
			if(can.now)s+='<div style="font-size:12px;color:#cccccc;margin-top:5px;text-align:right">'+can.now+'</div>';
			s+='</div></td>';
			s+='</tr></table></div>';
			s+='<span style="font-size:12px;color:#aaaaaa;position:absolute;top:2px;right:2px;display:inline-block;height:16px;width:16px;line-height:14px;text-align:center;background-color:rgba(0,0,0,0.1);color:white;border-radius:50%;" onclick="js.showtodo.tododel(\''+sid+'\',1)" class="cursor">x</span>';
			s+='</div>';
			
			var s1 = '<div style="position:fixed;z-index:999;top:5px;right:10px;max-height:'+(winHb()-20)+'px;overflow:auto" id="maintododiv" ></div>';
			if(!get('maintododiv'))$('body').append(s1);
			$('#'+sid+'').remove();
			$('#maintododiv').prepend(s);
			$('#'+sid+'').click(function(){
				var bo = can.click();
				if(bo)js.showtodo.tododel(this.id);
			});
		},
		tododel:function(sid,lx){
			$('#'+sid+'').remove();
			var obj = $('#maintododiv');
			if(!obj.html())obj.remove();
		}
		
	}
	
})(jQuery); 