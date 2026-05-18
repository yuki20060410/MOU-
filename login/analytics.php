<?php
/**
 * login/analytics.php
 *
 * 訪問者アナリティクスダッシュボード。
 * ログイン済みの管理者のみアクセス可能。
 */
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../lib/db.php';

// ---- 期間フィルター -------------------------------------------
$range = $_GET['range'] ?? '30';  // 日数
$range = in_array($range, ['7', '30', '90', '365']) ? (int)$range : 30;

// ---- サマリー数値 --------------------------------------------
$summary = $pdo->prepare("
    SELECT
        COUNT(*)                          AS total_visits,
        COUNT(DISTINCT ip_hash)           AS unique_visitors,
        COUNT(DISTINCT DATE(visited_at))  AS active_days
    FROM visitors
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
");
$summary->execute([':days' => $range]);
$s = $summary->fetch(PDO::FETCH_ASSOC);

// ---- 日別推移 ------------------------------------------------
$daily = $pdo->prepare("
    SELECT
        DATE(visited_at)       AS day,
        COUNT(*)               AS visits,
        COUNT(DISTINCT ip_hash) AS unique_v
    FROM visitors
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
    GROUP BY day
    ORDER BY day ASC
");
$daily->execute([':days' => $range]);
$dailyRows = $daily->fetchAll(PDO::FETCH_ASSOC);

// ---- デバイス比率 -------------------------------------------
$deviceStmt = $pdo->prepare("
    SELECT device_type, COUNT(*) AS cnt
    FROM visitors
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
    GROUP BY device_type
    ORDER BY cnt DESC
");
$deviceStmt->execute([':days' => $range]);
$deviceRows = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);

// ---- 時間帯分布 ---------------------------------------------
$hourStmt = $pdo->prepare("
    SELECT HOUR(visited_at) AS hr, COUNT(*) AS cnt
    FROM visitors
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
    GROUP BY hr
    ORDER BY hr ASC
");
$hourStmt->execute([':days' => $range]);
$hourRows = $hourStmt->fetchAll(PDO::FETCH_ASSOC);
$hourMap = array_fill(0, 24, 0);
foreach ($hourRows as $h) { $hourMap[(int)$h['hr']] = (int)$h['cnt']; }

// ---- 曜日分布 -----------------------------------------------
$weekdayLabels = ['日','月','火','水','木','金','土'];
$wdStmt = $pdo->prepare("
    SELECT DAYOFWEEK(visited_at) AS wd, COUNT(*) AS cnt
    FROM visitors
    WHERE visited_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
    GROUP BY wd
    ORDER BY wd ASC
");
$wdStmt->execute([':days' => $range]);
$wdRows = $wdStmt->fetchAll(PDO::FETCH_ASSOC);
$wdMap = array_fill(1, 7, 0);
foreach ($wdRows as $w) { $wdMap[(int)$w['wd']] = (int)$w['cnt']; }

// ---- 最近の訪問 10件 ----------------------------------------
$recentStmt = $pdo->prepare("
    SELECT visited_at, page, device_type, referrer
    FROM visitors
    ORDER BY visited_at DESC
    LIMIT 10
");
$recentStmt->execute();
$recentRows = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// ---- JS用データ整形 -----------------------------------------
$dayLabels    = json_encode(array_column($dailyRows, 'day'),      JSON_UNESCAPED_UNICODE);
$dayVisits    = json_encode(array_column($dailyRows, 'visits'),   JSON_UNESCAPED_UNICODE);
$dayUnique    = json_encode(array_column($dailyRows, 'unique_v'), JSON_UNESCAPED_UNICODE);
$deviceLabels = json_encode(array_column($deviceRows, 'device_type'), JSON_UNESCAPED_UNICODE);
$deviceCounts = json_encode(array_column($deviceRows, 'cnt'),         JSON_UNESCAPED_UNICODE);
$hourData     = json_encode(array_values($hourMap), JSON_UNESCAPED_UNICODE);
$wdData       = json_encode(array_values($wdMap),   JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>訪問者アナリティクス | ムー管理画面</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
    background: #f0f4f8;
    color: #333;
  }

  /* ===== ヘッダー ===== */
  header {
    background: linear-gradient(135deg, #1a56db, #0ea5e9);
    color: #fff;
    padding: 18px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,0.18);
  }
  header h1 { font-size: 1.25rem; }
  .header-links a {
    color: #d1e8ff;
    text-decoration: none;
    margin-left: 20px;
    font-size: 0.9rem;
    transition: color .2s;
  }
  .header-links a:hover { color: #fff; }

  /* ===== メイン ===== */
  main {
    max-width: 1100px;
    margin: 32px auto;
    padding: 0 16px 60px;
  }

  /* ===== 期間切り替え ===== */
  .range-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 28px;
    flex-wrap: wrap;
  }
  .range-tabs a {
    padding: 8px 20px;
    border-radius: 30px;
    background: #fff;
    color: #1a56db;
    border: 2px solid #1a56db;
    text-decoration: none;
    font-weight: bold;
    font-size: 0.9rem;
    transition: .2s;
  }
  .range-tabs a.active,
  .range-tabs a:hover {
    background: #1a56db;
    color: #fff;
  }

  /* ===== サマリーカード ===== */
  .summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-bottom: 30px;
  }
  .summary-card {
    background: #fff;
    border-radius: 14px;
    padding: 22px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    border-left: 5px solid #1a56db;
  }
  .summary-card .label {
    font-size: 0.82rem;
    color: #888;
    margin-bottom: 6px;
  }
  .summary-card .value {
    font-size: 2.2rem;
    font-weight: bold;
    color: #1a56db;
  }
  .summary-card .sub {
    font-size: 0.8rem;
    color: #aaa;
    margin-top: 4px;
  }

  /* ===== チャートカード ===== */
  .chart-card {
    background: #fff;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    margin-bottom: 24px;
  }
  .chart-card h2 {
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: 18px;
    color: #444;
    border-left: 4px solid #1a56db;
    padding-left: 10px;
  }
  .chart-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  /* ===== 最近の訪問テーブル ===== */
  .recent-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
  }
  .recent-table th {
    background: #eaf0fb;
    padding: 10px 14px;
    text-align: left;
    color: #444;
    font-weight: bold;
  }
  .recent-table td {
    padding: 9px 14px;
    border-bottom: 1px solid #f0f0f0;
    color: #555;
    word-break: break-all;
  }
  .recent-table tr:hover td { background: #f7faff; }

  .badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: bold;
  }
  .badge-pc     { background: #dbeafe; color: #1d4ed8; }
  .badge-sp     { background: #dcfce7; color: #166534; }
  .badge-tablet { background: #fef9c3; color: #854d0e; }

  /* ===== スマホ ===== */
  @media (max-width: 640px) {
    .chart-grid { grid-template-columns: 1fr; }
    .summary-card .value { font-size: 1.7rem; }
  }
</style>
</head>
<body>

<header>
  <h1>📊 訪問者アナリティクス　― カフェ＆ケーキラボ ムー</h1>
  <div class="header-links">
    <a href="index.php">← 商品管理</a>
    <a href="logout.php">ログアウト</a>
  </div>
</header>

<main>

  <!-- 期間タブ -->
  <div class="range-tabs">
    <?php foreach (['7'=>'7日間','30'=>'30日間','90'=>'90日間','365'=>'1年間'] as $v=>$label): ?>
      <a href="?range=<?= $v ?>"
         class="<?= ($range == $v) ? 'active' : '' ?>">
        <?= $label ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- サマリー -->
  <div class="summary-grid">
    <div class="summary-card">
      <div class="label">総アクセス数</div>
      <div class="value"><?= number_format($s['total_visits']) ?></div>
      <div class="sub">過去 <?= $range ?> 日間</div>
    </div>
    <div class="summary-card" style="border-left-color:#10b981;">
      <div class="label">ユニーク訪問者数</div>
      <div class="value" style="color:#10b981;"><?= number_format($s['unique_visitors']) ?></div>
      <div class="sub">推定ユニーク人数</div>
    </div>
    <div class="summary-card" style="border-left-color:#f59e0b;">
      <div class="label">平均日次アクセス</div>
      <?php $avg = $s['active_days'] > 0 ? round($s['total_visits'] / $s['active_days'], 1) : 0; ?>
      <div class="value" style="color:#f59e0b;"><?= $avg ?></div>
      <div class="sub">訪問のあった日で計算</div>
    </div>
  </div>

  <!-- 日別推移グラフ -->
  <div class="chart-card">
    <h2>日別アクセス推移</h2>
    <canvas id="dailyChart" height="90"></canvas>
  </div>

  <!-- 内訳2列 -->
  <div class="chart-grid">
    <div class="chart-card">
      <h2>デバイス別</h2>
      <canvas id="deviceChart" height="200"></canvas>
    </div>
    <div class="chart-card">
      <h2>時間帯別アクセス</h2>
      <canvas id="hourChart" height="200"></canvas>
    </div>
  </div>

  <!-- 曜日分布 -->
  <div class="chart-card">
    <h2>曜日別アクセス</h2>
    <canvas id="wdChart" height="80"></canvas>
  </div>

  <!-- 最近の訪問 -->
  <div class="chart-card">
    <h2>最近の訪問 (最新10件)</h2>
    <div style="overflow-x:auto;">
      <table class="recent-table">
        <thead>
          <tr>
            <th>日時</th>
            <th>ページ</th>
            <th>デバイス</th>
            <th>リファラ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentRows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['visited_at']) ?></td>
            <td><?= htmlspecialchars($r['page']) ?></td>
            <td>
              <?php
                $badgeClass = match($r['device_type']) {
                  'PC'      => 'badge-pc',
                  'スマホ'  => 'badge-sp',
                  default   => 'badge-tablet',
                };
              ?>
              <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r['device_type']) ?></span>
            </td>
            <td><?= $r['referrer'] ? htmlspecialchars(parse_url($r['referrer'], PHP_URL_HOST) ?: $r['referrer']) : '直接アクセス' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<script>
// ===== 日別推移 =====
new Chart(document.getElementById('dailyChart'), {
  type: 'line',
  data: {
    labels: <?= $dayLabels ?>,
    datasets: [
      {
        label: '総アクセス',
        data: <?= $dayVisits ?>,
        borderColor: '#1a56db',
        backgroundColor: 'rgba(26,86,219,0.08)',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
      },
      {
        label: 'ユニーク',
        data: <?= $dayUnique ?>,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16,185,129,0.06)',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
      }
    ]
  },
  options: {
    plugins: { legend: { position: 'top' } },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1 } }
    }
  }
});

