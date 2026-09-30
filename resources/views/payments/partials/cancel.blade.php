{{-- Explicit confirmation submits the unchanged role-specific cancellation POST contract. --}}
<section class="pay-danger-area" aria-labelledby="cancel-area-heading">
    <h2 id="cancel-area-heading">Cancel this checkout payment</h2>
    <p class="pay-muted pay-small">This closes every linked Seller Order. @if ($admin)Use Reject Proof above when the Buyer only needs to correct their evidence.@else Cancel only if you no longer want these Orders.@endif</p>
    <details id="payment-cancel-details">
        <summary class="pay-button pay-button--danger" id="payment-cancel-trigger">{{ $admin ? 'Cancel Payment / Orders' : 'Cancel Order' }}</summary>
        <div id="payment-cancel-panel" class="pay-confirm">
            <span class="pay-icon"><x-lucide-triangle-alert aria-hidden="true" /></span>
            <h2 id="payment-cancel-title">Cancel payment and all linked Orders?</h2>
            <div id="payment-cancel-description"><p>This cancellation cannot be undone.</p><ul><li>Every Seller Order linked to this checkout payment will be cancelled.</li><li>The backend restores inventory and reverses applied Voucher effects.</li><li>Purchased Cart rows are not recreated. To shop again, add the items to your Cart yourself.</li></ul></div>
            <form method="POST" action="{{ route($admin ? 'admin.payments.cancel' : 'buyer.payments.cancel', $payment) }}">@csrf
                @if ($admin)
                    {{-- Keep Admin reason validation names and limits identical to the backend. --}}
                    <div class="pay-field"><label for="cancel-reason">Cancellation reason</label><textarea id="cancel-reason" name="reason" required maxlength="1000" aria-describedby="cancel-reason-help">{{ old('reason') }}</textarea><p id="cancel-reason-help" class="pay-help">Record why the entire checkout group is being closed.</p></div>
                @endif
                <div class="pay-actions"><button type="button" class="pay-button" id="payment-cancel-keep" hidden autofocus>Keep payment open</button><button type="submit" class="pay-button pay-button--danger">Confirm cancellation</button></div>
            </form>
        </div>
    </details>
</section>
@push('scripts')
<script>
    // Native focus containment and Escape replace confirm(); details preserves an accessible no-script path.
    (() => {
        const details = document.getElementById('payment-cancel-details');
        let trigger = document.getElementById('payment-cancel-trigger');
        const panel = document.getElementById('payment-cancel-panel');
        const keep = document.getElementById('payment-cancel-keep');
        if (!details || typeof HTMLDialogElement === 'undefined' || !HTMLDialogElement.prototype.showModal) return;
        const dialog = document.createElement('dialog');
        dialog.className = 'pay-dialog';
        dialog.id = 'payment-cancel-dialog';
        dialog.setAttribute('aria-labelledby', 'payment-cancel-title');
        dialog.setAttribute('aria-describedby', 'payment-cancel-description');
        details.parentElement.append(dialog);
        dialog.append(panel);
        keep.hidden = false;
        // Once enhanced, expose a real dialog-launching button instead of an empty disclosure to assistive technology.
        const button = document.createElement('button');
        button.type = 'button';
        button.id = trigger.id;
        button.className = trigger.className;
        button.textContent = trigger.textContent;
        details.before(button);
        details.remove();
        trigger = button;
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.setAttribute('aria-controls', dialog.id);
        trigger.addEventListener('click', event => { event.preventDefault(); dialog.showModal(); keep.focus(); });
        keep.addEventListener('click', () => dialog.close());
        // Wrap the modal's controls explicitly so Chrome never parks keyboard focus on the document body.
        dialog.addEventListener('keydown', event => {
            if (event.key !== 'Tab') return;
            const controls = dialog.querySelectorAll('button:not([disabled]), input:not([type="hidden"]):not([disabled]), textarea:not([disabled])');
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        dialog.addEventListener('close', () => trigger.focus());
    })();
</script>
@endpush
