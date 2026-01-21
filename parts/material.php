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

     <!-- アレルギー表示 -->
    <div class="allergy-area">  
      <h3 class="allergy-title">アレルゲン２８品目中</h3>
      <p id="modalAllergies" class="allergy-list"></p>
    </div>  
  </div>
</div>


<script>
  window.productList = <?= json_encode(
    array_map(function ($p) {
      return [
        'personal_code' => $p['personal_code'],
        'name'          => $p['name'],
        'material1'     => $p['material1'] ?? '',
        'material2'     => $p['material2'] ?? '',
        'image'         => "../origin/images/" . basename($p['image_path'])
      ];
    }, $product),
    JSON_UNESCAPED_UNICODE
  ) ?>;


  window.allergyMaster = <?= json_encode($allergyMaster, JSON_UNESCAPED_UNICODE) ?>;

  //アレルギー抽出用の JS 関数を追加
  function extractAllergiesJS(materialText, allergyMaster) {
  return allergyMaster.filter(allergy =>
    materialText.includes(allergy)
  );
}
</script>
