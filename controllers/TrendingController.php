<?php
require_once __DIR__ . '/../classes/TrendingService.php';

class TrendingController {
    private TrendingService $service;

    public function __construct(?TrendingService $service = null) {
        $this->service = $service ?? new TrendingService();
    }

    public function getTrending(int $limit = 4, int $offset = 0): array {
        return $this->service->getTrending($limit, $offset);
    }
}
