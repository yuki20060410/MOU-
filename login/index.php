<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 未ログインはログイン画面へ
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user     = $_SESSION['user'];
$username = htmlspecialchars($user['name']    ?? 'ゲスト', ENT_QUOTES, 'UTF-8');
$picture  = htmlspecialchars($user['picture'] ?? '', ENT_QUOTES, 'UTF-8');

/* ===== DB接続 ===== */
$pdo = new PDO(
    'mysql:host=localhost;dbname=webSite_db;charset=utf8',
    'webSite',
    'yuki',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

/* ===== 限定商品（有効期間内 & is_active=1） ===== */
$stmt = $pdo->query("
    SELECT *
    FROM seasonal_products
    WHERE is_active = 1
      AND (start_date IS NULL OR start_date <= CURDATE())
      AND (end_date   IS NULL OR end_date   >= CURDATE())
    ORDER BY created_at DESC
");
$seasonals = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== お知らせ（is_active=1 / 新しい順） ===== */
$stmt = $pdo->query("
    SELECT *
    FROM announcements
    WHERE is_active = 1
    ORDER BY created_at DESC
");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== 今月・来月の特別日（closed / special）===== */
$stmt = $pdo->query("
    SELECT date, status, note
    FROM business_calendar
    WHERE date BETWEEN
        DATE_FORMAT(CURDATE(), '%Y-%m-01')
        AND LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))
    ORDER BY date ASC
");
$specialDays = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $specialDays[$row['date']] = ['status' => $row['status'], 'note' => $row['note']];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員ページ | カフェ＆ケーキラボ ムー</title>
    <link rel="stylesheet" href="../css/member.css">
</head>
<body>

<!-- ヘッダー -->
<header class="member-header">
    <span class="site-name">カフェ＆ケーキラボ　ムー</span>
    <div class="user-info">
        <?php if ($picture): ?>
            <img src="<?= $picture ?>" alt="プロフィール画像" referrerpolicy="no-referrer">
        <?php endif; ?>
        <span class="user-name"><?= $username ?> さん</span>
        <a href="logout.php" class="logout-btn">ログアウト</a>
    </div>
</header>

<main class="member-main">

    <!-- ウェルカム -->
    <div class="welcome-banner">
        <span class="icon">☕</span>
        <div>
            <h1>ようこそ、<?= $username ?> さん</h1>
            <p>会員限定ページです。最新の限定メニューや営業カレンダーをご確認いただけます。</p>
        </div>
    </div>

    <!-- ① 限定メニュー -->
    <section>
        <h2 class="section-title"><span class="icon">🎄</span>今季の限定メニュー</h2>

        <?php if (empty($seasonals)): ?>
            <p class="empty-msg">現在の限定メニューはありません</p>
        <?php else: ?>
        <div class="seasonal-grid">
            <?php foreach ($seasonals as $s): ?>
            <div class="seasonal-card">
                <span class="badge">LIMITED</span>

                <?php if ($s['image_path'] && file_exists('../' . $s['image_path'])): ?>
                    <img src="../<?= htmlspecialchars($s['image_path'], ENT_QUOTES) ?>"
                         alt="<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>">
                <?php else: ?>
                    <div class="no-image">🍰</div>
                <?php endif; ?>

                <div class="card-body">
                    <div class="card-name"><?= htmlspecialchars($s['name'], ENT_QUOTES) ?></div>

                    <?php if ($s['price']): ?>
                        <div class="card-price">¥<?= number_format($s['price']) ?></div>
                    <?php endif; ?>

                    <?php if ($s['description']): ?>
                        <div class="card-desc"><?= nl2br(htmlspecialchars($s['description'], ENT_QUOTES)) ?></div>
                    <?php endif; ?>

                    <?php if ($s['start_date'] || $s['end_date']): ?>
                        <span class="card-period">
                            <?php
                            $start = $s['start_date'] ? date('n/j', strtotime($s['start_date'])) : '';
                            $end   = $s['end_date']   ? date('n/j', strtotime($s['end_date']))   : '';
                            if ($start && $end) echo "期間：{$start} 〜 {$end}";
                            elseif ($end)        echo "〜 {$end} まで";
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- ② 営業カレンダー -->
    <section>
        <h2 class="section-title"><span class="icon">📅</span>営業カレンダー</h2>
        <div class="calendar-wrap">

            <!-- 月切り替えナビ -->
            <div class="calendar-nav">
                <button id="prevMonth">&#8249;</button>
                <span class="month-label" id="monthLabel"></span>
                <button id="nextMonth">&#8250;</button>
            </div>

            <!-- カレンダー本体 -->
            <div class="calendar-grid" id="calendarGrid"></div>

            <!-- 凡例 -->
            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-dot normal"></div>通常営業
                </div>
                <div class="legend-item">
                    <div class="legend-dot closed"></div>休業日
                </div>
                <div class="legend-item">
                    <div class="legend-dot special"></div>特別営業
                </div>
            </div>
        </div>
    </section>

    <!-- ③ お知らせ -->
    <section>
        <h2 class="section-title"><span class="icon">📢</span>お知らせ</h2>

        <?php if (empty($announcements)): ?>
            <p class="empty-msg">現在のお知らせはありません</p>
        <?php else: ?>
        <div class="announcement-list">
            <?php foreach ($announcements as $a): ?>
            <div class="announcement-item" onclick="this.classList.toggle('open')">
                <div class="ann-head">
                    <span class="ann-title"><?= htmlspecialchars($a['title'], ENT_QUOTES) ?></span>
                    <span class="ann-date"><?= date('Y/m/d', strtotime($a['created_at'])) ?></span>
                </div>
                <?php if ($a['body']): ?>
                    <div class="ann-body"><?= nl2br(htmlspecialchars($a['body'], ENT_QUOTES)) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

</main>

<footer class="member-footer">
    <p>© カフェ＆ケーキラボ（ムー）&ensp;|&ensp;<a href="../index.php" style="color:inherit;">ホームに戻る</a></p>
</footer>

<script>
// ===== カレンダー描画 JS =====

// PHPから特別日データを受け取る
const specialDays = <?= json_encode($specialDays, JSON_UNESCAPED_UNICODE) ?>;

const DAYS = ['日','月','火','水','木','金','土'];

let viewYear  = new Date().getFullYear();
let viewMonth = new Date().getMonth(); // 0-indexed

function pad(n) { return String(n).padStart(2, '0'); }

function renderCalendar() {
    const grid  = document.getElementById('calendarGrid');
    const label = document.getElementById('monthLabel');
    grid.innerHTML = '';

    label.textContent = `${viewYear}年 ${viewMonth + 1}月`;

    // 曜日ヘッダー
    DAYS.forEach((d, i) => {
        const el = document.createElement('div');
        el.className = 'cal-header' + (i === 0 ? ' sun' : i === 6 ? ' sat' : '');
        el.textContent = d;
        grid.appendChild(el);
    });

    const firstDay = new Date(viewYear, viewMonth, 1).getDay();
    const lastDate = new Date(viewYear, viewMonth + 1, 0).getDate();
    const today    = new Date().toISOString().slice(0, 10);

    // 空白セル
    for (let i = 0; i < firstDay; i++) {
        const el = document.createElement('div');
        el.className = 'cal-day empty';
        grid.appendChild(el);
    }

    // 日付セル
    for (let d = 1; d <= lastDate; d++) {
        const dateStr = `${viewYear}-${pad(viewMonth + 1)}-${pad(d)}`;
        const dow = new Date(viewYear, viewMonth, d).getDay();
        const special = specialDays[dateStr];

        const el = document.createElement('div');
        let cls = 'cal-day ';

        if      (special?.status === 'closed')  cls += 'closed';
        else if (special?.status === 'special') cls += 'special';
        else if (dow === 0) cls += 'normal sunday';
        else if (dow === 6) cls += 'normal saturday';
        else                cls += 'normal';

        if (dateStr === today) cls += ' today';
        el.className = cls;

        // 日付数字
        const numEl = document.createElement('span');
        numEl.className = 'day-num';
        numEl.textContent = d;
        el.appendChild(numEl);

        // 特別ラベル
        if (special) {
            const tagEl = document.createElement('span');
            tagEl.className = 'day-tag';
            tagEl.textContent = special.status === 'closed' ? '休' : '特';
            el.appendChild(tagEl);

            // タップでメモ表示
            if (special.note) {
                el.title = special.note;
            }
        }

        // 木曜（定休日）は毎週グレーに
        if (dow === 4 && !special) {
            el.classList.add('closed');
            const tagEl = document.createElement('span');
            tagEl.className = 'day-tag';
            tagEl.textContent = '休';
            el.appendChild(tagEl);
        }

        grid.appendChild(el);
    }
}

document.getElementById('prevMonth').addEventListener('click', () => {
    viewMonth--;
    if (viewMonth < 0) { viewMonth = 11; viewYear--; }
    renderCalendar();
});

document.getElementById('nextMonth').addEventListener('click', () => {
    viewMonth++;
    if (viewMonth > 11) { viewMonth = 0; viewYear++; }
    renderCalendar();
});

renderCalendar();
</script>

</body>
</html>
