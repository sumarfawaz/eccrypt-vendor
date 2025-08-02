<?php
if (!current_user_can('administrator')) {
    // Option 1: Redirect non-admins to home page or login
    wp_redirect(home_url());
    exit;

    // Option 2: Or show a "no access" message instead of redirecting:
    /*
    wp_die('You do not have permission to view this page.', 'Access Denied', ['response' => 403]);
    */
}
?>


<?php get_header(); ?>

<div class="vendor-kyc-details">

    <h1><?php the_title(); ?></h1>

    <p><strong>Description:</strong> <?php the_content(); ?></p>

    <?php
    $post_id = get_the_ID();

    $fields = [
        'registrant_name' => 'Registrant Name',
        'registrant_email' => 'Registrant Email',
        'registrant_phone' => 'Phone Number',
        'registrant_phone_2' => 'Alternate Phone',
        'registrant_designation' => 'Designation',
        'business_name' => 'Business Name',
        'business_domain' => 'Business Domain',
        'business_nature' => 'Nature of Business',
        'business_email' => 'Business Email',
        'business_phone' => 'Business Phone',
        'business_address' => 'Business Address',
        'payment_method' => 'Payment Method',

        // Bank details
        'bank_account_number' => 'Bank Account Number',
        'account_holder_name' => 'Account Holder Name',
        'bank' => 'Bank Name',
        'bank_branch' => 'Bank Branch',

        // Wallet details
        'wallet_address' => 'Wallet Address',
        'wallet_network' => 'Wallet Network',

        // Tech stack
        'tech_stack' => 'Preferred Tech Stack',

        // File upload
        'agreement_file_url' => 'Signed Agreement File',
    ];

    echo '<table border="1" cellpadding="8" cellspacing="0">';
    echo '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';

    foreach ($fields as $meta_key => $label) {
        $value = get_post_meta($post_id, $meta_key, true);
        if (!$value)
            continue;

        // Display file links if it's a URL
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value_display = '<a href="' . esc_url($value) . '" target="_blank">View File</a>';
        } else {
            $value_display = esc_html($value);
        }

        echo "<tr><td>{$label}</td><td>{$value_display}</td></tr>";
    }

    echo '</tbody></table>';
    ?>


</div>

<?php get_footer(); ?>