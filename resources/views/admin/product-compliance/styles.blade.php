{{-- Scoped surfaces keep this behavioral workspace readable in all three existing themes without changing the shell. --}}
@push('styles')
<style>
.compliance-workspace { --pc-bg:#fff; --pc-text:#102d43; --pc-muted:#526579; --pc-border:#cad6df; --pc-input:#fff; color:var(--pc-text); max-width:1100px; margin:auto; min-width:0; }
[data-theme="dark"] .compliance-workspace { --pc-bg:#14263a; --pc-text:#edf4fa; --pc-muted:#b9c8d7; --pc-border:#42576d; --pc-input:#0f2032; }
[data-theme="oled"] .compliance-workspace { --pc-bg:#080808; --pc-text:#f4f4f4; --pc-muted:#bdbdbd; --pc-border:#454545; --pc-input:#000; }
.compliance-workspace h1 { font-size:1.6rem; font-weight:700; overflow-wrap:anywhere; }
.compliance-workspace h2 { font-size:1.1rem; font-weight:700; margin-bottom:.6rem; }
.compliance-workspace p { margin:.5rem 0; overflow-wrap:anywhere; }
.pc-panel { background:var(--pc-bg); border:1px solid var(--pc-border); border-radius:1rem; padding:1.25rem; margin:1rem 0; min-width:0; }
.pc-muted { color:var(--pc-muted); }
.pc-controls { display:flex; flex-wrap:wrap; gap:.75rem; align-items:end; }
.pc-controls label { display:flex; flex-direction:column; gap:.3rem; flex:1 1 160px; }
.compliance-workspace input,.compliance-workspace select,.compliance-workspace textarea { background:var(--pc-input); color:var(--pc-text); border:1px solid var(--pc-border); border-radius:.5rem; padding:.65rem; width:100%; min-width:0; }
.pc-button { display:inline-flex; align-items:center; justify-content:center; padding:.65rem 1rem; background:#087f78; color:#fff; border-radius:.5rem; font-weight:600; }
.compliance-workspace a { text-decoration:underline; text-underline-offset:3px; }
.compliance-workspace :focus-visible { outline:3px solid #12b8aa; outline-offset:3px; }
.pc-list { display:grid; gap:.75rem; }
.pc-row { display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; border-bottom:1px solid var(--pc-border); padding:.75rem 0; }
.pc-row>div { min-width:0; flex:1 1 240px; overflow-wrap:anywhere; }
.pc-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr)); gap:1rem; }
.pc-images { display:flex; flex-wrap:wrap; gap:.75rem; }
.pc-images img { width:140px; height:140px; object-fit:contain; }
.pc-error { border-left:4px solid #e87878; padding:.75rem; }
</style>
@endpush
