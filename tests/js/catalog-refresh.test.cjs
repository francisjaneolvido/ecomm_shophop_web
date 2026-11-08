const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/shophop-catalog-refresh.js'), 'utf8');
const flush = () => new Promise(resolve => setImmediate(resolve));
function browser() {
    let value = 'initial', reloads = 0, requests = 0;
    const events = {}, timers = [];
    const document = {
        hidden: false,
        querySelector: () => ({dataset:{catalogRevisionUrl:'/catalog/revision', catalogRevision:'initial'}}),
        addEventListener: (name, fn) => { events[name] = fn; },
    };
    vm.runInNewContext(source, {
        document, window:{addEventListener: (name, fn) => { events[name] = fn; }},
        location:{reload: () => { reloads++; }},
        fetch: async () => { requests++; return {ok:true,json:async()=>({revision:value})}; },
        setInterval:(fn,ms)=>{ assert.equal(ms,15000); timers.push(fn); }, setTimeout:()=>{},
    });
    return {document, events, timers, setRevision(v){value=v;}, get reloads(){return reloads;},get requests(){return requests;}};
}

test('a changed catalog triggers an automatic browser reload', async () => {
    const b=browser(); await flush();
    assert.equal(b.reloads,0);
    b.setRevision('approved-product'); await b.timers[0]();
    assert.equal(b.reloads,1);
});
test('unchanged catalog avoids needless page reloads', async () => {
    const b=browser(); await flush(); await b.timers[0]();
    assert.equal(b.requests,2); assert.equal(b.reloads,0);
});
test('background tab pauses polling and resumes on visibility', async () => {
    const b=browser(); await flush(); b.document.hidden=true;
    b.setRevision('updated'); await b.timers[0](); assert.equal(b.requests,1);
    b.document.hidden=false; b.events.visibilitychange(); await flush(); assert.equal(b.reloads,1);
});
test('unsent input and form submission prevent automatic reload', async () => {
    const b=browser(); await flush();
    b.events.input({target:{matches:()=>true}}); b.setRevision('updated');
    await b.timers[0](); assert.equal(b.reloads,0);
    const c=browser(); await flush(); c.events.submit(); c.setRevision('updated');
    await c.timers[0](); assert.equal(c.reloads,0);
});
