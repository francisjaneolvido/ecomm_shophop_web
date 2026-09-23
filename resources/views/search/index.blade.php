@extends('layouts.app')

@section('title', 'Search ShopHop')

@section('content')
    <section class="max-w-310 mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="max-w-3xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-teal-dark">Public catalogue</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-navy">
                @if ($search === '')
                    Search ShopHop
                @else
                    Search results for <span class="text-teal-dark">“{{ $search }}”</span>
                @endif
            </h1>
        </div>

        @if ($search === '')
            <div class="mt-7 max-w-3xl rounded-2xl border border-gray-border bg-gray-bg/70 px-5 py-5 text-[13px] leading-relaxed text-navy/65">
                Enter a product name, category, or description to search the available ShopHop catalogue.
            </div>
        @elseif ($products->isEmpty())
            <div class="mt-7 max-w-3xl rounded-2xl border border-gray-border bg-gray-bg/70 px-5 py-8 text-center">
                <x-lucide-search-x class="w-7 h-7 text-navy/30 mx-auto mb-3" />
                <p class="text-[14px] font-semibold text-navy">No available products matched “{{ $search }}”.</p>
                <p class="mt-1 text-[12px] text-navy/55">Try a product name, category, or description.</p>
            </div>
        @else
            <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($products as $product)
                    <article data-search-result class="overflow-hidden rounded-2xl border border-gray-border bg-white shadow-sm">
                        <div class="aspect-square bg-gray-bg">
                            <img
                                src="{{ $product->image ? asset('storage/' . ltrim($product->image, '/')) : asset('images/placeholder-product.jpg') }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-dark">{{ $product->category }}</p>
                            <h2 class="mt-1 text-[14px] font-semibold leading-snug text-navy">{{ $product->name }}</h2>
                            @if ($product->description)
                                <p class="mt-2 text-[11px] leading-relaxed text-navy/55">{{ \Illuminate\Support\Str::limit($product->description, 120) }}</p>
                            @endif
                            <p class="mt-3 text-[15px] font-bold text-teal-dark">₱{{ number_format((float) $product->price, 2) }}</p>
                            <p class="mt-1 text-[11px] text-navy/55">{{ (int) $product->stock }} in stock</p>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($products->hasPages())
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </section>
@endsection
