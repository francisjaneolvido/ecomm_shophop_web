@push('styles')
<style>
    #logistics-registration-modal input[type="password"]::-webkit-strong-password-auto-fill-button,
    #logistics-registration-modal input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    #logistics-registration-modal input[type="password"]::-ms-reveal,
    #logistics-registration-modal input[type="password"]::-ms-clear {
        display: none !important;
    }

    #logistics-registration-modal .step-circle {
        transition:
            background-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            border-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.35s ease;
    }

    #logistics-registration-modal .step-circle.step-circle-active {
        transform: scale(1.08);
        box-shadow: 0 0 0 5px rgba(20, 184, 166, 0.15);
    }

    #logistics-registration-modal .step-check {
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #logistics-registration-modal .step-line {
        transition: background-color 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }

    #logistics-registration-modal .step-label {
        transition: color 0.3s ease, font-weight 0.3s ease;
    }

    #logistics-registration-modal #logistics-register-form button[type="button"],
    #logistics-registration-modal #logistics-register-form button[type="submit"] {
        transition:
            transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.25s ease,
            background-color 0.25s ease;
    }

    #logistics-registration-modal #logistics-register-form button[type="button"]:active,
    #logistics-registration-modal #logistics-register-form button[type="submit"]:active {
        transform: scale(0.97);
    }

    #logistics-registration-modal .req-dot {
        transition:
            background-color 0.25s ease,
            border-color 0.25s ease,
            transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #logistics-registration-modal .otp-box { transition: border-color .2s ease, box-shadow .2s ease, transform .15s ease; }
    #logistics-registration-modal .otp-box:focus { transform: translateY(-2px); }
    #logistics-registration-modal .otp-box.otp-filled { animation: logisticsOtpPop .2s ease; }
    @keyframes logisticsOtpPop {
        0% { transform: scale(1); }
        45% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    #logistics-registration-modal [data-coverage-chip] { animation: logisticsChipFadeIn .25s ease; }
    @keyframes logisticsChipFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    #logistics-terms-scroll { scrollbar-width: thin; scrollbar-color: #99cfc9 transparent; }
    #logistics-terms-scroll::-webkit-scrollbar { width: 6px; }
    #logistics-terms-scroll::-webkit-scrollbar-thumb { background-color: #99cfc9; border-radius: 999px; }
    #logistics-terms-scroll-wrap.terms-at-bottom #logistics-terms-scroll-fade { opacity: 0; }
</style>
@endpush