// ===== デバイス円グラフ =====
new Chart(document.getElementById('deviceChart'), {
  type: 'doughnut',
  data: {
    labels: <?= $deviceLabels ?>,
    datasets: [{
      data: <?= $deviceCounts ?>,
      backgroundColor: ['#1a56db','#10b981','#f59e0b','#ef4444'],
      borderWidth: 2,
    }]
  },
  options: {
    plugins: { legend: { position: 'bottom' } },
    cutout: '60%',
  }
});

// ===== 時間帯棒グラフ =====
new Chart(document.getElementById('hourChart'), {
  type: 'bar',
  data: {
    labels: Array.from({length:24}, (_,i) => i + '時'),
    datasets: [{
      label: 'アクセス数',
      data: <?= $hourData ?>,
      backgroundColor: 'rgba(26,86,219,0.65)',
      borderRadius: 4,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});

// ===== 曜日棒グラフ =====
new Chart(document.getElementById('wdChart'), {
  type: 'bar',
  data: {
    labels: ['日','月','火','水','木','金','土'],
    datasets: [{
      label: 'アクセス数',
      data: <?= $wdData ?>,
      backgroundColor: [
        '#ef4444','#1a56db','#1a56db','#1a56db',
        '#1a56db','#1a56db','#0ea5e9'
      ],
      borderRadius: 6,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});
</script>
</body>
</html>
