//2026-01-26新增的
//单据操作下自定义的js方法

var zhangarrjodedata = false;
$.fincreatejizhang 	 = function(d, d2, tobj){
	var modenum = d2.modenum;
	js.tanbody('finoptsotcks',''+d2.name+'.'+modenum+'',300,200,{
		html:'<div style="padding:10px">选择记账的财务帐号：<select id="optchangkude" class="form-control"><option value="">请选择...</option></select><br>说明：<textarea id="optchangkude1" class="form-control"></textarea></div>',
		btn:[{text:'生成'}]
	});
	$('#finoptsotcks_btn0').click(function(){
		savejizhangtemp(d, this);
	});
	if(zhangarrjodedata){
		js.setselectdata(get('optchangkude'), zhangarrjodedata, 'value');
	}else{
		js.setmsg('加载中...','','msgview_finoptsotcks');
		$.ajax({
			url:js.getajaxurl('getzhang','mode_finfybx|input','flow'),
			dataType:'json',
			success:function(ret){
				js.setmsg('','','msgview_finoptsotcks');
				zhangarrjodedata = ret;
				js.setselectdata(get('optchangkude'), zhangarrjodedata, 'value');
			}
		});
	}
	
	function savejizhangtemp(d, odi){
		var da = {accountid:get('optchangkude').value,sm:get('optchangkude1').value,id:d.id,mknum:modenum}
		if(!da.accountid){
			js.msgerror('请选择财务帐号');
			return;
		}
		odi.disabled = true;
		js.setmsg('生成中...','','msgview_finoptsotcks');
		
		$.ajax({
			url:js.getajaxurl('createjizhang','mode_finfybx|input','flow'),
			data:da,
			dataType:'json',type:'post',
			success:function(ret){
				if(ret.success){
					js.tanclose('finoptsotcks');
					js.msgok('生成成功');
					tobj.reload();
				}else{
					odi.disabled = false;
					js.setmsg(ret.msg,'red','msgview_finoptsotcks');
				}
			}
		});
	}
}