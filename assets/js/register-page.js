document.addEventListener('DOMContentLoaded', function () {
    const steps = document.querySelectorAll('.form-step');
    const walletStep = document.querySelector('.payment-wallet');
    const bankStep = document.querySelector('.payment-bank');
    const form = document.getElementById('vendorKYCForm');
    let current = 0;
    const stepHistory = [];

    let selectedPaymentMethod = null;
    const formData = {};

    let agreementUploaded = false; // <-- Flag to avoid multiple alerts

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
                formData[input.name] = input.files.length > 0 ? input.files[0] : null;
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

    function uploadAgreementFile(file) {
        const uploadFormData = new FormData();
        uploadFormData.append('action', 'submit_kyc_form');
        uploadFormData.append('security', kyc_ajax_object.nonce);
        uploadFormData.append('current_step', '4');
        uploadFormData.append('agreement_file', file);

        return fetch(kyc_ajax_object.ajax_url, {
            method: 'POST',
            body: uploadFormData
        }).then(res => res.json());
    }

    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!validateStep(current)) return;

            const currentStep = steps[current];
            saveStepData(current);

            if (currentStep.querySelector('input[name="payment_method"]')) {
                const method = currentStep.querySelector('input[name="payment_method"]:checked');
                if (!method) {
                    alert("Please select a payment method.");
                    stepHistory.pop();
                    return;
                }

                selectedPaymentMethod = method.value;

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

            if (steps[current] === steps[4]) { // Agreement step
                const fileInput = currentStep.querySelector('input[type="file"][name="agreement_file"]');
                if (!fileInput || fileInput.files.length === 0) {
                    alert("Please upload the agreement file.");
                    return;
                }

                // If already uploaded, skip re-upload and alert
                if (!agreementUploaded) {
                    btn.disabled = true;
                    try {
                        const result = await uploadAgreementFile(fileInput.files[0]);
                        if (result.success) {
                            formData['agreement_file_url'] = result.data.agreement_file_url;

                            //alert('Agreement file uploaded successfully.');
                            agreementUploaded = true; // Mark uploaded

                            stepHistory.push(current);
                            current++;
                            showStep(current);
                        } else {
                            alert('File upload failed: ' + (result.data.errors ? result.data.errors.join(', ') : 'Unknown error'));
                        }
                    } catch (err) {
                        console.error(err);
                        alert('File upload error occurred.');
                    }
                    btn.disabled = false;
                } else {
                    // Already uploaded - just proceed without alert or re-upload
                    stepHistory.push(current);
                    current++;
                    showStep(current);
                }

                return;
            }


            if (steps[current] === walletStep || steps[current] === bankStep) {
                current = 4;
                showStep(current);
                return;
            }

            if (current < steps.length - 1) {
                stepHistory.push(current);
                current++;
                showStep(current);
            }
        });
    });

    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', () => {
            if (stepHistory.length > 0) {
                current = stepHistory.pop();
                showStep(current);
            }
        });
    });

    const techStackSelect = document.getElementById('techStackSelect');
    if (techStackSelect) {
        techStackSelect.addEventListener('change', function () {
            document.getElementById('wordpressMessage').style.display =
                this.value === 'wordpress' ? 'block' : 'none';
            document.getElementById('customMessage').style.display =
                this.value === 'custom' ? 'block' : 'none';
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (form.dataset.isSubmitting === 'true') {
            return;
        }
        form.dataset.isSubmitting = 'true';

        const finalStepIndex = steps.length - 1;

        if (!validateStep(finalStepIndex)) {
            alert('Please fill all required fields before submitting.');
            form.dataset.isSubmitting = 'false';
            return;
        }

        saveStepData(finalStepIndex);

        const submitData = new FormData();

        submitData.append('action', 'submit_kyc_form');
        submitData.append('security', kyc_ajax_object.nonce);
        submitData.append('current_step', steps[finalStepIndex].dataset.step);

        for (const key in formData) {
            if (formData[key] instanceof File) {
                submitData.append(key, formData[key]);
            } else {
                submitData.append(key, formData[key]);
            }
        }

        fetch(kyc_ajax_object.ajax_url, {
            method: 'POST',
            body: submitData,
        })
            .then(response => response.json())
            .then(result => {
                form.dataset.isSubmitting = 'false';

                if (result.success) {
                    document.getElementById('kyc-message').innerHTML = `<div class="notice-success">✅ ${result.data.message}</div>`;
                    form.reset();
                    form.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = true);
                } else {
                    document.getElementById('kyc-message').innerHTML = `<div class="notice-error">❌ ${result.data.errors.join('<br>')}</div>`;
                }
            })
            .catch(err => {
                form.dataset.isSubmitting = 'false';
                console.error('AJAX Error:', err);
                alert('Something went wrong.');
            });
    });

    showStep(current);
});
