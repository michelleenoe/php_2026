<?php
$__signup_state = $_SESSION['open_dialog'] ?? null;
$__signup_active = ($__signup_state === 'signup') ? ' active' : '';

$old = $_SESSION['signup_old'] ?? [];
$err = $_SESSION['signup_error_field'] ?? '';

$nameErr  = str_contains($err, 'Full name');
$userErr  = str_contains($err, 'Username');
$emailErr = str_contains($err, 'Email');
$passErr  = str_contains($err, 'Password');
?>

<?php if ($__signup_state === 'signup_verified'): ?>
<div class="x-dialog active" id="signupVerifiedDialog" role="dialog" aria-modal="true">
    <div class="x-dialog__overlay"></div>
    <div class="x-dialog__content">
        <button class="x-dialog__close" aria-label="Close">&times;</button>

        <div class="x-dialog__header">
            <img src="/public/img/weave-logo.png" alt="Weave logo" class="x-dialog__logo">
        </div>

        <div class="x-dialog__body">
            <h2>Verify your email</h2>
            <p>We’ve sent a verification link to your email address.</p>
            <p>Please check your inbox and click the link to activate your account.</p>

            <form action="/api/resend-verification.php" method="POST" class="resend-verification-form">
                <input type="hidden" name="email"
                    value="<?= htmlspecialchars($_SESSION['last_signup_email'] ?? '') ?>">
                <button class="x-dialog__btn resend-verification-btn">Resend verification email</button>
            </form>
        </div>
    </div>
</div>
<?php unset($_SESSION['open_dialog']); return; ?>
<?php endif; ?>

<div 
    class="x-dialog<?= $__signup_active; ?>" 
    id="signupDialog" 
    role="dialog" 
    aria-modal="true"
    aria-labelledby="signupTitle"
    data-open-state="<?= ($__signup_state === 'signup') ? 'open' : 'closed'; ?>"
    mix-ignore
    mix-on="yes"
>
    <div class="x-dialog__overlay"></div>

    <div class="x-dialog__content">
        <button class="x-dialog__close" aria-label="Close">&times;</button>

        <div class="x-dialog__header">
            <img src="/public/img/weave-logo.png" alt="Weave logo" class="x-dialog__logo">
        </div>

        <h2 id="signupTitle">Create your account</h2>

        <form class="x-dialog__form" action="bridge-signup" method="POST" autocomplete="off">

            <label for="inp_fullname">Full name</label>
            <input 
                id="inp_fullname"
                name="user_full_name"
                type="text"
                placeholder="Enter your name"
                data-rule="<?= REGEX_FULLNAME ?>"
                data-error="Full name must be <?= userFullNameMin ?>-<?= userFullNameMax ?> characters"
                value="<?= htmlspecialchars($old['user_full_name'] ?? '') ?>"
                class="<?= $nameErr ? 'x-error' : '' ?>"
            >
            <label for="inp_username">Username</label>
            <input 
                id="inp_username"
                name="user_username"
                type="text"
                placeholder="Choose a username"
                data-rule="<?= REGEX_USERNAME ?>"
                data-error="Username must be <?= usernameMin ?>-<?= usernameMax ?> characters"
                value="<?= htmlspecialchars($old['user_username'] ?? '') ?>"
                class="<?= $userErr ? 'x-error' : '' ?>"
            >

            <label for="inp_email">Email</label>
            <input 
                id="inp_email"
                name="user_email"
                type="text"
                placeholder="Your email"
                data-rule="<?= REGEX_EMAIL ?>"
                data-error="Enter a valid email (must include @ and .)"
                value="<?= htmlspecialchars($old['user_email'] ?? '') ?>"
                class="<?= $emailErr ? 'x-error' : '' ?>"
            >

            <label for="inp_password">Password</label>
            <input 
                id="inp_password"
                name="user_password"
                type="password"
                placeholder="Create a password"
                data-rule="<?= REGEX_PASSWORD ?>"
                data-error="Password must be <?= passwordMin ?>-<?= passwordMax ?> characters"
                class="<?= $passErr ? 'x-error' : '' ?>"
            >

            <label for="2_inp_password">Confirm password</label>
            <input 
                id="2_inp_password"
                name="user_password_confirm"
                type="password"
                placeholder="Confirm password"
                data-rule="<?= REGEX_PASSWORD ?>"
                data-error="Password must be <?= passwordMin ?>-<?= passwordMax ?> characters"
                data-match="inp_password"          
                data-match-error="Passwords do not match"
                class="<?= $passErr ? 'x-error' : '' ?>"
            >

            <button type="submit" class="x-dialog__btn">Sign up</button>
        </form>

        <p class="x-dialog__alt">
            Already have an account? 
            <a href="#" data-open="loginDialog">Log in</a>
        </p>
    </div>
</div>

<?php
unset($_SESSION['signup_old'], $_SESSION['signup_error_field'], $_SESSION['open_dialog']);
?>
