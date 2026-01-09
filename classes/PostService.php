<?php
require_once __DIR__ . '/../models/PostModel.php';

class PostService {
    private PostModel $postModel;

    public function __construct(?PostModel $postModel = null) {
        $this->postModel = $postModel ?? new PostModel();
    }

    public function getFeedPosts(int $limit, ?string $viewerPk = null): array {
        return $this->postModel->getPostsForFeed($limit, $viewerPk);
    }

    public function getPostsByUser(string $userPk, ?string $viewerPk = null): array {
        return $this->postModel->getPostsByUser($userPk, $viewerPk);
    }

    public function getPostsForUserWithReposts(string $userPk, ?string $viewerPk = null): array {
        return $this->postModel->getPostsForUserWithReposts($userPk, $viewerPk);
    }

    public function getPostsLikedByUser(string $userPk, ?string $viewerPk = null): array {
        return $this->postModel->getPostsLikedByUser($userPk, $viewerPk);
    }

    public function getPostById(string $postPk, ?string $viewerPk = null) {
        return $this->postModel->getPostById($postPk, $viewerPk);
    }

    public function createPost(string $postPk, string $message, string $imagePath, string $userPk): void {
        $this->postModel->createPost($postPk, $message, $imagePath, $userPk);
    }

    public function getPostsByHashtag(string $tag, ?string $viewerPk = null): array {
        return $this->postModel->getPostsByHashtag($tag, $viewerPk);
    }

    public function updatePost(string $postPk, string $message, string $userPk): bool {
        return $this->postModel->updatePost($postPk, $message, $userPk);
    }

    public function deletePost(string $postPk, string $userPk): bool {
        return $this->postModel->deletePost($postPk, $userPk);
    }
}
