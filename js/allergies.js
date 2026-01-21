
function openIngredientsModal(el) {
  const name = el.closest(".product-card")
                 .querySelector(".product-name").textContent;

  const material1 = el.dataset.material1 || "";
  const material2 = el.dataset.material2 || "";

  const materialsText = `${material1} ${material2}`.trim();

  document.getElementById("modalName").textContent = name;
  document.getElementById("modalMaterials").innerHTML =
    material2 ? `${material1}<br>${material2}` : material1;

  renderAllergyTable(materialsText);

  document.getElementById("ingredientsModal").style.display = "block";
}




function renderAllergyTable(materialText) {
  const header = document.getElementById("allergyHeader");
  const body = document.getElementById("allergyBody");

  header.innerHTML = "";
  body.innerHTML = "";


  Object.values(allergies).forEach(a => {

    // 判定
    let hit = materialText.includes(a.name);

    if (!hit && a.aliases) {
      hit = a.aliases.some(alias => materialText.includes(alias));
    }

    // 含まれていないものはスキップ
    if (!hit) return;

    // ヘッダ
    const th = document.createElement("th");
    th.textContent = a.name;
    header.appendChild(th);

    // 中身（全部◯）
    const td = document.createElement("td");
    td.textContent = "◯";
    td.className = "allergy-ok";
    body.appendChild(td);
  });

  // 何もなかった場合
  if (header.children.length === 0) {
    const th = document.createElement("th");
    th.textContent = "該当なし";
    header.appendChild(th);

    const td = document.createElement("td");
    td.textContent = "アレルギー物質は含まれていません";
    body.appendChild(td);
  }
  console.log(allergies);

}


