document.addEventListener("DOMContentLoaded", () => {

  // 画像リスト
  let images = Array.from(document.querySelectorAll(".product-img"));


  // モーダル要素
  const modal = document.getElementById("modal");
  const modalImage = document.getElementById("modalImage");
  const modalText = document.getElementById("modalText");
  const modalPrice = document.getElementById("modalPrice");
  const closeModal = document.getElementById("closeModal");

  // モーダル内の矢印
  const arrowLeft = modal.querySelector(".arrow.left");
  const arrowRight = modal.querySelector(".arrow.right");

  let currentIndex=0;
  
  // -------------------------------
  // 画像クリック → モーダルを開く
  // -------------------------------
  function bindImageClick() {
    images.forEach((image, index) => {
      image.addEventListener("click", () => {
        currentIndex = index;
        modalImage.src = image.src;
        modalText.textContent = image.alt;
        modalPrice.textContent = "価格: ¥" + image.dataset.price;
        modal.style.display = "flex";
      });
    });
  }

  bindImageClick();

  //メニューをクリックしたらフィルタ＋並び替え
  document.querySelectorAll(".menu-items .item").forEach(item => {
    item.addEventListener("click", () => {
       // ボタンの data-type を取得（テキスト依存なし）
        const type = item.dataset.type;

        const list = document.querySelector(".products");
        const cards = Array.from(list.querySelectorAll(".product-card"));

        // type の一致・不一致で並び替え
        const sorted = [
            ...cards.filter(c => c.dataset.type === type),
            ...cards.filter(c => c.dataset.type !== type)
        ];

        // DOM に再追加（順番が変わる）
        sorted.forEach(c => list.appendChild(c));

        // 重要 images を再取得しないと画像送りが壊れる
        images = Array.from(document.querySelectorAll(".product-card img"));

        bindImageClick();
        // 表示中の画像位置をリセット
        currentIndex = 0;
    });
  });


  // -------------------------------
  // 左矢印クリック（前の画像へ）
  // -------------------------------
  arrowLeft.addEventListener("click", (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + images.length) % images.length;

    modalImage.src = images[currentIndex].src;
    modalText.textContent = images[currentIndex].alt;
    modalPrice.textContent = "価格: ¥" + images[currentIndex].dataset.price;
  });

  // -------------------------------
  // 右矢印クリック（次の画像へ）
  // -------------------------------
  arrowRight.addEventListener("click", (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % images.length;

    modalImage.src = images[currentIndex].src;
    modalText.textContent = images[currentIndex].alt;
    modalPrice.textContent = "価格: ¥" + images[currentIndex].dataset.price;
  });

  // -------------------------------
  // モーダル背景クリック → 閉じる
  // -------------------------------
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });

  // -------------------------------
  // 閉じるボタン
  // -------------------------------
  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // -------------------------------
  // キーボード操作 ← → Esc Enter
  // -------------------------------
  document.addEventListener("keydown", (e) => {
    if (modal.style.display !== "flex") return;

    if (e.key === "ArrowLeft") arrowLeft.click();
    if (e.key === "ArrowRight") arrowRight.click();
    if (e.key === "Escape" || e.key === "Enter") closeModal.click();
  });


  

  //クリスマスケーキを押したら名前えに含まれている画像が先頭に来る
  document.getElementById("specialMenu").addEventListener("click", () => {

    const keyword = "クリスマス";

    const list = document.querySelector(".products");
    const cards = Array.from(list.querySelectorAll(".product-card"));

    const matched = [];
    const others = [];

    cards.forEach(card => {
      const name = card.querySelector(".product-name").textContent;
      if (name.includes(keyword)) {
        matched.push(card);
      } else {
        others.push(card);
      }
    });

    // 先頭に「keyword」を含む商品
    [...matched, ...others].forEach(card => list.appendChild(card));

    //並び替え後に再取得
    images = Array.from(document.querySelectorAll(".product-img"));
    bindImageClick();
    
    const currentSrc = modalImage.src;
    const newIndex = images.findIndex(img => img.src === currentSrc);

    currentIndex = newIndex !== -1 ? newIndex : 0;
  });
  
  //--------------------------------
  // スマホ スワイプ対応（画像スライド）
  // -------------------------------
  let touchStartX = 0;
  let touchEndX = 0;

  // スワイプ対象は「画像」
  modalImage.addEventListener("touchstart", e => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  modalImage.addEventListener("touchend", e => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  }, { passive: true });

  function handleSwipe() {
    const diff = touchStartX - touchEndX;

    // 少しのタップは無視（誤作動防止）
    if (Math.abs(diff) < 40) return;

    if (diff > 0) {
      // ← 左にスワイプ → 次へ
      currentIndex = (currentIndex + 1) % images.length;
    } else {
      // → 右にスワイプ → 前へ
      currentIndex = (currentIndex - 1 + images.length) % images.length;
    }

    modalImage.src = images[currentIndex].src;
    modalText.textContent = images[currentIndex].alt;
    modalPrice.textContent =
      "価格: ¥" + images[currentIndex].dataset.price;
  }

});

document.getElementById("reloadBtn").addEventListener("click", () => {
  location.reload();
});


