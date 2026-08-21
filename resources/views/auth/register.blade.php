@extends('layouts.app')

@section('title', 'Create Account — ShopHop')

@section('content')

<section class="relative overflow-hidden bg-gray-bg">

    {{-- =========================================================
        REGISTRATION HERO / SPLIT SCREEN
    ========================================================= --}}
    <div class="relative min-h-[calc(100vh-56px)]">

        <div class="grid lg:grid-cols-[1fr_560px] min-h-[calc(100vh-56px)]">


            {{-- =====================================================
                LEFT ARTISTIC PANEL
            ====================================================== --}}
            <div
                class="relative hidden lg:flex
                       overflow-hidden
                       bg-navy
                       px-10 xl:px-16
                       py-14
                       items-center"
            >

                {{-- Background decorations --}}
                <div
                    class="pointer-events-none absolute
                           -top-32 -left-32
                           w-96 h-96
                           rounded-full
                           bg-teal/20
                           blur-2xl"
                ></div>

                <div
                    class="pointer-events-none absolute
                           -bottom-40 -right-24
                           w-120 h-120
                           rounded-full
                           bg-teal/10"
                ></div>

                <div
                    class="pointer-events-none absolute
                           top-[18%] right-[12%]
                           w-32 h-32
                           rounded-full
                           border border-white/10"
                ></div>

                <div
                    class="pointer-events-none absolute
                           bottom-[12%] left-[10%]
                           w-20 h-20
                           rounded-full
                           border border-teal/30"
                ></div>


                {{-- Tiny dots --}}
                <div class="pointer-events-none absolute inset-0 opacity-20">
                    <div class="absolute top-20 left-20 w-1.5 h-1.5 rounded-full bg-white"></div>
                    <div class="absolute top-36 left-[42%] w-1 h-1 rounded-full bg-teal"></div>
                    <div class="absolute top-[28%] right-[18%] w-1.5 h-1.5 rounded-full bg-white"></div>
                    <div class="absolute bottom-[28%] left-[20%] w-1 h-1 rounded-full bg-teal"></div>
                    <div class="absolute bottom-24 right-[32%] w-1.5 h-1.5 rounded-full bg-white"></div>
                </div>


                <div class="relative z-10 w-full max-w-2xl mx-auto">


                    {{-- Logo --}}
                    <div class="flex items-center gap-3">

                        <div
                            class="w-14 h-14
                                   rounded-2xl
                                   bg-white
                                   flex items-center justify-center
                                   shadow-xl shadow-black/10"
                        >
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="ShopHop"
                                class="w-10 h-10 object-contain"
                            >
                        </div>

                        <div>

                            <p class="text-white text-2xl font-bold leading-none">
                                ShopHop
                            </p>

                            <p
                                class="text-teal
                                       text-[9px]
                                       tracking-[0.24em]
                                       mt-2"
                            >
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>


                    {{-- Main artwork content --}}
                    <div class="mt-12 xl:mt-16">

                        <span
                            class="inline-flex items-center gap-2
                                   rounded-full
                                   bg-white/10
                                   border border-white/10
                                   backdrop-blur
                                   px-4 py-2
                                   text-xs font-semibold
                                   text-teal"
                        >
                            <x-lucide-sparkles class="w-4 h-4" />

                            YOUR SHOPPING JOURNEY STARTS HERE
                        </span>


                        <h1
                            class="mt-6
                                   text-white
                                   text-5xl xl:text-6xl
                                   font-extrabold
                                   leading-[1.05]
                                   max-w-xl"
                        >
                            Discover more.
                            <span class="block text-teal">
                                Shop your way.
                            </span>
                        </h1>


                        <p
                            class="mt-6
                                   text-white/65
                                   text-base xl:text-lg
                                   leading-relaxed
                                   max-w-lg"
                        >
                            Create your ShopHop account and enjoy a smoother
                            way to discover products, save favorites,
                            and manage your shopping experience.
                        </p>

                    </div>


                    {{-- Artistic shopping cards --}}
                    <div
                        class="relative
                               mt-12 xl:mt-14
                               h-72
                               max-w-xl"
                    >

                        {{-- Main center card --}}
                        <div
                            class="absolute
                                   left-1/2 top-1/2
                                   -translate-x-1/2
                                   -translate-y-1/2
                                   w-52 h-52
                                   rounded-4xl
                                   bg-white
                                   shadow-2xl
                                   flex flex-col
                                   items-center justify-center"
                        >

                            <div
                                class="w-20 h-20
                                       rounded-3xl
                                       bg-teal-light
                                       flex items-center justify-center
                                       text-teal-dark"
                            >
                                <x-lucide-shopping-bag class="w-10 h-10" />
                            </div>

                            <p class="text-navy font-bold text-lg mt-5">
                                ShopHop
                            </p>

                            <p class="text-navy/40 text-xs mt-1">
                                Find. Love. Shop.
                            </p>

                        </div>


                        {{-- Floating card - wishlist --}}
                        <div
                            class="absolute
                                   left-4 top-4
                                   w-40
                                   rounded-2xl
                                   bg-white/95
                                   backdrop-blur
                                   p-4
                                   shadow-xl
                                   rotate-[-5deg]"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-teal-light
                                           flex items-center justify-center"
                                >
                                    <x-lucide-heart class="w-5 h-5 text-teal-dark" />
                                </div>

                                <div>

                                    <p class="text-navy text-xs font-semibold">
                                        Save Favorites
                                    </p>

                                    <p class="text-navy/40 text-[10px] mt-1">
                                        Keep what you love
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Floating card - secure --}}
                        <div
                            class="absolute
                                   right-0 top-10
                                   w-40
                                   rounded-2xl
                                   bg-white/95
                                   backdrop-blur
                                   p-4
                                   shadow-xl
                                   rotate-[5deg]"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-teal-light
                                           flex items-center justify-center"
                                >
                                    <x-lucide-shield-check class="w-5 h-5 text-teal-dark" />
                                </div>

                                <div>

                                    <p class="text-navy text-xs font-semibold">
                                        Secure Account
                                    </p>

                                    <p class="text-navy/40 text-[10px] mt-1">
                                        Shop with confidence
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Floating card - delivery --}}
                        <div
                            class="absolute
                                   left-10 bottom-0
                                   w-40
                                   rounded-2xl
                                   bg-teal
                                   p-4
                                   shadow-xl
                                   rotate-[4deg]"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-white/15
                                           flex items-center justify-center"
                                >
                                    <x-lucide-truck class="w-5 h-5 text-white" />
                                </div>

                                <div>

                                    <p class="text-white text-xs font-semibold">
                                        Easy Shopping
                                    </p>

                                    <p class="text-white/60 text-[10px] mt-1">
                                        Everything in one place
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Floating mini badge --}}
                        <div
                            class="absolute
                                   right-9 bottom-3
                                   inline-flex items-center gap-2
                                   bg-white
                                   rounded-full
                                   px-4 py-2
                                   shadow-lg"
                        >

                            <span class="w-2 h-2 rounded-full bg-teal"></span>

                            <span class="text-[10px] font-semibold text-navy">
                                Ready to hop in?
                            </span>

                        </div>

                    </div>

                </div>

            </div>



            {{-- =====================================================
                RIGHT REGISTRATION PANEL
            ====================================================== --}}
            <div
                class="bg-white
                       px-4 sm:px-8 xl:px-10
                       py-8 sm:py-10 lg:py-12"
            >

                <div class="max-w-xl mx-auto">


                    {{-- Mobile branding --}}
                    <div class="lg:hidden flex items-center gap-3 mb-7">

                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="ShopHop"
                            class="w-10 h-10 object-contain"
                        >

                        <div>

                            <p class="font-bold text-navy">
                                ShopHop
                            </p>

                            <p class="text-[7px] tracking-[0.2em] text-teal-dark mt-1">
                                HOP IN. SHOP MORE.
                            </p>

                        </div>

                    </div>


                    {{-- Header --}}
                    <div class="mb-7">

                        <p
                            class="text-xs
                                   font-semibold
                                   tracking-wide
                                   text-teal-dark
                                   mb-2"
                        >
                            CREATE ACCOUNT
                        </p>

                        <h2 class="text-navy text-3xl sm:text-4xl">
                            Sign Up
                        </h2>

                        <p
                            class="text-sm
                                   text-navy/50
                                   mt-2
                                   leading-relaxed"
                        >
                            Enter your details below to create your ShopHop account.
                        </p>

                    </div>



                    {{-- Validation Errors --}}
                    @if ($errors->any())

                        <div
                            class="mb-6
                                   rounded-2xl
                                   border border-red-200
                                   bg-red-50
                                   p-4"
                        >

                            <div class="flex gap-3">

                                <x-lucide-circle-alert
                                    class="w-5 h-5
                                           text-red-500
                                           shrink-0
                                           mt-0.5"
                                />

                                <div>

                                    <p class="text-sm font-semibold text-red-700">
                                        Please check your information.
                                    </p>

                                    <ul class="mt-2 space-y-1 text-xs text-red-600">

                                        @foreach ($errors->all() as $error)

                                            <li>
                                                • {{ $error }}
                                            </li>

                                        @endforeach

                                    </ul>

                                </div>

                            </div>

                        </div>

                    @endif



                    <form
                        action="{{ route('register.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        @csrf


                        {{-- =================================================
                            PERSONAL DETAILS
                        ================================================== --}}
                        <div class="grid sm:grid-cols-2 gap-4">


                            {{-- First Name --}}
                            <div>

                                <label
                                    for="first_name"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    First Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    value="{{ old('first_name') }}"
                                    required
                                    autocomplete="given-name"
                                    placeholder="Enter first name"
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           placeholder:text-navy/30
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>


                            {{-- Last Name --}}
                            <div>

                                <label
                                    for="last_name"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    Last Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    value="{{ old('last_name') }}"
                                    required
                                    autocomplete="family-name"
                                    placeholder="Enter last name"
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           placeholder:text-navy/30
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>


                            {{-- Middle Initial --}}
                            <div>

                                <label
                                    for="middle_initial"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    Middle Initial
                                </label>

                                <input
                                    type="text"
                                    id="middle_initial"
                                    name="middle_initial"
                                    value="{{ old('middle_initial') }}"
                                    maxlength="2"
                                    placeholder="e.g. M."
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           placeholder:text-navy/30
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>


                            {{-- Sex --}}
                            <div>

                                <label
                                    for="sex"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    Sex
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="sex"
                                    name="sex"
                                    required
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm text-navy
                                           outline-none
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >
                                    <option value="">
                                        Select sex
                                    </option>

                                    <option
                                        value="Male"
                                        @selected(old('sex') === 'Male')
                                    >
                                        Male
                                    </option>

                                    <option
                                        value="Female"
                                        @selected(old('sex') === 'Female')
                                    >
                                        Female
                                    </option>
                                </select>

                            </div>


                            {{-- Email --}}
                            <div class="sm:col-span-2">

                                <label
                                    for="email"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    E-mail
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    placeholder="your@email.com"
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           placeholder:text-navy/30
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>


                            {{-- Contact --}}
                            <div>

                                <label
                                    for="contact_no"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    Contact No.
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="tel"
                                    id="contact_no"
                                    name="contact_no"
                                    value="{{ old('contact_no') }}"
                                    required
                                    inputmode="numeric"
                                    maxlength="11"
                                    placeholder="09XXXXXXXXX"
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           placeholder:text-navy/30
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>


                            {{-- Birthday --}}
                            <div>

                                <label
                                    for="birthday"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    Birthday
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="date"
                                    id="birthday"
                                    name="birthday"
                                    value="{{ old('birthday') }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    required
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>


                            {{-- Age --}}
                            <div>

                                <label
                                    for="age"
                                    class="block
                                           text-xs font-medium
                                           text-navy
                                           mb-2"
                                >
                                    Age
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="number"
                                    id="age"
                                    value="{{ old('age') }}"
                                    readonly
                                    placeholder="Auto-generated"
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-gray-bg
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none"
                                >

                            </div>

                        </div>



                        {{-- Divider --}}
                        <div class="h-px bg-gray-border my-6"></div>



                        {{-- =================================================
                            ADDRESS
                        ================================================== --}}
                        <div>

                            <div class="flex items-center gap-2 mb-4">

                                <x-lucide-map-pin
                                    class="w-4 h-4 text-teal-dark"
                                />

                                <p class="text-sm font-semibold text-navy">
                                    Address
                                </p>

                            </div>


                            <div
                                id="address-status"
                                class="hidden
                                       mb-4
                                       text-xs
                                       text-teal-dark"
                            >
                                Loading address information...
                            </div>


                            <div class="grid sm:grid-cols-2 gap-4">


                                {{-- Province --}}
                                <div>

                                    <label
                                        for="province"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Province
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="province"
                                        name="province_code"
                                        required
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >
                                        <option value="">
                                            Select province
                                        </option>
                                    </select>

                                    <input
                                        type="hidden"
                                        id="province_name"
                                        name="province_name"
                                        value="{{ old('province_name') }}"
                                    >

                                </div>


                                {{-- City / Municipality --}}
                                <div>

                                    <label
                                        for="municipality"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Municipality / City
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="municipality"
                                        name="municipality_code"
                                        required
                                        disabled
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               disabled:bg-gray-bg
                                               disabled:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >
                                        <option value="">
                                            Select municipality / city
                                        </option>
                                    </select>

                                    <input
                                        type="hidden"
                                        id="municipality_name"
                                        name="municipality_name"
                                        value="{{ old('municipality_name') }}"
                                    >

                                </div>


                                {{-- Barangay --}}
                                <div class="sm:col-span-2">

                                    <label
                                        for="barangay"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Barangay
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        id="barangay"
                                        name="barangay_code"
                                        required
                                        disabled
                                        class="w-full
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               disabled:bg-gray-bg
                                               disabled:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >
                                        <option value="">
                                            Select barangay
                                        </option>
                                    </select>

                                    <input
                                        type="hidden"
                                        id="barangay_name"
                                        name="barangay_name"
                                        value="{{ old('barangay_name') }}"
                                    >

                                </div>


                                {{-- Street --}}
                                <div class="sm:col-span-2">

                                    <label
                                        for="street_address"
                                        class="block text-xs font-medium text-navy mb-2"
                                    >
                                        Street / House No. / Subdivision
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <textarea
                                        id="street_address"
                                        name="street_address"
                                        rows="3"
                                        required
                                        placeholder="House no., street, subdivision, building, etc."
                                        class="w-full
                                               resize-none
                                               rounded-xl
                                               border border-gray-border
                                               bg-white
                                               px-4 py-3
                                               text-sm text-navy
                                               outline-none
                                               placeholder:text-navy/30
                                               focus:border-teal
                                               focus:ring-4
                                               focus:ring-teal/10
                                               transition"
                                    >{{ old('street_address') }}</textarea>

                                </div>

                            </div>

                        </div>



                        <div class="h-px bg-gray-border my-6"></div>



                        {{-- =================================================
                            VALID ID
                        ================================================== --}}
                        <div>

                            <label
                                for="valid_id"
                                class="block text-xs font-medium text-navy mb-2"
                            >
                                Upload Valid ID
                                <span class="text-red-500">*</span>
                            </label>


                            <label
                                for="valid_id"
                                class="flex
                                       items-center gap-4
                                       rounded-xl
                                       border-2
                                       border-dashed
                                       border-gray-border
                                       bg-gray-bg
                                       hover:border-teal
                                       hover:bg-teal-light/30
                                       px-4 py-4
                                       cursor-pointer
                                       transition"
                            >

                                <div
                                    class="shrink-0
                                           w-11 h-11
                                           rounded-xl
                                           bg-white
                                           flex items-center justify-center
                                           text-teal-dark
                                           shadow-sm"
                                >
                                    <x-lucide-upload class="w-5 h-5" />
                                </div>

                                <div class="min-w-0">

                                    <p class="text-sm font-semibold text-navy">
                                        Choose a valid ID
                                    </p>

                                    <p class="text-[11px] text-navy/40 mt-1">
                                        JPG, JPEG, PNG or PDF · Max 5MB
                                    </p>

                                    <p
                                        id="file-name"
                                        class="hidden
                                               text-[11px]
                                               text-teal-dark
                                               font-medium
                                               mt-1
                                               truncate"
                                    ></p>

                                </div>


                                <input
                                    type="file"
                                    id="valid_id"
                                    name="valid_id"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    required
                                    class="hidden"
                                >

                            </label>

                        </div>



                        <div class="h-px bg-gray-border my-6"></div>



                        {{-- =================================================
                            PASSWORD
                        ================================================== --}}
                        <div class="grid sm:grid-cols-2 gap-4">

                            <div>

                                <label
                                    for="password"
                                    class="block text-xs font-medium text-navy mb-2"
                                >
                                    Password
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimum 8 characters"
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           placeholder:text-navy/30
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>


                            <div>

                                <label
                                    for="password_confirmation"
                                    class="block text-xs font-medium text-navy mb-2"
                                >
                                    Confirm Password
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Re-enter password"
                                    class="w-full
                                           rounded-xl
                                           border border-gray-border
                                           bg-white
                                           px-4 py-3
                                           text-sm
                                           text-navy
                                           outline-none
                                           placeholder:text-navy/30
                                           focus:border-teal
                                           focus:ring-4
                                           focus:ring-teal/10
                                           transition"
                                >

                            </div>

                        </div>



                        {{-- Approval notice --}}
                        <div
                            class="mt-6
                                   rounded-xl
                                   border border-teal/20
                                   bg-teal-light/50
                                   p-4"
                        >

                            <div class="flex gap-3">

                                <x-lucide-info
                                    class="w-4 h-4
                                           text-teal-dark
                                           shrink-0
                                           mt-0.5"
                                />

                                <p
                                    class="text-[11px] sm:text-xs
                                           text-navy/60
                                           leading-relaxed"
                                >
                                    After submitting your registration, please
                                    wait for the administrator's approval.
                                    Your approval status will be sent to your
                                    registered email.
                                </p>

                            </div>

                        </div>



                        {{-- Submit --}}
                        <button
                            type="submit"
                            class="w-full
                                   mt-6
                                   inline-flex
                                   items-center justify-center gap-2
                                   bg-teal
                                   hover:bg-teal-dark
                                   text-white
                                   text-sm font-semibold
                                   py-3.5
                                   rounded-xl
                                   shadow-lg shadow-teal/20
                                   hover:-translate-y-0.5
                                   transition-all duration-300"
                        >
                            Create Account

                            <x-lucide-arrow-right class="w-4 h-4" />
                        </button>


                        {{-- Sign in --}}
                        <div class="text-center mt-6">

                            <p class="text-xs text-navy/40">
                                Already have an account?

                                <a
                                    href="{{ route('login') }}"
                                    class="font-semibold
                                           text-teal-dark
                                           hover:text-navy
                                           transition"
                                >
                                    Sign In
                                </a>
                            </p>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection



