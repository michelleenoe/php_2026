<?php
require_once __DIR__ . '/../models/SearchModel.php';


class SearchController {

    private SearchModel $model;

    public function __construct(PDO $db) {
        $this->model = new SearchModel($db);
    }

    public function handle() {
        $query     = trim($_POST["query"] ?? "");
        $src       = $_GET["src"] ?? "generic";
        $vertical  = $_GET["vertical"] ?? "all";

        if ($src === "trend_click" && $vertical === "trends") {
            $cleanTag = ltrim($query, "#");
            return [
                "users" => [],
                "posts" => $this->model->searchHashtag($cleanTag)
            ];
        }

        return [
            "users" => $this->model->searchUsers($query),
            "posts" => $this->model->searchPosts($query),
        ];
    }
}
