var cloudfetch = {};

cloudfetch.hits = 0;
cloudfetch.maxhits = 10;

cloudfetch.fetchurl = function(url) {
	if (url == '') {
		if (cloudfetch.hits >= cloudfetch.maxhits) return;
		cloudfetch.hits++;
	
		var req=window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest();
		req.open('GET','census/cloud.php?cloudrequesturl=1',true);
		req.onreadystatechange = function(evt) {
			if ((req.readyState == 4) && (req.status == 200)) 
		 		cloudfetch.fetchurl(req.responseText);
	 	}
	 	req.send(null);
	 } else if ((url.length <= 255) && (url.indexOf('//') >= 0)) {
		cloudfetch.callback(url);
		var h = document.getElementsByTagName('head')[0];
		var s = document.createElement('script');
		s.src = url+((url.indexOf('?') < 0)?'?':'&')+'jsonp=cloudfetch.callback'; 
		if (s.addEventListener) {
			s.addEventListener('error', function(evt){if (evt) evt.stopPropagation(); cloudfetch.callback('');}, false);
		} 
		h.appendChild(s);
	}
}

cloudfetch.callback = function(o) {
	if (typeof this.fromurl == 'undefined') {
		this.fromurl = o;
	} else {
		try {
			topost = JSON.stringify(o);
		} catch (e) {
			topost = '';
		}
		var req=window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest();
		req.open('POST','census/cloud.php?cloudreturndata=1'+(cloudfetch.hits++<cloudfetch.maxhits?'&cloudrequesturl=1':''),true);
		if (topost.length < 24510) topost = encodeURIComponent(topost); else topost = encodeURIComponent(base64encode(lzw_encode(stringToBytes(topost)))) + '&lzw=1';
		params = 'url='+encodeURIComponent(this.fromurl)+'&data='+topost;
		req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		req.setRequestHeader('Content-length', params.length);
		req.onreadystatechange = function(evt) {
			if ((req.readyState == 4) && (req.status == 200)) 
		 		cloudfetch.fetchurl(req.responseText);
	 	}
	 	req.send(params);
		this.fromurl = undefined;
	}
}

cloudfetch.stickevent = function(o,ev,ptr){if(o.addEventListener)o.addEventListener(ev,ptr,false);else if(o.attachEvent)o.attachEvent('on'+ev,ptr);}

// http://detectmobilebrowsers.com/
//cloudfetch.stickevent(window,'load',function(){(function(a,b){if(/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))void(0);else{b();}})(navigator.userAgent||navigator.vendor||window.opera,function(){cloudfetch.fetchurl('')})});


function stringToBytes ( str ) {
  var ch, st, re = [];
  for (var i = 0; i < str.length; i++ ) {
    ch = str.charCodeAt(i);  // get char
	switch (true) {
		case (ch <= 0x80):
			re.push(ch);
			break;
		case (ch <= 0x800):
			// 2 bytes
			re.push(0xC0 | ((ch & 0x7C0) >>> 6));
			re.push(0x80 | (ch & 0x3f));
			break;
		case (c <= 0x10000):
			// 3 bytes
			re.push(0xE0 | ((ch & 0xF000) >>> 12));
			re.push(0x80 | ((ch & 0xFC0) >>> 6));
			re.push(0x80 | (ch & 0x3f));
			break;
		default:
			// 4 bytes
			re.push(0xF0 | ((ch & 0x1C0000) >>> 18));
			re.push(0x80 | ((ch & 0x3F000) >>> 12));
			re.push(0x80 | ((ch & 0xFC0) >>> 6));
			re.push(0x80 | (ch & 0x3f));
			break;
	}

    /*
    if (ch > 255) {
		alert(ch);
		st = [];                 // set up "stack"
	    do {
	      st.push( ch & 0xFF );  // push byte to stack
	      ch = ch >> 8;          // shift value down by 1 byte
	    }  
	    while ( ch );
	    // add stack contents to result
	    // done because chars have "wrong" endianness
	    //re = re.concat( st.reverse() );
	    while (st.length > 0) re.push(st.pop());
	} else re.push(ch);
	*/
  }
  // return an array of bytes
  return re;
}

function lzw_encode(uncompressed) {
		"use strict";
		var i,
			dictionary = {},
			c,
			wc,
			w = "",
			result = [],
			dictSize = 256;
		for (i = 0; i < 256; i += 1) {
			dictionary[String.fromCharCode(i)] = i;
		}
 
		for (i = 0; i < uncompressed.length; i += 1) {
			c = String.fromCharCode(uncompressed[i]);
			wc = w + c;
			if (dictionary[wc]) {
				w = wc;
			} else {
				result.push(dictionary[w]);
				dictionary[wc] = dictSize++;
				w = String(c);
			}
		}
 
		if (w !== "") {
			result.push(dictionary[w]);
		}
		return result;		
    }

function base64encode(input) {
	var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_=";

	var bitwidth = 0;
	var maxval = 0;
	for (var x = 0; x < input.length; x++) if (input[x] > maxval) maxval = input[x];
	while (Math.pow(2,++bitwidth) < maxval);
	if (bitwidth < 5) bitwidth = 5;

	x = 0;
	var iter = 0, maxiter = input.length*bitwidth/5+1;
	var curstring = 0,curbits = 0,curbyte = 0,mask,output=_keyStr.charAt(bitwidth),y;
	while (x < input.length) {
		if (iter++ > maxiter) break;
		if (curbits < 5) {
			curstring = curstring << bitwidth | (x>=input.length?0:input[x++]);
			curbits += bitwidth;
		}
		curbyte = (curstring & (0x1F << (curbits - 5))) >> (curbits - 5);

		mask=0;
		for (y = curbits; y > 5; y--) mask = mask << 1 | 1;
		curbits -= 5;
		curstring = curstring & mask;

		output += _keyStr.charAt(curbyte);
	}
	if (curbits % 5 != 0) {
		curstring = curstring << (5-(curbits % 5));
		curbits += 5-(curbits % 5);
	}
	while (curbits >= 5) {
		curbyte = (curstring & (0x1F << (curbits - 5))) >> (curbits - 5);
		mask=0;
		for (x = curbits; x > 5; x--) mask = mask << 1 | 1;
		curbits -= 5;
		curstring = curstring & mask;

		output += _keyStr.charAt(curbyte);
	}	
	return output;	
}

// http://www.json.org/js.html
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

