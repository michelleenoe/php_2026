<?php
try {
    require_once __DIR__ . "/../x.php";
    session_start();
    _ensureLogin('/');

    require_once __DIR__ . "/../db.php";
    require_once __DIR__ . "/../controllers/SearchController.php";

    $controller = new SearchController($_db);
    $results = $controller->handle();

    $users = $results["users"];
    $posts = $results["posts"];

    require_once __DIR__ . "/../views/search.php";

} catch (Exception $e) {
    http_response_code($e->getCode());
    echo $e->getMessage();
}
