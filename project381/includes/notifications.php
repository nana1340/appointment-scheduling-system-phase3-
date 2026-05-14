<?php
function createNotification($pdo, $userId, $message) {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, is_read, created_at)
        VALUES (?, ?, 0, NOW())
    ");

    $stmt->execute([$userId, $message]);
}

function getUnreadNotifications($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
        ORDER BY created_at DESC
    ");

    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function markNotificationAsRead($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE id = ?
        AND user_id = ?
    ");

    $stmt->execute([$notificationId, $userId]);
}
?>