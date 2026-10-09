{{-- Low-priority invitation immediately before marketplace footer; shopping content comes first. --}}
<section aria-labelledby="sh-logistics-invite-title" class="bg-[#f3f6f8] border-t border-gray-border/70 py-7 sm:py-9">
    <div class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-[#d9e7e9] bg-white p-5 sm:px-7 sm:py-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
            <div class="flex gap-3 sm:gap-4 items-start min-w-0">
                <div aria-hidden="true" class="h-10 w-10 shrink-0 flex items-center justify-center bg-teal-light text-teal-dark rounded-xl">
                    <x-lucide-truck class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-[10px] font-bold tracking-[.12em] uppercase text-teal-dark">For delivery businesses</p>
                    <h2 id="sh-logistics-invite-title" class="mt-1 text-base sm:text-lg font-bold text-navy">Work with ShopHop Logistics</h2>
                    <p class="mt-1 text-[11px] sm:text-xs leading-relaxed text-navy/65 max-w-xl">Own a logistics or sorting center? Discover our partner portal for riders, parcel sorting and deliveries.</p>
                </div>
            </div>
            <a href="{{ route('logistics.home') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-teal bg-teal px-5 py-2.5 text-xs font-bold text-white transition hover:bg-teal-dark hover:border-teal-dark" aria-label="Explore ShopHop Logistics partner website">
                Logistics Partner Portal <x-lucide-arrow-up-right class="w-4 h-4" />
            </a>
        </div>
    </div>
</section>
