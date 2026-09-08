@push('styles')
<style>
    /* Hide Chrome's built-in "strong password suggestion" and
       "saved credentials" icons inside password fields so only
       our custom show/hide eye button appears. */
    #buyer-registration-modal input[type="password"]::-webkit-strong-password-auto-fill-button,
    #buyer-registration-modal input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Hide Edge's built-in reveal-password icon for the same reason. */
    #buyer-registration-modal input[type="password"]::-ms-reveal,
    #buyer-registration-modal input[type="password"]::-ms-clear {
        display: none !important;
    }

    #buyer-registration-modal .step-circle {
        transition:
            background-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            border-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.35s ease;
    }

    #buyer-registration-modal .step-circle.step-circle-active {
        transform: scale(1.08);
        box-shadow: 0 0 0 5px rgba(20, 184, 166, 0.15);
    }

    #buyer-registration-modal .step-check {
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #buyer-registration-modal .step-line {
        transition: background-color 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }

    #buyer-registration-modal .step-label {
        transition: color 0.3s ease, font-weight 0.3s ease;
    }

    #buyer-registration-modal #buyer-register-form button[type="button"],
    #buyer-registration-modal #buyer-register-form button[type="submit"] {
        transition:
            transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.25s ease,
            background-color 0.25s ease;
    }

    #buyer-registration-modal #buyer-register-form button[type="button"]:active,
    #buyer-registration-modal #buyer-register-form button[type="submit"]:active {
        transform: scale(0.97);
    }

    #buyer-registration-modal .req-dot {
        transition:
            background-color 0.25s ease,
            border-color 0.25s ease,
            transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #buyer-registration-modal .req-item.req-satisfied .req-dot {
        transform: scale(1.12);
    }
</style>
@endpush


