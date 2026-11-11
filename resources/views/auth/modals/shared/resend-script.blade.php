    const resendForm = resendButton.closest('form');
    const resendFeedback = document.createElement('p');
    resendFeedback.className = 'mt-2 text-xs text-navy';
    resendFeedback.setAttribute('role', 'status');
    resendFeedback.setAttribute('aria-live', 'polite');
    resendForm.after(resendFeedback);
    let resendDeadline = Date.now() + {{ (int) ($retryAfter ?? 0) }} * 1000;
    let resendPending = false;
    function updateResendTimer() {
        const remaining = Math.max(0, Math.ceil((resendDeadline - Date.now()) / 1000));
        resendButton.disabled = resendPending || remaining > 0;
        resendText.textContent = resendPending ? 'Sending code...' :
            (remaining > 0 ? 'Resend code in ' + remaining + 's' : 'Resend verification code');
    }
    resendForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (resendButton.disabled) return;
        resendPending = true;
        resendForm.setAttribute('aria-busy', 'true');
        resendFeedback.textContent = '';
        updateResendTimer();
        try {
            const response = await fetch(resendForm.action, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }, body: new FormData(resendForm),
            });
            const payload = await response.json().catch(() => ({}));
            const retryAfter = Number(payload.retry_after || response.headers?.get('Retry-After') || 0);
            if (retryAfter) resendDeadline = Date.now() + retryAfter * 1000;
            resendFeedback.textContent = payload.message || (response.ok ?
                'Verification email accepted for sending.' :
                'Unable to resend the code. Please try again.');
            if (response.status === 419) resendFeedback.textContent =
                'Your session has expired. Please sign in again to continue verification.';
            if (response.ok) {
                otpInputs.forEach(input => { input.value = ''; });
                hiddenCodeInput.value = '';
                updateOtpState();
                otpInputs[0]?.focus();
            }
        } catch (error) {
            resendFeedback.textContent = 'Unable to connect. Check your connection and try again.';
        } finally {
            resendPending = false;
            resendForm.removeAttribute('aria-busy');
            updateResendTimer();
        }
    });
    updateResendTimer();
    window.setInterval(updateResendTimer, 1000);
