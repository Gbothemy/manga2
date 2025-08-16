<?php
require_once __DIR__ . '/config.php';

function send_mail(string $to, string $subject, string $message, string $from = null): bool {
    $headers = [];
    $from = $from ?: AppConfig::emailFrom();
    $headers[] = 'From: ' . $from;
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers_str = implode("\r\n", $headers);

    $ok = @mail($to, $subject, $message, $headers_str);
    if (!$ok) {
        error_log('[MAIL_FAIL] to=' . $to . ' subject=' . $subject);
    }
    return $ok;
}