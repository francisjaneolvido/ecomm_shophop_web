@php
    $serverBlocked = collect($activationChecks)->contains(fn ($check) => !$check['passed']);
@endphp
<div class="rm-activation" data-activation-panel>
    <h3>Review &amp; activate rider</h3>
    <p>Main Hub approval is complete. Confirm the official barangays this Rider may serve for pickup and delivery.</p>
    <ul class="rm-checklist" aria-label="Activation requirements">
        @foreach($activationChecks as $check)
        <li class="{{ $check['passed'] ? 'rm-check-ok' : 'rm-check-blocked' }}">
            <strong>{{ $check['passed'] ? 'Ready' : 'Action required' }}: {{ $check['label'] }}</strong>
            @unless($check['passed'])<span>{{ $check['help'] }}</span>@endunless
        </li>
        @endforeach
    </ul>
    <form method="POST" action="{{ $actionUrl }}" data-rider-activation data-server-blocked="{{ $serverBlocked ? '1' : '0' }}">
        @csrf
        <input type="hidden" name="action" value="activate">
        <fieldset>
            <legend>Official barangays</legend>
            <p class="ops-panel-note">Saved accepted or preferred coverage is preselected for review. You can change it before confirming. Counts show other active Riders in this company.</p>
            <label class="sh-field rm-search"><span class="sh-label">Search barangays</span><input type="search" class="sh-input" data-coverage-search placeholder="Search barangay or city"></label>
            <div class="rm-coverage-list">
            @forelse($coverage as $key => $place)
                @php
                    $full = $capacity[$key]['active_count'] >= $capacity[$key]['limit'];
                    $override = in_array($key, $overrides, true);
                @endphp
                <label class="rm-coverage-option" data-coverage-option data-label="{{ mb_strtolower($place['label']) }}">
                    <input type="checkbox" name="coverage[]" value="{{ $key }}" data-capacity-blocked="{{ $full && !$override ? '1' : '0' }}" @checked(in_array($key, $selectedKeys, true))>
                    <span><strong>{{ $place['label'] }}</strong>
                        <small>{{ $capacity[$key]['active_count'] }} / {{ $capacity[$key]['limit'] }} other active riders</small>
                        @if($full && !$override)<small class="rm-warning">Full: Main Hub capacity approval required.</small>
                        @elseif($override)<small>Individual Main Hub override approved.</small>@endif
                    </span>
                </label>
            @empty<p class="ops-empty">{{ $coverageError ?: 'Configure Branch pickup and delivery service areas first.' }}</p>@endforelse
            </div>
        </fieldset>
        <p class="rm-selection-status" role="status" data-coverage-status>Review the selected barangays before activation. Full barangays require an individual Main Hub override.</p>
        <label class="rm-confirm"><input type="checkbox" name="confirm_activation" value="1" required> I confirm the official barangays and authorize this Rider to operate for this Branch.</label>
        <p class="ops-panel-note">Existing parcel and cash custody are checked when you submit. Work schedules can be managed after activation.</p>
        <button class="sh-btn-primary" type="submit" data-activate-button @disabled($serverBlocked)>Confirm coverage &amp; activate</button>
    </form>
</div>
