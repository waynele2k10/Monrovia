// STRINGS

String.prototype.isAnyOf = function(ary){ var obj = this;return ary.any(function(item,indx){return(item==obj);}) };

String.prototype.getExtension = function (){ var indx = this.lastIndexOf('.')+1; if(indx>0){return this.substring(indx).toLowerCase();}else{return '';}; };

/*
String.prototype.getFilePath = function (){
	var ret = this;
	var i = ret.indexOf('://');
	if(i>-1){
		ret = ret.substring(i+3);
		i = ret.indexOf('/');
		if(i>-1) ret = ret.substring(i);
	}
	return ret;
}
*/

String.prototype.getServer = function (){ var ret = '';var i = this.indexOf('://');if(i>-1){ret = this.substr(i+3);i = ret.indexOf('/');if(i>-1) ret = ret.substring(0,i);};return ret; };

String.prototype.getFileName = function(){ var indx = this.lastIndexOf('/'); return (indx==-1)?this:this.substr(indx+1); }

String.prototype.contains = String.prototype.include;
String.prototype.replaceAll = String.prototype.gsub;

if(!String.prototype.trim) String.prototype.trim=function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};

// BROWSER INFO

window.details = {
	innerHeight:function(){
		  if( typeof( window.innerWidth ) == 'number' ) {
		    //Non-IE
		    return window.innerHeight;
		  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		    //IE 6+ in 'standards compliant mode'
		    return document.documentElement.clientHeight;
		  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		    //IE 4 compatible
		    return document.body.clientHeight;
		  }
	},
	
	innerWidth:function(){
		  if( typeof( window.innerWidth ) == 'number' ) {
		    //Non-IE
		    return window.innerWidth;
		  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		    //IE 6+ in 'standards compliant mode'
		    return document.documentElement.clientWidth;
		  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		    //IE 4 compatible
		    return document.body.clientWidth;
		  }
	},
	
	scrollTop:function(){
		return (window.pageYOffset||document.body.scrollTop||document.documentElement.scrollTop);
	},
	
	scrollLeft:function(){
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			return window.pageXOffset;
		} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
			//DOM compliant
			return document.body.scrollLeft;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
			//IE6 standards compliant mode
			return document.documentElement.scrollLeft;
		}
	},
	
	ieVersion:function()
		// Returns the version of Internet Explorer or a -1
		// (indicating the use of another browser).
		{
		  var rv = -1; // Return value assumes failure.
		  if (navigator.appName == 'Microsoft Internet Explorer')
		  {
		    var ua = navigator.userAgent;
		    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		    if (re.exec(ua) != null)
		      rv = parseFloat( RegExp.$1 );
		  }
		  return rv;
	},
	
	queryString:function(txt){
		// RETURNS QUERYSTRING PARAMETER VALUE
		// USAGE:
		// window.details.queryString()['parameter_name'];
		// window.details.queryString('er=1&redir=%2Fbusiness%2Fprojectors%2Fextranet.asp%3F')['er'];
		var ret = [];
		txt = (txt||window.location.search);
		if(!txt.indexOf('?')) txt = txt.substr(1);
		var expressions = txt.split('&');
		for(var i=0;i<expressions.length;i++){
			var parts = expressions[i].split('=');
			if(parts.length==2){
				ret[unescape(parts[0])] = unescape(parts[1]);
			}
		}
		return ret;
	}
	
}