@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const birthdayInput =
            document.getElementById('birthday');

        const ageInput =
            document.getElementById('age');

        const provinceSelect =
            document.getElementById('province');

        const municipalitySelect =
            document.getElementById('municipality');

        const barangaySelect =
            document.getElementById('barangay');

        const provinceNameInput =
            document.getElementById('province_name');

        const municipalityNameInput =
            document.getElementById('municipality_name');

        const barangayNameInput =
            document.getElementById('barangay_name');

        const addressStatus =
            document.getElementById('address-status');

        const validIdInput =
            document.getElementById('valid_id');

        const fileName =
            document.getElementById('file-name');


        /*
        |--------------------------------------------------------------------------
        | AGE
        |--------------------------------------------------------------------------
        */

        function calculateAge() {

            if (!birthdayInput.value) {
                ageInput.value = '';
                return;
            }

            const birthday =
                new Date(
                    birthdayInput.value +
                    'T00:00:00'
                );

            const today =
                new Date();

            let age =
                today.getFullYear() -
                birthday.getFullYear();

            const monthDifference =
                today.getMonth() -
                birthday.getMonth();

            if (
                monthDifference < 0 ||
                (
                    monthDifference === 0 &&
                    today.getDate() <
                    birthday.getDate()
                )
            ) {
                age--;
            }

            ageInput.value =
                age >= 0
                    ? age
                    : '';

        }


        birthdayInput.addEventListener(
            'change',
            calculateAge
        );

        calculateAge();



        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        validIdInput.addEventListener(
            'change',
            function () {

                if (this.files.length > 0) {

                    fileName.textContent =
                        this.files[0].name;

                    fileName.classList.remove(
                        'hidden'
                    );

                } else {

                    fileName.textContent = '';

                    fileName.classList.add(
                        'hidden'
                    );

                }

            }
        );



        /*
        |--------------------------------------------------------------------------
        | PSGC ADDRESS
        |--------------------------------------------------------------------------
        */

        const PSGC_BASE =
            'https://psgc.gitlab.io/api';


        const oldProvinceCode =
            @json(old('province_code'));

        const oldMunicipalityCode =
            @json(old('municipality_code'));

        const oldBarangayCode =
            @json(old('barangay_code'));


        function setAddressStatus(
            message,
            isError = false
        ) {

            if (!message) {

                addressStatus.classList.add(
                    'hidden'
                );

                return;
            }

            addressStatus.textContent =
                message;

            addressStatus.classList.remove(
                'hidden'
            );

            addressStatus.classList.toggle(
                'text-red-500',
                isError
            );

            addressStatus.classList.toggle(
                'text-teal-dark',
                !isError
            );

        }


        function resetSelect(
            select,
            text
        ) {

            select.innerHTML =
                `<option value="">${text}</option>`;

            select.disabled = true;

        }


        function fillSelect(
            select,
            items,
            placeholder,
            selectedCode = null
        ) {

            select.innerHTML =
                `<option value="">${placeholder}</option>`;

            items.forEach(function (item) {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    item.code;

                option.textContent =
                    item.name;

                if (
                    selectedCode &&
                    item.code === selectedCode
                ) {
                    option.selected = true;
                }

                select.appendChild(
                    option
                );

            });

            select.disabled = false;

        }


        async function fetchJson(url) {

            const response =
                await fetch(url);

            if (!response.ok) {
                throw new Error(
                    'Unable to load address data.'
                );
            }

            return response.json();

        }


        async function loadProvinces() {

            try {

                setAddressStatus(
                    'Loading provinces...'
                );

                const provinces =
                    await fetchJson(
                        `${PSGC_BASE}/provinces/`
                    );

                provinces.sort(
                    (a, b) =>
                        a.name.localeCompare(
                            b.name
                        )
                );

                fillSelect(
                    provinceSelect,
                    provinces,
                    'Select province',
                    oldProvinceCode
                );

                if (oldProvinceCode) {

                    provinceNameInput.value =
                        provinceSelect.options[
                            provinceSelect.selectedIndex
                        ]?.text || '';

                    await loadMunicipalities(
                        oldProvinceCode,
                        oldMunicipalityCode
                    );

                }

                setAddressStatus('');

            } catch (error) {

                setAddressStatus(
                    'Unable to load address dropdowns. Please refresh the page.',
                    true
                );

            }

        }


        async function loadMunicipalities(
            provinceCode,
            selectedCode = null
        ) {

            resetSelect(
                municipalitySelect,
                'Loading municipality / city...'
            );

            resetSelect(
                barangaySelect,
                'Select barangay'
            );

            municipalityNameInput.value = '';
            barangayNameInput.value = '';


            if (!provinceCode) {

                resetSelect(
                    municipalitySelect,
                    'Select municipality / city'
                );

                return;
            }


            try {

                const cities =
                    await fetchJson(
                        `${PSGC_BASE}/provinces/${provinceCode}/cities-municipalities/`
                    );

                cities.sort(
                    (a, b) =>
                        a.name.localeCompare(
                            b.name
                        )
                );


                fillSelect(
                    municipalitySelect,
                    cities,
                    'Select municipality / city',
                    selectedCode
                );


                if (selectedCode) {

                    municipalityNameInput.value =
                        municipalitySelect.options[
                            municipalitySelect.selectedIndex
                        ]?.text || '';

                    await loadBarangays(
                        selectedCode,
                        oldBarangayCode
                    );

                }

            } catch (error) {

                resetSelect(
                    municipalitySelect,
                    'Unable to load municipalities'
                );

            }

        }


        async function loadBarangays(
            municipalityCode,
            selectedCode = null
        ) {

            resetSelect(
                barangaySelect,
                'Loading barangays...'
            );

            barangayNameInput.value = '';


            if (!municipalityCode) {

                resetSelect(
                    barangaySelect,
                    'Select barangay'
                );

                return;
            }


            try {

                const barangays =
                    await fetchJson(
                        `${PSGC_BASE}/cities-municipalities/${municipalityCode}/barangays/`
                    );

                barangays.sort(
                    (a, b) =>
                        a.name.localeCompare(
                            b.name
                        )
                );


                fillSelect(
                    barangaySelect,
                    barangays,
                    'Select barangay',
                    selectedCode
                );


                if (selectedCode) {

                    barangayNameInput.value =
                        barangaySelect.options[
                            barangaySelect.selectedIndex
                        ]?.text || '';

                }

            } catch (error) {

                resetSelect(
                    barangaySelect,
                    'Unable to load barangays'
                );

            }

        }


        provinceSelect.addEventListener(
            'change',
            function () {

                provinceNameInput.value =
                    this.options[
                        this.selectedIndex
                    ]?.text || '';

                loadMunicipalities(
                    this.value
                );

            }
        );


        municipalitySelect.addEventListener(
            'change',
            function () {

                municipalityNameInput.value =
                    this.options[
                        this.selectedIndex
                    ]?.text || '';

                loadBarangays(
                    this.value
                );

            }
        );


        barangaySelect.addEventListener(
            'change',
            function () {

                barangayNameInput.value =
                    this.options[
                        this.selectedIndex
                    ]?.text || '';

            }
        );


        loadProvinces();

    });
</script>

@endpush