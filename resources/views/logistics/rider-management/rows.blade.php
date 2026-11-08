        @forelse($riders as $r)
        <tr><td><strong>{{ $r->name }}</strong><div class="ops-row-sub">{{ $r->email }}</div><div class="ops-row-sub">{{ $r->vehicle_type }} {{ $r->plate_number }}</div></td>
            <td>{{ $r->sortingCenter?->name ?? $r->lifecycleProfile?->preferredBranch?->name ?? 'Awaiting assignment' }}<div class="ops-row-sub">{{ $r->sortingCenter?->province }}</div></td>
            <td>{{ ucwords(str_replace('_',' ',$r->application_status)) }}@if(!$r->email_verified_at)<div class="ops-row-sub">Email verification required</div>@endif</td>
            <td>{{ ucwords(str_replace('_',' ',$r->operational_status)) }}</td><td>{{ ucwords(str_replace('_',' ',$r->availabilityState())) }}</td>
            <td><a class="ops-action" href="{{ request()->fullUrlWithQuery(['rider'=>$r->id]) }}#rider-details">View details</a></td></tr>
        @empty<tr><td colspan="6" class="ops-empty">No riders match these filters.</td></tr>@endforelse
