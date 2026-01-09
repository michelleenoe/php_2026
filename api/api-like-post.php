<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
require_once __DIR__ . '/../classes/LikeService.php';
require_once __DIR__ . '/../controllers/BaseApiController.php';

class LikeApi extends BaseApiController {
    public function handle(): void {
        $user = $this->currentUser();
        if (!$user) {
            $this->unauthorized(['error' => 'Please login to like a post']);
            return;
        }
        $postPk = $_POST['post_pk'] ?? '';
        if ($postPk === '') {
            $this->badRequest(['error' => 'missing_post_pk']);
            return;
        }

        try {
            $likeService = new LikeService();
            $likeService->like($user['user_pk'], $postPk);
            $this->json(['success' => true]);
            return;
        } catch (Exception $e) {
            $code = $e->getCode();
            if ($code === 400) {
                $this->badRequest(['error' => $e->getMessage()]);
                return;
            }
            $this->serverError(['error' => $e->getMessage()]);
            return;
        }
    }
}

(new LikeApi())->handle();
