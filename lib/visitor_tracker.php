<?php
/**
 * lib/visitor_tracker.php
 *
 * 訪問を記録するライブラリ。
 * index.php の冒頭で include するだけで動作します。
 *
 * 使い方:
 *   require_once 'lib/visitor_tracker.php';
 *   recordVisit($pdo);   // $pdo は既存のDB接続をそのまま渡す
 */

function recordVisit(PDO $pdo, string $page = '/'): void
{
    // ---- ボット除外 ----------------------------------------
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $botPattern = '/bot|crawl|slurp|spider|mediapartners|google|baidu|bing|msn/i';
    if (preg_match($botPattern, $ua)) {
        return;
    }

    // ---- 同一セッション内の連続カウント防止 ----------------
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sessionKey = 'visited_' . md5($page);
    if (isset($_SESSION[$sessionKey])) {
        return;  // 同じページを同セッション内で2回目以降は記録しない
    }
    $_SESSION[$sessionKey] = true;

    // ---- IP をハッシュ化（プライバシー保護） ---------------
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ipHash = hash('sha256', $ip . 'mu_cafe_salt_2025'); // saltはお好みで変更可

    // ---- デバイス判定 ---------------------------------------
    $device = 'PC';
    if (preg_match('/Mobile|Android|iPhone/i', $ua)) {
        $device = 'スマホ';
    } elseif (preg_match('/iPad|Tablet/i', $ua)) {
        $device = 'タブレット';
    }

    // ---- リファラ -------------------------------------------
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    if ($referrer !== null) {
        $referrer = mb_substr($referrer, 0, 500);
    }

    // ---- DB に INSERT ----------------------------------------
    try {
        $stmt = $pdo->prepare("
            INSERT INTO visitors (visited_at, page, ip_hash, device_type, referrer)
            VALUES (NOW(), :page, :ip_hash, :device_type, :referrer)
        ");
        $stmt->execute([
            ':page'        => mb_substr($page, 0, 255),
            ':ip_hash'     => $ipHash,
            ':device_type' => $device,
            ':referrer'    => $referrer,
        ]);
    } catch (PDOException $e) {
        // トラッキング失敗はサイト表示に影響させない（無視）
        error_log('visitor_tracker error: ' . $e->getMessage());
    }
}
