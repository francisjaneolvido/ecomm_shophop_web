@extends($layout)
@section('title','Rider Management')
@section('content')
<div class="ops-module space-y-5" data-rider-management>
    <header class="ops-hero">
        <div><span class="eyebrow">{{ $scope['role']==='main' ? 'Main Hub | Company oversight' : 'Branch | Daily operations' }}</span>
        <h1>Rider Management</h1>
        <p class="description">{{ $scope['role']==='main' ? 'Review company applications, confirm primary Branches and oversee coverage, capacity and transfers.' : 'Activate approved riders, confirm official barangay coverage and manage daily availability and schedules.' }}</p></div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="sh-btn-soft" onclick="window.location.reload()">Refresh</button>
            @if($scope['role']==='branch')
            <a class="sh-btn-primary" href="{{ route(request()->routeIs('logistics.staff.*')?'logistics.staff.rider-batches.index':'branch.rider-batches.index') }}">Open rider batches</a>
            @elseif(!$scope['center'])
            <a class="sh-btn-soft" href="{{ route('logistics.rider-batches.index') }}">Monitor rider batches</a>
            <a class="sh-btn-soft" href="{{ route('logistics.riders.index',['settings'=>1]) }}">Company settings & service areas</a>
            @endif
        </div>
    </header>
    @if(session('status'))<div role="status" class="sh-alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="sh-alert-error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <p class="ops-panel-note">Every rider can perform both pickup and delivery. Main Hub approval is followed by assigned Branch activation.</p>

    @if($scope['role']==='main')
    <details class="ops-panel p-4"><summary class="font-semibold cursor-pointer">Active riders by Branch</summary>
        <div class="grid gap-3 mt-3 md:grid-cols-3">
            @forelse($branches as $b)<a class="sh-stat" href="{{ route($prefix.'index',['branch'=>$b->id,'state'=>'active']) }}">
                <p class="sh-stat-label">{{ $b->name }}</p><p class="sh-stat-value" data-management-branch-count="{{ $b->id }}">{{ $activeByBranch[$b->id] ?? 0 }}</p>
                <p class="sh-stat-note">{{ $b->municipality }}, {{ $b->province }}</p></a>
            @empty<p class="ops-empty">No active Branches configured.</p>@endforelse
        </div>
    </details>
    @endif

    <form method="GET" class="ops-panel p-4 grid gap-3 md:grid-cols-3 xl:grid-cols-6">
        <label class="sh-field"><span class="sh-label">Name or email</span><input class="sh-input" name="q" value="{{ request('q') }}" maxlength="150" placeholder="Search riders"></label>
        @if($scope['role']==='main')
        <label class="sh-field"><span class="sh-label">Branch</span><select class="sh-input" name="branch"><option value="">All company Branches</option>@foreach($branches as $b)<option value="{{ $b->id }}" @selected(request('branch')==$b->id)>{{ $b->name }}</option>@endforeach</select></label>
        <label class="sh-field"><span class="sh-label">Province</span><select class="sh-input" name="province"><option value="">All provinces</option>@foreach($branches->pluck('province')->filter()->unique()->sort() as $province)<option @selected(request('province')===$province)>{{ $province }}</option>@endforeach</select></label>
        @endif
        <label class="sh-field"><span class="sh-label">Status</span><select class="sh-input" name="state"><option value="">All statuses</option>@foreach(['pending_review'=>'Pending review','under_review'=>'Under review','needs_documents'=>'Needs documents','pending_activation'=>'Pending activation','active'=>'Active','inactive'=>'Inactive','suspended'=>'Suspended','rejected'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" @selected(request('state')===$value)>{{ $label }}</option>@endforeach</select></label>
        <label class="sh-field"><span class="sh-label">Official barangay</span><input class="sh-input" name="barangay" value="{{ request('barangay') }}" maxlength="150" placeholder="Barangay"></label>
        <div class="flex gap-2 items-end"><button class="sh-btn-primary">Apply</button><a class="sh-btn-soft" href="{{ route($prefix.'index') }}">Clear</a></div>
    </form>

    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>{{ $scope['role']==='main'?'Company rider directory':'Branch riders & pending activation' }}</h2><p class="ops-panel-note"><span data-management-total>{{ $riders->total() }}</span> matching riders. Select a rider to review details and available actions.</p></div></div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Rider</th><th>Primary / preferred Branch</th><th>Application</th><th>Operations</th><th>Availability</th><th></th></tr></thead><tbody data-management-rows>
        @include('logistics.rider-management.rows')
        </tbody></table></div><div class="p-4" data-management-pagination>{{ $riders->links() }}</div>
    </section>

    @if($selected)
    @php
        $actionUrl=route($prefix.'action',$selected->id);
        $p=$selected->managementProfile();
        $selectedKeys=collect($p?->accepted_coverage ?? $p?->preferred_coverage ?? [])->pluck('key')->all();
        $reviewable=in_array($selected->application_status,['pending_review','under_review','needs_documents'],true);
    @endphp
    <section id="rider-details" class="ops-panel p-5 space-y-4">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div><p class="eyebrow">Rider profile</p><h2 class="text-xl font-semibold">{{ $selected->name }}</h2><p class="ops-panel-note">{{ $selected->email }} &middot; {{ $selected->contact_no }}</p></div>
            <a class="sh-btn-soft" href="{{ request()->fullUrlWithoutQuery('rider') }}">Close details</a>
        </div>
        <dl class="grid gap-4 md:grid-cols-3">
            <div><dt class="sh-label">Home address</dt><dd>{{ $selected->applicationAddress() ?: 'Not provided' }}</dd></div>
            <div><dt class="sh-label">Vehicle</dt><dd>{{ $selected->vehicle_type }} &middot; {{ $selected->plate_number ?: 'No plate on file' }}</dd></div>
            <div><dt class="sh-label">Primary Branch</dt><dd>{{ $details['branch']['name'] ?? 'Awaiting Main Hub confirmation' }}</dd></div>
        </dl>
        <div class="flex flex-wrap gap-2">
            @foreach(['or_cr'=>'Vehicle OR/CR','id_or_license'=>'ID / license'] as $slot=>$label)
            @if($selected->getAttribute($slot.'_path'))<a class="sh-btn-soft" href="{{ route($prefix.'document',[$selected->id,$slot]) }}" target="_blank" rel="noopener">{{ $label }}</a>@endif
            @endforeach
        </div>
        <div><p class="sh-label">Preferred coverage</p><p class="ops-panel-note">{{ collect($details['preferred_coverage'])->pluck('label')->implode(' ? ') ?: 'Not provided on legacy registration' }}</p></div>
        <div><p class="sh-label">Official coverage</p><p class="ops-panel-note">{{ collect($details['official_coverage'])->pluck('label')->implode(' ? ') ?: 'Branch must confirm official coverage' }}</p></div>
        @if($details['assignment_confirmation_required'])<div class="sh-alert-error">The rider must accept the confirmed Branch and choose coverage in the mobile app before activation.</div>@endif
        @if($details['document_request'])<div class="ops-inline-alert">{{ $details['document_request']['message'] }}</div>@endif
        @if($details['rejection_reason'])<div class="sh-alert-error">{{ $details['rejection_reason'] }}</div>@endif

        @if($scope['role']==='main' && $reviewable)
        <div class="grid gap-4 md:grid-cols-2">
            <form method="POST" action="{{ $actionUrl }}" class="space-y-3">@csrf
                <h3 class="font-semibold">Review application</h3>
                <label class="sh-field"><span class="sh-label">Confirmed primary Branch</span><select class="sh-input" name="branch_id">
                    <option value="">Choose company Branch</option>@foreach($branches as $b)<option value="{{ $b->id }}" @selected(($selected->sorting_center_id ?: $p?->preferred_branch_id)==$b->id)>{{ $b->name }} &middot; {{ $b->municipality }}</option>@endforeach
                </select></label>
                <div class="flex gap-2 flex-wrap"><button class="sh-btn-soft" name="action" value="under_review">Mark under review</button><button class="sh-btn-primary" name="action" value="approve">Approve for Branch activation</button></div>
            </form>
            <form method="POST" action="{{ $actionUrl }}" class="space-y-3">@csrf
                <h3 class="font-semibold">Documents or rejection</h3>
                <label class="sh-field"><span class="sh-label">Reason / requested correction</span><textarea class="sh-input" name="reason" rows="3" required maxlength="2000"></textarea></label>
                <fieldset><legend class="sh-label">Documents to request</legend><label class="mr-3"><input type="checkbox" name="documents[]" value="or_cr"> OR/CR</label><label><input type="checkbox" name="documents[]" value="id_or_license"> ID / license</label></fieldset>
                <div class="flex gap-2 flex-wrap"><button class="sh-btn-soft" name="action" value="needs_documents">Request documents</button><button class="sh-btn-soft" name="action" value="reject">Reject application</button></div>
            </form>
        </div>
        @endif

        @if($scope['role']==='branch' && $selected->application_status==='approved')
        <form method="POST" action="{{ $actionUrl }}" class="space-y-3">@csrf<input type="hidden" name="action" value="activate">
            <h3 class="font-semibold">Confirm official barangays & activate</h3>
            <p class="ops-panel-note">Coverage supports pickup and delivery. Capacity includes active riders from every Branch of this company. Full barangays require an individual Main Hub override.</p>
            <div class="grid gap-2 md:grid-cols-2 max-h-72 overflow-auto">
            @forelse($coverage as $key=>$place)
                <label class="flex gap-2 items-start rounded-lg border border-slate-200 p-3">
                    <input type="checkbox" class="mt-1 accent-teal-600" name="coverage[]" value="{{ $key }}" @checked(in_array($key,$selectedKeys,true))>
                    <span>{{ $place['label'] }}<span class="block ops-row-sub">{{ $capacity[$key]['active_count'] }} / {{ $capacity[$key]['limit'] }} active riders</span></span>
                </label>
            @empty<p class="ops-empty">Configure Branch pickup and delivery service areas first.</p>@endforelse
            </div><button class="sh-btn-primary" @disabled(!$coverage || $details['assignment_confirmation_required'] || $selected->operational_status==='suspended')>Confirm coverage & activate</button>
        </form>
        @if($selected->canOperate())
        <div class="grid gap-4 md:grid-cols-2">
            <form method="POST" action="{{ $actionUrl }}" class="space-y-3">@csrf<input type="hidden" name="action" value="availability">
                <h3 class="font-semibold">Availability</h3><select class="sh-input" name="state">@foreach(['online'=>'Online','offline'=>'Offline','on_break'=>'On break'] as $state=>$label)<option value="{{ $state }}" @selected($selected->availabilityState()===$state)>{{ $label }}</option>@endforeach</select><button class="sh-btn-soft">Update availability</button>
            </form>
            <form method="POST" action="{{ $actionUrl }}" class="space-y-3">@csrf<input type="hidden" name="action" value="schedule">
                <h3 class="font-semibold">Add work schedule</h3><p class="ops-panel-note">Times use Philippine time. A batch must fit within a configured work window.</p>
                <label class="sh-field"><span class="sh-label">Starts</span><input class="sh-input" type="datetime-local" name="starts_at" required></label>
                <label class="sh-field"><span class="sh-label">Ends</span><input class="sh-input" type="datetime-local" name="ends_at" required></label>
                <button class="sh-btn-soft">Add schedule</button>
            </form>
        </div>
        @endif
        <form method="POST" action="{{ $actionUrl }}" class="space-y-3">@csrf<input type="hidden" name="action" value="transfer">
            <h3 class="font-semibold">Request Branch transfer</h3><select class="sh-input" name="branch_id" required><option value="">Destination Branch</option>@foreach($branches->where('id','!=',$selected->sorting_center_id) as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
            <label class="sh-field"><span class="sh-label">Transfer reason</span><textarea class="sh-input" name="reason" required maxlength="2000"></textarea></label><button class="sh-btn-soft">Send to Main Hub</button>
        </form>
        @endif

        @if($scope['role']==='main' && $coverage)
        <details><summary class="font-semibold cursor-pointer">Barangay capacity & individual overrides</summary>
            <p class="ops-panel-note mt-3">Default: six unique active riders per barangay per company, shared by pickup and delivery. An override applies only to this rider. Lowering a limit does not deactivate existing riders.</p>
            <form method="POST" action="{{ $actionUrl }}" class="grid gap-3 mt-3 md:grid-cols-2">@csrf<input type="hidden" name="action" value="capacity">
                <label class="sh-field"><span class="sh-label">Barangay</span><select class="sh-input" name="location_key" required>@foreach($coverage as $key=>$place)<option value="{{ $key }}">{{ $place['label'] }} &middot; {{ $capacity[$key]['active_count'] }}/{{ $capacity[$key]['limit'] }}</option>@endforeach</select></label>
                <label class="sh-field"><span class="sh-label">Action</span><select class="sh-input" name="capacity_action"><option value="grant">Grant this rider an override</option><option value="revoke">Revoke this rider's override</option><option value="limit">Configure company barangay limit</option></select></label>
                <label class="sh-field"><span class="sh-label">New limit (for limit action)</span><input class="sh-input" type="number" min="0" name="rider_limit" value="6"></label>
                <label class="sh-field"><span class="sh-label">Recorded reason</span><input class="sh-input" name="reason" required maxlength="2000"></label><button class="sh-btn-primary">Record capacity decision</button>
            </form>
        </details>
        @endif

        @if($selected->application_status==='approved')
        <form method="POST" action="{{ $actionUrl }}" class="flex flex-wrap gap-3 items-end">@csrf<input type="hidden" name="action" value="operational_status">
            <label class="sh-field"><span class="sh-label">{{ $scope['role']==='main'?'Compliance status':'Branch deactivation' }}</span><select class="sh-input" name="state">
                @if($scope['role']==='main')<option value="suspended">Suspend operations</option><option value="pending_activation">Release for Branch reactivation</option>
                @else<option value="inactive">Deactivate</option>@endif</select></label>
            <label class="sh-field flex-1"><span class="sh-label">Reason</span><input class="sh-input" name="reason" required maxlength="2000"></label><button class="sh-btn-soft">Record status change</button>
        </form>
        @endif

        <details><summary class="font-semibold cursor-pointer">Work schedules & operational history</summary>
            <div class="space-y-2 mt-3">
                @forelse($details['schedules'] as $schedule)
                <div class="flex justify-between gap-3 items-center"><span>{{ \Carbon\Carbon::parse($schedule->starts_at,'UTC')->setTimezone('Asia/Manila')->format('M j, g:i A') }} &ndash; {{ \Carbon\Carbon::parse($schedule->ends_at,'UTC')->setTimezone('Asia/Manila')->format('M j, g:i A') }}</span>
                @if($scope['role']==='branch')<form method="POST" action="{{ $actionUrl }}">@csrf<input type="hidden" name="action" value="schedule"><input type="hidden" name="cancel_id" value="{{ $schedule->id }}"><button class="sh-btn-soft">Cancel window</button></form>@endif</div>
                @empty<p class="ops-panel-note">No upcoming managed work windows.</p>@endforelse
            </div>
            <p class="sh-label mt-4">Parcel outcomes</p><div class="flex flex-wrap gap-3">@forelse($performance as $status=>$total)<span>{{ ucwords(str_replace('_',' ',$status)) }}: <strong>{{ $total }}</strong></span>@empty<span class="ops-panel-note">No parcel history.</span>@endforelse</div>
            <ol class="space-y-3 mt-4">@foreach($details['history'] as $event)<li><strong>{{ ucwords(str_replace('_',' ',$event->event)) }}</strong><div class="ops-row-sub">{{ $event->created_at }}</div>
                @if(!empty($event->details['reason']))<p class="ops-panel-note">{{ $event->details['reason'] }}</p>@endif</li>@endforeach</ol>
        </details>
    </section>
    @endif

    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2>Controlled Branch transfers</h2><p class="ops-panel-note">Main Hub decides requests. Outstanding batches, parcel custody and unremitted COD must be resolved before approval.</p></div></div>
        <div class="ops-table-scroll"><table class="ops-table"><thead><tr><th>Rider</th><th>From / to</th><th>Status & reason</th><th>Decision</th></tr></thead><tbody>
        @forelse($transfers as $transfer)
        <tr><td>{{ $transfer->rider?->name }}</td><td>{{ $transfer->fromBranch?->name }}<div class="ops-row-sub">To {{ $transfer->toBranch?->name }}</div></td>
            <td>{{ ucfirst($transfer->status) }}<div class="ops-row-sub">{{ $transfer->reason }}</div><div class="ops-row-sub">{{ $transfer->decision_reason }}</div></td>
            <td>@if($scope['role']==='main' && $transfer->status==='pending')
                <form method="POST" action="{{ route($prefix.'transfer',$transfer->id) }}" class="space-y-2">@csrf<input class="sh-input" name="reason" required maxlength="2000" placeholder="Decision reason" aria-label="Transfer decision reason">
                <div class="flex gap-2"><button class="sh-btn-primary" name="decision" value="approve">Approve transfer</button><button class="sh-btn-soft" name="decision" value="reject">Reject</button></div></form>
                @else<span class="ops-panel-note">{{ $transfer->decided_at ?: 'Awaiting Main Hub review' }}</span>@endif</td></tr>
        @empty<tr><td colspan="4" class="ops-empty">No transfer requests.</td></tr>@endforelse
        </tbody></table></div><div class="p-4">{{ $transfers->links() }}</div>
    </section>
    <p class="ops-panel-note" role="status" data-management-sync>Directory statuses refresh every 15 seconds while visible. Open forms keep your entries.</p>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shophop-rider-management.js') }}" defer></script>
@endpush
