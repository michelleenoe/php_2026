<?php
$postActive = false;
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['open_dialog']) && $_SESSION['open_dialog'] === 'post') {
  $postActive = true;
  unset($_SESSION['open_dialog']);
}
?>
<div class="x-dialog <?php echo $postActive ? 'active' : ''; ?>" id="postDialog" role="dialog" aria-modal="true" aria-labelledby="postTitle">
  <div class="x-dialog__overlay"></div>
  <div class="x-dialog__content">
    <button class="x-dialog__close" aria-label="Close">&times;</button>
    <img src="/public/img/weave-logo.png" alt="Weave logo" class="post-logo">
    <h2 id="signupPost">Create your post</h2>
    <form class="x-dialog__form" action="api-create-post" method="POST" enctype="multipart/form-data" autocomplete="off">
      <input
        type="hidden"
        name="redirect_to"
        id="redirectToInput"
        value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
      <input
        type="hidden"
        name="post_pk"
        id="postPkInput"
        value="<?php echo htmlspecialchars($updatePk); ?>">
        <textarea id="post-dialog-textarea" type="text" maxlength="200" name="post_message" placeholder="Your post message here"><?php echo isset($_SESSION['old_post_message']) ? htmlspecialchars($_SESSION['old_post_message']) : ''; ?></textarea>
        
        <div id="postImagePreviewContainer" class="post-image-preview-container" style="display: none;">
          <img id="postImagePreview" src="" alt="Preview" class="post-image-preview">
          <button type="button" class="post-image-remove" id="removePostImage">&times;</button>
        </div>

        <div class="post-image-wrapper">
          <div class="post-image-text">
            <p class="post-image-main">Add an image to boost engagement.</p>
            <p class="post-image-hint">Max 1 image per post.</p>
          </div>
          <label class="post-image-upload-btn" id="postImageUploadBtn">
            <i class="fa-solid fa-image"></i>
            <span>Add Image</span>
            <input type="file" name="post_image" accept="image/*" hidden id="postImageInput">
          </label>
        </div>

      <button type="submit" class="x-dialog__btn">Post</button>
    </form>
  </div>
</div>
