<?php
$pdo = new PDO('mysql:host=localhost;dbname=webSite_db;charset=utf8', 'webSite', 'yuki');

$sql ="
        SELECT P.*, M.material1, M.material2 
        from products P
        LEFT join materials M
            on P.personal_code = M.product_code
        order by P.id ASC;";

$stmt = $pdo->query($sql);
$product = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
<?php include 'parts/header.php'; ?>
<main>

    <div class="menu-items">
        <span class="item" data-type="1000">ケーキ</span>
        <span class="item" data-type="2000">タルト</span>
        <span class="item" data-type="3000">パフェ</span>
        <span class="item" data-type="4000">かき氷</span>
        <span class="item" data-type="5000">その他</span>
        <span class="item" data-type="6000">ドリンク</span>
    </div>

    <div class="products">
        <?php foreach ($product as $p): ?>
            <?php include 'parts/productCard.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php include 'parts/modal.php'; ?>
    <?php include 'parts/material.php'; ?>
</main>
<?php include 'parts/footer.php'; ?>