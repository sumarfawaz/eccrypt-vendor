<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Basic field validation
    if (empty($_POST['registrant_name']))
        $errors[] = 'Registrant name is required.';
    if (empty($_POST['registrant_email']))
        $errors[] = 'Registrant email is required.';
    if (empty($_POST['payment_method']))
        $errors[] = 'Payment method is required.';
    if (empty($_POST['tech_stack'])) {
    $errors[] = 'Tech Stack is required.';
}


    // Agreement upload
    $agreement_path = '';
    if (isset($_FILES['agreement_file']) && $_FILES['agreement_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = wp_upload_dir();
        $file_name = basename($_FILES['agreement_file']['name']);
        $destination = $upload_dir['path'] . '/' . $file_name;

        if (move_uploaded_file($_FILES['agreement_file']['tmp_name'], $destination)) {
            $agreement_path = $upload_dir['url'] . '/' . $file_name;
        } else {
            $errors[] = 'Failed to upload agreement file.';
        }
    } else {
        $errors[] = 'Agreement file is required.';
    }

    // Save if no errors
    if (empty($errors)) {
        $post_id = wp_insert_post([
            'post_type'    => 'vendor_kyc',
            'post_status'  => 'publish',
            'post_title'   => sanitize_text_field($_POST['registrant_name']),
            'post_content' => '',
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            $fields = [
                'registrant_name', 'registrant_email', 'registrant_phone', 'registrant_phone_2',
                'business_nature', 'registrant_designation', 'business_email', 'business_phone',
                'business_address', 'business_name', 'business_domain', 'payment_method',
                'bank_account_number', 'account_holder_name', 'bank', 'bank_branch',
                'wallet_address', 'wallet_network', 'tech_stack'
            ];

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                }
            }

            if ($agreement_path) {
                update_post_meta($post_id, 'agreement_file', esc_url($agreement_path));
            }

            echo '<div class="notice-success">✅ Form submitted successfully and saved!</div>';
        } else {
            echo '<div class="notice-error">❌ Failed to save form data.</div>';
        }
    } else {
        echo '<div class="notice-error">❌ ' . implode('<br>', $errors) . '</div>';
    }
}
?>


<form id="vendorKYCForm" method="post" enctype="multipart/form-data">
    <!-- STEP 1 -->
    <div class="form-step active" data-step="1">
        <h2>Vendor KYC</h2>
        <input type="text" name="registrant_name" placeholder="Registrant Name" required>
        <input type="email" name="registrant_email" placeholder="Registrant Email" required>
        <input type="text" name="registrant_phone" placeholder="Phone" required>
        <input type="text" name="registrant_phone_2" placeholder="Phone 2">
        <input type="text" name="business_nature" placeholder="Business Nature" required>
        <input type="text" name="registrant_designation" placeholder="Designation" required>
        <input type="email" name="business_email" placeholder="Business Email" required>
        <input type="text" name="business_phone" placeholder="Business Phone" required>
        <textarea name="business_address" placeholder="Business Address" required></textarea>
        <input type="text" name="business_name" placeholder="Business Name" required>
        <input type="text" name="business_domain" placeholder="Business Domain" required>
        <button type="button" class="next-step">Next</button>
    </div>

    <!-- STEP 2: Payment Method -->
    <div class="form-step" data-step="2">
        <h2>Payment Method</h2>
        <label><input type="radio" name="payment_method" value="wallet"> Wallet Transfer (Recovery fee included)</label>
        <label><input type="radio" name="payment_method" value="bank"> Bank Transfer</label>
        <button type="button" class="prev-step">Back</button>
        <button type="button" class="next-step">Next</button>
    </div>

    <!-- STEP 3 (BANK) -->
    <div class="form-step payment-bank" style="display: none;" data-step="3-bank">
        <h2>Bank Details</h2>
        <input type="text" id="bank_account_number" name="bank_account_number" placeholder="Account Number" data-bank>
        <div class="error-message" id="error-bank_account_number"></div>

        <input type="text" id="account_holder_name" name="account_holder_name" placeholder="Account Holder's Name" data-bank>
        <div class="error-message" id="error-account_holder_name"></div>

        <input type="text" id="bank" name="bank" placeholder="Bank Name" data-bank>
        <div class="error-message" id="error-bank"></div>

        <input type="text" id="bank_branch" name="bank_branch" placeholder="Bank Branch" data-bank>
        <div class="error-message" id="error-bank_branch"></div>

        <button type="button" class="prev-step payment-back">Back</button>
        <button type="button" class="next-step">Next</button>
    </div>

    <!-- STEP 3 (WALLET) -->
    <div class="form-step payment-wallet" style="display: none;" data-step="3-wallet">
        <h2>Wallet Details</h2>

        <input type="text" id="wallet_address" name="wallet_address" placeholder="Wallet Address" data-wallet>
        <div class="error-message" id="error-wallet_address"></div>

        <input type="text" id="wallet_network" name="wallet_network" placeholder="Wallet Network" data-wallet>
        <div class="error-message" id="error-wallet_network"></div>

        <p class="info">⚠️ Must have a Polygon Wallet</p>

        <button type="button" class="prev-step payment-back">Back</button>
        <button type="button" class="next-step">Next</button>
    </div>

    <!-- STEP 4: Agreement -->
    <div class="form-step" data-step="4">
        <h2>Agreement</h2>
        <p><a href="/your-agreement-template.pdf" download>Download Agreement Template</a></p>
        <label>Upload Signed Agreement (PDF):
            <input type="file" name="agreement_file" accept=".pdf" required>
        </label>
        <p class="info">⚠️ All transactions will be released in USDT.</p>
        <button type="button" class="prev-step">Back</button>
        <button type="button" class="next-step">Next</button>
    </div>

    <!-- STEP 5: Terms and Tech Stack -->
    <div class="form-step" data-step="5">
        <h2>Terms and Tech Stack</h2>
        <label>Tech Stack:</label>
        <select name="tech_stack" id="techStackSelect" required>
            <option value="">Select</option>
            <option value="wordpress">WordPress & WooCommerce</option>
            <option value="custom">Custom Platform</option>
        </select>

        <div id="wordpressMessage" class="conditional-message">
            <p>✅ Please expose your WooCommerce API keys for integration.</p>
        </div>
        <div id="customMessage" class="conditional-message">
            <p>✅ We will provide you with our API documentation.</p>
        </div>

        <button type="button" class="prev-step">Back</button>
        <button type="submit">Submit</button>
    </div>
</form>

<div id="kyc-message"></div>


