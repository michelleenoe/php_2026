<?php
require_once __DIR__ . '/../x.php';

abstract class BaseApiController {
    public function ensureLogin(string $redirect = '/'): void {
        _ensureLogin($redirect);
    }

    public function currentUser(): ?array {
        return _currentUser();
    }

    public function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }

    public function badRequest(array $data = ['error' => 'Bad request']): void {
        $this->json($data, 400);
    }

    public function unauthorized(array $data = ['error' => 'Unauthorized']): void {
        $this->json($data, 401);
    }

    public function serverError(array $data = ['error' => 'Server error']): void {
        $this->json($data, 500);
    }
}
