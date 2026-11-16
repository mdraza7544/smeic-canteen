<?php
// Legacy QR view — redirects to the new token-based system.
$type = $_GET['type'] ?? 'id';
$id   = (int)($_GET['id'] ?? 0);
$target = 'index.php?action=student/token&type=' . urlencode($type);
if ($type === 'order' && $id > 0) {
    $target .= '&id=' . $id;
}
redirect(url($target));
