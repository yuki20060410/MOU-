<section class="product-card" data-type="<?= htmlspecialchars($p['category'])?>">
    <img src="images/<?= basename($p["image_path"]) ?>"
     alt="<?= htmlspecialchars($p['name']) ?>"
     class="product-img"
     data-image="/webSaito/origin/images/<?= basename($p["image_path"]) ?>"
     data-price="<?= htmlspecialchars($p['price']) ?>">


    <h2 class="product-name">
        <?= nl2br(htmlspecialchars($p['name'])) ?>
    </h2>

    <p class="ingredients-toggle"
       onclick="openIngredientsModal(this)"
       data-id="<?= htmlspecialchars($p['personal_code']) ?>"
       data-material1="<?= htmlspecialchars($p['material1'] ?? '') ?>"
       data-material2="<?= htmlspecialchars($p['material2'] ?? '') ?>">
        使用食材を表示
    </p>

    <p class="ingredients"></p>
</section>
