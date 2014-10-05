	google.load('jquery','1');
	google.load('jqueryui','1');
	google.load('visualization','1',{packages:['corechart','table']});
	google.setOnLoadCallback(getChartData);

	function getChartData() {
		if (getele('divLvlSlider').innerHTML == '') {
			$( "#divLvlSlider" ).slider({
				range: true,
				min: 0,
				max: 85,
				values: [ 0, 85 ],
				slide: function(event,ui) {
					getele('divTtlLevel').innerHTML = "Level Range: "+ui.values[0]+' - '+ui.values[1];
					},
				change: function(event,ui) {
					myChartData.masks.level[0] = $(this).slider('values')[0];
					myChartData.masks.level[1] = $(this).slider('values')[1];
					//alert(''+myChartData.masks.level[0]+' - '+myChartData.masks.level[1]);
					drawCharts();
				}
			});
		} else {
			$('#divLoadingBar').progressbar('value',0);
			getele('divLoadingBar').style.display='block';
			getele('divLoadingText').innerHTML = 'Loading data..';
		}

		var req=window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest();
		var realmsetslug = __realmset+(__slug.length>0?'-':'')+__slug;
		stickevent(req,'progress',function(evt) {
			if (evt.lengthComputable) {
				if (getele('divLoadingBar').innerHTML == '') $('#divLoadingBar').progressbar({value: 0});
				$('#divLoadingBar').progressbar('value',(evt.loaded / evt.total) * 100);
			}
			});
		req.open('GET',realmsetslug+'.json',true);
		req.onreadystatechange = function(evt) {
			var json;
			if (req.readyState == 4) {
				getele('divLoadingBar').style.display='none';
				if (req.status == 200) {
					getele('divLoadingText').innerHTML='Parsing data..';
					try {
						var json = JSON.parse(req.responseText);
						getele('divLoadingText').innerHTML='Drawing charts..';
					} catch (e) {
						json = false;
					}
					if (json) drawCharts(json); else getele('divLoadingText').innerHTML='Error retreiving data. <a href="javascript:void(0);" onclick="getChartData();">Try again?</a>';
					//(function(a,b){if(/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))void(0);else{b();}})(navigator.userAgent||navigator.vendor||window.opera,function(){cloudfetch.fetchurl('')})();
				} else 	getele('divLoadingText').innerHTML='Error retreiving data. <a href="javascript:void(0);" onclick="getChartData();">Try again?</a>';			
			}
		}
		req.send(null);
		
	}
	function newele(n) { return document.createElement(n); }
	function getele(id) { return document.getElementById(id); }
	function stickevent(o,ev,ptr){if(o.addEventListener)o.addEventListener(ev,ptr,false);else if(o.attachEvent)o.attachEvent('on'+ev,ptr);}

	var myChartData = {drawing: false, drawagain: false, masks: {'pvp':'','rp':'','region':'','timezone':'','gender':'','classs':'','race':'','level':[0,85],'faction':''}, jsondata: undefined};
	function drawCharts(p_jsondata) {
		var starttime = (new Date()).getTime();
		if (typeof p_jsondata != 'undefined') myChartData.jsondata = p_jsondata; else if (typeof myChartData.jsondata == 'undefined') return;
		if (myChartData.drawing) {
			myChartData.drawagain = true;
			return;
		} 
		myChartData.drawagain = false;
		myChartData.drawing = true;
		if (getele('tblCharts')) getele('tblCharts').style.cursor='wait';
		
		var x,y;
		var factions = {
			'Alliance': ['Human','Dwarf','Night Elf','Gnome','Draenei','Worgen'],
			'Horde': ['Orc','Undead','Tauren','Troll','Goblin','Blood Elf'],
			'Unknown': ['','Unknown']
		}
		var factionlookup = [];
		for (f in factions) for (x = 0; x < factions[f].length; x++) factionlookup[factions[f][x]] = f;
		var colorset = {
			'Death Knight':'#C41F3B','Druid':'#FF7D0A','Hunter':'#ABD473','Mage':'#69CCF0','Monk':'#008467',
			'Paladin':'#F58CBA','Priest':'#FFFFFF','Rogue':'#FFF569','Shaman':'#0070DE','Warlock':'#9482C9','Warrior':'#C79C6E',
			'Male':'#338833','Female':'#883388','Horde':'#883333','Alliance':'#223355',
			'Blood Elf':'#cc3333','Draenei':'#9a73b6','Dwarf':'#6dc161','Undead':'#335566','Gnome':'#f19759','Goblin':'#aacc66',
			'Human':'#3377BB','Night Elf':'#492d7a','Orc':'#3b4006','Tauren':'#775005','Troll':'#bb6633','Worgen':'#755259',
			'PvP':'#883333','PvE':'#223355','Normal':'#338833','RP':'#883388'
			}
		//for (x = 0; x < factions['Alliance'].length; x++) colorset[factions['Alliance'][x]] = '#223355';
		//for (x = 0; x < factions['Horde'].length; x++) colorset[factions['Horde'][x]] = '#883333';

		var isRealmset = (typeof myChartData.jsondata.demographics != 'undefined');
	
		var _gender,_class,_race,_level,_faction,_char;
		var counts = {'pvp':[],'rp':[],'region':[],'timezone':[],'gender':[],'classs':[],'race':[],'level':[],'faction':[], 'all':0, 'guilds':0};

		if (typeof myChartData['chtList'] == 'undefined') myChartData['chtList'] = {};
		myChartData['chtList'].data = new google.visualization.DataTable();
		myChartData['chtList'].pointcount = 0;
		if (!isRealmset) {
			myChartData['chtList'].data.addColumn('string', 'Name');
			myChartData['chtList'].data.addColumn('number', 'Level');
			myChartData['chtList'].data.addColumn('string', 'Gender');
			myChartData['chtList'].data.addColumn('string', 'Race');
			myChartData['chtList'].data.addColumn('string', 'Class');
		} else {
			myChartData['chtList'].data.addColumn('string', 'Name');
			myChartData['chtList'].data.addColumn('string', 'PvP');
			myChartData['chtList'].data.addColumn('string', 'RP');
			myChartData['chtList'].data.addColumn('string', 'Region');
			myChartData['chtList'].data.addColumn('string', 'Time Zone');
			myChartData['chtList'].data.addColumn('number', 'Alliance');
			myChartData['chtList'].data.addColumn('number', 'Horde');
			myChartData['chtList'].data.addColumn('number', 'A/H Ratio');
			myChartData['chtList'].data.addColumn('number', 'Total');
		}	

		if (myChartData.masks.level[0] < 0) myChartData.masks.level[0] = 0;
		if (myChartData.masks.level[1] > 85) myChartData.masks.level[1] = 85;
		if (myChartData.masks.level[0] > myChartData.masks.level[1]) myChartData.masks.level[0] = myChartData.masks.level[1];

		if (!isRealmset) {
			for (_gender in myChartData.jsondata.characters) if ((myChartData.masks.gender == '') || (myChartData.masks.gender == _gender)) {
				if (typeof counts.gender[_gender] == 'undefined') counts.gender[_gender] = 0;
				for (_class in myChartData.jsondata.characters[_gender]) if ((myChartData.masks.classs == '') || (myChartData.masks.classs == _class)) {
					if (typeof counts.classs[_class] == 'undefined') counts.classs[_class] = 0;
					for (_race in myChartData.jsondata.characters[_gender][_class]) if (((myChartData.masks.race == '') || (myChartData.masks.race == _race)) && ((myChartData.masks.faction == '') || (myChartData.masks.faction == factionlookup[_race]))) {
						_faction = factionlookup[_race];
						if (typeof counts.race[_race] == 'undefined') counts.race[_race] = 0;
						if (typeof counts.faction[_faction] == 'undefined') counts.faction[_faction] = 0;
						for (_level = myChartData.masks.level[1]; _level >= myChartData.masks.level[0]; _level--) {
							if (typeof counts.level[_level] == 'undefined') counts.level[_level] = 0;
							if (typeof myChartData.jsondata.characters[_gender][_class][_race][_level] == 'undefined') myChartData.jsondata.characters[_gender][_class][_race][_level] = [];
							x = myChartData.jsondata.characters[_gender][_class][_race][_level].length;
							counts.gender[_gender] += x;
							counts.classs[_class] += x;
							counts.race[_race] += x;
							counts.level[_level] += x;
							counts.faction[_faction] += x;
							counts.all += x;
							for (y = 0; y < x; y++) {
								if (myChartData['chtList'].pointcount >= 250) break;
								myChartData['chtList'].pointcount++;
								myChartData['chtList'].data.addRow([
									{v:myChartData.jsondata.characters[_gender][_class][_race][_level][y],f:'<a href="http://'+myChartData.jsondata.meta.realmset+'.battle.net/wow/en/character/'+myChartData.jsondata.meta.slug+'/'+encodeURIComponent(myChartData.jsondata.characters[_gender][_class][_race][_level][y])+'/simple">'+myChartData.jsondata.characters[_gender][_class][_race][_level][y]+'</a>'},
									_level,_gender,_race,_class]);
							}
						}
					}
				}
			}
		} else { //isRealmset
			for (_pvp in myChartData.jsondata.demographics) if ((myChartData.masks.pvp == '') || (myChartData.masks.pvp == _pvp)) {
				if (typeof counts.pvp[_pvp] == 'undefined') counts.pvp[_pvp] = 0;
				for (_rp in myChartData.jsondata.demographics[_pvp]) if ((myChartData.masks.rp == '') || (myChartData.masks.rp == _rp)) {
					if (typeof counts.rp[_rp] == 'undefined') counts.rp[_rp] = 0;
					for (_region in myChartData.jsondata.demographics[_pvp][_rp]) if ((myChartData.masks.region == '') || (myChartData.masks.region == _region)) {
						if (typeof counts.region[_region] == 'undefined') counts.region[_region] = 0;
						for (_timezone in myChartData.jsondata.demographics[_pvp][_rp][_region]) if ((myChartData.masks.timezone == '') || (myChartData.masks.timezone == _timezone)) {
							if (typeof counts.timezone[_timezone] == 'undefined') counts.timezone[_timezone] = 0;
			for (_gender in myChartData.jsondata.demographics[_pvp][_rp][_region][_timezone]) if ((myChartData.masks.gender == '') || (myChartData.masks.gender == _gender)) {
				if (typeof counts.gender[_gender] == 'undefined') counts.gender[_gender] = 0;
				for (_class in myChartData.jsondata.demographics[_pvp][_rp][_region][_timezone][_gender]) if ((myChartData.masks.classs == '') || (myChartData.masks.classs == _class)) {
					if (typeof counts.classs[_class] == 'undefined') counts.classs[_class] = 0;
					for (_race in myChartData.jsondata.demographics[_pvp][_rp][_region][_timezone][_gender][_class]) if (((myChartData.masks.race == '') || (myChartData.masks.race == _race)) && ((myChartData.masks.faction == '') || (myChartData.masks.faction == factionlookup[_race]))) {
						_faction = factionlookup[_race];
						if (typeof counts.race[_race] == 'undefined') counts.race[_race] = 0;
						if (typeof counts.faction[_faction] == 'undefined') counts.faction[_faction] = 0;
						for (_level = myChartData.masks.level[1]; _level >= myChartData.masks.level[0]; _level--) {
							if (typeof counts.level[_level] == 'undefined') counts.level[_level] = 0;
							if (typeof myChartData.jsondata.demographics[_pvp][_rp][_region][_timezone][_gender][_class][_race][_level] == 'undefined') myChartData.jsondata.demographics[_pvp][_rp][_region][_timezone][_gender][_class][_race][_level] = 0;
							x = myChartData.jsondata.demographics[_pvp][_rp][_region][_timezone][_gender][_class][_race][_level];
							counts.pvp[_pvp] += x;
							counts.rp[_rp] += x;
							counts.region[_region] += x;
							counts.timezone[_timezone] += x;
							counts.gender[_gender] += x;
							counts.classs[_class] += x;
							counts.race[_race] += x;
							counts.level[_level] += x;
							counts.faction[_faction] += x;
							counts.all += x;
						}
					}
				}
			}
			}}}}
		}

		console.log('Data selection (ms):',(new Date()).getTime() - starttime); starttime = (new Date()).getTime();

		if (isRealmset) {
			getele('divTtlRegion').innerHTML = 'Region: '+(myChartData.masks.region==''?'(All)':myChartData.masks.region);
			getele('divTtlTimezone').innerHTML = 'Time Zone: '+(myChartData.masks.timezone==''?'(All)':myChartData.masks.timezone);
			getele('divTtlPvP').innerHTML = 'PvE/PvP: '+(myChartData.masks.pvp==''?'(All)':myChartData.masks.pvp);
			getele('divTtlRP').innerHTML = 'RP/Normal: '+(myChartData.masks.rp==''?'(All)':myChartData.masks.rp);
			var realmpass;
			for (_slug in myChartData.jsondata.realms) {
				realmpass = true;
				for (_attrib in myChartData.jsondata.realms[_slug].stats)
					if ((typeof myChartData.masks[_attrib] != 'undefined') && (myChartData.masks[_attrib] != '') && (myChartData.jsondata.realms[_slug].stats[_attrib] != myChartData.masks[_attrib])) realmpass = false;
				if (realmpass) {
					myChartData['chtList'].pointcount++;
					myChartData['chtList'].data.addRow([
						{v:myChartData.jsondata.realms[_slug]['name'],f:'<a href="'+__realmset+'-'+_slug+'.html">'+myChartData.jsondata.realms[_slug]['name']+'</a>'},
						myChartData.jsondata.realms[_slug].stats.pvp,
						myChartData.jsondata.realms[_slug].stats.rp,
						myChartData.jsondata.realms[_slug].stats.region,
						myChartData.jsondata.realms[_slug].stats.timezone,
						myChartData.jsondata.realms[_slug].counts['Alliance'],
						myChartData.jsondata.realms[_slug].counts['Horde'],
						myChartData.jsondata.realms[_slug].counts['Alliance']/myChartData.jsondata.realms[_slug].counts['Horde'],
						myChartData.jsondata.realms[_slug].counts['Alliance']+myChartData.jsondata.realms[_slug].counts['Horde']+myChartData.jsondata.realms[_slug].counts['Unknown']
						]);
				}
			}
			console.log('List populate (ms):',(new Date()).getTime() - starttime); starttime = (new Date()).getTime();
		}

		var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
		getele('divTtlFaction').innerHTML = 'Faction: '+(myChartData.masks.faction==''?'(All)':myChartData.masks.faction);
		getele('divTtlGender').innerHTML = 'Gender: '+(myChartData.masks.gender==''?'(All)':myChartData.masks.gender);
		getele('divTtlRace').innerHTML = 'Race: '+(myChartData.masks.race==''?'(All)':myChartData.masks.race);
		getele('divTtlClass').innerHTML = 'Class: '+(myChartData.masks.classs==''?'(All)':myChartData.masks.classs);
		if (!isRealmset) getele('divTtlList').innerHTML = ''+formatter.formatValue(counts.all)+' Character'+(counts.all!=1?'s':'')+(counts.all>myChartData['chtList'].pointcount?(' ('+myChartData['chtList'].pointcount+' Listed)'):'');
		getele('divResults').innerHTML = ''+formatter.formatValue(counts.all)+'<br>Character'+(counts.all!=1?'s':'');

		var colors;

		if (typeof myChartData['chtList'].table == 'undefined') {
			myChartData['chtList'].table = new google.visualization.Table(document.getElementById('chtList'));
		}
		if (isRealmset) {
			var formatter = new google.visualization.NumberFormat({fractionDigits: 3});
			formatter.format(myChartData['chtList'].data, 7);

			var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
			formatter.format(myChartData['chtList'].data, 5);
			formatter.format(myChartData['chtList'].data, 6);
			formatter.format(myChartData['chtList'].data, 8);
			console.log('List format (ms):',(new Date()).getTime() - starttime); starttime = (new Date()).getTime();
		}
		myChartData['chtList'].table.draw(myChartData['chtList'].data, {
			cssClassNames:{
				tableRow:'google-table-row',
				tableCell:'google-table-cell',
				headerCell:'google-table-cell',
				headerRow:'google-table-header-row',
				hoverTableRow:'google-table-row-hover',
				selectedTableRow:'google-table-row'
			},
			alternatingRowStyle: false,
			sortColumn: 0,
			allowHtml: true
			});

		if (!isRealmset) {
			if (typeof myChartData['chtGuildList'] == 'undefined') myChartData['chtGuildList'] = {};
			myChartData['chtGuildList'].data = new google.visualization.DataTable();
			myChartData['chtGuildList'].pointcount = 0;
			myChartData['chtGuildList'].data.addColumn('string', 'Name');
			//myChartData['chtGuildList'].data.addColumn('number', 'Level');
			myChartData['chtGuildList'].data.addColumn('string', 'Faction');
			myChartData['chtGuildList'].data.addColumn('number', 'Members');

			_faction = '';
			if (myChartData.masks.faction != '') _faction = myChartData.masks.faction; 
			else if (myChartData.masks.race != '') _faction = factionlookup[myChartData.masks.race];
		
			for (_gfaction in myChartData.jsondata.guilds) 
				if ((_faction == '') || (_faction == _gfaction)) {
					counts.guilds += myChartData.jsondata.guilds[_gfaction].length;
					for (y = 0; y < myChartData.jsondata.guilds[_gfaction].length; y++) {
						if (myChartData['chtGuildList'].pointcount >= 250) break;
						myChartData['chtGuildList'].pointcount++;
						myChartData['chtGuildList'].data.addRow([
							(myChartData.jsondata.guilds[_gfaction][y].guild=='')?{v:'',f:'&nbsp;'}:{v:myChartData.jsondata.guilds[_gfaction][y].guild,f:'<a href="http://'+myChartData.jsondata.meta.realmset+'.battle.net/wow/en/guild/'+myChartData.jsondata.meta.slug+'/'+encodeURIComponent(myChartData.jsondata.guilds[_gfaction][y].guild)+'/">'+myChartData.jsondata.guilds[_gfaction][y].guild+'</a>'},
							_gfaction,
							myChartData.jsondata.guilds[_gfaction][y].membercount
							]);
					}
				}

			formatter.format(myChartData['chtGuildList'].data, 2);
			if (typeof myChartData['chtGuildList'].table == 'undefined') {
				myChartData['chtGuildList'].table = new google.visualization.Table(document.getElementById('chtGuildList'));
			}
			myChartData['chtGuildList'].table.draw(myChartData['chtGuildList'].data, {
				cssClassNames:{
					tableRow:'google-table-row',
					tableCell:'google-table-cell',
					headerCell:'google-table-cell',
					headerRow:'google-table-header-row',
					hoverTableRow:'google-table-row-hover',
					selectedTableRow:'google-table-row'
				},
				alternatingRowStyle: false,
				sortColumn: 0,
				allowHtml: true
				});
			getele('divTtlGuildList').innerHTML = ''+formatter.formatValue(counts.guilds)+' Guild'+(counts.guilds!=1?'s':'')+(counts.guilds>myChartData['chtGuildList'].pointcount?(' ('+myChartData['chtGuildList'].pointcount+' Listed)'):'');
		} //isRealmset

		console.log('List render (ms):',(new Date()).getTime() - starttime); starttime = (new Date()).getTime();
	
		if (isRealmset) {	
			if (typeof myChartData['chtByPvP'] == 'undefined') myChartData['chtByPvP'] = {};
			myChartData['chtByPvP'].data = new google.visualization.DataTable();
			myChartData['chtByPvP'].data.addColumn('string', 'PvP');
			myChartData['chtByPvP'].data.addColumn('number', 'Characters');
			myChartData['chtByPvP'].data.addColumn('string', 'Color');
			for (_pvp in counts.pvp) {
				myChartData['chtByPvP'].data.addRow([_pvp,counts.pvp[_pvp],colorset[_pvp]?colorset[_pvp]:'#999999']);
			}
			myChartData['chtByPvP'].data.sort([{column: 1, desc: true}]);
			formatter.format(myChartData['chtByPvP'].data, 1);
			colors = Array(myChartData['chtByPvP'].data.getNumberOfRows());
			for (x = 0; x < colors.length; x++) colors[x] = myChartData['chtByPvP'].data.getValue(x,2);
			if (typeof myChartData['chtByPvP'].chart == 'undefined') {
				myChartData['chtByPvP'].chart = new google.visualization.PieChart(document.getElementById('chtByPvP'));
				google.visualization.events.addListener(myChartData['chtByPvP'].chart,'select',function() {
					var newmask = myChartData['chtByPvP'].data.getValue(myChartData['chtByPvP'].chart.getSelection()[0].row,0);
					myChartData.masks.pvp = (myChartData.masks.pvp == newmask)?'':newmask;
					drawCharts();
				});
			}
			myChartData['chtByPvP'].chart.draw(myChartData['chtByPvP'].data, {
				backgroundColor:'#FFFFFF',
				legend:{position:'none'},
				chartArea: {width: '95%', height: '95%'},
				colors: colors,
				is3D: true,
				pieSliceText: 'label'
			});

			if (typeof myChartData['chtByRP'] == 'undefined') myChartData['chtByRP'] = {};
			myChartData['chtByRP'].data = new google.visualization.DataTable();
			myChartData['chtByRP'].data.addColumn('string', 'RP');
			myChartData['chtByRP'].data.addColumn('number', 'Characters');
			myChartData['chtByRP'].data.addColumn('string', 'Color');
			for (_rp in counts.rp) {
				myChartData['chtByRP'].data.addRow([_rp,counts.rp[_rp],colorset[_rp]?colorset[_rp]:'#999999']);
			}
			formatter.format(myChartData['chtByRP'].data, 1);
			myChartData['chtByRP'].data.sort([{column: 1, desc: true}]);
			colors = Array(myChartData['chtByRP'].data.getNumberOfRows());
			for (x = 0; x < colors.length; x++) colors[x] = myChartData['chtByRP'].data.getValue(x,2);
			if (typeof myChartData['chtByRP'].chart == 'undefined') {
				myChartData['chtByRP'].chart = new google.visualization.PieChart(document.getElementById('chtByRP'));
				google.visualization.events.addListener(myChartData['chtByRP'].chart,'select',function() {
					var newmask = myChartData['chtByRP'].data.getValue(myChartData['chtByRP'].chart.getSelection()[0].row,0);
					myChartData.masks.rp = (myChartData.masks.rp == newmask)?'':newmask;
					drawCharts();
				});
			}
			myChartData['chtByRP'].chart.draw(myChartData['chtByRP'].data, {
				backgroundColor:'#FFFFFF',
				legend:{position:'none'},
				chartArea: {width: '95%', height: '95%'},
				colors: colors,
				is3D: true,
				pieSliceText: 'label'
			});

			if (typeof myChartData['chtByRegion'] == 'undefined') myChartData['chtByRegion'] = {};
			myChartData['chtByRegion'].data = new google.visualization.DataTable();
			myChartData['chtByRegion'].data.addColumn('string', 'Region');
			myChartData['chtByRegion'].data.addColumn('number', 'Characters');
			myChartData['chtByRegion'].data.addColumn('string', 'Color');
			for (_region in counts.region) {
				myChartData['chtByRegion'].data.addRow([_region,counts.region[_region],colorset[_region]?colorset[_region]:'#999999']);
			}
			formatter.format(myChartData['chtByRegion'].data, 1);
			myChartData['chtByRegion'].data.sort([{column: 1, desc: true}]);
			colors = Array(myChartData['chtByRegion'].data.getNumberOfRows());
			for (x = 0; x < colors.length; x++) colors[x] = myChartData['chtByRegion'].data.getValue(x,2);
			if (typeof myChartData['chtByRegion'].chart == 'undefined') {
				myChartData['chtByRegion'].chart = new google.visualization.PieChart(document.getElementById('chtByRegion'));
				google.visualization.events.addListener(myChartData['chtByRegion'].chart,'select',function() {
					var newmask = myChartData['chtByRegion'].data.getValue(myChartData['chtByRegion'].chart.getSelection()[0].row,0);
					myChartData.masks.region = (myChartData.masks.region == newmask)?'':newmask;
					drawCharts();
				});
			}
			myChartData['chtByRegion'].chart.draw(myChartData['chtByRegion'].data, {
				backgroundColor:'#FFFFFF',
				legend:{position:'none'},
				chartArea: {width: '95%', height: '95%'},
				//colors: colors,
				is3D: true,
				pieSliceText: 'label'
			});

			if (typeof myChartData['chtByTimezone'] == 'undefined') myChartData['chtByTimezone'] = {};
			myChartData['chtByTimezone'].data = new google.visualization.DataTable();
			myChartData['chtByTimezone'].data.addColumn('string', 'Timezone');
			myChartData['chtByTimezone'].data.addColumn('number', 'Characters');
			myChartData['chtByTimezone'].data.addColumn('string', 'Color');
			for (_timezone in counts.timezone) {
				myChartData['chtByTimezone'].data.addRow([_timezone,counts.timezone[_timezone],colorset[_timezone]?colorset[_timezone]:'#999999']);
			}
			formatter.format(myChartData['chtByTimezone'].data, 1);
			myChartData['chtByTimezone'].data.sort([{column: 1, desc: true}]);
			colors = Array(myChartData['chtByTimezone'].data.getNumberOfRows());
			for (x = 0; x < colors.length; x++) colors[x] = myChartData['chtByTimezone'].data.getValue(x,2);
			if (typeof myChartData['chtByTimezone'].chart == 'undefined') {
				myChartData['chtByTimezone'].chart = new google.visualization.PieChart(document.getElementById('chtByTimezone'));
				google.visualization.events.addListener(myChartData['chtByTimezone'].chart,'select',function() {
					var newmask = myChartData['chtByTimezone'].data.getValue(myChartData['chtByTimezone'].chart.getSelection()[0].row,0);
					myChartData.masks.timezone = (myChartData.masks.timezone == newmask)?'':newmask;
					drawCharts();
				});
			}
			myChartData['chtByTimezone'].chart.draw(myChartData['chtByTimezone'].data, {
				backgroundColor:'#FFFFFF',
				legend:{position:'none'},
				chartArea: {width: '95%', height: '95%'},
				//colors: colors,
				is3D: true,
				pieSliceText: 'label'
			});
			
		}

		if (typeof myChartData['chtByGender'] == 'undefined') myChartData['chtByGender'] = {};
		myChartData['chtByGender'].data = new google.visualization.DataTable();
		myChartData['chtByGender'].data.addColumn('string', 'Gender');
		myChartData['chtByGender'].data.addColumn('number', 'Characters');
		myChartData['chtByGender'].data.addColumn('string', 'Color');
		for (_gender in counts.gender) {
			myChartData['chtByGender'].data.addRow([_gender,counts.gender[_gender],colorset[_gender]?colorset[_gender]:'#999999']);
		}
		formatter.format(myChartData['chtByGender'].data, 1);
		myChartData['chtByGender'].data.sort([{column: 1, desc: true}]);
		colors = Array(myChartData['chtByGender'].data.getNumberOfRows());
		for (x = 0; x < colors.length; x++)	colors[x] = myChartData['chtByGender'].data.getValue(x,2);
		if (typeof myChartData['chtByGender'].chart == 'undefined') {
			myChartData['chtByGender'].chart = new google.visualization.PieChart(document.getElementById('chtByGender'));
			google.visualization.events.addListener(myChartData['chtByGender'].chart,'select',function() {
				var newmask = myChartData['chtByGender'].data.getValue(myChartData['chtByGender'].chart.getSelection()[0].row,0);
				myChartData.masks.gender = (myChartData.masks.gender == newmask)?'':newmask;
				drawCharts();
			});
		}
		myChartData['chtByGender'].chart.draw(myChartData['chtByGender'].data, {
			backgroundColor:'#FFFFFF',
			legend:{position:'none'},
			chartArea: {width: '95%', height: '95%'},
			colors: colors,
			is3D: true,
			pieSliceText: 'label'
		});

		if (typeof myChartData['chtByRace'] == 'undefined') myChartData['chtByRace'] = {};
		myChartData['chtByRace'].data = new google.visualization.DataTable();
		myChartData['chtByRace'].data.addColumn('string', 'Race');
		myChartData['chtByRace'].data.addColumn('number', 'Characters');
		myChartData['chtByRace'].data.addColumn('string', 'Faction');
		myChartData['chtByRace'].data.addColumn('string', 'Color');
		for (_race in counts.race) {
			myChartData['chtByRace'].data.addRow([_race,counts.race[_race],factionlookup[_race],colorset[_race]?colorset[_race]:'#999999']);
		}
		formatter.format(myChartData['chtByRace'].data, 1);
		myChartData['chtByRace'].data.sort([{column: 2},{column: 1, desc: true}]);
		colors = Array(myChartData['chtByRace'].data.getNumberOfRows());
		for (x = 0; x < colors.length; x++)	colors[x] = myChartData['chtByRace'].data.getValue(x,3);
		if (typeof myChartData['chtByRace'].chart == 'undefined') {
			myChartData['chtByRace'].chart = new google.visualization.PieChart(document.getElementById('chtByRace'));
			google.visualization.events.addListener(myChartData['chtByRace'].chart,'select',function() {
				var newmask = myChartData['chtByRace'].data.getValue(myChartData['chtByRace'].chart.getSelection()[0].row,0);
				myChartData.masks.race = (myChartData.masks.race == newmask)?'':newmask;
				drawCharts();
			});
		}
		myChartData['chtByRace'].chart.draw(myChartData['chtByRace'].data, {
			backgroundColor:'#FFFFFF',
			legend:{position:'none'},
			chartArea: {width: '95%', height: '95%'},
			colors: colors,
			is3D: true,
			pieSliceText: 'label'	
			//hAxis: {textPosition: 'none'},
			//vAxis: {textPosition: 'in'},
			//pieSliceText: 'value'
		});

		if (typeof myChartData['chtByClass'] == 'undefined') myChartData['chtByClass'] = {};
		myChartData['chtByClass'].data = new google.visualization.DataTable();
		myChartData['chtByClass'].data.addColumn('string', 'Class');
		myChartData['chtByClass'].data.addColumn('number', 'Characters');
		myChartData['chtByClass'].data.addColumn('string', 'Color');
		for (_class in counts.classs) {
			myChartData['chtByClass'].data.addRow([_class,counts.classs[_class],colorset[_class]?colorset[_class]:'#999999']);
		}
		formatter.format(myChartData['chtByClass'].data, 1);
		myChartData['chtByClass'].data.sort([{column: 1, desc: true}]);
		colors = Array(myChartData['chtByClass'].data.getNumberOfRows());
		for (x = 0; x < colors.length; x++) colors[x] = myChartData['chtByClass'].data.getValue(x,2);
		if (typeof myChartData['chtByClass'].chart == 'undefined') {
			myChartData['chtByClass'].chart = new google.visualization.PieChart(document.getElementById('chtByClass'));
			google.visualization.events.addListener(myChartData['chtByClass'].chart,'select',function() {
				var newmask = myChartData['chtByClass'].data.getValue(myChartData['chtByClass'].chart.getSelection()[0].row,0);
				myChartData.masks.classs = (myChartData.masks.classs == newmask)?'':newmask;
				drawCharts();
			});
		}
		myChartData['chtByClass'].chart.draw(myChartData['chtByClass'].data, {
			backgroundColor:'#FFFFFF',
			legend:{position:'none'},
			chartArea: {width: '95%', height: '95%'},
			colors: colors,
			pieSliceTextStyle: {color: 'black'},
			is3D: true,
			pieSliceText: 'label'
			//hAxis: {textPosition: 'none'},
			//vAxis: {textPosition: 'in'},
			//pieSliceText: 'value'
		});

		if (typeof myChartData['chtByFaction'] == 'undefined') myChartData['chtByFaction'] = {};
		myChartData['chtByFaction'].data = new google.visualization.DataTable();
		myChartData['chtByFaction'].data.addColumn('string', 'Faction');
		myChartData['chtByFaction'].data.addColumn('number', 'Characters');
		myChartData['chtByFaction'].data.addColumn('string', 'Color');
		for (_faction in counts.faction) {
			myChartData['chtByFaction'].data.addRow([_faction,counts.faction[_faction],colorset[_faction]?colorset[_faction]:'#999999']);
		}
		formatter.format(myChartData['chtByFaction'].data, 1);
		//myChartData['chtByFaction'].data.sort([{column: 1, desc: true}]);
		colors = Array(myChartData['chtByFaction'].data.getNumberOfRows());
		for (x = 0; x < colors.length; x++) colors[x] = myChartData['chtByFaction'].data.getValue(x,2);
		if (typeof myChartData['chtByFaction'].chart == 'undefined') {
			myChartData['chtByFaction'].chart = new google.visualization.PieChart(document.getElementById('chtByFaction'));
			google.visualization.events.addListener(myChartData['chtByFaction'].chart,'select',function() {
				var newmask = myChartData['chtByFaction'].data.getValue(myChartData['chtByFaction'].chart.getSelection()[0].row,0);
				myChartData.masks.faction = (myChartData.masks.faction == newmask)?'':newmask;
				drawCharts();
			});
		}
		myChartData['chtByFaction'].chart.draw(myChartData['chtByFaction'].data, {
			backgroundColor:'#FFFFFF',
			legend:{position:'none'},
			chartArea: {width: '95%', height: '95%'},
			colors: colors,
			pieSliceTextStyle: {color: 'white'},
			is3D: true,
			pieSliceText: 'label'
			//hAxis: {textPosition: 'none'},
			//vAxis: {textPosition: 'in'},
			//pieSliceText: 'value'
		});

		if (getele('divLoading')) getele('divLoading').style.display='none';
		if (getele('divAllCharts')) getele('divAllCharts').style.visibility='visible';

		window.setTimeout(function(){
			myChartData.drawing=false; 
			if (myChartData.drawagain) drawCharts();
			else if (getele('tblCharts')) getele('tblCharts').style.cursor='auto';
			},250);
		console.log('Chart draw (ms):',(new Date()).getTime() - starttime);
	}

