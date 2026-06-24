<?php if(!defined('HOST'))die('not access');?>
<script >
$(document).ready(function(){
	
	var barr = {};
	var c={
		init:function(){
			$(form('blackip','safeform')).blur(c.blurinput);
			$(form('whiteip','safeform')).blur(c.blurinput);
			
			js.ajax(js.getajaxurl('safeget','{mode}','{dir}'),{},function(ret){
				c.showmodedata(ret);
			},'get,json');
		},
		blurinput:function(){
			var val = strreplace(this.value);
			if(val)val = val.replace(/\.\*/gi,'');
			this.value = val;
		},
		savecog:function(o1){
			var msgid = 'msgview_safeset';
			var da = js.getformdata('safeform');
			js.setmsg('保存中...','', msgid);
			o1.disable = true;
			js.ajax(js.getajaxurl('safesave','{mode}','{dir}'), da, function(s){
				o1.disable = false;
				if(s=='ok'){
					js.setmsg('保存成功','green', msgid);
				}else{
					js.setmsg(s,'', msgid);
				}
			},'post');
		},
		showmodedata:function(ret){
			for(var i in ret)if(form(i, 'safeform'))form(i, 'safeform').value = ret[i];
			
		}
	};
	js.initbtn(c);
	c.init();
	
});
</script>

<div align="left">
<div  style="padding:10px;">
		<form name="safeform">
		
		<table cellspacing="0" width="700" border="0" cellpadding="0">
		
		<tr>
			<td  colspan="4"><div style="border-radius:5px" class="inputtitle">安全设置
			<div style="padding:5px;line-height:18px;font-size:12px;color:#888888">也可以自己打开配置文件(config/iplist.php)来修改，因为系统固定人员使用，建议进行设置</div>
			</div></td>
		</tr>
		
	
		<tr>
			<td  align="right" width="20%" width="180">我当前IP：</td>
			<td class="tdinput"  width="30%" height="30"><?=$rock->ip?></td>
		
			<td  align="right"  width="20%" ></td>
			<td class="tdinput"  width="30%"></td>
		</tr>
		
		<tr>
			<td  align="right">访问IP黑名单：</td>
			<td class="tdinput" colspan="3">
				<textarea style="width:95%" name="blackip" class="textarea"></textarea>
				<div style="font-size:12px;color:#888888">多个,分开如：127.0.0.1,192.168.1.100，也可以写192.168.1这样就是限制192.168.1.*所有的</div>
			</td>
		</tr>
		
		<tr>
			<td  align="right">访问IP白名单：</td>
			<td class="tdinput" colspan="3">
				<textarea style="width:95%;height:60px" placeholder="多个,分开" name="whiteip" class="textarea"></textarea>
				<div style="font-size:12px;color:#888888">黑名单设置*，在设置白名单才能生效</div>
			</td>
		</tr>
		
		<tr>
			<td  align="right">可访问的区域：</td>
			<td class="tdinput" colspan="3">
				<textarea style="width:95%;height:60px" placeholder="多个,分开，如厦门,上海" name="whitecity" class="textarea"></textarea>
				<div style="font-size:12px;color:#888888">白名单内不受这个的影响，内网IP不限制</div>
			</td>
		</tr>
		
		<tr>
			<td  align="right">多次访问限制：</td>
			<td class="tdinput" colspan="3">
				<input name="gaptime" type="number" onfocus="js.focusval=this.value" onblur="js.number(this)" min="0" max="10" value="0" style="width:50px" class="form-control">秒内，
				限制访问不超过<input name="gapnums" type="number" value="0" style="width:50px" class="form-control">次
				<div style="font-size:12px;color:#888888">白名单内不受这个的影响，0次不限制</div>
			</td>
		</tr>
		
		
		<tr>
			<td  align="right"></td>
			<td style="padding:15px 0px" colspan="3" align="left"><button click="savecog" class="btn btn-success" type="button"><i class="icon-save"></i>&nbsp;保存</button>&nbsp;<span id="msgview_safeset">
		</td>
		</tr>
		
		</table>
		</form>
</div>
</div>