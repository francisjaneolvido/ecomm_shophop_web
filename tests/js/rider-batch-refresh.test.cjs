const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname,'../../public/js/shophop-rider-batches.js'),'utf8');

function setup(response) {
    let tick, calls=0, focused=false, blocked=false, html='', opened=false, timerStarts=0;
    const events={},windowEvents={},status={textContent:''},total={dataset:{batchTotal:'in_progress'},textContent:'0'};
    const card = {dataset:{batchId:'1'},querySelector(selector){
        return selector==='details[open]'?{}:{setAttribute(name){if(name==='open')opened=true;}};
    }};
    const routes={dataset:{liveUrl:'https://example.test/rider-batches?branch=1'},contains:()=>focused,
        querySelectorAll:()=>[card],get innerHTML(){return html;},set innerHTML(value){html=value;}};
    const document={hidden:false,activeElement:{},getElementById:()=>routes,
        querySelector:(selector)=>selector==='[data-batch-sync-status]'?status:(blocked?{}:null),
        querySelectorAll:()=>[total],addEventListener:(name,callback)=>{events[name]=callback;}};
    vm.runInNewContext(source,{
        document,window:{addEventListener:(name,callback)=>{windowEvents[name]=callback;}},
        fetch:async()=>{calls++;return response?response():{ok:true,status:200,json:async()=>({success:true,data:{html:'<article>Updated route</article>',totals:{in_progress:3}}})};},
        AbortController,Date,
        setTimeout:()=>1,clearTimeout:()=>{},
        setInterval:(callback,ms)=>{assert.equal(ms,15000);tick=callback;timerStarts++;return timerStarts;},clearInterval:()=>{}
    });
    return {document,events,windowEvents,status,total,tick:()=>tick(),calls:()=>calls,
        html:()=>html,opened:()=>opened,timerStarts:()=>timerStarts,
        focus:(value)=>{focused=value;},confirm:(value)=>{blocked=value;}};
}
test('visible authenticated polling refreshes cards, totals and expanded route details',async()=>{
    const h=setup();await h.tick();
    assert.equal(h.calls(),1);assert.equal(h.html(),'<article>Updated route</article>');
    assert.equal(h.total.textContent,'3');assert.equal(h.opened(),true);
    assert.match(h.status.textContent,/Routes updated/);
});
test('hidden pages, confirmation dialogs and focused route controls suspend polling',async()=>{
    const h=setup();h.document.hidden=true;await h.tick();assert.equal(h.calls(),0);
    h.document.hidden=false;h.focus(true);await h.tick();assert.equal(h.calls(),0);
    h.focus(false);h.confirm(true);await h.tick();assert.equal(h.calls(),0);
    h.confirm(false);await h.tick();assert.equal(h.calls(),1);
});
test('expired sessions stop repeated requests and preserve previous route cards',async()=>{
    const h=setup(async()=>({ok:false,status:403}));
    await h.tick();await h.tick();assert.equal(h.calls(),1);
    assert.equal(h.html(),'');assert.match(h.status.textContent,/Session expired/);
});
test('server read failures retain existing view and retry on the following interval',async()=>{
    let first=true;
    const h=setup(async()=>first?(first=false,{ok:false,status:503}):
        {ok:true,status:200,json:async()=>({success:true,data:{html:'Recovered'}})});
    await h.tick();assert.match(h.status.textContent,/could not refresh/);assert.equal(h.html(),'');
    await h.tick();assert.equal(h.html(),'Recovered');assert.equal(h.calls(),2);
});
test('returning through browser back cache resumes polling',async()=>{
    const h=setup();h.windowEvents.pagehide();await h.tick();assert.equal(h.calls(),0);
    h.windowEvents.pageshow({persisted:true});await new Promise(resolve=>setImmediate(resolve));
    assert.equal(h.timerStarts(),2);assert.equal(h.calls(),1);
});
