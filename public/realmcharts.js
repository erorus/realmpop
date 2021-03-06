function newele(n)
{
    return document.createElement(n);
}

function getele(id)
{
    return document.getElementById(id);
}

function stickevent(o, ev, ptr)
{
    if (o.addEventListener) {
        o.addEventListener(ev, ptr, false);
    } else {
        if (o.attachEvent) {
            o.attachEvent('on' + ev, ptr);
        }
    }
}

var __connectedRealms = {};
var __maxLevel = __lookups.maxLevel;

//google.load('jquery','1');
//google.load('jqueryui','1');
google.load('visualization', '1', {packages: ['corechart']});
google.setOnLoadCallback(getChartData);

/*	function loadstep2() {
		//jQuery.ajax({url: "jquery.tablesorter.min.js", cache: true, dataType: 'script', complete: function(data, textStatus) { getChartData(); }});
		var scr = document.createElement('script');
		scr.type = 'text/javascript';
		scr.src = 'jquery.tablesorter.min.js';
		stickevent(scr,'load',getChartData);
		document.getElementsByTagName('head')[0].appendChild(scr);
	}
*/
function getChartData()
{
    if (getele('divLvlSlider').innerHTML == '') {
        $("#divLvlSlider").slider({
            range: true,
            min: 0,
            max: __maxLevel,
            values: [0, __maxLevel],
            slide: function (event, ui) {
                getele('divTtlLevel').innerHTML = "Level Range: " + ui.values[0] + ' - ' + ui.values[1];
            },
            change: function (event, ui) {
                myChartData.masks.level[0] = $(this).slider('values')[0];
                myChartData.masks.level[1] = $(this).slider('values')[1];
                //alert(''+myChartData.masks.level[0]+' - '+myChartData.masks.level[1]);
                drawCharts();
            }
        });
    }
    if (getele('divLoadingBar').innerHTML != '') {
        $('#divLoadingBar').progressbar('value', 0);
        getele('divLoadingBar').style.display = 'block';
    }
    getele('divLoadingText').innerHTML = 'Loading data..';

    var requests = {};

    var realmsetslug = __realmset + (__slug.length > 0 ? '-' : '') + __slug;

    requests[realmsetslug] = {loaded: 0, done: false};

    var req = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
    stickevent(req, 'progress', dataAjaxProgress(requests, realmsetslug));
    req.open('GET', realmsetslug + '.json', true);
    stickevent(req, 'readystatechange', dataAjaxRSC(requests, realmsetslug));
    requests[realmsetslug].xmlhr = req;

    var reqcr = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
    reqcr.open('GET', 'connected-realms.json', true);
    reqcr.onreadystatechange = function (evt) {
        var json;
        if (reqcr.readyState == 4) {
            if (reqcr.status == 200) {
                try {
                    var json = jQuery.parseJSON(reqcr.responseText);
                } catch (e) {
                    json = false;
                }
                if (!json) {
                    getele('divLoadingText').innerHTML = 'Error retrieving connected realm data. <a href="javascript:void(0);" onclick="getChartData();">Try again?</a>';
                    return false;
                }

                __connectedRealms = json;

                if (realmsetslug in json) {
                    for (var x = 0; x < json[realmsetslug].length; x++) {
                        var req = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
                        stickevent(req, 'progress', dataAjaxProgress(requests, json[realmsetslug][x]));
                        req.open('GET', json[realmsetslug][x] + '.json', true);
                        stickevent(req, 'readystatechange', dataAjaxRSC(requests, json[realmsetslug][x]));
                        requests[json[realmsetslug][x]] = {loaded: 0, done: false, xmlhr: req};
                    }
                }

                for (var rss in requests) {
                    requests[rss].xmlhr.send(null);
                }
            } else {
                getele('divLoadingText').innerHTML = 'Error retrieving connected realm data. <a href="javascript:void(0);" onclick="getChartData();">Try again?</a>';
            }
        }
    }
    reqcr.send(null);
}

