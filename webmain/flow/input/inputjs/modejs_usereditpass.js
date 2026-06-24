

$.usereditpass 	 = function(d, d2, tobj){
	var s = '';
	s+='<div class="btn-group"  style="width:260px;">';
	s+='<input class="input" placeholder="新的密码" autocomplete="off" style="flex:1" id="newpassedit">';
	s+='<button id="usereditpass_btnv" class="webbtn webbtn-default">随机生成</button>';
	s+='</div>';
	s+='<div style="color:gray;font-size:12px">修改后，用户登录后也需在自行修改</div>';
	js.tanbody('usereditpass','修改['+d.name+']的密码',300,200,{
		html:'<div style="padding:10px">'+s+'</div>',
		btn:[{text:'确定修改'}]
	});
	$('#usereditpass_btn0').click(function(){
		var val = get('newpassedit').value;
		if(!val)return;
		if(val.length<4){js.msgerror('密码必须4位以上');return;}
		
		js.setmsg('修改中...','','msgview_usereditpass');
		this.disabled = true;
		$.ajax({
			url:js.getajaxurl('editpass','mode_user|input','flow'),
			type:'get',
			data:{pass:jm.base64encode(val),id:d.id},
			success:function(){
				js.tanclose('usereditpass');
				js.msgok('修改成功');
			}
		});
	});
	
	$('#usereditpass_btnv').click(function(){
		get('newpassedit').value = secureRandomString(12);
	});
	

	function secureRandomString(length = 16) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=';
		const charArray = new Uint8Array(length);
		window.crypto.getRandomValues(charArray);
		  
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars[charArray[i] % chars.length];
		}
		return result;
	}

}