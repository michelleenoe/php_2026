<?php
require_once __DIR__ . '/../controllers/BaseApiController.php';
require_once __DIR__ . '/../classes/PostService.php';
require_once __DIR__ . '/../x.php';
$db = _db();

class GetPostApi extends BaseApiController {
    private PostService $postService;

    public function __construct() {
        $this->postService = new PostService();
    }

    public function handle(): void {
        $this->ensureLogin('/');
        $postPk = $_GET['post_pk'] ?? null;

        if (!$postPk) {
            $this->badRequest(['success' => false, 'error' => 'missing_post_pk']);
            return;
        }

        try {
            $viewer = $this->currentUser();
            $post = $this->postService->getPostById($postPk, $viewer['user_pk'] ?? null);
            if (!$post) {
                $this->json(['success' => false, 'error' => 'not_found'], 404);
            }
            $this->json(['success' => true, 'post' => $post]);
        } catch (Exception $e) {
            error_log('api-get-post error: ' . $e->getMessage());
            $this->serverError(['success' => false, 'error' => 'server_error']);
        }
    }
}

(new GetPostApi())->handle();
