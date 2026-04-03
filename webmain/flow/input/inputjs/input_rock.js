/**
*	公共使用的录入js
*/


function oninputblur(name,zb,obj,zbxu,row){};
function initbodys(){};

function geturlact(act,cns){
	var url=js.getajaxurl(act,'mode_'+modenum+'|input','flow',cns);
	return url;
}

function initbody(){
	initbodys();
}

var c = {
	
	//可重写的方法
	onselectdata:{},
	onselectdataall:function(){},
	changeuser_before:function(){},
	onselectdatabefore:function(){},
	htmlediteritems:function(){},
	uploadback:function(){},
	uploadfileibefore:function(){},
	onselectmap:function(){},
	
	notfun:function(){
		js.msg('msg','不支持');
	},
	downshow:function(sid){
		js.fileopt(sid, 0);
	},
	uploadfilei:function(sna,ssi){
		this.upload(sna,0);
	},
	initupss:function(sna){
		this.upload(sna, 1);
	},
	upload:function(sna,lx){
		js.chajian('rockcfile',{
			sna:sna,
			openlx:openlx,
			modenum:modenum,
			modexl:lx,
			mid:mid,
			c:this
		})
	},
	save:function(){
		var da = js.getformdata(),i,len=arr.length,f,val;
		var btn= get('AltS');
		for(i=0;i<len;i++){
			f = arr[i];
			if(f.islu=='1' && f.isbt=='1'){
				val = da[f.fields];
				if(isempt(val)){
					js.setmsg('“'+f.name+'”不能为空');
					return;
				}
			}
		}
		btn.disabled = true;
		js.setmsg('保存中...');
		js.ajax(''+saveurl+'&openlx='+openlx+'&num='+modenum+'&mid='+mid+'', da, function(ret){
			if(ret.success){
				js.setmsg('保存成功','green');
				c.formdisabled();
			}else{
				btn.disabled = false;
			    js.setmsg(ret.msg);
			}
		}, 'post,json', function(msg){
			btn.disabled = false;
			js.setmsg(msg);
		});
	},
	getselobj:function(fv){
		var o = form(fv);
		if(!o)return;
		var o1= o.options[o.selectedIndex];
		return o1;
	},
	getselattr:function(fv,art){
		var o1 = this.getselobj(fv);
		if(!o1)return '';
		return $(o1).attr(art);
	},
	formdisabled:function(){
		$('form').find('*').attr('disabled', true);
		$('#fileupaddbtn').remove();
	},
	setfields:function(fid,na){
		if(ismobile==1)na=''+na+':'
		$('#div_'+fid+'').parent().prev().text(na);
	},
	fieldshide:function(fid){
		var o = $('#div_'+fid+'').parent();
		o.hide();
		o.prev().hide();
	},
	fieldsshow:function(fid){
		var o = $('#div_'+fid+'').parent();
		o.show();
		o.prev().show();
	},
	showviews:function(o1){
		js.imgview(o1,true);
	},
	
	uploadimgclear:function(fid){
		get('imgview_'+fid+'').src='images/noimg.jpg';
		form(fid).value='';
	},
	inputblur:function(o1,zb){
		var nae = o1.name;
		oninputblur(nae,zb, o1);
	},
	selectdata:function(){
		this.notfun();
	},
	selectdataclear:function(){
		this.notfun();
	},
	relevant:function(){
		this.notfun();
	},
	autograph:function(){
		this.notfun();
	}
}

js.changeuser=function(){
	c.notfun();
}

js.changeclear=function(){
	c.notfun();
}

js.fileopt = function(fid,lx){
	js.msgerror('不支持');
}