/**
	rockqueue 下载文件
	caratename：chenxihu
	caratetime：2104-04-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.rockoa.com
*/

(function ($) {
	
	
	$.downfile	= function(da){
		var url = da.url;
		if(!url)return;
		if(da.obj && clientbool && window.rockclient && rockclient.downFile){
			var obj  = $(da.obj).parent();
			obj.html('下载中...');
			rockclient.downFile({
				url:url,
				filename:da.filename
			}, function(ret,err){
				if(err){obj.html(err.msg);return;}
				if(ret.bili==100){
					js.setoption('fileinfo'+da.fileid+'', ret.path);
					obj.html(strformat.filestrlx(da.fileid, da.otype));
				}else{
					obj.html('下载中('+ret.bili+'%)...');
				}
			});
		}else{
			js.location(url);
		}
		
	};
	
	$.downfileneidata = {};
	
	$.downfilenei = function(da){
		if(da.obj && da.type==1){
			var obj  = $(da.obj).parent();
			obj.html('下载中...');
			var url = da.url;
			if(url.substr(0,4)!='http')url = NOWURL+url;
			url = jm.base64encode(url);
			$.downfileneidata[url] = {
				obj:obj,
				fileid:da.id,
				otype:da.otype
			};
		}
	}
	
	$.downfilecall = function(ret){
		var da = $.downfileneidata[ret.url];
		if(da){
			var obj  = da.obj;
			var state = ret.state;
			if(state=='ok'){
				js.setoption('fileinfo'+da.fileid+'', ret.path);
				obj.html(strformat.filestrlx(da.fileid, da.otype));
			}
			if(state=='cancel'){
				obj.html('下载失败 '+strformat.filestrlx(da.fileid, da.otype));
			}
			if(state=='change'){
				obj.html('下载中('+ret.bili+'%)...');
			}
		}else{
			var state = ret.state;
			if(state=='ok'){
				var nr = '“'+ret.filename+'”下载完成';
				if(js.showtodo){
					js.showtodo.todo({
						title:'文件下载',
						body:nr,
						path:ret.path,
						click:function(){
							rockclient.rockFun('openFolder', {
								pathbase64:this.path
							});
							return true;
						}
					});
				}else{
					js.msg('success', nr);
					get('msgshowdivla').onclick=function(){
						rockclient.rockFun('openFolder', {pathbase64:ret.path});
					};
				}
			}
			if(state=='cancel'){
				js.msg('msg', '“'+ret.filename+'”下载失败');
			}
		}
	}
	
})(jQuery);