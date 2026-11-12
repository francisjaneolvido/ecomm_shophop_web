<div class="help-content" data-help-center>
    <div class="help-section-heading"><h2>How can we help?</h2><p>Find an answer or open the right place to manage your purchase.</p></div>
    <div class="help-shortcuts">
        <a href="{{ auth()->user()?->account_type === 'buyer' ? route('buyer.orders') : route('login') }}"><x-lucide-package /><strong>My Orders</strong><span>Order status and delivery</span><x-lucide-arrow-up-right /></a>
        <a href="{{ auth()->user()?->account_type === 'buyer' ? route('buyer.messages') : route('login') }}"><x-lucide-message-circle /><strong>Messages</strong><span>Talk to your seller</span><x-lucide-arrow-up-right /></a>
        <a href="{{ auth()->user()?->account_type === 'buyer' ? route('buyer.profile').'#10193Cress' : route('login') }}"><x-lucide-map-pin /><strong>Address Book</strong><span>Update delivery details</span><x-lucide-arrow-up-right /></a>
    </div>
    <div class="help-section-heading"><h3>Frequently asked questions</h3></div>
    <label class="discovery-filter help-search"><x-lucide-search /><span class="sr-only">Search help articles</span><input type="search" data-help-search placeholder="Search orders, delivery, payments..." maxlength="100"></label>
    <div class="help-topics" role="group" aria-label="Filter help topics">
        <button type="button" data-help-topic="all" aria-pressed="true">All topics</button>
        @foreach(collect(config('shophop_help', []))->pluck('topic')->unique() as $topic)<button type="button" data-help-topic="{{ $topic }}" aria-pressed="false">{{ $topic }}</button>@endforeach
    </div>
    <div class="help-faq-list">
        @foreach(config('shophop_help', []) as $faq)
            <details data-help-faq data-help-topic-name="{{ $faq['topic'] }}" data-help-text="{{ mb_strtolower($faq['question'].' '.$faq['answer']) }}">
                <summary>{{ $faq['question'] }}<x-lucide-chevron-down /></summary>
                <p>{{ $faq['answer'] }}</p>
            </details>
        @endforeach
    </div>
    <p class="help-no-results" data-help-empty role="status" hidden>No help articles match your search. Try another topic or submit a support request.</p>
    @unless(request()->routeIs('help.index', 'buyer.help'))
        <div class="help-contact-strip"><div><h3>Still need help?</h3><p>Report a problem and check your support request status.</p></div><a class="discovery-primary" href="{{ route('buyer.help') }}#support-request">Contact support <x-lucide-arrow-right /></a></div>
    @endunless
</div>
