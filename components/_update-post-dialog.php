<?php
$updateActive  = !empty($_SESSION['open_dialog']) && $_SESSION['open_dialog'] === 'update';
$updatePk      = $_SESSION['old_update_post_pk']      ?? '';
$updateMessage = $_SESSION['old_update_post_message'] ?? '';

if ($updateActive) {
  unset($_SESSION['open_dialog'], $_SESSION['old_update_post_pk'], $_SESSION['old_update_post_message']);
}
?>

<div class="x-dialog <?php echo $updateActive ? 'active' : ''; ?>" id="updatePostDialog" role="dialog" aria-modal="true" aria-labelledby="updatePostTitle">
  <div class="x-dialog__overlay"></div>
  <div class="x-dialog__content">
    <button class="x-dialog__close" aria-label="Close">&times;</button>
    <div class="x-dialog__header">
    <img src="/public/img/weave-logo.png" alt="Weave logo" class="post-logo">
    </div>
    <h2 id="updatePostTitle">Edit your post</h2>
    <form class="x-dialog__form" action="api-update-post" method="POST" autocomplete="off">
    <input type="hidden" name="post_pk" id="postPkInput" value="<?php echo htmlspecialchars($updatePk, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="redirect_to" id="redirectToInput" value="">
      <textarea type="text" maxlength="200" name="post_message" id="postMessageInput" placeholder="Your post message here" <?php echo $updateActive ? 'autofocus' : ''; ?>><?php echo htmlspecialchars($updateMessage, ENT_QUOTES, 'UTF-8'); ?></textarea>
      <div class="dialog-btn-group">
        <button type="submit" class="x-dialog__btn">Update</button>
        <button type="button" class="x-dialog__btn_del" id="deletePostBtn">Delete</button>

      </div>

      <div id="deleteConfirm" class="delete-confirm">
        <span>Are you sure you want to delete this post?</span>
        <div class="delete-confirm__buttons">
          <button type="button"
                  id="deleteCancel"
                  class="delete-confirm__btn delete-confirm__btn--secondary">
            No
          </button>
          <button type="button"
                  id="deleteConfirmYes"
                  class="delete-confirm__btn delete-confirm__btn--danger">
            Yes
          </button>
        </div>
      </div>
      
    </form>

  </div>
 </div>

