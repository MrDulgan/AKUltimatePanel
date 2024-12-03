<!-- page/register.php -->

<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if the user is already logged in
if (isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-info">You are already logged in.</div>';
    exit();
}

// Include CSRF token generation
require_once '../inc/csrf.php';
$csrf_token = generate_csrf_token();
?>

<div class="modal-dialog modal-dialog-centered">
  <div class="modal-content ak-main-card">
    <div class="modal-header justify-content-center">
      <h5 class="modal-title ak-card-title" id="registrationModalLabel">Registration</h5>
    </div>
    <div class="modal-body text-center">
      <form id="registrationForm">
        <div id="messageContainer" class="mt-3"></div>
        <div class="ak-input-group">
          <input type="text" name="username" class="form-control ak-input" placeholder="USERNAME" required>
        </div>
        <div class="ak-input-group">
          <input type="email" name="email" class="form-control ak-input" placeholder="E-MAIL" required>
        </div>
        <div class="ak-input-group">
          <input type="email" name="confirm_email" class="form-control ak-input" placeholder="CONFIRM E-MAIL" required>
        </div>
        <div class="ak-input-group position-relative">
          <input type="password" name="password" class="form-control ak-input" id="register_password" placeholder="PASSWORD" required>
          <span class="password-generator" id="generatePassword" data-bs-toggle="tooltip" title="Generate Password">🔒</span>
        </div>
        <div class="password-strength-bar" id="passwordStrengthBar"></div>
        <div class="ak-input-group">
          <input type="password" name="confirm_password" class="form-control ak-input" placeholder="CONFIRM PASSWORD" required>
        </div>
        <div class="ak-input-group">
          <img src="inc/captcha.php?action=image&rand=<?php echo rand(); ?>" alt="CAPTCHA" class="captcha-img">
          <input type="text" name="captcha" class="form-control ak-input" placeholder="Enter CAPTCHA" required>
        </div>
        <input type="text" name="website" style="display:none;">
        <button type="submit" class="ak-button mx-auto">REGISTER</button>
        <div class="form-check mt-3">
          <input type="checkbox" class="form-check-input" id="gameRules" required>
          <label class="form-check-label" for="gameRules">I have read and agree to the <a href="#">game rules</a>.</label>
        </div>
      </form>
    </div>
  </div>
</div>