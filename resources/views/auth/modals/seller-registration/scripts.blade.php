@push('scripts')
<script>
    (function () {

        const modal = document.getElementById('seller-registration-modal');

        if (!modal) {
            return;
        }

        const dialog = document.getElementById('seller-registration-dialog');
        const filePreviewModal = document.getElementById('seller-file-preview-modal');
        const filePreviewFullImage = document.getElementById('seller-file-preview-full-image');
        const filePreviewPdfFrame = document.getElementById('seller-file-preview-pdf-frame');
        const filePreviewName = document.getElementById('seller-file-preview-name');

        let lastFocusedElement = null;
        let provincesLoaded = false;
        let closeTimeoutId = null;
        const filePreviewUrls = {};
        let activeFilePreviewKey = null;


        /*
        |--------------------------------------------------------------------------
        | MODAL OPEN / CLOSE — fade + scale transition
        |--------------------------------------------------------------------------
        */

        function openSellerRegistrationModal() {

            lastFocusedElement = document.activeElement;

            if (closeTimeoutId) {
                window.clearTimeout(closeTimeoutId);
                closeTimeoutId = null;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {

                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');

                    if (dialog) {
                        dialog.classList.remove('opacity-0', 'scale-95', 'translate-y-3');
                        dialog.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                    }

                });
            });

            

            if (!provincesLoaded) {
                provincesLoaded = true;
                loadProvinces();
            }

            window.setTimeout(function () {
                const firstField = document.getElementById('seller_first_name');
                if (firstField) firstField.focus();
            }, 50);

        }

        function closeSellerRegistrationModal() {

            closeSellerFilePreview();

            modal.classList.add('opacity-0');
            modal.classList.remove('opacity-100');

            if (dialog) {
                dialog.classList.add('opacity-0', 'scale-95', 'translate-y-3');
                dialog.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            }

            document.body.classList.remove('overflow-hidden');

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }

            closeTimeoutId = window.setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                closeTimeoutId = null;
            }, 300);

        }

        // Opened when account-type-modal (or anything else) dispatches this event.
        document.addEventListener('shophop:open-registration-modal', function (event) {
            if (event.detail && event.detail.type === 'seller') {
                openSellerRegistrationModal();
            }
        });

        document.addEventListener('click', function (event) {

            const filePreviewCloseTrigger = event.target.closest('[data-seller-file-preview-close]');

            if (filePreviewCloseTrigger && filePreviewModal && filePreviewModal.contains(filePreviewCloseTrigger)) {
                event.preventDefault();
                closeSellerFilePreview();
                return;
            }

            const backTrigger = event.target.closest('[data-seller-registration-modal-back]');

            if (backTrigger && modal.contains(backTrigger)) {
                event.preventDefault();
                closeSellerRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-account-type-modal'));
                return;
            }

            const signInTrigger = event.target.closest('[data-seller-registration-modal-signin]');

            if (signInTrigger && modal.contains(signInTrigger)) {
                event.preventDefault();
                closeSellerRegistrationModal();
                document.dispatchEvent(new CustomEvent('shophop:open-login-modal'));
                return;
            }

            const closeTrigger = event.target.closest('[data-seller-registration-modal-close]');

            if (closeTrigger && modal.contains(closeTrigger)) {
                event.preventDefault();
                closeSellerRegistrationModal();
            }

        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            if (filePreviewModal && filePreviewModal.getAttribute('aria-hidden') === 'false') {
                closeSellerFilePreview();
                return;
            }

            if (modal.getAttribute('aria-hidden') === 'false') {
                closeSellerRegistrationModal();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | FIELD REFERENCES
        |--------------------------------------------------------------------------
        */

        const birthdayInput = document.getElementById('seller_birthday');
        const ageInput = document.getElementById('seller_age');

        const provinceSelect = document.getElementById('seller_province');
        const municipalitySelect = document.getElementById('seller_municipality');
        const barangaySelect = document.getElementById('seller_barangay');

        const provinceNameInput = document.getElementById('seller_province_name');
        const municipalityNameInput = document.getElementById('seller_municipality_name');
        const barangayNameInput = document.getElementById('seller_barangay_name');

        const addressStatus = document.getElementById('seller-address-status');

        const validIdInput = document.getElementById('seller_valid_id');
        const fileName = document.getElementById('seller-file-name');
        const validIdError = document.getElementById('seller_valid_id_error');

        const businessPermitInput = document.getElementById('seller_business_permit');
        const businessPermitFileName = document.getElementById('seller-business-permit-file-name');
        const businessPermitError = document.getElementById('seller_business_permit_error');

        const registerForm = document.getElementById('seller-register-form');


        /*
        |--------------------------------------------------------------------------
        | AGE
        |--------------------------------------------------------------------------
        */

        function calculateAge() {

            if (!birthdayInput.value) {
                ageInput.value = '';
                return;
            }

            const birthday = new Date(birthdayInput.value + 'T00:00:00');
            const today = new Date();

            let age = today.getFullYear() - birthday.getFullYear();
            const monthDifference = today.getMonth() - birthday.getMonth();

            if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthday.getDate())) {
                age--;
            }

            ageInput.value = age >= 0 ? age : '';

        }

        birthdayInput.addEventListener('change', calculateAge);
        calculateAge();


        /*
        |--------------------------------------------------------------------------
        | FILE PICKERS (shared validator for valid ID + business permit)
        |--------------------------------------------------------------------------
        */

        function setupFilePicker(inputEl, nameEl, errorEl) {

            inputEl.addEventListener('change', function () {

                if (this.files.length > 0) {

                    const file = this.files[0];

                    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    const maxSizeBytes = 5 * 1024 * 1024;

                    if (!allowedTypes.includes(file.type)) {
                        showError(inputEl, errorEl, 'Only JPG, JPEG, PNG or PDF files are allowed.');
                        nameEl.textContent = '';
                        nameEl.classList.add('hidden');
                        this.value = '';
                        return;
                    }

                    if (file.size > maxSizeBytes) {
                        showError(inputEl, errorEl, 'File is too large. Maximum size is 5MB.');
                        nameEl.textContent = '';
                        nameEl.classList.add('hidden');
                        this.value = '';
                        return;
                    }

                    showError(inputEl, errorEl, '');
                    nameEl.textContent = file.name;
                    nameEl.classList.remove('hidden');

                } else {
                    nameEl.textContent = '';
                    nameEl.classList.add('hidden');
                }

            });

        }

        setupFilePicker(validIdInput, fileName, validIdError);
        setupFilePicker(businessPermitInput, businessPermitFileName, businessPermitError);

        /*
        |--------------------------------------------------------------------------
        | LIVE IMAGE / PDF PREVIEW FOR UPLOADED FILES
        |--------------------------------------------------------------------------
        */

        function closeSellerFilePreview() {
            if (!filePreviewModal) return;

            filePreviewModal.classList.add('hidden');
            filePreviewModal.classList.remove('flex');
            filePreviewModal.setAttribute('aria-hidden', 'true');

            if (filePreviewFullImage) {
                filePreviewFullImage.classList.add('hidden');
                filePreviewFullImage.removeAttribute('src');
            }

            if (filePreviewPdfFrame) {
                filePreviewPdfFrame.classList.add('hidden');
                filePreviewPdfFrame.removeAttribute('src');
            }

            activeFilePreviewKey = null;

            if (modal.getAttribute('aria-hidden') === 'false') {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        }

        function openSellerFilePreview(key, fallbackName) {
            if (!filePreviewModal || !filePreviewUrls[key]) return;

            const entry = filePreviewUrls[key];
            activeFilePreviewKey = key;

            if (filePreviewName) {
                filePreviewName.textContent = entry.name || fallbackName || 'Uploaded file';
            }

            if (entry.type === 'image' && filePreviewFullImage) {
                filePreviewFullImage.src = entry.url;
                filePreviewFullImage.classList.remove('hidden');

                if (filePreviewPdfFrame) {
                    filePreviewPdfFrame.classList.add('hidden');
                    filePreviewPdfFrame.removeAttribute('src');
                }
            }

            if (entry.type === 'pdf' && filePreviewPdfFrame) {
                filePreviewPdfFrame.src = entry.url;
                filePreviewPdfFrame.classList.remove('hidden');

                if (filePreviewFullImage) {
                    filePreviewFullImage.classList.add('hidden');
                    filePreviewFullImage.removeAttribute('src');
                }
            }

            filePreviewModal.classList.remove('hidden');
            filePreviewModal.classList.add('flex');
            filePreviewModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        }

        function setupSellerFilePreview(config) {
            const input = document.getElementById(config.inputId);
            const nameEl = document.getElementById(config.nameId);
            const card = document.getElementById(config.cardId);
            const image = document.getElementById(config.imageId);
            const pdf = document.getElementById(config.pdfId);
            const status = document.getElementById(config.statusId);
            const viewButton = document.getElementById(config.viewId);
            const changeButton = document.getElementById(config.changeId);

            if (!input || !card) return;

            function clearInlinePreview() {
                if (filePreviewUrls[config.key]) {
                    URL.revokeObjectURL(filePreviewUrls[config.key].url);
                    delete filePreviewUrls[config.key];
                }

                if (activeFilePreviewKey === config.key) closeSellerFilePreview();

                card.classList.add('hidden');

                if (image) {
                    image.classList.add('hidden');
                    image.removeAttribute('src');
                }

                if (pdf) {
                    pdf.classList.add('hidden');
                    pdf.classList.remove('flex');
                }

                if (status) status.textContent = 'Uploaded file';
            }

            function renderInlinePreview(file) {
                clearInlinePreview();

                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                const isImage = file.type.startsWith('image/');

                if (!isPdf && !isImage) return;

                const objectUrl = URL.createObjectURL(file);
                filePreviewUrls[config.key] = {
                    url: objectUrl,
                    type: isPdf ? 'pdf' : 'image',
                    name: file.name,
                };

                card.classList.remove('hidden');
                if (status) status.textContent = file.name;

                if (isImage && image) {
                    image.src = objectUrl;
                    image.classList.remove('hidden');

                    if (pdf) {
                        pdf.classList.add('hidden');
                        pdf.classList.remove('flex');
                    }
                }

                if (isPdf && pdf) {
                    pdf.classList.remove('hidden');
                    pdf.classList.add('flex');

                    if (image) {
                        image.classList.add('hidden');
                        image.removeAttribute('src');
                    }
                }
            }

            input.addEventListener('change', function () {
                if (!this.files || !this.files.length) {
                    clearInlinePreview();
                    return;
                }

                // Existing field validation still owns what file types/sizes are valid.
                // This listener only previews a file that remains selected afterward.
                window.setTimeout(function () {
                    if (!input.files || !input.files.length) {
                        clearInlinePreview();
                        return;
                    }
                    renderInlinePreview(input.files[0]);
                }, 0);
            });

            if (viewButton) {
                viewButton.addEventListener('click', function () {
                    openSellerFilePreview(config.key, nameEl ? nameEl.textContent : 'Uploaded file');
                });
            }

            if (changeButton) {
                changeButton.addEventListener('click', function () {
                    input.click();
                });
            }
        }

        setupSellerFilePreview({
            key: 'valid-id',
            inputId: 'seller_valid_id',
            nameId: 'seller-file-name',
            cardId: 'seller-valid-id-preview-card',
            imageId: 'seller-valid-id-preview-image',
            pdfId: 'seller-valid-id-preview-pdf',
            statusId: 'seller-valid-id-preview-status',
            viewId: 'seller-valid-id-preview-view',
            changeId: 'seller-valid-id-preview-change',
        });

        setupSellerFilePreview({
            key: 'business-permit',
            inputId: 'seller_business_permit',
            nameId: 'seller-business-permit-file-name',
            cardId: 'seller-business-permit-preview-card',
            imageId: 'seller-business-permit-preview-image',
            pdfId: 'seller-business-permit-preview-pdf',
            statusId: 'seller-business-permit-preview-status',
            viewId: 'seller-business-permit-preview-view',
            changeId: 'seller-business-permit-preview-change',
        });



        /*
        |--------------------------------------------------------------------------
        | REAL-TIME FIELD VALIDATION
        |--------------------------------------------------------------------------
        */

        const nameRegex = /^[A-Za-zÀ-ÿñÑ\s'.-]*$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const contactRegex = /^09\d{9}$/;

        const passwordUppercaseRegex = /[A-Z]/;
        const passwordLowercaseRegex = /[a-z]/;
        const passwordNumberRegex = /[0-9]/;
        const passwordSpecialRegex = /[!@#$%^&*]/;

        function showError(input, errorEl, message) {

            if (message) {

                errorEl.textContent = message;
                errorEl.classList.remove('hidden');

                input.classList.add('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                input.classList.remove('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');

            } else {

                errorEl.textContent = '';
                errorEl.classList.add('hidden');

                input.classList.remove('border-red-400', 'focus:border-red-400', 'focus:ring-red-100');
                input.classList.add('border-gray-border/70', 'focus:border-teal', 'focus:ring-teal/10');

            }

        }

        function validateNameField(input, errorEl, label) {

            const value = input.value;

            if (!value && input.required) {
                showError(input, errorEl, `${label} is required.`);
                return false;
            }

            if (value && !nameRegex.test(value)) {
                showError(input, errorEl, `${label} should not contain numbers or special characters.`);
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validateEmailField(input, errorEl) {

            const value = input.value.trim();

            if (!value && input.required) {
                showError(input, errorEl, 'Email is required.');
                return false;
            }

            if (value && !emailRegex.test(value)) {
                showError(input, errorEl, 'Please enter a valid email address (e.g. juan@example.com).');
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validateContactField(input, errorEl) {

            const value = input.value.trim();

            if (!value && input.required) {
                showError(input, errorEl, 'Contact number is required.');
                return false;
            }

            if (value && !contactRegex.test(value)) {
                showError(input, errorEl, 'Enter a valid 11-digit number starting with 09 (e.g. 09171234567).');
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validateRequiredField(input, errorEl, label) {

            const value = input.value.trim();

            if (!value) {
                showError(input, errorEl, `${label} is required.`);
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function updatePasswordRequirements(value) {

            const checks = {
                length: value.length >= 8,
                uppercase: passwordUppercaseRegex.test(value),
                lowercase: passwordLowercaseRegex.test(value),
                number: passwordNumberRegex.test(value),
                special: passwordSpecialRegex.test(value),
            };

            Object.keys(checks).forEach(function (key) {

                const item = modal.querySelector(`[data-req="${key}"]`);

                if (!item) return;

                const dot = item.querySelector('.req-dot');
                const check = item.querySelector('.req-check');
                const satisfied = checks[key];

                item.classList.toggle('req-satisfied', satisfied);
                item.classList.toggle('text-teal-dark', satisfied);
                item.classList.toggle('text-navy/40', !satisfied && key !== 'special');
                item.classList.toggle('text-navy/30', !satisfied && key === 'special');

                dot.classList.toggle('bg-teal', satisfied);
                dot.classList.toggle('border-teal', satisfied);
                dot.classList.toggle('border-gray-border', !satisfied);

                check.classList.toggle('hidden', !satisfied);

            });

        }

        function validatePasswordField(input, errorEl) {

            const value = input.value;

            if (!value && input.required) {
                showError(input, errorEl, 'Password is required.');
                return false;
            }

            if (value && value.length < 8) {
                showError(input, errorEl, 'Password must be at least 8 characters long.');
                return false;
            }

            if (value && !passwordUppercaseRegex.test(value)) {
                showError(input, errorEl, 'Password must contain at least 1 uppercase letter (A–Z).');
                return false;
            }

            if (value && !passwordLowercaseRegex.test(value)) {
                showError(input, errorEl, 'Password must contain at least 1 lowercase letter (a–z).');
                return false;
            }

            if (value && !passwordNumberRegex.test(value)) {
                showError(input, errorEl, 'Password must contain at least 1 number (0–9).');
                return false;
            }

            showError(input, errorEl, '');
            return true;

        }

        function validatePasswordConfirmation(passwordInput, confirmInput, errorEl) {

            const value = confirmInput.value;

            if (!value && confirmInput.required) {
                showError(confirmInput, errorEl, 'Please confirm your password.');
                return false;
            }

            if (value && value !== passwordInput.value) {
                showError(confirmInput, errorEl, 'Passwords do not match.');
                return false;
            }

            showError(confirmInput, errorEl, '');
            return true;

        }


        const firstNameInput = document.getElementById('seller_first_name');
        const firstNameError = document.getElementById('seller_first_name_error');

        firstNameInput.addEventListener('input', function () {
            validateNameField(firstNameInput, firstNameError, 'First name');
        });


        const lastNameInput = document.getElementById('seller_last_name');
        const lastNameError = document.getElementById('seller_last_name_error');

        lastNameInput.addEventListener('input', function () {
            validateNameField(lastNameInput, lastNameError, 'Last name');
        });


        const middleInitialInput = document.getElementById('seller_middle_initial');
        const middleInitialError = document.getElementById('seller_middle_initial_error');

        middleInitialInput.addEventListener('input', function () {
            validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
        });


        const emailInput = document.getElementById('seller_email');
        const emailError = document.getElementById('seller_email_error');

        emailInput.addEventListener('input', function () {
            validateEmailField(emailInput, emailError);
        });

        emailInput.addEventListener('blur', function () {
            validateEmailField(emailInput, emailError);
        });


        const contactInput = document.getElementById('seller_contact_no');
        const contactError = document.getElementById('seller_contact_no_error');

        contactInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            validateContactField(contactInput, contactError);
        });


        const sexInput = document.getElementById('seller_sex');
        const sexError = document.getElementById('seller_sex_error');

        sexInput.addEventListener('change', function () {
            validateRequiredField(sexInput, sexError, 'Sex');
        });


        const birthdayError = document.getElementById('seller_birthday_error');

        birthdayInput.addEventListener('change', function () {
            validateRequiredField(birthdayInput, birthdayError, 'Birthday');
        });


        const provinceError = document.getElementById('seller_province_error');
        const municipalityError = document.getElementById('seller_municipality_error');
        const barangayError = document.getElementById('seller_barangay_error');

        provinceSelect.addEventListener('change', function () {
            validateRequiredField(provinceSelect, provinceError, 'Province');
        });

        municipalitySelect.addEventListener('change', function () {
            validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
        });

        barangaySelect.addEventListener('change', function () {
            validateRequiredField(barangaySelect, barangayError, 'Barangay');
        });


        const streetAddressInput = document.getElementById('seller_street_address');
        const streetAddressError = document.getElementById('seller_street_address_error');

        streetAddressInput.addEventListener('input', function () {
            validateRequiredField(streetAddressInput, streetAddressError, 'Street address');
        });


        const businessNameInput = document.getElementById('seller_business_name');
        const businessNameError = document.getElementById('seller_business_name_error');

        businessNameInput.addEventListener('input', function () {
            validateRequiredField(businessNameInput, businessNameError, 'Business name');
        });


        const businessCategorySelect = document.getElementById('seller_business_category');
        const businessCategoryError = document.getElementById('seller_business_category_error');

        businessCategorySelect.addEventListener('change', function () {
            validateRequiredField(businessCategorySelect, businessCategoryError, 'Line of business');
        });


        const passwordInput = document.getElementById('seller_password');
        const passwordError = document.getElementById('seller_password_error');

        const passwordConfirmInput = document.getElementById('seller_password_confirmation');
        const passwordConfirmError = document.getElementById('seller_password_confirmation_error');

        passwordInput.addEventListener('input', function () {

            updatePasswordRequirements(passwordInput.value);
            validatePasswordField(passwordInput, passwordError);

            if (passwordConfirmInput.value) {
                validatePasswordConfirmation(passwordInput, passwordConfirmInput, passwordConfirmError);
            }

        });

        passwordConfirmInput.addEventListener('input', function () {
            validatePasswordConfirmation(passwordInput, passwordConfirmInput, passwordConfirmError);
        });


        /*
        |--------------------------------------------------------------------------
        | SHOW / HIDE PASSWORD
        |--------------------------------------------------------------------------
        */

        function setupPasswordToggle(inputEl, buttonEl) {

            const showIcon = buttonEl.querySelector('.password-icon-show');
            const hideIcon = buttonEl.querySelector('.password-icon-hide');

            buttonEl.addEventListener('click', function () {

                const isHidden = inputEl.type === 'password';

                inputEl.type = isHidden ? 'text' : 'password';

                showIcon.style.display = isHidden ? 'none' : '';
                hideIcon.style.display = isHidden ? '' : 'none';

                buttonEl.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                buttonEl.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

                inputEl.focus();

                const caretPosition = inputEl.value.length;
                inputEl.setSelectionRange(caretPosition, caretPosition);

            });

        }

        setupPasswordToggle(passwordInput, document.getElementById('seller_toggle_password'));
        setupPasswordToggle(passwordConfirmInput, document.getElementById('seller_toggle_password_confirmation'));


        /*
        |--------------------------------------------------------------------------
        | STEP-BY-STEP FLOW (5 steps) — plain show/hide, same as buyer modal
        |--------------------------------------------------------------------------
        */

        function getPanel(step) {
            return modal.querySelector(`[data-step-panel="${step}"]`);
        }

        function updateProgressBar(activeStep) {

            modal.querySelectorAll('[data-step-circle]').forEach(function (circle) {

                const s = parseInt(circle.dataset.stepCircle, 10);
                const numberEl = circle.querySelector('.step-number');
                const checkEl = circle.querySelector('.step-check');

                circle.classList.remove(
                    'bg-teal', 'border-teal', 'text-white',
                    'bg-white', 'border-gray-border', 'text-navy/30',
                    'step-circle-active'
                );

                if (s < activeStep) {

                    circle.classList.add('bg-teal', 'border-teal', 'text-white');
                    numberEl.classList.add('hidden');
                    checkEl.classList.remove('hidden');

                } else if (s === activeStep) {

                    circle.classList.add('bg-teal', 'border-teal', 'text-white', 'step-circle-active');
                    numberEl.classList.remove('hidden');
                    checkEl.classList.add('hidden');

                } else {

                    circle.classList.add('bg-white', 'border-gray-border', 'text-navy/30');
                    numberEl.classList.remove('hidden');
                    checkEl.classList.add('hidden');

                }

            });

            modal.querySelectorAll('[data-step-label]').forEach(function (label) {

                const s = parseInt(label.dataset.stepLabel, 10);

                label.classList.toggle('text-navy', s <= activeStep);
                label.classList.toggle('font-semibold', s <= activeStep);
                label.classList.toggle('text-navy/30', s > activeStep);
                label.classList.toggle('font-medium', s > activeStep);

            });

            modal.querySelectorAll('[data-step-line]').forEach(function (line) {

                const s = parseInt(line.dataset.stepLine, 10);

                line.classList.toggle('bg-teal', s < activeStep);
                line.classList.toggle('bg-gray-border', s >= activeStep);

            });

        }

        function goToStep(step) {

            modal.querySelectorAll('[data-step-panel]').forEach(function (panel) {
                const s = parseInt(panel.dataset.stepPanel, 10);
                panel.classList.toggle('hidden', s !== step);
            });

            updateProgressBar(step);

            if (dialog) {
                dialog.scrollTo({ top: 0, behavior: 'smooth' });
            }

        }

        function validateAndReportPanel(panel) {

            const fields = panel.querySelectorAll('input[required], select[required], textarea[required]');

            for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return false;
                }
            }

            return true;

        }

        function validatePanelSilently(panel) {

            const fields = panel.querySelectorAll('input[required], select[required], textarea[required]');

            let allValid = true;

            fields.forEach(function (field) {
                if (!field.checkValidity()) {
                    allValid = false;
                }
            });

            return allValid;

        }

        function validateFileInput(inputEl, errorEl, label) {

            if (!inputEl.files || inputEl.files.length === 0) {
                showError(inputEl, errorEl, `Please upload your ${label}.`);
                return false;
            }

            showError(inputEl, errorEl, '');
            return true;

        }

        function validateTerms() {

            const termsInput = document.getElementById('seller_terms');
            const termsErrorEl = document.getElementById('seller_terms_error');

            if (!termsInput.checked) {
                showError(termsInput, termsErrorEl, 'You must agree to the Terms and Conditions.');
                return false;
            }

            showError(termsInput, termsErrorEl, '');
            return true;

        }

        function validateStep1() {

            const isFirstNameValid = validateNameField(firstNameInput, firstNameError, 'First name');
            const isLastNameValid = validateNameField(lastNameInput, lastNameError, 'Last name');
            const isMiddleInitialValid = validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
            const isEmailValid = validateEmailField(emailInput, emailError);
            const isContactValid = validateContactField(contactInput, contactError);
            const isSexValid = validateRequiredField(sexInput, sexError, 'Sex');
            const isBirthdayValid = validateRequiredField(birthdayInput, birthdayError, 'Birthday');

            if (!isFirstNameValid || !isLastNameValid || !isMiddleInitialValid ||
                !isEmailValid || !isContactValid || !isSexValid || !isBirthdayValid) {
                return false;
            }

            return validateAndReportPanel(getPanel(1));

        }

        function validateStep2() {

            const isProvinceValid = validateRequiredField(provinceSelect, provinceError, 'Province');
            const isMunicipalityValid = validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
            const isBarangayValid = validateRequiredField(barangaySelect, barangayError, 'Barangay');
            const isStreetAddressValid = validateRequiredField(streetAddressInput, streetAddressError, 'Street address');

            if (!isProvinceValid || !isMunicipalityValid || !isBarangayValid || !isStreetAddressValid) {
                return false;
            }

            return validateAndReportPanel(getPanel(2));

        }

        function validateStep3() {

            const isBusinessNameValid = validateRequiredField(businessNameInput, businessNameError, 'Business name');
            const isBusinessCategoryValid = validateRequiredField(businessCategorySelect, businessCategoryError, 'Line of business');

            if (!isBusinessNameValid || !isBusinessCategoryValid) {
                return false;
            }

            return validateAndReportPanel(getPanel(3));

        }

        function validateStep4() {

            const isValidIdValid = validateFileInput(validIdInput, validIdError, 'valid ID');
            const isBusinessPermitValid = validateFileInput(businessPermitInput, businessPermitError, 'business permit');

            return isValidIdValid && isBusinessPermitValid;

        }


        document.getElementById('seller-step1-next').addEventListener('click', function () {
            if (validateStep1()) goToStep(2);
        });

        document.getElementById('seller-step2-back').addEventListener('click', function () {
            goToStep(1);
        });

        document.getElementById('seller-step2-next').addEventListener('click', function () {
            if (validateStep2()) goToStep(3);
        });

        document.getElementById('seller-step3-back').addEventListener('click', function () {
            goToStep(2);
        });

        document.getElementById('seller-step3-next').addEventListener('click', function () {
            if (validateStep3()) goToStep(4);
        });

        document.getElementById('seller-step4-back').addEventListener('click', function () {
            goToStep(3);
        });

        document.getElementById('seller-step4-next').addEventListener('click', function () {
            if (validateStep4()) goToStep(5);
        });

        document.getElementById('seller-step5-back').addEventListener('click', function () {
            goToStep(4);
        });


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT GUARD
        |--------------------------------------------------------------------------
        */

        registerForm.addEventListener('submit', function (e) {

            const isFirstNameValid = validateNameField(firstNameInput, firstNameError, 'First name');
            const isLastNameValid = validateNameField(lastNameInput, lastNameError, 'Last name');
            const isMiddleInitialValid = validateNameField(middleInitialInput, middleInitialError, 'Middle initial');
            const isEmailValid = validateEmailField(emailInput, emailError);
            const isContactValid = validateContactField(contactInput, contactError);
            const isSexValid = validateRequiredField(sexInput, sexError, 'Sex');
            const isBirthdayValid = validateRequiredField(birthdayInput, birthdayError, 'Birthday');
            const isStep1RequiredValid = validatePanelSilently(getPanel(1));

            const step1Ok = isFirstNameValid && isLastNameValid && isMiddleInitialValid &&
                isEmailValid && isContactValid && isSexValid && isBirthdayValid && isStep1RequiredValid;

            if (!step1Ok) {
                e.preventDefault();
                goToStep(1);
                return;
            }

            const isProvinceValid = validateRequiredField(provinceSelect, provinceError, 'Province');
            const isMunicipalityValid = validateRequiredField(municipalitySelect, municipalityError, 'Municipality / City');
            const isBarangayValid = validateRequiredField(barangaySelect, barangayError, 'Barangay');
            const isStreetAddressValid = validateRequiredField(streetAddressInput, streetAddressError, 'Street address');
            const isStep2RequiredValid = validatePanelSilently(getPanel(2));

            const step2Ok = isProvinceValid && isMunicipalityValid && isBarangayValid &&
                isStreetAddressValid && isStep2RequiredValid;

            if (!step2Ok) {
                e.preventDefault();
                goToStep(2);
                return;
            }

            const isBusinessNameValid = validateRequiredField(businessNameInput, businessNameError, 'Business name');
            const isBusinessCategoryValid = validateRequiredField(businessCategorySelect, businessCategoryError, 'Line of business');
            const isStep3RequiredValid = validatePanelSilently(getPanel(3));

            const step3Ok = isBusinessNameValid && isBusinessCategoryValid && isStep3RequiredValid;

            if (!step3Ok) {
                e.preventDefault();
                goToStep(3);
                return;
            }

            const step4Ok = validateStep4();

            if (!step4Ok) {
                e.preventDefault();
                goToStep(4);
                return;
            }

            const isPasswordValid = validatePasswordField(passwordInput, passwordError);
            const isPasswordConfirmValid = validatePasswordConfirmation(passwordInput, passwordConfirmInput, passwordConfirmError);
            const isTermsValid = validateTerms();

            if (!isPasswordValid || !isPasswordConfirmValid || !isTermsValid) {
                e.preventDefault();
                goToStep(5);
                return;
            }

        });


        /*
        |--------------------------------------------------------------------------
        | PSGC ADDRESS
        |--------------------------------------------------------------------------
        */

        const PSGC_BASE = 'https://psgc.gitlab.io/api';

        const oldProvinceCode = @json(old('province_code'));
        const oldMunicipalityCode = @json(old('municipality_code'));
        const oldBarangayCode = @json(old('barangay_code'));

        function setAddressStatus(message, isError = false) {

            if (!message) {
                addressStatus.classList.add('hidden');
                return;
            }

            addressStatus.textContent = message;
            addressStatus.classList.remove('hidden');
            addressStatus.classList.toggle('text-red-500', isError);
            addressStatus.classList.toggle('text-teal-dark', !isError);

        }

        function resetSelect(select, text) {
            select.innerHTML = `<option value="">${text}</option>`;
            select.disabled = true;
        }

        function fillSelect(select, items, placeholder, selectedCode = null) {

            select.innerHTML = `<option value="">${placeholder}</option>`;

            items.forEach(function (item) {

                const option = document.createElement('option');
                option.value = item.code;
                option.textContent = item.name;

                if (selectedCode && item.code === selectedCode) {
                    option.selected = true;
                }

                select.appendChild(option);

            });

            select.disabled = false;

        }

        async function fetchJson(url) {

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Unable to load address data.');
            }

            return response.json();

        }

        async function loadProvinces() {

            try {

                setAddressStatus('Loading provinces...');

                const provinces = await fetchJson(`${PSGC_BASE}/provinces/`);

                provinces.sort((a, b) => a.name.localeCompare(b.name));

                fillSelect(provinceSelect, provinces, 'Select province', oldProvinceCode);

                if (oldProvinceCode) {

                    provinceNameInput.value = provinceSelect.options[provinceSelect.selectedIndex]?.text || '';

                    await loadMunicipalities(oldProvinceCode, oldMunicipalityCode);

                }

                setAddressStatus('');

            } catch (error) {
                setAddressStatus('Unable to load address dropdowns. Please refresh the page.', true);
            }

        }

        async function loadMunicipalities(provinceCode, selectedCode = null) {

            resetSelect(municipalitySelect, 'Loading municipality / city...');
            resetSelect(barangaySelect, 'Select barangay');

            municipalityNameInput.value = '';
            barangayNameInput.value = '';

            if (!provinceCode) {
                resetSelect(municipalitySelect, 'Select municipality / city');
                return;
            }

            try {

                const cities = await fetchJson(`${PSGC_BASE}/provinces/${provinceCode}/cities-municipalities/`);

                cities.sort((a, b) => a.name.localeCompare(b.name));

                fillSelect(municipalitySelect, cities, 'Select municipality / city', selectedCode);

                if (selectedCode) {

                    municipalityNameInput.value = municipalitySelect.options[municipalitySelect.selectedIndex]?.text || '';

                    await loadBarangays(selectedCode, oldBarangayCode);

                }

            } catch (error) {
                resetSelect(municipalitySelect, 'Unable to load municipalities');
            }

        }

        async function loadBarangays(municipalityCode, selectedCode = null) {

            resetSelect(barangaySelect, 'Loading barangays...');

            barangayNameInput.value = '';

            if (!municipalityCode) {
                resetSelect(barangaySelect, 'Select barangay');
                return;
            }

            try {

                const barangays = await fetchJson(`${PSGC_BASE}/cities-municipalities/${municipalityCode}/barangays/`);

                barangays.sort((a, b) => a.name.localeCompare(b.name));

                fillSelect(barangaySelect, barangays, 'Select barangay', selectedCode);

                if (selectedCode) {
                    barangayNameInput.value = barangaySelect.options[barangaySelect.selectedIndex]?.text || '';
                }

            } catch (error) {
                resetSelect(barangaySelect, 'Unable to load barangays');
            }

        }

        provinceSelect.addEventListener('change', function () {

            provinceNameInput.value = this.options[this.selectedIndex]?.text || '';

            loadMunicipalities(this.value);

        });

        municipalitySelect.addEventListener('change', function () {

            municipalityNameInput.value = this.options[this.selectedIndex]?.text || '';

            loadBarangays(this.value);

        });

        barangaySelect.addEventListener('change', function () {
            barangayNameInput.value = this.options[this.selectedIndex]?.text || '';
        });


        // Initial visual state — safe even while the modal is still hidden.
        updateProgressBar(1);

    })();
</script>
@endpush