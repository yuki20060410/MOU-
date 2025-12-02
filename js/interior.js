document.addEventListener("DOMContentLoaded", () => {

  // すべての商品画像
  let images = Array.from(document.querySelectorAll(".interior_img"));

  // モーダル関係
  const modal = document.getElementById("modal");
  const modalImage = document.getElementById("modalImage");
  const closeModal = document.getElementById("closeModal");
  const arrowLeft = document.querySelector(".arrow.left");
  const arrowRight = document.querySelector(".arrow.right");

  let currentIndex = 0;

// -------------------------------
  // 画像クリック → モーダルを開く
  // -------------------------------
  images.forEach((image, index) => {
    image.addEventListener("click", () => {
      currentIndex = index;
      modalImage.src = image.src;
      modal.style.display = "flex";
    });
  });
  // -------------------------------
  // 左矢印クリック（前の画像へ）
  // -------------------------------
  arrowLeft.addEventListener("click", (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    modalImage.src = images[currentIndex].src;

  });

  // -------------------------------
  // 右矢印クリック（次の画像へ）
  // -------------------------------
  arrowRight.addEventListener("click", (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % images.length;
    modalImage.src = images[currentIndex].src;
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

});
