!function($,e){var t,s,n,o,i,r,a,c,p=this;p.FS=p.FS||{},p.FS.PostMessage=(s=new NoJQueryPostMessageMixin("postMessage","receiveMessage"),n={},o=decodeURIComponent(document.location.hash.replace(/^#/,"")),i=o.substring(0,o.indexOf("/","https://"===o.substring(0,"https://".length)?8:7)),r=""!==o,a=$(window),c=$("html"),{init:function(e,o){t=e,s.receiveMessage((function(e){var t=JSON.parse(e.data);if(n[t.type])for(var s=0;s<n[t.type].length;s++)n[t.type][s](t.data)}),t),FS.PostMessage.receiveOnce("forward",(function(e){window.location=e.url})),(o=o||[]).length>0&&a.on("scroll",(function(){for(var e=0;e<o.length;e++)FS.PostMessage.postScroll(o[e])}))},init_child:function(){this.init(i),$(window).bind("load",(function(){FS.PostMessage.postHeight(),FS.PostMessage.post("loaded")}))},hasParent:function(){return r},postHeight:function(e,t){e=e||0,t=t||"#wrap_section",this.post("height",{height:e+$(t).outerHeight(!0)})},postScroll:function(e){this.post("scroll",{top:a.scrollTop(),height:a.height()-parseFloat(c.css("paddingTop"))-parseFloat(c.css("marginTop"))},e)},post:function(e,t,n){console.debug("PostMessage.post",e),n?s.postMessage(JSON.stringify({type:e,data:t}),n.src,n.contentWindow):s.postMessage(JSON.stringify({type:e,data:t}),o,window.parent)},receive:function(t,s){console.debug("PostMessage.receive",t),e===n[t]&&(n[t]=[]),n[t].push(s)},receiveOnce:function(e,t){this.is_set(e)||this.receive(e,t)},is_set:function(t){return e!=n[t]},parent_url:function(){return o},parent_subdomain:function(){return i}})}(jQuery);