@extends('admin.layout')

@section('title', 'Platform Settings')

@section('content')
@php
  $card  = 'ops-panel';
  $input = 'sh-input mt-1 block w-full';
  $label = 'sh-label block';
  $hint  = 'mt-1 text-[11px] text-slate-400';
  $isOn  = (bool) $setting->maintenance_mode;
@endphp

<div class="mx-auto max-w-6xl space-y-6">

  {{-- ============ HEADER ============ --}}
  <header class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 id="platformSettingsTitle" class="text-2xl font-bold text-navy">Platform Settings</h1>
      <p class="mt-1 text-sm text-slate-500">General information and maintenance mode for ShopHop.</p>
    </div>
    <div class="flex items-center gap-3 text-xs text-slate-500">
      @if($isOn)
        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
          <span class="h-2 w-2 rounded-full bg-amber-500"></span> Maintenance
        </span>
      @else
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
          <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Live
        </span>
      @endif
      @if($setting->updated_at)
        <span>Last updated: {{ $setting->updated_at->diffForHumans() }}</span>
      @endif
    </div>
  </header>

  {{-- ============ ALERTS ============ --}}
  @if(session('status'))
    <div role="status" class="flex items-start gap-2 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
      <span aria-hidden="true">✓</span><span>{{ session('status') }}</span>
    </div>
  @endif
  @if($errors->any())
    <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      <p class="font-semibold">Please fix the following:</p>
      <ul class="mt-1 list-inside list-disc space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="grid gap-6 lg:grid-cols-[200px_1fr]">

    {{-- ============ SIDE NAV ============ --}}
    <nav aria-label="Sections" class="hidden lg:block">
      <ul class="sticky top-6 space-y-1 text-sm">
        <li><a href="#general" class="block rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">General</a></li>
        <li><a href="#maintenance" class="block rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">Maintenance mode</a></li>
        <li><a href="#related" class="block rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">Other settings</a></li>
      </ul>
    </nav>

    {{-- ============ FORM ============ --}}
    <form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6" data-maintenance-initial="{{ $isOn ? '1' : '0' }}">
      @csrf

      {{-- GENERAL --}}
      <section id="general" class="{{ $card }} scroll-mt-6 p-5 sm:p-6">
        <h2 class="text-base font-bold text-slate-800">General</h2>
        <p class="mt-1 text-xs text-slate-500">The platform name and public contact details.</p>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
          <label class="{{ $label }} sm:col-span-2">Platform name
            <input name="name" required maxlength="80" value="{{ old('name', $setting->name) }}" class="{{ $input }}">
          </label>

          <label class="{{ $label }} sm:col-span-2">Description
            <textarea id="descInput" name="description" rows="3" maxlength="255" class="{{ $input }}" placeholder="A short description of ShopHop">{{ old('description', $setting->description) }}</textarea>
            <span class="{{ $hint }} block text-right"><span id="descCount">0</span>/255</span>
          </label>

          <label class="{{ $label }}">Support email
            <input type="email" name="support_email" maxlength="160" value="{{ old('support_email', $setting->support_email) }}" placeholder="support@shophop.com" class="{{ $input }}">
          </label>

          <label class="{{ $label }}">Support phone
            <input name="support_phone" maxlength="40" value="{{ old('support_phone', $setting->support_phone) }}" placeholder="09XX XXX XXXX" class="{{ $input }}">
          </label>
        </div>
      </section>

      {{-- MAINTENANCE --}}
      <section id="maintenance" class="{{ $card }} scroll-mt-6 p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="max-w-xl">
            <h2 class="text-base font-bold text-slate-800">Maintenance mode</h2>
            <p class="mt-1 text-xs leading-relaxed text-slate-500">
              When on, buyers, sellers, and riders cannot access the marketplace. Admins can still sign in.
            </p>
          </div>

          <label class="relative inline-flex shrink-0 cursor-pointer items-center" for="maintenanceToggle">
            <span class="sr-only">Turn on maintenance mode</span>
            <input id="maintenanceToggle" type="checkbox" name="maintenance_mode" value="1" class="peer sr-only" @checked(old('maintenance_mode', $setting->maintenance_mode))>
            <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-amber-500 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500/40
                         after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition after:content-[''] peer-checked:after:translate-x-5"></span>
          </label>
        </div>

        <div id="maintenanceWarning" class="mt-4 hidden rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
          Maintenance mode is set to <strong>ON</strong>. It will start as soon as you save.
        </div>

        <label class="{{ $label }} mt-5">Message shown to visitors
          <input id="msgInput" name="maintenance_message" maxlength="500" value="{{ old('maintenance_message', $setting->maintenance_message) }}" placeholder="We're under maintenance. Please check back soon." class="{{ $input }}">
        </label>

        <div class="mt-4">
          <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Preview</p>
          <div class="mt-1.5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center">
            <p class="text-xs font-semibold text-slate-400">503</p>
            <p id="msgPreview" class="mt-1 text-sm text-slate-700"></p>
          </div>
        </div>
      </section>

      {{-- RELATED --}}
      <section id="related" class="{{ $card }} scroll-mt-6 p-5 sm:p-6">
        <h2 class="text-base font-bold text-slate-800">Other settings</h2>
        <p class="mt-1 text-xs text-slate-500">These live elsewhere so each setting has a single source of truth.</p>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
          <a href="{{ route('admin.commission') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-teal-300 hover:bg-teal-50/40">
            <p class="text-sm font-semibold text-slate-800 group-hover:text-teal-700">ShopHop Finances</p>
            <p class="mt-1 text-xs text-slate-500">Commission, shipping rates, vouchers, payouts.</p>
          </a>
          <a href="{{ route('admin.accounts') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-teal-300 hover:bg-teal-50/40">
            <p class="text-sm font-semibold text-slate-800 group-hover:text-teal-700">Admin accounts</p>
            <p class="mt-1 text-xs text-slate-500">Add or disable admin accounts.</p>
          </a>
          <a href="{{ route('admin.reports') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-teal-300 hover:bg-teal-50/40">
            <p class="text-sm font-semibold text-slate-800 group-hover:text-teal-700">Reports</p>
            <p class="mt-1 text-xs text-slate-500">Live figures and CSV export.</p>
          </a>
        </div>
      </section>

      {{-- STICKY SAVE BAR --}}
      <div class="sticky bottom-4 z-10 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 shadow-lg backdrop-blur">
        <p class="hidden text-xs text-slate-500 sm:block">Save to apply your changes.</p>
        <div class="ml-auto flex items-center gap-2">
          <a href="{{ route('admin.settings') }}" class="sh-btn-soft inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
          <button type="submit" class="sh-btn inline-flex items-center rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40">Save settings</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  var form    = document.getElementById('settingsForm');
  var toggle  = document.getElementById('maintenanceToggle');
  var warning = document.getElementById('maintenanceWarning');
  var msg     = document.getElementById('msgInput');
  var preview = document.getElementById('msgPreview');
  var desc    = document.getElementById('descInput');
  var count   = document.getElementById('descCount');
  var fallback = "We're under maintenance. Please check back soon.";

  function syncMsg()   { preview.textContent = msg.value.trim() || fallback; }
  function syncDesc()  { count.textContent = desc.value.length; }
  function syncState() {
    var initial = form.dataset.maintenanceInitial === '1';
    warning.classList.toggle('hidden', !(toggle.checked && !initial));
  }

  msg.addEventListener('input', syncMsg);
  desc.addEventListener('input', syncDesc);
  toggle.addEventListener('change', syncState);

  form.addEventListener('submit', function (e) {
    var initial = form.dataset.maintenanceInitial === '1';
    if (toggle.checked && !initial) {
      if (!confirm('Turn on maintenance mode? Buyers, sellers, and riders will be locked out until you turn it off.')) {
        e.preventDefault();
      }
    }
  });

  syncMsg(); syncDesc(); syncState();
})();
</script>
@endsection