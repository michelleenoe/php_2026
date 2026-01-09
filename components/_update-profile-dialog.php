<div class="x-dialog" id="updateProfileDialog" role="dialog" aria-modal="true" aria-labelledby="updateProfileTitle">
  <div class="x-dialog__overlay"></div>
  <div class="x-dialog__content">
    <button class="x-dialog__close" aria-label="Close">&times;</button>
    <div class="x-dialog__header">
    <img src="/public/img/weave-logo.png" alt="Weave logo" class="post-logo">
    </div>
    <h2 id="updateProfileTitle">Update your information</h2>
    <form class="x-dialog__form" action="/api-update-profile" method="POST" autocomplete="off">
      <input
        name="user_full_name"
        type="text"
        maxlength="20"
        placeholder="Name"
        value="<?php echo htmlspecialchars($_SESSION["user"]["user_full_name"] ?? ''); ?>"
    
      >
      <input
        name="user_username"
        type="text"
        maxlength="20"
        placeholder="Username"
        value="<?php echo htmlspecialchars($_SESSION["user"]["user_username"] ?? ''); ?>"
  
      >
      <input
        name="user_email"
        type="email"
        maxlength="50"
        placeholder="Email"
        value="<?php echo htmlspecialchars($_SESSION["user"]["user_email"] ?? ''); ?>"

      >
      <button type="submit" class="x-dialog__btn">Update</button>
      <button type="button" class="x-dialog__btn_del">Delete</button>
    </form>
  </div>
</div>
