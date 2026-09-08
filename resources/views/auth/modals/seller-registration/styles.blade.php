@push('styles')
<style>
    /* Hide Chrome's built-in "strong password suggestion" and
       "saved credentials" icons inside password fields so only
       our custom show/hide eye button appears. */
    #seller-registration-modal input[type="password"]::-webkit-strong-password-auto-fill-button,
    #seller-registration-modal input[type="password"]::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Hide Edge's built-in reveal-password icon for the same reason. */
    #seller-registration-modal input[type="password"]::-ms-reveal,
    #seller-registration-modal input[type="password"]::-ms-clear {
        display: none !important;
    }

    #seller-registration-modal .step-circle {
        transition:
            background-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            border-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
            transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.35s ease;
    }

    #seller-registration-modal .step-circle.step-circle-active {
        transform: scale(1.08);
        box-shadow: 0 0 0 5px rgba(20, 184, 166, 0.15);
    }

    #seller-registration-modal .step-check {
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #seller-registration-modal .step-line {
        transition: background-color 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }

    #seller-registration-modal .step-label {
        transition: color 0.3s ease, font-weight 0.3s ease;
    }

    #seller-registration-modal #seller-register-form button[type="button"],
    #seller-registration-modal #seller-register-form button[type="submit"] {
        transition:
            transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.25s ease,
            background-color 0.25s ease;
    }

    #seller-registration-modal #seller-register-form button[type="button"]:active,
    #seller-registration-modal #seller-register-form button[type="submit"]:active {
        transform: scale(0.97);
    }

    #seller-registration-modal .req-dot {
        transition:
            background-color 0.25s ease,
            border-color 0.25s ease,
            transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #seller-registration-modal .req-item.req-satisfied .req-dot {
        transform: scale(1.12);
    }

    /* Hide the dialog's scrollbar visually but keep it functional
   as a safety net for very short viewports. */
    #seller-registration-dialog {
        scrollbar-width: none;      /* Firefox */
        -ms-overflow-style: none;   /* old Edge/IE */
    }

    #seller-registration-dialog::-webkit-scrollbar {
        display: none;              /* Chrome / Safari / new Edge */
    }
</style>
@endpush


