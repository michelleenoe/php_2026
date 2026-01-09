<?php
require_once __DIR__ . '/../models/CommentModel.php';

class PostController {
    public function renderPostWithCount(array $post) {
        $commentModel = new CommentModel();
        $targetPk = $post['comment_target_pk'] ?? $post['post_pk'];
        $commentCount = $commentModel->countForPost($targetPk);

        
        include __DIR__ . '/../views/partials/post.php';
    }
}