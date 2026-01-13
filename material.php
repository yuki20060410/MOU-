<!-- 材料モーダル -->
<div id="ingredientsModal" class="modal">
  <div class="modal-content">
    <span id="ingredientsClose" class="close"
          onclick="closeIngredientsModal()">&times;</span>

    <!-- 左上ブロック -->
    <div class="top-left">
      <h2 id="modalName" class="modal-name"></h2>
      <img id="materialImage" class="material-image">
    </div>

    <!-- 矢印（画像基準） -->
    <div class="arrow left"></div>
    <div class="arrow right"></div>

    <!-- 中央表示 -->
    <h2 class="zai">使用食材</h2>
    <p id="modalMaterials"></p>
  </div>
</div>



<script>
    const productList = [
        <?php foreach ($product as $p): ?>
        {
            personal_code: <?= json_encode($p['personal_code']) ?>,
            name: <?= json_encode($p['name'], JSON_UNESCAPED_UNICODE) ?>,
            material1: <?= json_encode($p['material1'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
            material2: <?= json_encode($p['material2'] ?? '', JSON_UNESCAPED_UNICODE) ?>
        },
        <?php endforeach; ?>
    ];

    const modalImage = document.getElementById("materialImage");

    let productListSorted = [...productList]; // 並べ替え後の順に追従
    let currentIndex = 0;

    const ingredientsModal = document.getElementById("ingredientsModal");
    const ingArrowLeft = ingredientsModal.querySelector(".arrow.left");
    const ingArrowRight = ingredientsModal.querySelector(".arrow.right");

    function updateProductListOrder() {
        // 現在のDOM順に並べ替え
        const cards = Array.from(document.querySelectorAll(".product-card"));
        productListSorted = cards.map(card => {
            const id = card.querySelector(".ingredients-toggle").dataset.id;
            return productList.find(p => p.personal_code == id);
        });
    }

    function openIngredientsModal(elm) {
        updateProductListOrder(); // 並べ替え後に更新
        const card = elm.closest(".product-card");
        const img = card.querySelector(".product-img");

        const personal_code = elm.dataset.id;
        currentIndex = productListSorted.findIndex(p => p.personal_code == personal_code);
        if (currentIndex < 0) currentIndex = 0;
        modalImage.src = img.dataset.image; 
        modalImage.alt = img.alt;

        updateModal();
        ingredientsModal.style.display = "flex";
    }

    function updateModal() {
        const p = productListSorted[currentIndex]; 
        const m1 = p.material1 || "材料なし";
        const m2 = p.material2 || "";
        document.getElementById("modalName").textContent = p.name;
        document.getElementById("modalMaterials").innerHTML = `${m1}${m2 ? "<br>" + m2 : ""}`;
    }

    function closeIngredientsModal() {
        ingredientsModal.style.display = "none";
    }

    // 左右矢印
    ingArrowLeft.addEventListener("click", (e) => {
        e.stopPropagation();
        currentIndex = (currentIndex - 1 + productListSorted.length) % productListSorted.length;
        updateModal();
    });

    ingArrowRight.addEventListener("click", (e) => {
        e.stopPropagation();
        currentIndex = (currentIndex + 1) % productListSorted.length;
        updateModal();
    });

    // 背景クリックで閉じる
    ingredientsModal.addEventListener("click", (e) => {
        if (e.target === ingredientsModal) closeIngredientsModal();
    });

    // キーボード操作
    document.addEventListener("keydown", (e) => {
        if (ingredientsModal.style.display !== "flex") return;
        if (e.key === "ArrowLeft") ingArrowLeft.click();
        if (e.key === "ArrowRight") ingArrowRight.click();
        if (e.key === "Escape" || e.key === "Enter") closeIngredientsModal();
    });
</script>
