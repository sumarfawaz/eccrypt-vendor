<?php
/*
    Template Name: Login
*/

get_header();

?>


<form id="vendorLoginForm">
    <label>Email:</label><br>
    <input type="email" id="email" name="email" required /><br><br>

    <label>Password:</label><br>
    <input type="password" id="password" name="password" required /><br><br>

    <button type="submit">Login</button>
</form>

<div id="loginMessage"></div>

<?php
get_footer();

?>



<script>
document.getElementById('vendorLoginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    const loginMessage = document.getElementById('loginMessage');
    loginMessage.textContent = '';

    try {
        const response = await fetch('http://192.168.8.189:8000/api/vendor/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
        });

        if (!response.ok) {
            throw new Error('Login failed: ' + response.status);
        }

        const data = await response.json();

        if (data.token) {
            localStorage.setItem('vendor_token', data.token);

            loginMessage.style.color = 'green';
            loginMessage.textContent = 'Login successful! Redirecting...';

            // Redirect to site base URL + /dashboard
            window.location.href = '<?php echo esc_url(home_url('/dashboard')); ?>';
        } else {
            throw new Error('Token not received');
        }

    } catch (error) {
        loginMessage.style.color = 'red';
        loginMessage.textContent = 'Error: ' + error.message;
    }
});
</script>
