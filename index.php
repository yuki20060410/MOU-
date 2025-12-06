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
    <?php include 'parts/menu-items.php'; ?>
    <div class="products">
        <?php foreach ($product as $p): ?>
            <?php include 'parts/productCard.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php include 'parts/modal.php'; ?>
    <?php include 'parts/material.php'; ?>
</main>
<?php include 'parts/footer.php'; ?>