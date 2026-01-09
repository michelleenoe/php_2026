<?php
require_once __DIR__ . '/../x.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$title = "Welcome";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="icon" type="image/x-icon" href="/public/favicon/favicon.ico">
  <link rel="stylesheet" href="/public/css/app.css">
  <link rel="stylesheet" href="/public/css/search.css">
  <link rel="stylesheet" href="/public/css/landing-page.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script type="module" src="/public/js/app.js"></script>
  <script defer src="/public/js/dialog.js"></script>
  <script src="/public/js/toast.js"></script>
  <script src="/public/js/validation.js" mix-ignore></script>
</head>

<body>

  <?php require_once __DIR__ . '/../components/___toast.php'; ?>

  <main class="x-landing">
    <div class="landing-container">
      <div class="x-landing__left">
        <div class="x-landing__logo" aria-hidden="true">
          <img src="/public/img/weave-logo.png" alt="Weave logo" class="logo">
        </div>
      </div>

      <div class="x-landing__right">
        <h1 class="x-landing__title">Happening now</h1>
        <h2 class="x-landing__subtitle">Join today.</h2>
        <div class="button-group">
          <button class="x-landing__btn x-landing__btn--signup" data-open="signupDialog">Sign up</button>
          <button class="x-landing__btn x-landing__btn--login" data-open="loginDialog">Log in</button>
        </div>
      </div>


      <?php
      require_once __DIR__ . "/../components/_login-dialog.php";
      require_once __DIR__ . "/../components/_signup-dialog.php";
      ?>
    </div>

  </main>