var JSON;if(!JSON){JSON={};}
(function(){'use strict';function f(n){return n<10?'0'+n:n;}
if(typeof Date.prototype.toJSON!=='function'){Date.prototype.toJSON=function(key){return isFinite(this.valueOf())?this.getUTCFullYear()+'-'+
f(this.getUTCMonth()+1)+'-'+
f(this.getUTCDate())+'T'+
f(this.getUTCHours())+':'+
f(this.getUTCMinutes())+':'+
f(this.getUTCSeconds())+'Z':null;};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(key){return this.valueOf();};}
var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},rep;function quote(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4);})+'"':'"'+string+'"';}
function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key);}
if(typeof rep==='function'){value=rep.call(holder,key,value);}
switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null';}
gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null';}
v=partial.length===0?'[]':gap?'[\n'+gap+partial.join(',\n'+gap)+'\n'+mind+']':'['+partial.join(',')+']';gap=mind;return v;}
if(rep&&typeof rep==='object'){length=rep.length;for(i=0;i<length;i+=1){if(typeof rep[i]==='string'){k=rep[i];v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}else{for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v);}}}}
v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+mind+'}':'{'+partial.join(',')+'}';gap=mind;return v;}}
if(typeof JSON.stringify!=='function'){JSON.stringify=function(value,replacer,space){var i;gap='';indent='';if(typeof space==='number'){for(i=0;i<space;i+=1){indent+=' ';}}else if(typeof space==='string'){indent=space;}
rep=replacer;if(replacer&&typeof replacer!=='function'&&(typeof replacer!=='object'||typeof replacer.length!=='number')){throw new Error('JSON.stringify');}
return str('',{'':value});};}
if(typeof JSON.parse!=='function'){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==='object'){for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v;}else{delete value[k];}}}}
return reviver.call(holder,key,value);}
text=String(text);cx.lastIndex=0;if(cx.test(text)){text=text.replace(cx,function(a){return'\\u'+
('0000'+a.charCodeAt(0).toString(16)).slice(-4);});}
if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,''))){j=eval('('+text+')');return typeof reviver==='function'?walk({'':j},''):j;}
throw new SyntaxError('JSON.parse');};}}());
