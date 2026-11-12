/* Same-origin record screens and existing inline forms share a native details dialog. */
(() => {
 'use strict';
 const body = document.body;
 if (!body.classList.contains('sh-management-ui')) return;
 let embedded = body.classList.contains('sh-console-embedded');
 try {
  if(parent!==window && window.name==='shophop-console-details' && parent.ShopHopDetails)embedded=true;
 } catch (_) {}
 if(embedded)body.classList.add('sh-console-embedded');
 const params = new URLSearchParams(location.search);
 const sourceForPage = () => {
  const id = params.get('detail_target');
  return id ? document.getElementById(id) : document.querySelector('[data-console-detail-source]');
 };
 if (embedded) {
  const source = sourceForPage();
  if (!source) {
   body.classList.remove('sh-console-embedded');
   try { parent.ShopHopDetails?.frameFailed(window); } catch (_) {}
   return;
  }
  source.setAttribute('data-console-detail-source', '');
  if(source instanceof HTMLDetailsElement)source.open=true;
  source.querySelectorAll('[data-console-detail-dismiss],h1').forEach(node=>node.hidden=true);
  document.querySelectorAll('.sh-alert-error,.sh-alert-success,[role=alert]').forEach(notice=>{
   if(!source.contains(notice) && !notice.closest('dialog,[role=dialog],[role=alertdialog]'))source.prepend(notice);
  });
  for (let node = source.parentElement; node; node = node.parentElement) node.setAttribute('data-console-detail-ancestor', '');
  document.querySelectorAll('dialog,[role=dialog],[role=alertdialog]').forEach(overlay => {
   for(let node=overlay;node && node!==body;node=node.parentElement) {
    if(node.hasAttribute('data-console-detail-ancestor')) break;
    node.setAttribute('data-console-detail-support','');
   }
  });
  body.classList.add('sh-console-detail-ready');
  const api = () => { try { return parent !== window ? parent.ShopHopDetails : null; } catch (_) { return null; } };
  api()?.frameReady(window, source.dataset.consoleDetailTitle || source.querySelector('h1,h2')?.textContent.trim() || document.title);
  let submitting = false;
  document.addEventListener('submit', event => {
   if (event.target.method.toLowerCase() !== 'get') {
    submitting = true;
    api()?.frameSubmitting(window);
   }
  }, true);
  window.addEventListener('pagehide', () => { if(submitting) api()?.frameChanged(window); });
  document.addEventListener('click', event => {
   const close=event.target.closest('[data-console-detail-dismiss],[data-console-details-close]');
   if(close && api()){event.preventDefault();api().close();return;}
   const link=event.target.closest('a[href]');
   if(!link || event.defaultPrevented || event.button!==0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download'))return;
   const url=new URL(link.href,location.href);
   if(url.hash && url.pathname===location.pathname && url.search===location.search)return;
   if(link.hasAttribute('data-console-details') && api()){
    event.preventDefault();api().open(link.href,link.dataset.consoleDetailTitle || link.textContent.trim(),null,link.dataset.consoleDetailTarget);return;
   }
   if(url.origin===location.origin && api()){
    event.preventDefault();parent.location.assign(url.href);
   }
  });
  document.addEventListener('keydown',event=>{
   if(event.key==='Escape' && !document.querySelector('dialog[open]') && api()){
    event.preventDefault();api().close();
   }
  });
  return;
 }
 const dialog = document.getElementById('consoleDetailsDialog');
 if(!dialog || typeof dialog.showModal!=='function') return;
 const content=dialog.querySelector('.sh-details-content'),title=dialog.querySelector('#consoleDetailsTitle');
 const status=dialog.querySelector('.sh-details-status'),error=dialog.querySelector('.sh-details-error');
 let frame=null,originator=null,local=null,placeholder=null,dirty=false,loading=false,timeout=null,lastRemote=null;
 const stateKey='shophop:detail-submit:'+location.pathname;
 function busy(value){loading=value;if(value)status.textContent='Loading details…';status.hidden=!value;content.setAttribute('aria-busy',String(value));}
 function reset(){
  clearTimeout(timeout);
  if(local){
   if(local instanceof HTMLDetailsElement)local.open=false;
   placeholder?.replaceWith(local);local=null;placeholder=null;
  }
  frame?.remove();frame=null;content.replaceChildren();content.classList.remove('rider-management');error.hidden=true;busy(false);
 }
 function show(label,trigger){
  title.textContent=label || 'View details';
  if(trigger)originator=trigger.closest('details')?.querySelector('summary') || trigger;
  const preview=document.getElementById('sellerOrderDialog');
  if(preview?.open)preview.close();
  document.querySelectorAll('.ops-actions[open],.so-actions-menu[open]').forEach(menu=>menu.open=false);
  if(!dialog.open){dialog.showModal();body.classList.add('sh-console-details-open');}
 }
 function openLocal(target,trigger){
  if(!target?.matches('[data-console-inline-detail]'))return;
  reset();dirty=false;lastRemote=null;
  const wrapper=target.closest('[data-rider-management]');
  content.classList.toggle('rider-management',!!wrapper);
  wrapper?.querySelectorAll(':scope>.sh-alert-error,:scope>.sh-alert-success').forEach(notice=>target.prepend(notice));
  placeholder=document.createComment('Record details return here');
  target.before(placeholder);local=target;
  if(target instanceof HTMLDetailsElement)target.open=true;
  content.append(target);
  show(target.dataset.consoleDetailTitle || trigger?.dataset.consoleDetailTitle || 'View details',trigger);
  target.querySelector('[autofocus]')?.focus();
 }
 function fail(){
  if(!dialog.open)return;
  clearTimeout(timeout);busy(false);error.hidden=false;
  if(frame)frame.hidden=true;
 }
 function openRemote(href,label,trigger,target){
  let url;try{url=new URL(href,location.href);}catch(_){return false;}
  if(url.origin!==location.origin || !['http:','https:'].includes(url.protocol))return false;
  const previousDirty=dialog.open && dirty;
  reset();dirty=previousDirty;show(label,trigger);
  url.searchParams.set('console_modal','1');
  if(target)url.searchParams.set('detail_target',target);
  lastRemote={href:url.href,label,target};
  busy(true);
  frame=document.createElement('iframe');frame.name='shophop-console-details';frame.title=label || 'Record details';
  frame.addEventListener('error',fail);
  frame.addEventListener('load',()=>{
   try{
    if(!frame?.contentDocument?.body.classList.contains('sh-console-detail-ready'))fail();
   }catch(_){fail();}
  });
  frame.src=url.href;content.append(frame);
  timeout=setTimeout(fail,30000);
  return true;
 }
 function close(){if(dialog.open)dialog.close();}
 window.ShopHopDetails={
  open:openRemote,close,
  frameReady(source,label){
   if(source!==frame?.contentWindow)return;
   clearTimeout(timeout);busy(false);error.hidden=true;frame.hidden=false;
   frame.contentWindow.ShopHopTheme?.apply(document.documentElement.dataset.theme || 'light',false);
   if(label)title.textContent=label;
  },
  frameFailed(source){if(source===frame?.contentWindow)fail();},
  frameSubmitting(source){if(source===frame?.contentWindow)status.textContent='Saving changes…';},
  frameChanged(source){if(source===frame?.contentWindow)dirty=true;}
 };
 dialog.addEventListener('close',()=>{
  const reload=dirty;
  if(local){
   const url=new URL(location.href);
   if(url.hash==='#'+local.id)url.hash='';
   if(local.id==='rider-details')url.searchParams.delete('rider');
   history.replaceState(null,'',url);
  }
  reset();body.classList.remove('sh-console-details-open');dirty=false;
  try{sessionStorage.removeItem(stateKey);}catch(_){}
  originator?.focus({preventScroll:true});
  if(reload)location.reload();
 });
 dialog.addEventListener('click',event=>{
  if(event.target.closest('[data-console-details-close],[data-console-detail-dismiss]')){event.preventDefault();close();return;}
  if(event.target.closest('[data-console-details-retry]')){
   if(lastRemote)openRemote(lastRemote.href,lastRemote.label,originator,lastRemote.target);
   return;
  }
  if(event.target===dialog){
   const rect=dialog.getBoundingClientRect();
   if(event.clientX<rect.left || event.clientX>rect.right || event.clientY<rect.top || event.clientY>rect.bottom)close();
  }
 });
 document.addEventListener('click',event=>{
  const trigger=event.target.closest('[data-console-details]');
  if(!trigger || event.defaultPrevented || event.button!==0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey)return;
  const selector=trigger.dataset.consoleDetails;
  const target=selector?.startsWith('#') ? document.getElementById(selector.slice(1)) : null;
  if(target){event.preventDefault();openLocal(target,trigger);return;}
  if(trigger instanceof HTMLAnchorElement && !trigger.target && openRemote(trigger.href,trigger.dataset.consoleDetailTitle || trigger.textContent.trim(),trigger,trigger.dataset.consoleDetailTarget))event.preventDefault();
 });
 document.addEventListener('submit',event=>{
  if(local && local.contains(event.target) && event.target.method.toLowerCase()!=='get'){
   try{sessionStorage.setItem(stateKey,local.id);}catch(_){}
  }
 },true);
 function openHash(){
  let id;try{id=decodeURIComponent(location.hash.slice(1));}catch(_){return;}
  const target=id ? document.getElementById(id) : null;
  if(target?.matches('[data-console-inline-detail]'))openLocal(target,null);
 }
 window.addEventListener('hashchange',openHash);
 let pending='';try{pending=sessionStorage.getItem(stateKey)||'';sessionStorage.removeItem(stateKey);}catch(_){}
 const auto=document.querySelector('[data-console-inline-detail][data-console-auto-open]') || (pending && document.getElementById(pending));
 if(auto)openLocal(auto,null);else openHash();
})();
