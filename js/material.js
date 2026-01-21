const modal = document.getElementById("ingredientsModal");
const modalImage = document.getElementById("materialImage");
const modalName = document.getElementById("modalName");
const modalMaterials = document.getElementById("modalMaterials");

const arrowLeft = modal.querySelector(".arrow.left");
const arrowRight = modal.querySelector(".arrow.right");
const closeBtn = document.getElementById("ingredientsClose");

let productListSorted = [...window.productList];
let currentIndex = 0;



//アレルギー抽出
function extractAllergiesJS(materialText, allergyMaster) {
  if (!materialText) return [];
  return allergyMaster.filter(allergy =>
    materialText.includes(allergy)
  );
}
const modalAllergies = document.getElementById("modalAllergies");




/* DOM順で並び替え */
function updateProductListOrder() {
  const cards = document.querySelectorAll(".product-card");
  productListSorted = [...cards].map(card => {
    const id = card.querySelector(".ingredients-toggle").dataset.id;
    return window.productList.find(p => p.personal_code == id);
  });
}

/* モーダル表示 */
window.openIngredientsModal = function (btn) {
  updateProductListOrder();

  const id = btn.dataset.id;
  currentIndex = productListSorted.findIndex(p => p.personal_code == id);
  if (currentIndex < 0) currentIndex = 0;

  updateModal();
  modal.style.display = "flex";
};

/* モーダル更新 */
function updateModal() {
  const p = productListSorted[currentIndex];

  // 商品名・画像
  modalName.textContent = p.name;
  modalImage.src = p.image;
  modalImage.alt = p.name;

  // 材料表示
  modalMaterials.innerHTML =
    (p.material1 || "材料なし") +
    (p.material2 ? "<br>" + p.material2 : "");

  // ===== アレルギー処理 =====
  const materialText = (p.material1 || "") + (p.material2 || "");

  const matched = extractAllergiesJS(
    materialText,
    window.allergyMaster
  );

  modalAllergies.textContent = matched.length
    ? matched.join("、")
    : "該当アレルギーなし";
}


/* 閉じる */
function closeIngredientsModal() {
  modal.style.display = "none";
}

closeBtn.onclick = closeIngredientsModal;

/* 矢印 */
arrowLeft.onclick = e => {
  e.stopPropagation();
  currentIndex = (currentIndex - 1 + productListSorted.length) % productListSorted.length;
  updateModal();
};

arrowRight.onclick = e => {
  e.stopPropagation();
  currentIndex = (currentIndex + 1) % productListSorted.length;
  updateModal();
};

/* 背景クリック */
modal.onclick = e => {
  if (e.target === modal) closeIngredientsModal();
};

/* キーボード */
document.addEventListener("keydown", e => {
  if (modal.style.display !== "flex") return;
  if (e.key === "ArrowLeft") arrowLeft.click();
  if (e.key === "ArrowRight") arrowRight.click();
  if (e.key === "Escape" || e.key === "Enter") closeIngredientsModal();
});


/* ===== スマホ スワイプ対応 ===== */
let touchStartX = 0;
let touchEndX = 0;

modal.addEventListener("touchstart", e => {
  touchStartX = e.changedTouches[0].screenX;
}, { passive: true });

modal.addEventListener("touchend", e => {
  touchEndX = e.changedTouches[0].screenX;
  handleSwipe();
}, { passive: true });

function handleSwipe() {
  const diff = touchStartX - touchEndX;

  // 50px以上動いたらスワイプと判定
  if (Math.abs(diff) < 50) return;

  if (diff > 0) {
    // 左スワイプ → 次
    currentIndex =
      (currentIndex + 1) % productListSorted.length;
  } else {
    // 右スワイプ → 前
    currentIndex =
      (currentIndex - 1 + productListSorted.length) %
      productListSorted.length;
  }

  updateModal();
}

const swipeArea = modalImage;

swipeArea.addEventListener("touchstart", e => {
  touchStartX = e.changedTouches[0].screenX;
}, { passive: true });

swipeArea.addEventListener("touchend", e => {
  touchEndX = e.changedTouches[0].screenX;
  handleSwipe();
}, { passive: true });
