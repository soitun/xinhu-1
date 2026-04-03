/**
*	信呼在线客服使用
*	caratename：雨中磐石(rainrock)
*	caratetime：2021-11-01 21:40:00
*	homepage:www.rockoa.com
*/

reim.kefu = {
	arrobj:{},
	loaddata:function(){
		if(!get('centshow3'))return;
		this.initreload();
	},
	initreload:function(){
		reim.ajax(reim.getapiurl('reim','rockkefu'),{}, function(ret){
			if(ret.data=='ok')reim.kefu.reload();
		});
	},
	reload:function(o1){
		$('#changetabs3').show();
		if(o1)$(o1).html(''+js.ling(12)+' 刷新中...');
		reim.ajax(reim.getapiurl('rockkefu','index'),{}, function(ret){
			reim.kefu.showdata(ret.data);
		});
	},
	showdata:function(da){
		this.arrobj={};
		if(!this.socketob && da.config)this.linkwebsocket(da.config,0);
		this.showlishi(da.history,da.zixunarr);
		this.showwait(da.waitarr);
	},
	linkwebsocket:function(conf,lx){
		this.reimconf = conf;
		clearTimeout(this.webtimes);
		if(this.ws)this.ws.close();
		if(!conf.wsurl)return;
		this.ws 	= new WebSocket(jm.base64decode(conf.wsurl));
		var me  	= this;
		this.myid 	= conf.id;
		this.ws.onopen = function(){
			this.send('{"from":"'+conf.recid+'","adminid":"'+conf.id+'","atype":"connect","sendname":"'+conf.name+'"}');
			me.socketob = true;
			if(lx==1)me.linkwebsocket(conf,2);
		};
		this.ws.onerror = function(e){
			me.socketob = false;
			me.reloadWebSocket(false);
		};
		this.ws.onmessage = function (evt){
			var ds = JSON.parse(evt.data);
			me.onmessage(ds);
		};
		this.ws.onclose = function(e){
			me.socketob = false;
			me.reloadWebSocket(false);
		};
	},
	reloadWebSocket:function(bo){
		clearTimeout(this.webtimes);
		if(!bo){
			this.webtimes=setTimeout('reim.kefu.reloadWebSocket(true)', 5*1000);
		}else{
			if(!this.socketob)this.linkwebsocket(this.reimconf,1);
		}
	},
	showonline:function(ty,id){
		var bh = ''+ty+'_'+id+'';
		var d1 = this.arrobj[bh];
		var id1 = 'lname_'+bh+'';
		if(d1 && get(id1) && d1.online==1)get(id1).style.fontWeight='bold';
	},
	showoffline:function(ty,id){
		var bh = ''+ty+'_'+id+'';
		var d1 = this.arrobj[bh];
		var id1 = 'lname_'+bh+'';
		if(d1 && get(id1))get(id1).style.fontWeight='';
		if(d1)d1.online=0;
	},
	showlishi:function(da,das){
		var ds = [],i,d1,id1,bh;
		for(i=0;i<da.length;i++){
			d1 = da[i];
			d1.receid = d1.id;
			if(d1.type=='zixun'){
				this.arrobj[''+d1.type+'_'+d1.receid+''] = d1;
			}
		}
		for(i=0;i<das.length;i++){
			d1 = das[i];
			d1.type = 'zixun';
			d1.receid = d1.id;
			bh = ''+d1.type+'_'+d1.receid+'';
			if(!this.arrobj[bh])this.arrobj[bh] = d1;
		}
		for(d1 in this.arrobj)ds.push(this.arrobj[d1]);
		js.setoption('kefulist', JSON.stringify(ds));
		reim.showhistory(reim.maindata.harr);
		for(i=0;i<ds.length;i++){
			d1  = ds[i];
			id1 = 'lname_'+d1.type+'_'+d1.id+'';
			if(get(id1) && d1.online==1)get(id1).style.fontWeight='bold';
		}
	},
	onmessage:function(d){
		//console.log(d);
		var lx=d.type;
		if(lx=='chehui'){
			$('#qipaocont_mess_'+d.messid+'').html(js.getmsg(jm.base64decode(d.mess),'green'));
			this.reload();
		}
		if(lx=='zixun' || lx=='rewait'){
			this.reload();
			if(d.title){
				var d1 = {
					gid:d.sendid,
					title:jm.base64decode(d.title),
					gname:d.gname,
					cont:d.mess,
					type:'zixun',
					face:d.face,
					sound:'web/res/sound/todo.ogg'
				};
				reim.receivechat(d1);
			}
		}
		if(lx=='zxoff'){
			this.showoffline('zixun', d.zixunid);
		}
		if(lx=='kftokf'){
			this.reload();
			var d1 = {
				gid:d.zxid,
				title:jm.base64decode(d.title),
				gname:d.gname,
				cont:d.mess,
				type:'zixun',
				face:d.face,
				sound:'web/res/sound/todo.ogg'
			};
			reim.receivechat(d1);
		}
	},
	showwait:function(ds){
		var i,s,d1;
		$('#kefulistwait').html('');
		for(i=0;i<ds.length;i++){
			d1 = {
				'name':ds[i].name,
				'type':'wait',
				'receid':ds[i].id,
				'cont':ds[i].cont,
				'face':ds[i].face,
				'online':ds[i].online,
				'subname':ds[i].subname,
				'qian':'wait',
				'stotal':ds[i].stotal
			}
			this.arrobj[''+d1.type+'_'+d1.receid+''] = d1;
			s = reim.showhistorys(d1,false, false, true);
			$('#kefulistwait').append(s);
			if(d1.online==1)this.showonline(d1.type,d1.receid);
		}
		$('#kefulistwait').append('<div align="center" style="padding:10px;"><a onclick="reim.kefu.reload(this)" style="font-size:12px;color:#bbbbbb" href="javascript:;"><i class="icon-refresh"></i> 刷新咨询</a></div>');
		reim.showbadge('wait');
	},
	openwait:function(d){
		var str = ''+d.name+'';
		if(d.subname)str+='<span style="color:'+maincolor+';font-size:10px">@'+d.subname+'</span>';
		js.confirm('确定要解答“'+str+'”此用户问题吗？', function(jg){
			if(jg=='yes')reim.kefu.openwaits(d);
		});
	},
	openwaits:function(d){
		$('#history_wait_'+d.receid+'').remove();
		this.arrobj[''+d.type+'_'+d.receid+''] = false;
		reim.showbadge('wait');
		js.loading('加入中...');
		reim.ajax(reim.getapiurl('rockkefu','addzixun'),{sid:d.receid}, function(ret){
			reim.kefu.reload();
			js.unloading();
			reim.changetabs(0);
			reim.showbadge('wait');
			reim.openchat('zixun',d.receid,d.name,d.face);
		});
	},
	showuser:function(sid){
		this.nowsid = sid;
		js.tanbody('zhuandivkefu','咨询人员信息',350,200,{
			html:'<div id="zhuandivkefu" style="height:300px;overflow:hidden;position:relative"><div align="center" style="padding:50px"><img src="images/mloading.gif"></div></div>'
		});
		reim.ajax(reim.getapiurl('rockkefu','getzxinfo'),{sid:this.nowsid},function(ret){
			reim.kefu.showzxinfo(ret.data);
		});
	},
	showzxinfo:function(ret){
		var str = '<table style="margin:10px">',k,v,s1;
		this.prinfo = ret.prinfo;
		this.prfies = ret.prfies;
		for(k in ret.prfies){
			v = ret.prinfo[k];
			if(v==null)v='';
			s1 = '<a onclick="reim.kefu.dbleditstr(\''+k+'\')" style="font-size:13px" class="hui">'+ret.prfies[k]+'</a>';
			if(k=='web'||k=='ip'||k=='adddt'||k=='zxdt'||k=='agentna'||k=='kefu')s1=ret.prfies[k];
			str+='<tr valign="top"><td nowrap class="cursor" style="color:gray;text-align:right;padding:4px 0px">'+s1+'：</td><td style="padding:4px 0px" fields="'+k+'" class="wrap">'+v+'</td></tr>';
		}
		str+='</table>';
		$('#zhuandivkefu').html(str);
		$('#zhuandivkefu').perfectScrollbar();
		js.resizetan('confirm');
	},
	shareuser:function(sid, slx){
		changkfid = 0;
		this.nowsid = sid;
		var nae = '转给其他客服';
		if(slx==1)nae = '加更多客服';
		js.confirm('<div id="zhuandivkefu"><img src="images/mloading.gif"></div>',function(jg){
			if(jg=='yes')reim.kefu.savezhuan(slx);
		},'',nae);
		this.zshouwku(false);
	},
	dbleditstr:function(fid){
		var v = this.prinfo[fid],nam=this.prfies[fid];
		if(v==null)v='';
		js.prompt('请填写内容：','填写“'+nam+'”的值',function(jg,txt,act){
			if(jg=='yes'){
				reim.kefu.saveval(fid,txt,'zhuandivkefu');
				return false;
			}
		},v);
	},
	saveval:function(fid,val,act){
		js.setmsg('保存中...','','msgview_'+act+'');
		reim.ajax(reim.getapiurl('rockkefu','savezixun'),{fid:fid,val:val,sid:this.nowsid},function(ret){
			reim.kefu.prinfo[fid] = val;
			$('#zhuandivkefu').find('td[fields="'+fid+'"]').html(val);
			js.setmsg('','','msgview_'+act+'');
		},'post');
	},
	zshouwku:function(d){
		if(!d){
			reim.ajax(reim.getapiurl('rockkefu','getkefu'),{sid:this.nowsid},function(ret){reim.kefu.zshouwku(ret.data);});
		}else{
			this.kefuarr = d;
			var i,len=d.length,a1,str='';
			for(i=0;i<len;i++){
				str+='<label><input type="radio" name="qhkefu" onclick="changkfid=this.value" value="'+d[i].id+'"><img src="'+d[i].face+'" align="absmiddle" width="20" height="20" style="border-radius:50%">'+d[i].name+'';
				if(d[i].ranking)str+='<font color=gray>('+d[i].ranking+')</font>';
				str+='</label>&nbsp;&nbsp;';
			}
			$('#zhuandivkefu').html(str);
			js.resizetan('confirm');
		}
	},
	savezhuan:function(slx){
		if(changkfid==0)return;
		js.loading('处理中...');
		reim.ajax(reim.getapiurl('rockkefu','savekefu'),{sid:this.nowsid,tid:changkfid,slx:slx},function(ret){
			js.msgok(ret.data);
		},'get',function(err){
			js.msgerror(err.msg);
		});
	}
}