<?php
$state = $_SESSION['open_dialog'] ?? null;
$active = ($state === 'login') ? ' active' : '';

$old = $_SESSION['login_old'] ?? [];
$err = $_SESSION['login_error'] ?? '';

$lowerErr = strtolower($err);
$emailErr = ($err === 'auth' || str_contains($err, 'Email'));
$passErr  = ($err === 'auth' || str_contains($err, 'Password'));
?>

<div 
    class="x-dialog<?php echo $active; ?>" 
    id="loginDialog"
    role="dialog"
    aria-modal="true"
    aria-labelledby="loginTitle"
    data-open-state="<?php echo ($state === 'login') ? 'open' : 'closed'; ?>"
    mix-ignore
    mix-on="yes"
>
    <div class="x-dialog__overlay"></div>

    <div class="x-dialog__content">
        <button class="x-dialog__close" aria-label="Close">&times;</button>

        <div class="x-dialog__header">
            <img src="/public/img/weave-logo.png" alt="Weave logo" class="x-dialog__logo">
        </div>

        <h2 id="loginTitle">Log in to Weave</h2>

        <form class="x-dialog__form" action="bridge-login" method="POST" autocomplete="off">

            <label for="login_email">Email</label>
            <input
                id="login_email"
                name="user_email"
                type="text"
                placeholder="Email"
                value="<?php echo htmlspecialchars($old['user_email'] ?? ''); ?>"
                class="<?php echo $emailErr ? 'x-error' : ''; ?>"
            >

            <label for="login_password">Password</label>
            <input
                id="login_password"
                name="user_password"
                type="password"
                placeholder="Password"
                class="<?php echo $passErr ? 'x-error' : ''; ?>"
            >

            <button class="x-dialog__btn">Next</button>
        </form>

        <p class="x-dialog__alt">
            Don't have an account?
            <a href="#" data-open="signupDialog">Sign up</a>
        </p>
    </div>
</div>

<?php
unset($_SESSION['login_old'], $_SESSION['login_error'], $_SESSION['open_dialog']);
?>
