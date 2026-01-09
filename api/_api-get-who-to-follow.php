<?php
session_start();
require_once __DIR__ . '/../x.php';
$db = _db();
$currentUser = $_SESSION['user']['user_pk'] ?? null;
if (!$currentUser) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit  = isset($_GET['limit'])  ? (int) $_GET['limit']  : 3;

$offset = max(0, $offset);
$limit  = max(1, min(10, $limit));

$sql = "
  SELECT users.*
  FROM users
  WHERE users.user_pk != :currentUser
    AND users.deleted_at IS NULL
    AND users.user_pk NOT IN (
      SELECT follow_user_fk
      FROM follows
      WHERE follower_user_fk = :currentUser
    )
  ORDER BY users.created_at DESC
  LIMIT :offset, :limit
";

$stmt = $db->prepare($sql);
$stmt->bindValue(':currentUser', $currentUser);
$stmt->bindValue(':offset',      $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit',       $limit,  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows);
