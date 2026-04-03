/**
*	在线编辑获取内容的20250606
*/

js.plugin_rockoffice = function(conf){
	if(conf){
		this.plugin_rockofficefileid= 0;
		this.plugin_rockoffice_conf = conf;
		this.plugin_rockofficeopen();
		if(conf.erand)js.plugin_rockofficestartv();
	}
}


js.plugin_rockofficeopen = function(){
	clearInterval(js.plugin_rockofficetime);
	if(js.plugin_rockofficebool)return;
	var conf = this.plugin_rockoffice_conf;
	if(!conf)return;
	var ws 	= new WebSocket(jm.base64decode(conf.wsurl));
	ws.onopen = function(){
		this.send('{"from":"'+conf.recid+'","adminid":"'+conf.adminid+'","atype":"connect","sendname":"'+conf.adminname+'"}');
		js.plugin_rockofficebool = true;
	}
	ws.onclose = function(e){
		js.plugin_rockofficebool = false;
		js.plugin_rockofficetime = setTimeout('js.plugin_rockofficeopen()',3000);
	};
	ws.onerror = function(e){
		js.plugin_rockofficebool = false;
		js.plugin_rockofficetime = setTimeout('js.plugin_rockofficeopen()',5000);
	};
	ws.onmessage = function(evt){
		js.plugin_rockofficebool = true;
		var ds = JSON.parse(evt.data);
		js.plugin_rockofficemessage(ds);
	};
	js.plugin_rockofficews = ws;
}

js.plugin_rockofficemessage = function(d){
	var xxtype = d.xxtype;
	if(d.waitmsg)js.msg('wait',jm.base64decode(d.waitmsg));
	if(d.msg)js.msg('success',jm.base64decode(d.msg));
	if(d.xxtype=='glast'){
		js.plugin_rockofficegetfile(d.fileid);
	}
}

js.plugin_rockofficegetfile = function(fid){
	if(this.plugin_rockofficefileid == fid)return;
	this.plugin_rockofficefileid  = fid;
	$.get('api.php?m=upload&a=editfileb&fileid='+fid+'', function(s){
		js.plugin_rockoffice_conf = '';
		if(s)js.msg('success',s);
	});
}

js.plugin_rockofficestart = function(){
	var d = this.plugin_rockoffice_conf;
	if(!d)return;
	if(this.plugin_rockofficefileid == d.fileid)return;
	$.get('api.php?m=upload&a=editfilec&fileid='+d.fileid+'&erand='+d.erand+'', function(s){
		if(s=='start'){
			js.msg('wait','获取编辑文件中...');
			js.plugin_rockofficegetfile(d.fileid);
		}
		if(s=='wait'){
			js.plugin_rockofficestartv();
		}
	});
}

js.plugin_rockofficestartv = function(){
	clearTimeout(js.plugin_rockofficestarts);
	js.plugin_rockofficestarts = setTimeout('js.plugin_rockofficestart()',10*1000);
}