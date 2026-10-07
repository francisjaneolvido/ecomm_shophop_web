{{-- Server validation and flash feedback stay visible after any form redirect, including a closed dialog. --}}
@if (session('status'))<div role="status" class="pay-notice">{{ session('status') }}</div>@endif
@if ($errors->any())
    <div role="alert" class="pay-notice pay-notice--error"><strong>We could not complete that action.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
