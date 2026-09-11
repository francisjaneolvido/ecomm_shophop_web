{{--
    SHARED SHOPHOP REGISTRATION SIDE PANEL

    Used by Buyer, Seller, and Logistics registration.
    Layout/styling stay identical across roles; only the content passed
    by each modal changes.

    Redesign notes: the feature list is now a connected "hop path"
    (dashed line threading through each icon node) instead of plain
    divided rows — a small nod to the brand name — and the eyebrow
    badge now matches the pill treatment used on the main site's hero.
--}}

@php
    $roleIcon    = $roleIcon ?? 'lucide-sparkles';
    $eyebrow     = $eyebrow ?? 'JOIN SHOPHOP';
    $title       = $title ?? 'Get started.';
    $highlight   = $highlight ?? 'Go further.';
    $description = $description ?? '';
    $features    = $features ?? [];
    $stats       = $stats ?? [];
@endphp

<aside
    class="shophop-role-panel relative hidden lg:flex h-full min-h-0 overflow-hidden bg-navy px-8 xl:px-9 py-7 xl:py-8"
    aria-label="{{ $eyebrow }}"
>
    {{-- Quiet background treatment: dot texture instead of a grid,
         to match the dot texture used on the main site's hero. --}}
    <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-teal/8 via-transparent to-transparent"></div>
    <div class="pointer-events-none absolute top-0 bottom-0 right-0 w-px bg-linear-to-b from-transparent via-teal/25 to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 opacity-[0.05] text-white bg-[radial-gradient(currentColor_1px,transparent_1px)] bg-size-[20px_20px]"></div>

    <div class="relative z-10 flex h-full w-full min-w-0 flex-col justify-between gap-8 pt-11">

        {{-- Brand --}}
        <div class="role-panel-enter role-panel-enter-1">
            <div class="flex items-center gap-3">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="ShopHop"
                    class="h-11 w-11 shrink-0 object-contain"
                >

                <div class="min-w-0">
                    <p class="text-[15px] font-extrabold leading-none tracking-[-0.02em] text-white">
                        ShopHop
                    </p>
                    <p class="mt-1.5 text-[6.5px] font-bold tracking-[0.24em] text-teal">
                        HOP IN. SHOP MORE.
                    </p>
                </div>
            </div>
        </div>

        {{-- Hero copy --}}
        <div class="role-panel-enter role-panel-enter-2">
            <div class="inline-flex items-center gap-1.5 bg-white/8 text-teal pl-2 pr-2.5 py-1.5 rounded-full">
                <x-dynamic-component :component="$roleIcon" class="h-3 w-3 shrink-0" />
                <span class="text-[8px] font-bold uppercase tracking-[0.14em]">
                    {{ $eyebrow }}
                </span>
            </div>

            <h2 class="mt-4 max-w-77.5 text-[1.9rem] xl:text-[2.05rem] font-extrabold leading-[1.04] tracking-[-0.045em]">
                <span class="block text-white">{{ $title }}</span>
                <span class="mt-1 block text-teal">{{ $highlight }}</span>
            </h2>

            @if ($description)
                <p class="mt-4 max-w-77.5 text-[11px] leading-[1.65] text-white/50">
                    {{ $description }}
                </p>
            @endif
        </div>

        {{-- Value propositions: a connected path of nodes,
             not stacked cards or plain divided rows. --}}
        @if (count($features))
            <div class="role-panel-enter role-panel-enter-3">
                <p class="mb-3 text-[8px] font-bold uppercase tracking-[0.16em] text-white/30">
                    Why ShopHop
                </p>

                <div class="relative">

                    {{-- Dashed path threading through every icon node --}}
                    <div class="absolute left-4 top-4 bottom-4 w-px border-l border-dashed border-teal/25"></div>

                    <div class="space-y-1">
                        @foreach ($features as $feature)
                            <div class="group relative flex items-start gap-3 py-2.5 transition-all duration-300 hover:pl-1">

                                <div class="relative z-10 mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-navy text-teal ring-2 ring-teal/30 transition-all duration-300 group-hover:bg-teal group-hover:text-navy group-hover:ring-teal">
                                    <x-dynamic-component :component="$feature['icon']" class="h-3.5 w-3.5" />
                                </div>

                                <div class="min-w-0 flex-1 pt-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[10.5px] font-semibold leading-snug text-white">
                                            {{ $feature['title'] }}
                                        </p>
                                        <x-lucide-arrow-right class="h-3 w-3 shrink-0 translate-x-1 text-teal opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100" />
                                    </div>

                                    @if (!empty($feature['description']))
                                        <p class="mt-1 text-[9px] leading-[1.45] text-white/35">
                                            {{ $feature['description'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        @endif

        {{-- Bottom trust block. mt-auto keeps it anchored to the bottom. --}}
        <div class="role-panel-enter role-panel-enter-4">
            @if (count($stats))
                <div class="grid grid-cols-3 gap-3 border-t border-white/9 pt-5">
                    @foreach ($stats as $stat)
                        <div class="min-w-0">
                            <p class="text-[14px] font-extrabold leading-none tracking-[-0.02em] text-white">
                                {{ $stat['value'] }}@if (!empty($stat['star']))<span class="text-[10px] text-teal">★</span>@endif
                            </p>
                            <p class="mt-1.5 truncate text-[7px] font-semibold uppercase tracking-widest text-white/30">
                                {{ $stat['label'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border-t border-white/9"></div>
            @endif

            <div class="mt-5 flex items-center gap-2 text-[8.5px] font-medium text-white/30">
                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-teal/10">
                    <x-lucide-shield-check class="h-3 w-3 text-teal" />
                </div>
                <span>Secure onboarding powered by ShopHop</span>
            </div>
        </div>
    </div>
</aside>

@once
    @push('styles')
        <style>
            .shophop-role-panel {
                isolation: isolate;
            }

            .role-panel-enter {
                opacity: 0;
                transform: translateY(10px);
                animation: shophopRolePanelEnter .5s cubic-bezier(.22, 1, .36, 1) forwards;
            }

            .role-panel-enter-1 { animation-delay: .03s; }
            .role-panel-enter-2 { animation-delay: .09s; }
            .role-panel-enter-3 { animation-delay: .15s; }
            .role-panel-enter-4 { animation-delay: .21s; }

            @keyframes shophopRolePanelEnter {
                from { opacity: 0; transform: translateY(10px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Only the right-hand form area scrolls on desktop. */
            .shophop-registration-scroll {
                scrollbar-width: thin;
                scrollbar-color: rgba(15, 118, 110, .24) transparent;
            }

            .shophop-registration-scroll::-webkit-scrollbar {
                width: 6px;
            }

            .shophop-registration-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .shophop-registration-scroll::-webkit-scrollbar-thumb {
                background: rgba(15, 118, 110, .20);
                border-radius: 999px;
            }

            .shophop-registration-scroll::-webkit-scrollbar-thumb:hover {
                background: rgba(15, 118, 110, .34);
            }

            @media (prefers-reduced-motion: reduce) {
                .role-panel-enter {
                    opacity: 1;
                    transform: none;
                    animation: none;
                }
            }
        </style>
    @endpush
@endonce