<?php

$message = '';
$type = 'ok';
$ttl = 5000;

if (session_status() !== PHP_SESSION_ACTIVE) {
	
	if (!headers_sent()) {
		session_start();
	}
}


if (!empty($_SESSION['toast'])) {
	$t = $_SESSION['toast'];
	$message = $t['message'] ?? '';
	$type = (isset($t['type']) && $t['type'] === 'error') ? 'error' : 'ok';
	$ttl = isset($t['ttl']) ? (int)$t['ttl'] : 6000;
	unset($_SESSION['toast']);
}

if ($message) {
	$safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
	$safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');

	echo "<div id=\"toast\" class=\"toast-container\">";
	echo "<div class=\"toast toast-{$safeType}\" data-ttl=\"{$ttl}\">{$safe}</div>";
	echo "</div>";
}
