<?php wp_enqueue_style('header-css'); ?>

<style>
    .menu-register,
    .menu-dashboard,
    .menu-logout,
    .menu-login {
        display: none;
    }
</style>

<header>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'main-menu',
        'container' => 'nav',
        'container_class' => 'main-nav',
        'menu_class' => 'primary-header',
    ));
    ?>



</header>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const token = localStorage.getItem('vendor_token'); // Your token key

        const registerLink = document.querySelector('.menu-register');
        const dashboardLink = document.querySelector('.menu-dashboard');
        const logoutItem = document.querySelector('.menu-logout');
        const loginItem = document.querySelector('.menu-login');


        if (token) {
            if (dashboardLink) dashboardLink.style.display = 'inline-block';
            if (logoutItem) {
                logoutItem.style.display = 'inline-block';

                logoutItem.addEventListener('click', function (e) {
                    e.preventDefault();
                    localStorage.removeItem('vendor_token'); // Clear token

                    // Redirect to site home URL dynamically
                    window.location.href = '<?php echo esc_url(home_url('/')); ?>';
                });
            }
            if (registerLink) registerLink.style.display = 'none';
            if (loginItem) loginItem.style.display = 'none';

        } else {
            if (registerLink) registerLink.style.display = 'inline-block';
            if (loginItem) loginItem.style.display = 'inline-block';

            if (dashboardLink) dashboardLink.style.display = 'none';
            if (logoutItem) logoutItem.style.display = 'none';
        }
    });
</script>