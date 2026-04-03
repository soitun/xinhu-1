//流程模块【flowelement.表单元素管理】下录入页面自定义js页面,初始函数
function initbodys(){
	c.fieldshide('xiaoshu');
	if(mid==0){
		form('mid').value = js.request('mkid');
		if(!form('mid').value){
			js.msgerror('没有选择模块，无法新增');
			c.formdisabled();
			return;
		}
	}
	form('attr').readOnly=false;
	form('fields').readOnly=false;
	form('dev').readOnly=false;
	
	c.onselectdata['attr']=function(sav,sna,sid){
		form('attr').value = sid;
	}
	c.onselectdata['fields']=function(sav,sna,sid){
		if(sav.subname)form('name').value = sav.subname;
	}
	$(form('fieldstype')).change(function(){
		c.changetypes();
	});
	var s = '<button type="button" onclick="setdatabtn()" class="webbtn btn-danger btn-xs">设置数据源</button>';
	$('#div_data').append(s);
}

c.onselectdatabefore=function(fid){
	if(fid=='fields')return {mkid:form('mid').value,iszb:form('iszb').value};
}
c.changetypes=function(){
	var val = form('fieldstype').value;
	if(val=='number'){
		c.fieldsshow('xiaoshu');
	}else{
		c.fieldshide('xiaoshu');
	}
}

function changesubmit(d){
	if(d.fieldstype.indexOf('change')==0){
		if(d.data=='' || d.data==d.fields){
			form('data').value = ''+d.fields+'id';
			return '此字段元素类型时，数据源必须填写用来存储选择来的Id，请填写为：'+d.fields+'id';
		}
	}
	if(d.islu=='1' && d.fields=='id')return 'id字段是不可以做录入项字段';
}

c.xuanchangs=function(){
	var val = form('fieldstype').value;
	if(val.indexOf('change')==0){
		var cans1 = {
			idobj:form('gongsi')
		};
		js.changeuser('AltS', 'deptusercheck', '选择范围', cans1);
	}else{
		js.msg('msg','元素类型不是选择人员部门的');
	}
}


var cmode = false;
function setdatabtn(){
	var lx = form('fieldstype').value;
	var fid = form('fields').value;
	if(!lx){
		js.msg('msg','请先选择“字段元素类型”');
		return;
	}
	if(lx.indexOf('change')==0){
		if(!fid){
			js.msg('msg','请先输入“对应字段”');
			return;
		}
		form('data').value = ''+fid+'id';
		return;
	}
	cmode = false;
	var shjyx = ',text,select,selectdatafalse,selectdatatrue,radio,checkboxall,textarea,';
	if(shjyx.indexOf(','+lx+',')==-1){
		js.msg('msg','此字段类型，无需设置数据源，或者可直接输入');
		return;
	}
	
	var s = '<div class="flex"><div style="width:100px" align="right">数据源模块：</div><input readonly onclick="xuanmode(this)" style="flex:1" placeholder="-请选择模块V-" class="input"></div>';
	s+='<div class="flex" style="margin-top:15px"><div style="width:100px" align="right">数据源条件：</div><select id="modewhere" style="flex:1" class="input"><option value="">-选择条件-</option></select></div>';
	s+='<div class="flex" style="margin-top:15px"><div style="width:100px" align="right">显示内容：</div><input style="flex:1" id="modeshowname" placeholder="如:{title}" class="input"></div>';
	s+='<div class="flex"><div style="width:100px" align="right"></div><div style="flex:1" id="modeshownamediv"></div></div>';
	s+='<div class="flex" style="margin-top:15px"><div style="width:100px" align="right">存储主键字段：</div><input style="flex:1" placeholder="默认的id，不用去改" readonly ondblclick="this.readOnly=false" value="" class="input" id="modeshowval"></div>';
	s+='<div class="flex" style="margin-top:15px"><div style="width:100px" align="right">子内容显示：</div><input style="flex:1" placeholder="留空就好了" value="" class="input" id="modeshownames"  readonly ondblclick="this.readOnly=false"></div>';
	js.tanbody('databody','使用模块数据做数据源',400,300,{
		html:'<div style="overflow:auto;max-height:450px"><div style="padding:15px"><form autocomplete="off" name="dataform">'+s+'</form></div></div>',
		btn:[{text:'确定选择'}]
	});
	$('#databody_btn0').click(function(){
		setdatabtnok();
	});
}

function setdatabtnok(){
	if(!cmode)return;
	var tj = get('modewhere').value;
	if(!tj){
		js.msg('msg','请选择数据源条件');
		return;
	}
	var zd = get('modeshowname').value;
	if(!zd){
		js.msg('msg','请输入显示内容字段');
		return;
	}
	var acta = form('data').value.split(',');
	var vzd = get('modeshowval').value;
	if(!vzd)vzd='id';
	var s = 'rmod:'+cmode.num+'|'+tj+'|'+zd+'|'+vzd+'';
	vzd = get('modeshownames').value;
	if(vzd)s+='|'+vzd+'';
	if(acta[1])s+=','+acta[1]+'';
	form('data').value = s;
	js.tanclose('databody');
}

function xuanmode(o1){
	js.selectmode(o1, o1, function(sna,val,d){
		cmode = d;
		changeflowwhere(d.id);
	});
}

function changeflowwhere(id1){
	var o1 = get('modewhere');
	o1.length = 1;
	$('#modeshownamediv').html('');
	js.ajax(geturlact('modewhere'),{modeid:id1},function(ret){
		js.setselectdata(o1, ret.wheredata, 'num');
		var farr = ret.fieldsarr,s='';
		for(var i=0;i<farr.length;i++){
			s+='<label><input type="checkbox" onclick="changeziduansv(this)" name="selfieldsabc" value="{'+farr[i].fields+'}">'+farr[i].name+'('+farr[i].fields+')</label>&nbsp;&nbsp;';
		}
		$('#modeshownamediv').html(s);
		js.resizetan('databody');
	}, 'get,json');
}

function changeziduansv(){
	var val = js.getchecked('selfieldsabc');
	val = val.replace(/[\,]/gi,'');
	get('modeshowname').value = val;
}

js.selectmode = function(obj, naobj, fun){
	this.chajian('rockselect', {
		viewobj:obj,
		num:'getmodearr',limit:20,
		url:js.getajaxurl('getmodearr','flow','main'),
		onitemclick:function(sna,val,d){fun(sna,val,d)},
		ondatachuli:function(da){
			var len=da.length,i,csd,types='',ds=[],dt=[];
			for(i=0;i<len;i++){
				csd = da[i];
				if(types!=csd.type){
					ds.push({name:csd.type,style:'font-weight:bold',disabled:true});
					dt.push({name:csd.type,value:csd.type,type:csd.type});
				}
				types = csd.type;
				csd.padding='24';
				ds.push(csd);
			}
			this.setSelectData(dt,'所有分类', 'type');
			return ds;
		},
		nameobj:naobj
	});
}