const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname,'../../public/js/shophop-rider-management.js'),'utf8');

function setup(response){
    let tick, calls=0, focus=false, timers=0;
    const events={}, windowEvents={}, status={textContent:''}, rows={innerHTML:'Old rows'},
        pagination={innerHTML:'Old pages'}, count={dataset:{managementBranchCount:'11'},textContent:'1'},total={textContent:'1'};
    const root={querySelector(s){return {'[data-management-sync]':status,'[data-management-rows]':rows,
        '[data-management-pagination]':pagination,'[data-management-total]':total}[s];},
        querySelectorAll:()=>[count]};
    const document={hidden:false,activeElement:{matches:()=>focus},
        querySelector:()=>root,addEventListener:(name,fn)=>events[name]=fn};
    vm.runInNewContext(source,{document,window:{location:{href:'https://example.test/rider-management?branch=11'},
        addEventListener:(name,fn)=>windowEvents[name]=fn},AbortController,
        setInterval:(fn,ms)=>{assert.equal(ms,15000);tick=fn;return ++timers;},clearInterval:()=>{},
        setTimeout:()=>1,clearTimeout:()=>{},
        fetch:async(url,options)=>{calls++;assert.equal(options.credentials,'same-origin');assert.equal(options.headers.Accept,'application/json');
            return response?response():{ok:true,status:200,json:async()=>({success:true,data:{rows:'New rows',pagination:'New pages',active_by_branch:{},total:2}})};}
    });
    return {document,events,windowEvents,status,rows,count,total,tick:()=>tick(),calls:()=>calls,focus:(v)=>focus=v,timers:()=>timers};
}
test('visible directory refresh updates rows and clears the last active rider count',async()=>{
    const h=setup();await h.tick();
    assert.equal(h.rows.innerHTML,'New rows');assert.equal(h.count.textContent,'0');assert.equal(h.total.textContent,'2');
});
test('typing and hidden pages preserve forms and avoid new requests',async()=>{
    const h=setup();h.focus(true);await h.tick();assert.equal(h.calls(),0);
    h.focus(false);h.document.hidden=true;await h.tick();assert.equal(h.calls(),0);
    h.document.hidden=false;await h.tick();assert.equal(h.calls(),1);
});
test('expired permissions stop polling without clearing current content',async()=>{
    const h=setup(async()=>({ok:false,status:403}));await h.tick();await h.tick();
    assert.equal(h.calls(),1);assert.equal(h.rows.innerHTML,'Old rows');assert.match(h.status.textContent,/permissions changed/);
});
test('read failures retain current directory and recover on next interval',async()=>{
    let failed=true;
    const h=setup(async()=>failed?(failed=false,{ok:false,status:503}):{ok:true,status:200,json:async()=>({success:true,data:{rows:'Recovered',pagination:'',active_by_branch:{11:3},total:3}})});
    await h.tick();assert.equal(h.rows.innerHTML,'Old rows');
    await h.tick();assert.equal(h.rows.innerHTML,'Recovered');assert.equal(h.count.textContent,'3');
});
test('browser back-cache restores polling but pagehide prevents background writes',async()=>{
    const h=setup();h.windowEvents.pagehide();await h.tick();assert.equal(h.calls(),0);
    h.windowEvents.pageshow({persisted:true});await new Promise(resolve=>setImmediate(resolve));
    assert.equal(h.timers(),2);assert.equal(h.calls(),1);
});
