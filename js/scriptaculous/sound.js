Sound={tracks:{},_enabled:true,template:new Template('<embed style="height:0" id="sound_#{track}_#{id}" src="#{url}" loop="false" autostart="true" hidden="true"/>'),enable:function(){Sound._enabled=true},disable:function(){Sound._enabled=false},play:function(b){if(!Sound._enabled){return}var a=Object.extend({track:"global",url:b,replace:false},arguments[1]||{});if(a.replace&&this.tracks[a.track]){$R(0,this.tracks[a.track].id).each(function(d){var c=$("sound_"+a.track+"_"+d);c.Stop&&c.Stop();c.remove()});this.tracks[a.track]=null}if(!this.tracks[a.track]){this.tracks[a.track]={id:0}}else{this.tracks[a.track].id++}a.id=this.tracks[a.track].id;$$("body")[0].insert(Prototype.Browser.IE?new Element("bgsound",{id:"sound_"+a.track+"_"+a.id,src:a.url,loop:1,autostart:true}):Sound.template.evaluate(a))}};if(Prototype.Browser.Gecko&&navigator.userAgent.indexOf("Win")>0){if(navigator.plugins&&$A(navigator.plugins).detect(function(a){return a.name.indexOf("QuickTime")!=-1})){Sound.template=new Template('<object id="sound_#{track}_#{id}" width="0" height="0" type="audio/mpeg" data="#{url}"/>')}else{if(navigator.plugins&&$A(navigator.plugins).detect(function(a){return a.name.indexOf("Windows Media")!=-1})){Sound.template=new Template('<object id="sound_#{track}_#{id}" type="application/x-mplayer2" data="#{url}"></object>')}else{if(navigator.plugins&&$A(navigator.plugins).detect(function(a){return a.name.indexOf("RealPlayer")!=-1})){Sound.template=new Template('<embed type="audio/x-pn-realaudio-plugin" style="height:0" id="sound_#{track}_#{id}" src="#{url}" loop="false" autostart="true" hidden="true"/>')}else{Sound.play=function(){}}}}};