<?php
/*
    Template Name: Register
*/
get_header();

wp_enqueue_style('registration-page-css'); ?>

<div id="vendor-registration-form">
  <h2>Register as Vendor</h2>
  <form id="vendorForm">
    <label>Name</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Phone</label><br>
    <input type="text" name="phone" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password</label><br>
    <input type="password" name="password_confirmation" required><br><br>

    <button type="submit">Register</button>
  </form>

  <div id="responseMessage"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("vendorForm");
  const responseMessage = document.getElementById("responseMessage");

  if (!form) return;

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const data = {
      name: form.name.value,
      email: form.email.value,
      phone: form.phone.value,
      password: form.password.value,
      password_confirmation: form.password_confirmation.value
    };

    try {
      const response = await fetch("http://192.168.8.189:8000/api/vendor/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify(data)
      });

      const resultText = await response.text();
      let result;

      try {
        result = JSON.parse(resultText);
      } catch {
        throw new Error("Invalid JSON: " + resultText);
      }

      if (response.ok) {
        localStorage.setItem("vendor_token", result.token);
        responseMessage.innerHTML =
          "<p style='color:green;'>✅ Registration successful. Token stored!</p>";
      } else {
        let msg = result.message || "❌ Registration failed.";
        if (result.errors) {
          msg += "<br>" + Object.values(result.errors).map(e => e.join("<br>")).join("<br>");
        }
        responseMessage.innerHTML = "<p style='color:red;'>" + msg + "</p>";
      }
    } catch (err) {
      console.error("Error:", err);
      responseMessage.innerHTML =
        "<p style='color:red;'>🚨 " + err.message + "</p>";
    }
  });
});
</script>

<?php get_footer(); ?>
