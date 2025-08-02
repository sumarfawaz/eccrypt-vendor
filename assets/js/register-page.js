document.addEventListener('DOMContentLoaded', function () {
    const steps = document.querySelectorAll('.form-step');
    const walletStep = document.querySelector('.payment-wallet');
    const bankStep = document.querySelector('.payment-bank');
    const form = document.getElementById('vendorKYCForm');
    let current = 0;
    const stepHistory = [];

    let selectedPaymentMethod = null;
    const formData = {};

    function showStep(index) {
        steps.forEach((step, i) => {
            const isActive = i === index;
            step.classList.toggle('active', isActive);
            step.style.display = isActive ? 'block' : 'none';
        });
    }

    function isVisible(el) {
        return el.offsetParent !== null;
    }

    function saveStepData(stepIndex) {
        const inputs = steps[stepIndex].querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (!isVisible(input)) return;

            if (input.type === 'radio') {
                if (input.checked) formData[input.name] = input.value;
            } else if (input.type === 'checkbox') {
                formData[input.name] = input.checked;
            } else if (input.type === 'file') {
                // For file input, save file object here if needed for FormData upload (optional)
                // Currently, this saves only file name, but to upload files properly, see note below
                formData[input.name] = input.files.length > 0 ? input.files[0].name : '';
            } else {
                formData[input.name] = input.value;
            }
        });
    }

    function validateStep(stepIndex) {
        const inputs = steps[stepIndex].querySelectorAll('input, select, textarea');
        let valid = true;

        inputs.forEach(input => {
            const isRequired = input.hasAttribute('required');
            const isVisibleField = isVisible(input);
            const fieldName = input.name || input.id || 'field';
            const errorId = `error-${fieldName}`;

            // Clear old error
            let oldError = document.getElementById(errorId);
            if (oldError) oldError.remove();

            if (isRequired && isVisibleField) {
                const isEmpty = input.type === 'file'
                    ? input.files.length === 0
                    : !input.value.trim();

                if (isEmpty) {
                    valid = false;

                    const errorEl = document.createElement('div');
                    errorEl.id = errorId;
                    errorEl.className = 'error-message';
                    errorEl.innerText = `${input.placeholder || 'This field'} is required.`;

                    input.classList.add('input-error');
                    input.insertAdjacentElement('afterend', errorEl);
                } else {
                    input.classList.remove('input-error');
                }
            }
        });

        return valid;
    }

    // Next buttons for steps 1 to 4
    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!validateStep(current)) return;

            const currentStep = steps[current];
            saveStepData(current);
            stepHistory.push(current);

            // Handle payment method selection on step 2
            if (currentStep.querySelector('input[name="payment_method"]')) {
                const method = currentStep.querySelector('input[name="payment_method"]:checked');
                if (!method) {
                    alert("Please select a payment method.");
                    stepHistory.pop();
                    return;
                }

                selectedPaymentMethod = method.value;

                // Toggle required attributes
                document.querySelectorAll('[data-bank]').forEach(input => {
                    input.required = selectedPaymentMethod === 'bank';
                });
                document.querySelectorAll('[data-wallet]').forEach(input => {
                    input.required = selectedPaymentMethod === 'wallet';
                });

                current = Array.from(steps).indexOf(
                    selectedPaymentMethod === 'wallet' ? walletStep : bankStep
                );
                showStep(current);
                return;
            }

            // After wallet/bank step, go to Agreement step (4)
            if (steps[current] === walletStep || steps[current] === bankStep) {
                current = 4;
                showStep(current);
                return;
            }

            // Normal next step (for steps 1, 4)
            if (current < steps.length - 1) {
                current++;
                showStep(current);
            }
        });
    });

    // Previous buttons
    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', () => {
            if (stepHistory.length > 0) {
                current = stepHistory.pop();
                showStep(current);
            }
        });
    });

    // Show/hide tech stack messages
    const techStackSelect = document.getElementById('techStackSelect');
    if (techStackSelect) {
        techStackSelect.addEventListener('change', function () {
            document.getElementById('wordpressMessage').style.display =
                this.value === 'wordpress' ? 'block' : 'none';
            document.getElementById('customMessage').style.display =
                this.value === 'custom' ? 'block' : 'none';
        });
    }

    // Final form submit event
form.addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent page reload

    // Prevent double submit by lock flag
    if (form.dataset.isSubmitting === 'true') {
        return; // Already submitting, ignore extra submits
    }
    form.dataset.isSubmitting = 'true';

    const finalStepIndex = steps.length - 1; // usually last step (5)

    if (!validateStep(finalStepIndex)) {
        alert('Please fill all required fields before submitting.');
        form.dataset.isSubmitting = 'false'; // release lock if validation fails
        return;
    }

    // Save final step data
    saveStepData(finalStepIndex);

    // Send whole form including file(s)
    const submitData = new FormData(form);
    submitData.append('action', 'submit_kyc_form');
    submitData.append('security', kyc_ajax_object.nonce);
    submitData.append('current_step', steps[finalStepIndex].dataset.step);

    fetch(kyc_ajax_object.ajax_url, {
        method: 'POST',
        body: submitData,
    })
    .then(response => response.json())
    .then(result => {
        form.dataset.isSubmitting = 'false'; // release lock on completion

        if (result.success) {
            document.getElementById('kyc-message').innerHTML = `<div class="notice-success">✅ ${result.data.message}</div>`;
            form.reset();
            // Disable inputs/buttons to prevent resubmission (optional)
            form.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = true);
        } else {
            document.getElementById('kyc-message').innerHTML = `<div class="notice-error">❌ ${result.data.errors.join('<br>')}</div>`;
        }
    })
    .catch(err => {
        form.dataset.isSubmitting = 'false'; // release lock on error
        console.error('AJAX Error:', err);
        alert('Something went wrong.');
    });
});


    showStep(current);
});
