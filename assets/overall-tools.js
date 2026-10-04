(function(){'use strict';
  var Suite={handlers:{},forms:{},remoteTasks:{
    'webpage-article-extractor':1,'website-font-finder':1,'website-asset-extractor':1,'website-color-palette-extractor':1,
    'rss-feed-finder-validator':1,'website-contact-social-extractor':1,'website-charset-language-checker':1,
    'public-source-code-viewer':1,'sitemap-comparison-tool':1,'http-header-comparison':1,'webpage-content-comparison':1,
    'website-migration-url-validator':1
  }};
  var q=function(s,r){return (r||document).querySelector(s);},qa=function(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s));};
  function track(eventName,slug,state){if(window.ufxTrack)window.ufxTrack(eventName,{tool_slug:slug||'',result_state:state||''});}
  function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];});}
  function cleanName(v){return String(v||'result.txt').replace(/[^a-z0-9._-]+/gi,'-').replace(/^-+|-+$/g,'').slice(0,120)||'result.txt';}
  function save(blob,name){var a=document.createElement('a'),url=URL.createObjectURL(blob);a.href=url;a.download=cleanName(name);document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(url);},1500);}
  function saveText(text,name,type){save(new Blob([text],{type:type||'text/plain;charset=utf-8'}),name||'result.txt');}
  function status(root,message,type){var el=q('.ufxots-status',root);el.className='ufxots-status'+(type?' is-'+type:'');el.textContent=message;}
  function summaryHtml(summary){var keys=Object.keys(summary||{});if(!keys.length)return '';return '<div class="ufxots-summary">'+keys.map(function(k){return '<div class="ufxots-stat"><span>'+esc(k)+'</span><strong>'+esc(summary[k])+'</strong></div>';}).join('')+'</div>';}
  function tableHtml(rows,columns){if(!rows||!rows.length)return '';var max=rows.reduce(function(n,row){return Math.max(n,row.length||0);},0),heads=columns&&columns.length?columns:Array.from({length:max},function(_,i){return i===0?'Type':'Value '+i;});return '<div class="ufxots-table-wrap"><table class="ufxots-table"><thead><tr>'+heads.map(function(h){return '<th>'+esc(h)+'</th>';}).join('')+'</tr></thead><tbody>'+rows.map(function(row){return '<tr>'+row.map(function(cell){return '<td>'+linkify(cell)+'</td>';}).join('')+'</tr>';}).join('')+'</tbody></table></div>';}
  function linkify(value){var text=String(value==null?'':value);if(/^https?:\/\/[^\s]+$/i.test(text))return '<a href="'+esc(text)+'" target="_blank" rel="noopener noreferrer">'+esc(text)+'</a>';return esc(text);}
  function result(root,data){data=data||{};var out=q('.ufxots-output',root),preview=q('.ufxots-preview',root),raw=q('.ufxots-raw-output',root),actions=q('.ufxots-output-actions',root),html='';
    preview.hidden=true;preview.innerHTML='';
    if(data.palette&&data.palette.length)html+='<div class="ufxots-palette">'+data.palette.map(function(c){return '<button type="button" class="ufxots-swatch" style="background:'+esc(c)+'" data-copy="'+esc(c)+'" title="Copy '+esc(c)+'"><span>'+esc(c)+'</span></button>';}).join('')+'</div>';
    html+=summaryHtml(data.summary||{});html+=tableHtml(data.rows||[],data.columns||null);
    if(data.code!=null)html+='<pre class="ufxots-code">'+esc(data.code)+'</pre>';
    if(data.html)html+=data.html;
    if(data.notice)html+='<p class="ufxots-notice">'+esc(data.notice)+'</p>';
    out.innerHTML=html||'<p class="ufxots-notice">The operation completed without a text result.</p>';
    raw.value=data.text!=null?String(data.text):(data.code!=null?String(data.code):rowsToText(data.rows||[]));
    root.dataset.downloadName=data.downloadName||'result.txt';actions.hidden=!raw.value;
    qa('[data-copy]',out).forEach(function(el){el.addEventListener('click',function(){copyText(el.dataset.copy);});});
    if(data.previewNode){preview.hidden=false;preview.appendChild(data.previewNode);}
    status(root,data.status||'Completed. Review the result before using it.','success');track('tool_result',root.dataset.ufxotsTool,'success');
  }
  function rowsToText(rows){return rows.map(function(row){return row.join('\t');}).join('\n');}
  function copyText(text){if(navigator.clipboard&&window.isSecureContext)return navigator.clipboard.writeText(String(text));var ta=document.createElement('textarea');ta.value=String(text);ta.style.position='fixed';ta.style.opacity='0';document.body.appendChild(ta);ta.select();document.execCommand('copy');ta.remove();return Promise.resolve();}
  function options(root){var out={};qa('[data-name]',root).forEach(function(el){if(el.type==='checkbox')out[el.dataset.name]=!!el.checked;else if(el.type==='radio'){if(el.checked)out[el.dataset.name]=el.value;}else out[el.dataset.name]=el.value;});return out;}
  function state(root){return {root:root,slug:root.dataset.ufxotsTool,primary:(q('.ufxots-primary',root)||{}).value||'',secondary:(q('.ufxots-secondary',root)||{}).value||'',files:Array.prototype.slice.call(((q('.ufxots-files',root)||{}).files)||[]),options:options(root),field:function(name){var el=q('[data-name="'+name+'"]',root);return el?(el.type==='checkbox'?el.checked:el.value):'';}};}
  var scriptLoads={};
  function loadScript(src,test,label){label=label||'browser library';if(test&&test())return Promise.resolve();if(!src)return Promise.reject(new Error('The '+label+' URL is unavailable.'));if(scriptLoads[src])return scriptLoads[src];scriptLoads[src]=new Promise(function(resolve,reject){var finished=false,timer;
    function pass(){if(finished)return;finished=true;clearTimeout(timer);resolve();}
    function fail(){if(finished)return;finished=true;clearTimeout(timer);delete scriptLoads[src];if(s&&s.parentNode)s.parentNode.removeChild(s);reject(new Error('The required '+label+' could not load. Check content blockers or your connection and try again.'));}
    timer=setTimeout(fail,20000);var old=document.querySelector('script[src="'+src.replace(/"/g,'\\"')+'"]');
    if(old){(function poll(){if(finished)return;if(!test||test())return pass();setTimeout(poll,100);})();return;}
    var s=document.createElement('script');s.src=src;s.async=true;s.onload=function(){if(!test||test())pass();else fail();};s.onerror=fail;document.head.appendChild(s);
  });return scriptLoads[src];}
  async function post(body){var controller=window.AbortController?new AbortController():null,timer=controller?setTimeout(function(){controller.abort();},55000):null,response;
    try{response=await fetch(UFXOTS.ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:body.toString(),credentials:'same-origin',signal:controller?controller.signal:undefined});}
    catch(error){if(error&&error.name==='AbortError')throw new Error('The request timed out. Please try again.');throw new Error('The request could not reach the server. Check your connection and try again.');}
    finally{if(timer)clearTimeout(timer);}
    var text=await response.text(),json;try{json=JSON.parse(text);}catch(error){throw new Error('The server returned an unreadable response (HTTP '+response.status+').');}return {response:response,json:json};
  }
  async function refreshNonce(){var body=new URLSearchParams();body.set('action','ufxots_refresh_nonce');var reply=await post(body);if(!reply.response.ok||!reply.json||!reply.json.success||!reply.json.data||!reply.json.data.nonce)throw new Error('The security token could not be refreshed. Reload the page and try again.');UFXOTS.nonce=reply.json.data.nonce;}
  async function remote(task,primary,secondary,opts,retried){var body=new URLSearchParams();body.set('action','ufxots_remote');body.set('nonce',UFXOTS.nonce);body.set('task',task);body.set('primary',primary||'');body.set('secondary',secondary||'');body.set('options',JSON.stringify(opts||{}));var reply=await post(body);if(reply.response.status===403&&!retried){await refreshNonce();return remote(task,primary,secondary,opts,true);}var json=reply.json;if(!reply.response.ok||!json||!json.success)throw new Error(json&&json.data&&json.data.message?json.data.message:'The remote check failed (HTTP '+reply.response.status+').');return json.data;}
  function field(type,label,name,value,extra){extra=extra||{};var id='ots-'+name+'-'+Math.random().toString(36).slice(2,7),attrs='';Object.keys(extra).forEach(function(k){if(k==='options'||k==='help')return;attrs+=' '+k+'="'+esc(extra[k])+'"';});var control='';
    if(type==='textarea')control='<textarea id="'+id+'" class="at-textarea" rows="'+esc(extra.rows||6)+'" data-name="'+esc(name)+'" placeholder="'+esc(extra.placeholder||'')+'">'+esc(value||'')+'</textarea>';
    else if(type==='select')control='<select id="'+id+'" class="at-input" data-name="'+esc(name)+'">'+(extra.options||[]).map(function(o){var v=Array.isArray(o)?o[0]:o,l=Array.isArray(o)?o[1]:o;return '<option value="'+esc(v)+'"'+(String(v)===String(value)?' selected':'')+'>'+esc(l)+'</option>';}).join('')+'</select>';
    else if(type==='checkbox')return '<label class="ufxots-check"><input type="checkbox" data-name="'+esc(name)+'"'+(value?' checked':'')+'> <span>'+esc(label)+(extra.help?'<small>'+esc(extra.help)+'</small>':'')+'</span></label>';
    else control='<input id="'+id+'" class="at-input" type="'+esc(type||'text')+'" data-name="'+esc(name)+'" value="'+esc(value==null?'':value)+'" placeholder="'+esc(extra.placeholder||'')+'"'+attrs+'>';
    return '<div class="at-field"><label for="'+id+'">'+esc(label)+'</label>'+control+(extra.help?'<small>'+esc(extra.help)+'</small>':'')+'</div>';
  }
  function grid(){return '<div class="ufxots-fields-grid">'+Array.prototype.slice.call(arguments).join('')+'</div>';}
  function checks(){return '<div class="ufxots-check-grid">'+Array.prototype.slice.call(arguments).join('')+'</div>';}
  Suite.api={q:q,qa:qa,esc:esc,status:status,result:result,save:save,saveText:saveText,copyText:copyText,remote:remote,loadScript:loadScript,field:field,grid:grid,checks:checks,state:state,cleanName:cleanName};
  Suite.register=function(slug,handler,form){if(handler)Suite.handlers[slug]=handler;if(form)Suite.forms[slug]=form;};
  Suite.registerRemote=function(slug){Suite.remoteTasks[slug]=1;};
  window.UFXOTSSuite=Suite;

  async function run(root){var st=state(root),button=q('.ufxots-run',root);button.disabled=true;status(root,'Working on your result…','working');track('tool_run',st.slug,'started');try{var data;
    if(Suite.handlers[st.slug])data=await Suite.handlers[st.slug](st,Suite.api);
    else if(Suite.remoteTasks[st.slug])data=await remote(st.slug,st.primary,st.secondary,st.options);
    else throw new Error('The tool handler is not available.');
    if(data!==false)result(root,data||{});
  }catch(error){status(root,error&&error.message?error.message:'The operation could not be completed.','error');track('tool_error',st.slug,'error');}finally{button.disabled=false;}}
  function reset(root){qa('input,textarea,select',root).forEach(function(el){if(el.closest('.ufxots-result-card'))return;if(el.type==='file')el.value='';else if(el.type==='checkbox'||el.type==='radio')el.checked=el.defaultChecked;else if(el.tagName==='SELECT'){var initial=Array.prototype.find.call(el.options,function(o){return o.defaultSelected;});el.value=initial?initial.value:(el.options[0]?el.options[0].value:'');}else el.value=el.defaultValue||'';});q('.ufxots-output',root).innerHTML='';q('.ufxots-raw-output',root).value='';q('.ufxots-output-actions',root).hidden=true;q('.ufxots-preview',root).hidden=true;q('.ufxots-preview',root).innerHTML='';qa('.ufxots-file-list',root).forEach(function(x){x.textContent='';});status(root,'Add the required input and run the tool.','');}
  function boot(){qa('[data-ufxots-tool]').forEach(function(root){var slug=root.dataset.ufxotsTool,builder=q('[data-builder]',root);if(builder&&Suite.forms[slug])builder.innerHTML=Suite.forms[slug](Suite.api);
      q('.ufxots-run',root).addEventListener('click',function(){run(root);});q('.ufxots-reset',root).addEventListener('click',function(){reset(root);});
      q('.ufxots-copy',root).addEventListener('click',function(){copyText(q('.ufxots-raw-output',root).value).then(function(){status(root,'Result copied to the clipboard.','success');track('tool_copy',slug,'success');});});
      q('.ufxots-download',root).addEventListener('click',function(){saveText(q('.ufxots-raw-output',root).value,root.dataset.downloadName||'result.txt');track('tool_download',slug,'success');});
      qa('.ufxots-files',root).forEach(function(input){input.addEventListener('change',function(){showFiles(root,input.files);});});
      qa('[data-drop]',root).forEach(function(drop){['dragenter','dragover'].forEach(function(ev){drop.addEventListener(ev,function(e){e.preventDefault();drop.classList.add('is-over');});});['dragleave','drop'].forEach(function(ev){drop.addEventListener(ev,function(e){e.preventDefault();drop.classList.remove('is-over');if(ev==='drop'&&e.dataTransfer&&e.dataTransfer.files.length){var input=q('.ufxots-files',drop);if(input){var dt=new DataTransfer();Array.prototype.slice.call(e.dataTransfer.files,0,input.multiple?undefined:1).forEach(function(f){dt.items.add(f);});input.files=dt.files;input.dispatchEvent(new Event('change',{bubbles:true}));}}});});});
    });}
  function showFiles(root,files){var list=q('.ufxots-file-list',root);if(!list)return;var arr=Array.prototype.slice.call(files||[]);list.textContent=arr.length?arr.slice(0,5).map(function(f){return f.name+' ('+formatBytes(f.size)+')';}).join(' • ')+(arr.length>5?' • +'+(arr.length-5)+' more':''):'';}
  function formatBytes(n){if(n<1024)return n+' B';if(n<1048576)return (n/1024).toFixed(1)+' KB';return (n/1048576).toFixed(1)+' MB';}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else setTimeout(boot,0);
})();
