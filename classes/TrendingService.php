<?php
require_once __DIR__ . '/../models/TrendingModel.php';

class TrendingService {
    private TrendingModel $trendingModel;

    public function __construct(?TrendingModel $trendingModel = null) {
        $this->trendingModel = $trendingModel ?? new TrendingModel();
    }

    public function getTrending(int $limit = 4, int $offset = 0): array {
        return $this->trendingModel->getTrending($limit, $offset);
    }
}
