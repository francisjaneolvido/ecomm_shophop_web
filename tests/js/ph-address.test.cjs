// Run: node tests/js/ph-address.test.cjs
const fs = require('fs');
const vm = require('vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public/js/shophop-ph-address.js', 'utf8');
const fixture = {
  '/regions/': [{code:'130000000',name:'NCR',regionName:'National Capital Region'}, {code:'040000000',name:'CALABARZON',regionName:'Region IV-A'}],
  '/provinces/': [{code:'043400000',name:'Laguna',regionCode:'040000000'}, {code:'064500000',name:'Negros Occidental',regionCode:'060000000'}, {code:'074600000',name:'Negros Oriental',regionCode:'070000000'}, {code:'076100000',name:'Siquijor',regionCode:'070000000'}],
  '/regions/130000000/cities-municipalities/': [{code:'137404000',name:'Quezon City'}, {code:'133900000',name:'City of Manila'}, {code:'137606000',name:'Pateros'}],
  '/provinces/043400000/cities-municipalities/': [{code:'043400100',name:'Santa Cruz'}],
  '/provinces/064500000/cities-municipalities/': [{code:'064500100',name:'Bacolod'}],
  '/provinces/074600000/cities-municipalities/': [{code:'074600100',name:'Dumaguete'}],
  '/provinces/076100000/cities-municipalities/': [{code:'076100100',name:'Siquijor'}],
  '/cities-municipalities/133900000/barangays/': [{code:'133900001',name:'Barangay 1'}],
};
const requests = [];
class Option {
  constructor(text, value, _default, selected) {this.text = text; this.textContent = text;this.value=value;this.selected=Boolean(selected);}
}
class Select {
  constructor() {this.options=[];this.disabled=false;this.listeners={};}
  replaceChildren(...children){this.options=children;}
  add(option){this.options.push(option);}
  get value(){return this.options.find(o=>o.selected)?.value || this.options[0]?.value || '';}
  set value(v){this.options.forEach(o=>o.selected=(o.value===v));}
  get selectedOptions(){return this.options.filter(o=>o.selected).length ? this.options.filter(o=>o.selected):[this.options[0]].filter(Boolean);}
  addEventListener(event, handler){this.listeners[event]=handler;}
}
const window={};
vm.runInNewContext(source, {window,Option,fetch:async url=>{
  const pathname=url.replace('https://psgc.gitlab.io/api','');
  requests.push(pathname);
  return {ok:pathname in fixture,json:async()=>fixture[pathname]};
}});
(async()=>{
  const api=window.ShopHopPHAddress;
  assert.equal((await api.regions()).find(r=>r.code==='180000000').name,'Negros Island Region (NIR)');
  assert.equal((await api.provinces('130000000')).length,0);
  assert.equal((await api.provinces('180000000')).length,3);
  assert.equal((await api.cities('130000000','130000000')).length,3);
  assert.equal((await api.cities('180000000','180000000')).length,3);
  const region=new Select(), province=new Select(),city=new Select(),barangay=new Select();
  const provinceName={value:''},cityName={value:''},barangayName={value:''},regionName={value:''};
  await api.bind({region,province,city,barangay,provinceName,cityName,barangayName,regionName,oldRegion:'130000000',oldProvince:'130000000',oldCity:'133900000',oldBarangay:'133900001',status:()=>{}});
  assert.equal(region.value,'130000000');
  assert.equal(province.value,'130000000');
  assert.equal(provinceName.value,'National Capital Region (NCR)');
  assert.equal(city.value,'133900000');
  assert.equal(cityName.value,'City of Manila');
  assert.equal(barangayName.value,'Barangay 1');
  assert.equal(regionName.value,'National Capital Region (NCR)');
  assert(!requests.some(path=>path.includes('/provinces/130000000/')), 'should never request an NCR province');
  const r2=new Select(), p2=new Select(), c2=new Select(), b2=new Select();
  const pn2={value:''},cn2={value:''},bn2={value:''};
  await api.bind({region:r2,province:p2,city:c2,barangay:b2,provinceName:pn2,cityName:cn2,barangayName:bn2,
    oldRegion:'040000000',oldProvince:'043400000',oldCity:'043400100',status:()=>{}});
  assert.equal(p2.value,'043400000');
  assert.equal(pn2.value,'Laguna');
  assert.equal(c2.value,'043400100');
  assert.equal(cn2.value,'Santa Cruz');

  console.log('PASS: Region listings, NIR, NCR no-province, City of Manila, barangay restoration and standard Laguna province path');
})().catch(e=>{console.error(e);process.exitCode=1});