function dataAjaxProgress(requests, realmsetslug)
{
    return function (evt) {
        requests[realmsetslug].loaded = evt.loaded;
        var totalloaded = 0;
        for (var rss in requests) {
            totalloaded += requests[rss].loaded;
        }

        if ((typeof __jsonsize != 'undefined') && (!isNaN(parseInt(__jsonsize, 10)))) {
            if (getele('divLoadingBar').innerHTML == '') {
                $('#divLoadingBar').progressbar({value: 0});
            }
            $('#divLoadingBar').progressbar('value', (totalloaded / __jsonsize) * 100);
        }
        if (totalloaded > 0) {
            getele('divLoadingText').innerHTML = 'Loading data.. (' + Math.round(totalloaded / 1024) + 'kB)';
        }
    }
}

function dataAjaxRSC(requests, realmsetslug)
{
    return function (evt) {
        var json;
        var req = requests[realmsetslug].xmlhr;
        if (req.readyState == 4) {
            requests[realmsetslug].done = true;

            if (req.status == 200) {
                try {
                    json = jQuery.parseJSON(req.responseText);
                } catch (e) {
                    json = false;
                }
            }
            requests[realmsetslug].json = json;

            var alldone = true;
            var allokay = true;
            for (var rss in requests) {
                alldone &= requests[rss].done;
                allokay &= requests[rss].done && (!!requests[rss].json);
            }
            if (!alldone) {
                return;
            }

            getele('divLoadingBar').style.display = 'none';
            if (allokay) {
                getele('divLoadingText').innerHTML = 'Compiling data..';
                for (var rss in requests) {
                    if (rss != realmsetslug) {
                        dataUnion(json.characters, requests[rss].json.characters, rss);
                    }
                }
                getele('divLoadingText').innerHTML = 'Drawing charts..';
                if (json) {
                    drawCharts(json);
                } else {
                    getele('divLoadingText').innerHTML = 'Error retrieving data. <a href="javascript:void(0);" onclick="getChartData();">Try again?</a>';
                }
            } else {
                getele('divLoadingText').innerHTML = 'Error retrieving data. <a href="javascript:void(0);" onclick="getChartData();">Try again?</a>';
            }
            delete requests;
        }
    }
}

function dataUnion(dest, src, rss)
{
    for (var k in src) {
        if (!isNaN(parseInt(k))) {
            for (var x in src[k]) {
                src[k][x] = rss + '|' + src[k][x];
            }
            if (k in dest) {
                dest[k] += src[k];
            } else {
                dest[k] = src[k];
            }
        }
        else {
            if (k in dest) {
                dataUnion(dest[k], src[k], rss);
            } else {
                dest[k] = src[k];
            }
        }
    }
}

var myChartData = {
    drawing: false,
    drawagain: false,
    masks: {'rp': '', 'region': '', 'gender': '', 'classs': '', 'race': '', 'level': [0, __maxLevel], 'faction': ''},
    jsondata: undefined
};

