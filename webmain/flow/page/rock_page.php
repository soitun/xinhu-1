//<script>	
	var c = {
		optalign:'',
		opttype:'',
		reload:function(){
			a.reload();
		},
		clickwin:function(o1,lx){
			var id=0;
			if(lx==1)id=a.changeid;
			openinput(modename,modenum,id,'opegs{rand}');
		},
		view:function(){
			var d=a.changedata;
			if(d.id)openxiangs(modename,modenum,d.id,'opegs{rand}');
		},
		searchbtn:function(){
			this.search({});
		},
		search:function(cans){
			var s=get('key_{rand}').value,zt='';
			if(get('selstatus_{rand}'))zt=get('selstatus_{rand}').value;
			var dke   = {key:s,keystatus:zt,search_value:'',xhfields:''},szd='';
			if(get('fields_{rand}'))szd = get('fields_{rand}').value;
			if(szd){
				dke.xhfields = szd;
				dke.xhlike   = get('like_{rand}').value;
				dke.xhkeygj  = get('keygj_{rand}').value;
				if(this.soutype=='select' || this.soutype=='rockcombo')dke.xhkeygj = get('selkey_{rand}').value;
				dke.xhkeygj  = jm.base64encode(dke.xhkeygj);
				dke.key 	 = '';
			}
			var canss = js.apply(dke, cans);
			a.setparams(canss,true);
		},
		searchhigh:function(){
			new highsearchclass({
				modenum:modenum,
				oncallback:function(d){
					c.searchhighb(d);
				}
			});
		},
		searchhighb:function(d){
			d.key='';
			d.xhfields='';
			d.search_value='';
			get('key_{rand}').value='';
			a.setparams(d,true);
		},
		searchuname:function(d){
			js.getuser({
				type:'deptusercheck',
				title:'<?=lang('搜索')?>'+d.name,
				changevalue:this.search_value,
				callback:function(sna,sid){
					c.searchunames(d,sna,sid);
				}
			});
		},
		search_value:'',
		searchunames:function(d,sna,sid){
			get('key_{rand}').value=sna;
			this.search_value = sid;
			var cs = {key:'',xhfields:'','search_fields':d.fields,'search_value':sid};
			a.setparams(cs,true);
		},
		daochu:function(o1,lx,lx1,e){
			new publicdaochuobj({
				'objtable':a,
				'modename':modename,
				'fieldsarr':fieldsarr,
				'modenum':modenum,
				'modenames':modenames,
				'isflow':isflow,
				'btnobj':o1
			});
		},
		getacturl:function(act){
			return js.getajaxurl(act,'mode_'+modenum+'|input','flow',{'modeid':modeid});
		},
		changatype:function(o1,lx){
			$("button[id^='changatype{rand}']").removeClass('active');
			$('#changatype{rand}_'+lx+'').addClass('active');
			a.setparams({atype:lx},true);
			var tit = $(o1).html();if(tit.indexOf(modename)<0)tit=modename+'('+tit+')';
			nowtabssettext(tit);
		},
		init:function(){
			$('#key_{rand}').keyup(function(e){
				if(e.keyCode==13)c.searchbtn();
			});
			$('#keygj_{rand}').keyup(function(e){
				if(e.keyCode==13)c.searchbtn();
			});
			this.initpage();
			this.soudownobj = $('#downbtn_{rand}').rockmenu({
				width:120,top:35,donghua:false,
				data:[{name:'<?=lang('高级搜索')?>',lx:0}],
				itemsclick:function(d, i){
					if(d.lx==0)c.searchhigh();
					if(d.lx==1)c.printlist();
					if(d.lx==2)c.setfieldslist();
					if(d.lx==3)c.searchuname(d);
				}
			});
			$('#fields_{rand}').change(function(){
				c.changefields();
			});
		},
		initpage:function(){
			
		},
		initpagebefore:function(){
			
		},
		onloadbefore:function(){},
		oncolumns:function(d){return d},
		loaddatabefore:function(d){
			var das = d.listinfo;
			if(das){
				isflow    = das.isflow;
				modeid    = das.modeid;
				modename  = das.modename;
				modenames = das.modenames;
				fieldsarr = das.fieldsarr;
				this.fieldzarr = das.fieldzarr; //子表搜索
				fieldsselarr = das.fieldsselarr;
				chufarr = das.chufarr;
				if(das.modetable)a.setCans({tablename:das.modetable});
				this.initcolumns(true);
			}
			this.onloadbefore(d);
		},
		loaddata:function(d){
			this.setdownsodata(d.souarr);
			if(d.modeid)modeid = d.modeid;
			if(modeid>101 && d.loadci==1 && (!d.atypearr || d.atypearr.length==0))js.confirm('<?=lang('notcolumns','base')?>',function(){window.open('<?=URLY?>view_columns.html')});
			if(!d.atypearr)return;
			var addobj = get('addbtn_{rand}');
			addobj.disabled=(d.isadd!=true);
			get('daobtn_{rand}').disabled=(d.isdaochu!=true);
			if(d.isdaochu)$('#daobtn_{rand}').show();
			if(d.isdaoru)$('#daoruspan_{rand}').show();
			var d1 = d.atypearr,len=d1.length,i,str='';
			for(i=0;i<len;i++){
				str+='<button class="btn btn-default" click="changatype,'+d1[i].num+'" id="changatype{rand}_'+d1[i].num+'" type="button">'+d1[i].name+'</button>';
			}
			$('#changatype{rand}').html(str);
			$('#changatype{rand}_'+atype+'').addClass('active');
			js.initbtn(c);
			if(d.isadd){
				addobj.title = '<?=lang('右键可新窗口打开新增')?>';
				$(addobj).on('contextmenu', function(e) {
					e.preventDefault();
					js.open('?a=lu&m=input&d=flow&num='+modenum+'&callback=opegs{rand}',950,500);
				});
			}
		},
		setdownsodata:function(darr){
			var ddata = [{name:'<?=lang('高级搜索')?>',lx:0}],dsd,i;
			if(darr)for(i=0;i<darr.length;i++){
				dsd = darr[i];
				dsd.lx=3;
				ddata.push(dsd);
			}
			if(admintype==1)ddata.push({name:'<?=lang('自定义列显示')?>',lx:2});
			ddata.push({name:'<?=lang('打印')?>',lx:1});
			this.soudownobj.setData(ddata);
		},
		setcinfo:{},
		setcolumns:function(fid, cnas){
			this.setcinfo[fid]=cnas;
			var d = false,i,ad=bootparams.columns,len=ad.length,oi=-1;
			for(i=0;i<len;i++){
				if(ad[i].dataIndex==fid){
					d = ad[i];
					oi= i;
					break;
				}
			}
			if(d){
				d = js.apply(d, cnas);
				bootparams.columns[oi]=d;
			}
		},
		daoru:function(){
			window['managelist'+modenum+''] = a;
			addtabs({num:'daoru'+modenum+'',url:'flow,input,daoru,modenum='+modenum+'',icons:'plus',name:'<?=lang('导入')?>'+modename+''});
		},
		initcolumns:function(bots){
			var num = 'columns_'+modenum+'_'+pnum+'',d=[],d1,d2={},i,len=fieldsarr.length,ebo,bok,sa=[{name:'<?=lang('默认搜索')?>',fields:'','inputtype':'dev'}];
			var nstr= fieldsselarr[num];if(!nstr)nstr='';
			if(nstr)nstr=','+nstr+',';
			if(nstr=='' && isflow>0){
				var nes = chufarr.base_name;if(!nes)nes='<?=lang('申请人')?>';
				d.push({text:nes,dataIndex:'base_name',sortable:true});
				nes = chufarr.base_deptname;if(!nes)nes='<?=lang('申请人部门')?>';
				d.push({text:nes,dataIndex:'base_deptname',sortable:true});
			}
			var celleditor = 0;
			for(i=0;i<len;i++){
				d1 = fieldsarr[i];
				bok= false;
				if(nstr==''){
					if(d1['islb']=='1')bok=true;
				}else{
					if(nstr.indexOf(','+d1.fields+',')>=0)bok=true;
				}
				if(bok){
					ebo = 0;
					d2={text:d1.name,dataIndex:d1.fields};
					if(d1.ispx=='1')d2.sortable=true;
					if(d1.width)d2.width=d1.width;
					if(d1.isalign=='1')d2.align='left';
					if(d1.isalign=='2')d2.align='right';
					if(d1.iseditlx=='1')ebo=1;
					if(d1.iseditlx=='2' && admintype=='1')ebo=1;
					if(d1.iseditlx=='3' && admintype=='1' && adminid=='1')ebo=1;
					if(ebo==1){
						d2.editor = true;
						celleditor= 1;
						d2.type   = d1.fieldstype;
						if(d1.fieldstype=='select' || d1.fieldstype=='rockcombo'){
							d2.type='select';
							d2.store=this.editorstore(d1.store);
						}
					}
					d.push(d2);
				}
				if(d1['issou']=='1'){
					d2={name:d1.name,fields:d1.fields,inputtype:d1.fieldstype,store:d1.store};
					sa.push(d2);
				}
			}
			if(isflow>0)d.push({text:'<?=lang('流程')?><?=lang('状态')?>',dataIndex:'statustext'});
			if(nstr=='' || nstr.indexOf(',caozuo,')>=0){
				d1 = {text:'',opttype:this.opttype,dataIndex:'caozuo',callback:'opegs{rand}'};
				(this.optalign=='left')?d.unshift(d1):d.push(d1);
			}
			for(i=0;i<d.length;i++)if(this.setcinfo[d[i].dataIndex])d[i] = js.apply(d[i],this.setcinfo[d[i].dataIndex]);
			d = this.oncolumns(d);
			if(celleditor==1)a.setCans({celleditor:true});
			bootparams.columns = d;
			if(bots)a.setColumns(d);
			d1 = this.fieldzarr;
			if(d1){
				for(i=0;i<d1.length;i++){
					sa.push({name:d1[i].name,fields:d1[i].fields,inputtype:d1[i].fieldstype,store:d1[i].store});
				}
			}
			this.souarr = sa;
			get('fields_{rand}').length=0;
			js.setselectdata(get('fields_{rand}'),sa,'fields');
			this.changefields();
			return d;
		},
		editorstore:function(d){
			var d1=[],i;
			if(d)for(i=0;i<d.length;i++)d1.push([d[i].value,d[i].name]);
			return d1;
		},
		changefields:function(){
			var o1 = get('fields_{rand}');
			if(!o1)return;
			var val = o1.value;
			var i,xa=false,len=this.souarr.length;
			for(i=0;i<len;i++){
				if(this.souarr[i].fields==val)xa=this.souarr[i];
			}
			if(!xa)return;
			var o2 = get('like_{rand}');
			o2.disabled=false;
			this.soutype = xa.inputtype;
			if(xa.inputtype=='dev'){
				$('#keygj_{rand}').hide();
				$('#key_{rand}').show();
				$('#selkey_{rand}').hide();
				o2.value='0';
				o2.disabled=true;
			}else if(xa.inputtype=='select' || xa.inputtype=='rockcombo'){
				$('#keygj_{rand}').hide();
				$('#key_{rand}').hide();
				$('#selkey_{rand}').show();
				o2.value='1';
				var o3 = get('selkey_{rand}');
				$(o3).html('<option value="">-<?=lang('请选择')?>-</option>');
				js.setselectdata(o3,xa.store,'value');
			}else{
				$('#keygj_{rand}').show();
				$('#selkey_{rand}').hide();
				$('#key_{rand}').hide();
				o2.value='0';
			}
		},
		setparams:function(cs){
			var ds = js.apply({},cs);
			a.setparams(ds);
		},
		storeurl:function(){
			var url = this.getacturl('publicstore')+'&pnum='+pnum+'';
			return url;
		},
		printlist:function(){
			var rnd = this.printrnd;
			if(!rnd){
				rnd = 'table'+js.getrand();
				window[rnd] = a;
				this.printrnd = rnd;
			}
			window.open('?d=public&m=print&table='+rnd+'&modenum='+modenum+'&modename='+jm.base64encode(modename)+'');
		},
		getbtnstr:function(txt, click, ys, ots,alx){
			if(!ys)ys='default';
			if(!ots)ots='';
			var str='<button class="btn btn-'+ys+'" id="btn'+click+'_{rand}" click="'+click+'" '+ots+' type="button">'+txt+'</button>';
			if(alx=='right')this.addrightbtn(str);
			return str;
		},
		addrightbtn:function(str){
			$('#tdright_{rand}').prepend(str+'&nbsp;&nbsp;');
		},
		setfieldslist:function(){
			new highsearchclass({
				modenum:modenum,
				modeid:modeid,
				type:1,
				isflow:isflow,
				pnum:pnum,atype:atype,
				fieldsarr:fieldsarr,
				fieldsselarr:fieldsselarr,
				oncallback:function(str){
					fieldsselarr[this.columnsnum]=str;
					c.initcolumns(true);
					c.reload();
				}
			});
		}
	};
	
	var bootparams = {
		fanye:true,modenum:modenum,listcreate:true,modename:modename,statuschange:false,tablename:jm.base64decode(listname),
		url:c.storeurl(),storeafteraction:'storeaftershow',storebeforeaction:'storebeforeshow',optobj:c,syspnum:pnum,
		params:{atype:atype},
		columns:[{text:"fields",dataIndex:"face"},{
			text:'',dataIndex:'caozuo',callback:'opegs{rand}'
		}],
		itemdblclick:function(){
			c.view();
		},
		load:function(d){
			c.loaddata(d);
		},
		loadbefore:function(d){
			c.loaddatabefore(d);
		}
	};
	c.initcolumns(false);
	opegs{rand}=function(){
		c.reload();
	}
