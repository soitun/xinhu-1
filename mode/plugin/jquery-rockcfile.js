/**
	edittable 选择相关插件
	caratename：chenxihu
	caratetime：2026-03-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.rockoa.com
*/

(function ($) {
	if(typeof(rockcfiledata)=='undefined')rockcfiledata = {};
	
	function rockcfile(cans){
		var me 		= this;
		var defv = js.applyIf(cans,{
			ctype:'',
			isedit:1,
			openlx:0,
			sna:'',
			filearr:{},
			modenum:'',
			mid:0,
			modexl:0, //0和1
			onchangebefore:function(){},
			uploadback:function(){}
		});
		for(var i in defv)this[i] = defv[i];
		
		
		this.clickstr = function(act,val){
			return 'rockcfiledata.'+this.sna+'.'+act+'('+val+')';
		}
		
		this.init = function(){
			rockcfiledata[this.sna]  = this;
			this.initupss(this.sna);
			this.click();
		}
		
		this.click=function(){
			if(this.upfbo){js.msg('msg','请等待上传完成在添加');return;}
			var obj = get('filed_'+this.sna+'_inp');
			if(!obj){
				js.msg('msg','无效了');
				return;
			}
			if(this.modexl==0)obj.click();//需要点击
			
			var upurl = '';
			if(this.openlx==4)upurl = '?d=ye&a=saveup&sysmodenum='+this.modenum+'&sysmid='+this.mid+'&sysuptype=file&openlx='+this.openlx+'';
			this.initupobj.setupurl(upurl);
		}
		
		this.initupss = function(sna){
			var o,o1,tsye,uptp='image',tdata;
			o1 = get('filed_'+sna+'');tsye=$(o1).attr('tsye');tdata=$(o1).attr('tdata');
			if(tsye=='file'){
				uptp='*';
				if(!isempt(tdata))uptp=tdata;
			}
			this.initupobj = $.rockupload({
				'inputfile':'filed_'+sna+'_inp',
				'initremove':false,'uptype':uptp,'formming':sna,
				'urlparams':{'sysmodenum':this.modenum,'sysmid':this.mid,'sysuptype':tsye},
				'oparams':{sname:sna,snape:tsye},
				'onsuccess':function(f,gstr){
					var sna= f.sname,tsye=f.snape,d=js.decode(gstr);
					if(tsye=='img'){
						get('imgview_'+sna+'').src = d.filepath;
						form(sna).value=d.filepath;
						me.upimages(sna,d.id,false, d.autoup);
					}else if(tsye=='file'){
						$('#meng_'+me.uprnd+'').remove();
						$('#up_'+me.uprnd+'').attr('upid_'+sna+'',d.id);
						me.upfbo = false;
						me.filearr['f'+d.id+''] = f;
						me.showupid(sna);//显示ID	
					}
					me.uploadback(sna, f);
					if(this.changenext)this.changenext();//上传下一个文件
				},
				'onprogress':function(f,bl){
					var sna= f.sname,tsye=f.snape;
					if(tsye=='file'){
						$('#meng_'+me.uprnd+'').html(''+bl+'%');
					}
				},
				onchange:function(f){
					var sna= f.sname,tsye=f.snape;
					if(tsye=='file'){
						var flx = js.filelxext(f.fileext);
						me.uprnd = js.getrand();
						me.upfbo = true;
						var s='<div onclick="'+me.clickstr('clickupfile','this,\''+sna+'\'')+'" id="up_'+me.uprnd+'" title="'+f.filename+'('+f.filesizecn+')"  class="upload_items">';
						if(f.isimg){
							s+='<img class="imgs" src="'+f.imgviewurl+'">'
						}else{
							s+='<div class="upload_items_items"><img src="web/images/fileicons/'+flx+'.gif" alian="absmiddle"> ('+f.filesizecn+')<br>'+f.filename+'</div>';
						}
						s+='<div id="meng_'+me.uprnd+'" class="upload_items_meng" style="font-size:16px">0%</div></div>';
						$('#'+sna+'_divadd').before(s);
					}else if(tsye=='img'){
						js.loading('上传中...');
					}
				},
				onerror:function(estr){
					me.upfbo = false;
					js.msg('msg',estr);
				},
				onchangebefore:function(){
					me.onchangebefore(this);
				}
			});
		}
		
		this.showupid=function(sna){
			var os = $('div[upid_'+sna+']'),fvid='';
			for(var i=0;i<os.length;i++){
				fvid+=','+$(os[i]).attr('upid_'+sna+'')+'';
			}
			if(fvid!='')fvid=fvid.substr(1);
			form(sna).value=fvid;
		}
		
		this.clickupfile=function(o1,sna, xs){
			if(!o1)o1 	= this.yuobj;
			this.yuobj 	= o1;
			var o 		= $(o1);
			var fid 	= o.attr('upid_'+sna+'');
			if(isempt(fid))return;
			var f = this.filearr['f'+fid+''];if(!f)return;
			if(this.isedit==0 || xs){
				js.alertclose();
				js.fileopt(fid,0);
			}else{
				var fileext = f.fileext,oflx=',doc,docx,ppt,pptx,xls,xlsx,',s1='';
				
				s1+='<a style="color:blue" href="javascript:;" onclick="js.alertclose();js.downshow('+fid+',\'abc\')">下载</a>';
				s1+='&nbsp; <a style="color:blue" href="javascript:;" onclick="'+me.clickstr('clickupfile','false,\''+sna+'\',true')+'">预览</a>';
				if(oflx.indexOf(','+fileext+',')>-1)s1+='&nbsp; <a style="color:blue" href="javascript:;" onclick="js.alertclose();js.fileopt('+fid+',2)">在线编辑</a>';
				if(this.openlx==4)s1='';
				
				js.confirm('确定要<font color=red>删除文件</font>：'+o1.title+'吗？'+s1+'',function(jg){
					if(jg=='yes'){
						o.remove();
						me.showupid(sna);
						if(!f.xuanbool)$.get(js.getajaxurl('delfile','upload','public',{id:fid,mid:me.mid,num:me.modenum}));
					}
				});
			}
		},
		
		this.upimages=function(fid,fileid,bs, lbu){
			if(!bs){
				if(lbu!=1){js.unloading();return;}
				js.loading('等待上传完成...');
				setTimeout(function(){me.upimages(fid, fileid, true);},3000);
			}else{
				js.ajax('api.php?m=login&a=upimagepath',{fileid:fileid,fid:fid},function(ret){
					js.unloading();
					var da = ret.data;
					if(da.path)form(da.fid).value=da.path;
				},'get,json');
			}
		}
	}

	$.rockcfile	= function(cans){
		if(rockcfiledata[cans.sna]){
			rockcfiledata[cans.sna].click();
		}else{
			var funcls = new rockcfile(cans);
			funcls.init();
			return funcls;
		}
	};
	
})(jQuery);