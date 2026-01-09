<?php
use PDO;
require_once __DIR__ . '/../models/SearchModel.php';

class HashtagController {

    private SearchModel $model;

    public function __construct(PDO $db) {
        $this->model = new SearchModel($db);
    }

    public function handle($tag) {
        $cleanTag = trim($tag);

        return [
            "tag"   => $cleanTag,
            "posts" => $this->model->searchHashtag($cleanTag)
        ];
    }
}