function drawCharts(p_jsondata)
{
    var starttime = (new Date()).getTime();
    if (typeof p_jsondata != 'undefined') {
        myChartData.jsondata = p_jsondata;
    } else {
        if (typeof myChartData.jsondata == 'undefined') {
            return;
        }
    }
    if (myChartData.drawing) {
        myChartData.drawagain = true;
        return;
    }
    myChartData.drawagain = false;
    myChartData.drawing = true;
    if (getele('tblCharts')) {
        getele('tblCharts').style.cursor = 'wait';
    }

    var x, y;
    var factions = __lookups.factions;
    var factionlookup = [];
    for (f in factions) {
        for (x = 0; x < factions[f].length; x++) {
            factionlookup[factions[f][x]] = f;
        }
    }
    var colorset = __lookups.colorSet;
    //for (x = 0; x < factions['Alliance'].length; x++) colorset[factions['Alliance'][x]] = '#223355';
    //for (x = 0; x < factions['Horde'].length; x++) colorset[factions['Horde'][x]] = '#883333';

    var isRealmset = (typeof myChartData.jsondata.demographics != 'undefined');

    var _gender, _class, _race, _level, _faction, _char;
    var counts = {
        'rp': [],
        'region': [],
        'gender': [],
        'classs': [],
        'race': [],
        'level': [],
        'faction': [],
        'all': 0,
        'guilds': 0
    };

    if (isRealmset) {
        myChartData['chtList'] = {};
        myChartData['chtList'].datatoadd = [];
        myChartData['chtList'].data = new google.visualization.DataTable();
        myChartData['chtList'].data.addColumn('string', 'Name');
        myChartData['chtList'].data.addColumn('string', 'RP');
        myChartData['chtList'].data.addColumn('string', 'Region');
        myChartData['chtList'].data.addColumn('number', 'Alliance');
        myChartData['chtList'].data.addColumn('number', 'Horde');
        myChartData['chtList'].data.addColumn('number', 'A/H Ratio');
        myChartData['chtList'].data.addColumn('number', 'Total');
    }

    if (myChartData.masks.level[0] < 0) {
        myChartData.masks.level[0] = 0;
    }
    if (myChartData.masks.level[1] > __maxLevel) {
        myChartData.masks.level[1] = __maxLevel;
    }
    if (myChartData.masks.level[0] > myChartData.masks.level[1]) {
        myChartData.masks.level[0] = myChartData.masks.level[1];
    }

    if (!isRealmset) {
        for (_gender in myChartData.jsondata.characters) {
            if ((myChartData.masks.gender == '') || (myChartData.masks.gender == _gender)) {
                if (typeof counts.gender[_gender] == 'undefined') {
                    counts.gender[_gender] = 0;
                }
                for (_class in myChartData.jsondata.characters[_gender]) {
                    if ((myChartData.masks.classs == '') || (myChartData.masks.classs == _class)) {
                        if (typeof counts.classs[_class] == 'undefined') {
                            counts.classs[_class] = 0;
                        }
                        for (_race in myChartData.jsondata.characters[_gender][_class]) {
                            if (((myChartData.masks.race == '') || (myChartData.masks.race == _race)) && ((myChartData.masks.faction == '') || (myChartData.masks.faction == factionlookup[_race]))) {
                                _faction = factionlookup[_race];
                                if (typeof counts.race[_race] == 'undefined') {
                                    counts.race[_race] = 0;
                                }
                                if (typeof counts.faction[_faction] == 'undefined') {
                                    counts.faction[_faction] = 0;
                                }
                                for (_level = myChartData.masks.level[1]; _level >= myChartData.masks.level[0]; _level--) {
                                    if (typeof counts.level[_level] == 'undefined') {
                                        counts.level[_level] = 0;
                                    }
                                    if (typeof myChartData.jsondata.characters[_gender][_class][_race][_level] == 'undefined') {
                                        myChartData.jsondata.characters[_gender][_class][_race][_level] = 0;
                                    }
                                    x = myChartData.jsondata.characters[_gender][_class][_race][_level];
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
                }
            }
        }
    } else { //isRealmset
        for (var _rp in myChartData.jsondata.demographics) {
            if ((myChartData.masks.rp == '') || (myChartData.masks.rp == _rp)) {
                if (typeof counts.rp[_rp] == 'undefined') {
                    counts.rp[_rp] = 0;
                }
                for (var _region in myChartData.jsondata.demographics[_rp]) {
                    if ((myChartData.masks.region == '') || (myChartData.masks.region == _region)) {
                        if (typeof counts.region[_region] == 'undefined') {
                            counts.region[_region] = 0;
                        }
                        for (_gender in myChartData.jsondata.demographics[_rp][_region]) {
                            if ((myChartData.masks.gender == '') || (myChartData.masks.gender == _gender)) {
                                if (typeof counts.gender[_gender] == 'undefined') {
                                    counts.gender[_gender] = 0;
                                }
                                for (_class in myChartData.jsondata.demographics[_rp][_region][_gender]) {
                                    if ((myChartData.masks.classs == '') || (myChartData.masks.classs == _class)) {
                                        if (typeof counts.classs[_class] == 'undefined') {
                                            counts.classs[_class] = 0;
                                        }
                                        for (_race in myChartData.jsondata.demographics[_rp][_region][_gender][_class]) {
                                            if (((myChartData.masks.race == '') || (myChartData.masks.race == _race)) && ((myChartData.masks.faction == '') || (myChartData.masks.faction == factionlookup[_race]))) {
                                                _faction = factionlookup[_race];
                                                if (typeof counts.race[_race] == 'undefined') {
                                                    counts.race[_race] = 0;
                                                }
                                                if (typeof counts.faction[_faction] == 'undefined') {
                                                    counts.faction[_faction] = 0;
                                                }
                                                for (_level = myChartData.masks.level[1]; _level >= myChartData.masks.level[0]; _level--) {
                                                    if (typeof counts.level[_level] == 'undefined') {
                                                        counts.level[_level] = 0;
                                                    }
                                                    if (typeof myChartData.jsondata.demographics[_rp][_region][_gender][_class][_race][_level] == 'undefined') {
                                                        myChartData.jsondata.demographics[_rp][_region][_gender][_class][_race][_level] = 0;
                                                    }
                                                    x = myChartData.jsondata.demographics[_rp][_region][_gender][_class][_race][_level];
                                                    counts.rp[_rp] += x;
                                                    counts.region[_region] += x;
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
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if (isRealmset) {
        getele('divTtlRegion').innerHTML = 'Region: ' + (myChartData.masks.region == '' ? '(All)' : myChartData.masks.region);
        getele('divTtlRP').innerHTML = 'RP/Normal: ' + (myChartData.masks.rp == '' ? '(All)' : myChartData.masks.rp);
        var realmpass;
        for (_slug in myChartData.jsondata.realms) {
            realmpass = true;
            for (_attrib in myChartData.jsondata.realms[_slug].stats) {
                if ((typeof myChartData.masks[_attrib] != 'undefined') && (myChartData.masks[_attrib] != '') && (myChartData.jsondata.realms[_slug].stats[_attrib] != myChartData.masks[_attrib])) {
                    realmpass = false;
                }
            }
            if (realmpass) {
                var listline = [
                    {
                        v: myChartData.jsondata.realms[_slug]['name'],
                        f: '<a href="' + __realmset + '-' + _slug + '.html">' + myChartData.jsondata.realms[_slug]['name'] + '</a>'
                    },
                    myChartData.jsondata.realms[_slug].stats.rp,
                    myChartData.jsondata.realms[_slug].stats.region,
                    myChartData.jsondata.realms[_slug].counts['Alliance'],
                    myChartData.jsondata.realms[_slug].counts['Horde'],
                    myChartData.jsondata.realms[_slug].counts['Alliance'] / myChartData.jsondata.realms[_slug].counts['Horde'],
                    myChartData.jsondata.realms[_slug].counts['Alliance'] + myChartData.jsondata.realms[_slug].counts['Horde'] + myChartData.jsondata.realms[_slug].counts['Unknown']
                ];

                if ((__realmset + '-' + _slug) in __connectedRealms) {
                    listline[0].f += ' <abbr title="Connected Realm" style="font-size: 50%">[CR]</abbr>';
                    for (var crslugx in __connectedRealms[__realmset + '-' + _slug]) {
                        var crslug = __connectedRealms[__realmset + '-' + _slug][crslugx].substr(3);
                        if (crslug in myChartData.jsondata.realms) {
                            listline[3] += myChartData.jsondata.realms[crslug].counts['Alliance'];
                            listline[4] += myChartData.jsondata.realms[crslug].counts['Horde'];
                            listline[5] = listline[3] / listline[4];
                            listline[6] += myChartData.jsondata.realms[crslug].counts['Alliance'] + myChartData.jsondata.realms[crslug].counts['Horde'] + myChartData.jsondata.realms[crslug].counts['Unknown'];
                        }
                    }
                }
                myChartData['chtList'].datatoadd.push(listline);
            }
        }
    }

    //console.log('Data selection (ms):',(new Date()).getTime() - starttime); starttime = (new Date()).getTime();

    var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
    getele('divTtlFaction').innerHTML = 'Faction: ' + (myChartData.masks.faction == '' ? '(All)' : myChartData.masks.faction);
    getele('divTtlGender').innerHTML = 'Gender: ' + (myChartData.masks.gender == '' ? '(All)' : myChartData.masks.gender);
    getele('divTtlRace').innerHTML = 'Race: ' + (myChartData.masks.race == '' ? '(All)' : myChartData.masks.race);
    getele('divTtlClass').innerHTML = 'Class: ' + (myChartData.masks.classs == '' ? '(All)' : myChartData.masks.classs);
    getele('divResults').innerHTML = '' + formatter.formatValue(counts.all) + '<br>Character' + (counts.all != 1 ? 's' : '');

    var colors;

    if (isRealmset) {
        myChartData['chtList'].data.addRows(myChartData['chtList'].datatoadd);
        myChartData['chtList'].datatoadd = [];

        var formatter = new google.visualization.NumberFormat({fractionDigits: 3});
        formatter.format(myChartData['chtList'].data, 5);

        var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
        formatter.format(myChartData['chtList'].data, 3);
        formatter.format(myChartData['chtList'].data, 4);
        formatter.format(myChartData['chtList'].data, 6);

        buildtablesort('List');
    }

    //console.log('List render (ms):',(new Date()).getTime() - starttime); starttime = (new Date()).getTime();

    if (isRealmset) {
        if (typeof myChartData['chtByRP'] == 'undefined') {
            myChartData['chtByRP'] = {};
        }
        myChartData['chtByRP'].data = new google.visualization.DataTable();
        myChartData['chtByRP'].data.addColumn('string', 'RP');
        myChartData['chtByRP'].data.addColumn('number', 'Characters');
        myChartData['chtByRP'].data.addColumn('string', 'Color');
        var ta = [];
        for (_rp in counts.rp) {
            ta.push([_rp, counts.rp[_rp], colorset[_rp] ? colorset[_rp] : '#999999']);
        }
        myChartData['chtByRP'].data.addRows(ta);
        formatter.format(myChartData['chtByRP'].data, 1);
        myChartData['chtByRP'].data.sort([{column: 1, desc: true}]);
        colors = Array(myChartData['chtByRP'].data.getNumberOfRows());
        for (x = 0; x < colors.length; x++) {
            colors[x] = myChartData['chtByRP'].data.getValue(x, 2);
        }
        if (typeof myChartData['chtByRP'].chart == 'undefined') {
            myChartData['chtByRP'].chart = new google.visualization.PieChart(document.getElementById('chtByRP'));
            google.visualization.events.addListener(myChartData['chtByRP'].chart, 'select', function () {
                var newmask = myChartData['chtByRP'].data.getValue(myChartData['chtByRP'].chart.getSelection()[0].row, 0);
                myChartData.masks.rp = (myChartData.masks.rp == newmask) ? '' : newmask;
                drawCharts();
            });
        }
        myChartData['chtByRP'].chart.draw(myChartData['chtByRP'].data, {
            backgroundColor: '#FFFFFF',
            legend: {position: 'none'},
            chartArea: {width: '95%', height: '95%'},
            colors: colors,
            is3D: true,
            pieSliceText: 'label'
        });

        if (typeof myChartData['chtByRegion'] == 'undefined') {
            myChartData['chtByRegion'] = {};
        }
        myChartData['chtByRegion'].data = new google.visualization.DataTable();
        myChartData['chtByRegion'].data.addColumn('string', 'Region');
        myChartData['chtByRegion'].data.addColumn('number', 'Characters');
        myChartData['chtByRegion'].data.addColumn('string', 'Color');
        ta = [];
        for (_region in counts.region) {
            ta.push([_region, counts.region[_region], colorset[_region] ? colorset[_region] : '#999999']);
        }
        myChartData['chtByRegion'].data.addRows(ta);
        formatter.format(myChartData['chtByRegion'].data, 1);
        myChartData['chtByRegion'].data.sort([{column: 1, desc: true}]);
        colors = Array(myChartData['chtByRegion'].data.getNumberOfRows());
        for (x = 0; x < colors.length; x++) {
            colors[x] = myChartData['chtByRegion'].data.getValue(x, 2);
        }
        if (typeof myChartData['chtByRegion'].chart == 'undefined') {
            myChartData['chtByRegion'].chart = new google.visualization.PieChart(document.getElementById('chtByRegion'));
            google.visualization.events.addListener(myChartData['chtByRegion'].chart, 'select', function () {
                var newmask = myChartData['chtByRegion'].data.getValue(myChartData['chtByRegion'].chart.getSelection()[0].row, 0);
                myChartData.masks.region = (myChartData.masks.region == newmask) ? '' : newmask;
                drawCharts();
            });
        }
        myChartData['chtByRegion'].chart.draw(myChartData['chtByRegion'].data, {
            backgroundColor: '#FFFFFF',
            legend: {position: 'none'},
            chartArea: {width: '95%', height: '95%'},
            //colors: colors,
            is3D: true,
            pieSliceText: 'label'
        });
    }

    if (typeof myChartData['chtByGender'] == 'undefined') {
        myChartData['chtByGender'] = {};
    }
    myChartData['chtByGender'].data = new google.visualization.DataTable();
    myChartData['chtByGender'].data.addColumn('string', 'Gender');
    myChartData['chtByGender'].data.addColumn('number', 'Characters');
    myChartData['chtByGender'].data.addColumn('string', 'Color');
    var ta = [];
    for (_gender in counts.gender) {
        ta.push([_gender, counts.gender[_gender], colorset[_gender] ? colorset[_gender] : '#999999']);
    }
    myChartData['chtByGender'].data.addRows(ta);
    formatter.format(myChartData['chtByGender'].data, 1);
    myChartData['chtByGender'].data.sort([{column: 1, desc: true}]);
    colors = Array(myChartData['chtByGender'].data.getNumberOfRows());
    for (x = 0; x < colors.length; x++) {
        colors[x] = myChartData['chtByGender'].data.getValue(x, 2);
    }
    if (typeof myChartData['chtByGender'].chart == 'undefined') {
        myChartData['chtByGender'].chart = new google.visualization.PieChart(document.getElementById('chtByGender'));
        google.visualization.events.addListener(myChartData['chtByGender'].chart, 'select', function () {
            var newmask = myChartData['chtByGender'].data.getValue(myChartData['chtByGender'].chart.getSelection()[0].row, 0);
            myChartData.masks.gender = (myChartData.masks.gender == newmask) ? '' : newmask;
            drawCharts();
        });
    }
    myChartData['chtByGender'].chart.draw(myChartData['chtByGender'].data, {
        backgroundColor: '#FFFFFF',
        legend: {position: 'none'},
        chartArea: {width: '95%', height: '95%'},
        colors: colors,
        is3D: true,
        pieSliceText: 'label'
    });

    if (typeof myChartData['chtByRace'] == 'undefined') {
        myChartData['chtByRace'] = {};
    }
    myChartData['chtByRace'].data = new google.visualization.DataTable();
    myChartData['chtByRace'].data.addColumn('string', 'Race');
    myChartData['chtByRace'].data.addColumn('number', 'Characters');
    myChartData['chtByRace'].data.addColumn('string', 'Faction');
    myChartData['chtByRace'].data.addColumn('string', 'Color');
    ta = [];
    for (_race in counts.race) {
        ta.push([_race, counts.race[_race], factionlookup[_race], colorset[_race] ? colorset[_race] : '#999999']);
    }
    myChartData['chtByRace'].data.addRows(ta);
    formatter.format(myChartData['chtByRace'].data, 1);
    myChartData['chtByRace'].data.sort([{column: 2}, {column: 1, desc: true}]);
    colors = Array(myChartData['chtByRace'].data.getNumberOfRows());
    for (x = 0; x < colors.length; x++) {
        colors[x] = myChartData['chtByRace'].data.getValue(x, 3);
    }
    if (typeof myChartData['chtByRace'].chart == 'undefined') {
        myChartData['chtByRace'].chart = new google.visualization.PieChart(document.getElementById('chtByRace'));
        google.visualization.events.addListener(myChartData['chtByRace'].chart, 'select', function () {
            var newmask = myChartData['chtByRace'].data.getValue(myChartData['chtByRace'].chart.getSelection()[0].row, 0);
            myChartData.masks.race = (myChartData.masks.race == newmask) ? '' : newmask;
            drawCharts();
        });
    }
    myChartData['chtByRace'].chart.draw(myChartData['chtByRace'].data, {
        backgroundColor: '#FFFFFF',
        legend: {position: 'none'},
        chartArea: {width: '95%', height: '95%'},
        colors: colors,
        is3D: true,
        pieSliceText: 'label'
        //hAxis: {textPosition: 'none'},
        //vAxis: {textPosition: 'in'},
        //pieSliceText: 'value'
    });

    if (typeof myChartData['chtByClass'] == 'undefined') {
        myChartData['chtByClass'] = {};
    }
    myChartData['chtByClass'].data = new google.visualization.DataTable();
    myChartData['chtByClass'].data.addColumn('string', 'Class');
    myChartData['chtByClass'].data.addColumn('number', 'Characters');
    myChartData['chtByClass'].data.addColumn('string', 'Color');
    ta = [];
    for (_class in counts.classs) {
        ta.push([_class, counts.classs[_class], colorset[_class] ? colorset[_class] : '#999999']);
    }
    myChartData['chtByClass'].data.addRows(ta);
    formatter.format(myChartData['chtByClass'].data, 1);
    myChartData['chtByClass'].data.sort([{column: 1, desc: true}]);
    colors = Array(myChartData['chtByClass'].data.getNumberOfRows());
    for (x = 0; x < colors.length; x++) {
        colors[x] = myChartData['chtByClass'].data.getValue(x, 2);
    }
    if (typeof myChartData['chtByClass'].chart == 'undefined') {
        myChartData['chtByClass'].chart = new google.visualization.PieChart(document.getElementById('chtByClass'));
        google.visualization.events.addListener(myChartData['chtByClass'].chart, 'select', function () {
            var newmask = myChartData['chtByClass'].data.getValue(myChartData['chtByClass'].chart.getSelection()[0].row, 0);
            myChartData.masks.classs = (myChartData.masks.classs == newmask) ? '' : newmask;
            drawCharts();
        });
    }
    myChartData['chtByClass'].chart.draw(myChartData['chtByClass'].data, {
        backgroundColor: '#FFFFFF',
        legend: {position: 'none'},
        chartArea: {width: '95%', height: '95%'},
        colors: colors,
        pieSliceTextStyle: {color: 'black'},
        is3D: true,
        pieSliceText: 'label'
        //hAxis: {textPosition: 'none'},
        //vAxis: {textPosition: 'in'},
        //pieSliceText: 'value'
    });

    if (typeof myChartData['chtByFaction'] == 'undefined') {
        myChartData['chtByFaction'] = {};
    }
    myChartData['chtByFaction'].data = new google.visualization.DataTable();
    myChartData['chtByFaction'].data.addColumn('string', 'Faction');
    myChartData['chtByFaction'].data.addColumn('number', 'Characters');
    myChartData['chtByFaction'].data.addColumn('string', 'Color');
    ta = [];
    for (_faction in counts.faction) {
        ta.push([_faction, counts.faction[_faction], colorset[_faction] ? colorset[_faction] : '#999999']);
    }
    myChartData['chtByFaction'].data.addRows(ta);
    formatter.format(myChartData['chtByFaction'].data, 1);
    //myChartData['chtByFaction'].data.sort([{column: 1, desc: true}]);
    colors = Array(myChartData['chtByFaction'].data.getNumberOfRows());
    for (x = 0; x < colors.length; x++) {
        colors[x] = myChartData['chtByFaction'].data.getValue(x, 2);
    }
    if (typeof myChartData['chtByFaction'].chart == 'undefined') {
        myChartData['chtByFaction'].chart = new google.visualization.PieChart(document.getElementById('chtByFaction'));
        google.visualization.events.addListener(myChartData['chtByFaction'].chart, 'select', function () {
            var newmask = myChartData['chtByFaction'].data.getValue(myChartData['chtByFaction'].chart.getSelection()[0].row, 0);
            myChartData.masks.faction = (myChartData.masks.faction == newmask) ? '' : newmask;
            drawCharts();
        });
    }
    myChartData['chtByFaction'].chart.draw(myChartData['chtByFaction'].data, {
        backgroundColor: '#FFFFFF',
        legend: {position: 'none'},
        chartArea: {width: '95%', height: '95%'},
        colors: colors,
        pieSliceTextStyle: {color: 'white'},
        is3D: true,
        pieSliceText: 'label'
        //hAxis: {textPosition: 'none'},
        //vAxis: {textPosition: 'in'},
        //pieSliceText: 'value'
    });

    if (getele('divLoading')) {
        getele('divLoading').style.display = 'none';
    }
    if (getele('divAllCharts')) {
        getele('divAllCharts').style.visibility = 'visible';
    }

    window.setTimeout(function () {
        myChartData.drawing = false;
        if (myChartData.drawagain) {
            drawCharts();
        } else {
            if (getele('tblCharts')) {
                getele('tblCharts').style.cursor = 'auto';
            }
        }
    }, 250);
    //console.log('Chart draw (ms):',(new Date()).getTime() - starttime);
}

function buildtablesort(nm)
{
    var fromjson = jQuery.parseJSON(myChartData['cht' + nm].data.toJSON());
    var tbl = document.createElement('table');
    tbl.id = 'tbl' + nm;
    tbl.className = "tablesorter";
    var thead = tbl.appendChild(document.createElement('thead'));
    var tr = thead.appendChild(document.createElement('tr'));
    for (_col in fromjson.cols) {
        var td = tr.appendChild(document.createElement('th'));
        td.innerHTML = fromjson.cols[_col].label;
        td.style.textAlign = (fromjson.cols[_col].type != 'string') ? 'right' : 'left';
    }
    var tbody = tbl.appendChild(document.createElement('tbody'));
    for (_row in fromjson.rows) {
        tr = tbody.appendChild(document.createElement('tr'));
        for (_c = 0; _c < fromjson.rows[_row].c.length; _c++) {
            td = tr.appendChild(document.createElement('td'));
            if (("f" in fromjson.rows[_row].c[_c]) && (fromjson.rows[_row].c[_c].f != null)) {
                td.innerHTML = fromjson.rows[_row].c[_c].f;
            } else {
                if ("v" in fromjson.rows[_row].c[_c]) {
                    td.innerHTML = fromjson.rows[_row].c[_c].v;
                }
            }
            if ("v" in fromjson.rows[_row].c[_c]) {
                td.setAttribute('sortby', fromjson.rows[_row].c[_c].v);
            }
            if (fromjson.cols[_c].type != 'string') {
                td.style.textAlign = 'right';
            }
        }
    }
    if (getele('cht' + nm).firstChild) {
        getele('cht' + nm).replaceChild(tbl, getele('cht' + nm).firstChild);
    } else {
        getele('cht' + nm).appendChild(tbl);
    }
    $('#tbl' + nm).tablesorter({
        textExtraction: function (node) {
            var r = node.getAttribute('sortby');
            return (r == null) ? node.innerHTML : r;
        }, sortList: (nm == 'GuildList' ? [[2, 1]] : [[0, 0]])
    });
}
