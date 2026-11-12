@extends('logistics.layouts.console')
@section('title', 'Demo Network Directory — ShopHop Logistics')
@section('content')
<div class="mx-auto max-w-7xl space-y-4 ops-page">
  <header class="ops-page-header">
    <div class="min-w-0"><p class="sh-kicker">Network &amp; People · Read-only</p><h1 class="ops-title mt-1">Nationwide Demo Directory</h1>
      <p class="ops-subtitle">Search city/municipal demo branches and their Web/Mobile logins. Demo locations do not guarantee physical delivery.</p>
    </div>
    <div class="ops-page-actions"><a class="ops-button" href="{{ route('logistics.riders.index') }}">Manage Riders</a></div>
  </header>
  <div class="grid gap-3 sm:grid-cols-3">
    <div class="sh-stat"><p class="sh-stat-label">City / municipal branches</p><p class="sh-stat-value">{{ number_format($total) }}</p><p class="sh-stat-note">Virtual demo locations</p></div>
    <div class="sh-stat"><p class="sh-stat-label">Provinces with branches</p><p class="sh-stat-value">{{ number_format($provinceCount) }}</p><p class="sh-stat-note">NCR / independent cities listed separately</p></div>
    <div class="sh-stat"><p class="sh-stat-label">Demo rider identities</p><p class="sh-stat-value">{{ number_format($riderCount) }}</p><p class="sh-stat-note">Check exact barangay eligibility before assignment</p></div>
  </div>
  <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs leading-5 text-amber-900">
    <strong>Virtual demo only.</strong> Accounts, branches, and riders do not establish real service availability, fleet capacity, or verified transport links. Existing historical provincial stations are retained and not listed here.
  </div>
  <section class="ops-panel">
    <div class="border-b border-gray-border p-4">
      <form class="flex flex-wrap items-end gap-2" method="GET" action="{{ route('logistics.demo-network.index') }}">
        <label class="sh-field min-w-[180px] flex-1"><span class="sh-label">City, province, branch code</span><input name="q" value="{{ $search }}" class="sh-input" maxlength="100" placeholder="Calamba, Pila, Bulacan…"></label>
        <label class="sh-field min-w-[170px] flex-1"><span class="sh-label">Province</span><select name="province" class="sh-select"><option value="">All provinces</option>@foreach($provinces as $item)<option value="{{ $item }}" @selected($province === $item)>{{ $item }}</option>@endforeach</select></label>
        <button class="sh-btn" type="submit">Filter</button>
        <a class="sh-btn-ghost" href="{{ route('logistics.demo-network.index') }}">Clear</a>
      </form>
      <p class="mt-2 text-xs text-navy/55">{{ $centers->firstItem() ?? 0 }}–{{ $centers->lastItem() ?? 0 }} of {{ number_format($centers->total()) }} branches · 35 per page</p>
    </div>
    <div class="ops-table-wrap">
      <table class="ops-table">
        <thead class="bg-gray-bg/70 text-[11px] uppercase tracking-wide text-navy/65"><tr>
          <th class="px-4 py-3">Branch</th><th class="px-4 py-3">Location / Parent</th><th class="px-4 py-3">Branch login</th><th class="px-4 py-3">Riders</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-border">
          @forelse($centers as $center)
            <tr class="align-top hover:bg-teal-light/20">
              <td class="px-4 py-3"><span class="font-bold text-navy">{{ $center->name }}</span><div class="text-[10px] text-navy/50">{{ $center->code }}</div></td>
              <td class="px-4 py-3">{{ $center->municipality }}<div class="text-[10px] text-navy/55">{{ $center->province ?: 'Independent / NCR' }} · {{ $center->region }}</div></td>
              <td class="px-4 py-3"><span class="break-all font-medium text-navy">{{ $center->user?->email ?? 'Not linked' }}</span>
                @if($center->user?->email)<button type="button" data-copy="{{ $center->user->email }}" class="ml-2 text-teal-dark underline underline-offset-2">Copy</button>@endif
                <div class="mt-1 text-[10px] text-navy/50">Web branch portal / Flutter branch scanner</div>
              </td>
              <td class="px-4 py-3 font-semibold">{{ $center->riders_count }}</td>
              <td class="px-4 py-3"><span class="ops-badge" data-status="{{ $center->status }}">{{ ucfirst($center->status) }}</span></td>
              <td class="px-4 py-3"><a data-console-details class="ops-button" href="{{ route('logistics.sorting-centers.show',$center) }}">View branch</a></td>
            </tr>
          @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-navy/60">No matching demo branches. Run the opt-in detailed network seeder or adjust search.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="border-t border-gray-border px-4 py-3">{{ $centers->links() }}</div>
  </section>
  <p class="text-xs text-navy/55">For seeded local demo accounts only, the initial password is <code>Password123</code>. Existing manually changed passwords are never reset by the seeder. Never use demo accounts outside local/testing.</p>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
  try { await navigator.clipboard.writeText(button.dataset.copy); button.textContent = 'Copied'; }
  catch (_) { button.textContent = 'Copy unavailable'; }
}));
</script>
@endpush
