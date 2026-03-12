<?php
// surprise.php
$pageTitle = 'Surprise Me';
require_once 'includes/header.php';
?>
<meta name="site-url" content="<?= SITE_URL ?>">
<div style="background:linear-gradient(135deg,#ff9f1c,#ffbf69);padding:4rem 0 3rem;text-align:center">
  <div class="container">
    <div style="font-size:5rem;animation:floatAnim 3s ease-in-out infinite">🎲</div>
    <h1 style="font-family:'Playfair Display',serif;color:white;font-size:3rem;margin:1rem 0 0.5rem">Surprise Me!</h1>
    <p style="color:rgba(255,255,255,0.85);font-size:1.1rem">Can't decide what to eat? Let us pick the perfect meal for you!</p>
  </div>
</div>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card-fe p-4">
        <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;text-align:center">Set Your Preferences</h5>
        <div class="form-group mb-3">
          <label>Budget</label>
          <select id="budget" class="form-control-fe">
            <option value="100">Under ₹100</option>
            <option value="200" selected>Under ₹200</option>
            <option value="300">Under ₹300</option>
            <option value="500">Under ₹500</option>
            <option value="999">Any Budget</option>
          </select>
        </div>
        <div class="form-group mb-3">
          <label>Food Type</label>
          <div class="d-flex gap-3 mt-1">
            <label style="cursor:pointer;display:flex;align-items:center;gap:0.5rem">
              <input type="radio" name="type" value="veg" checked> 🟢 Veg
            </label>
            <label style="cursor:pointer;display:flex;align-items:center;gap:0.5rem">
              <input type="radio" name="type" value="nonveg"> 🔴 Non-Veg
            </label>
            <label style="cursor:pointer;display:flex;align-items:center;gap:0.5rem">
              <input type="radio" name="type" value="any"> 🍽️ Any
            </label>
          </div>
        </div>
        <div class="form-group mb-4">
          <label>Spice Level</label>
          <select id="spice" class="form-control-fe">
            <option value="mild">🌿 Mild</option>
            <option value="medium" selected>🌶️ Medium</option>
            <option value="spicy">🔥 Spicy</option>
            <option value="any">⚡ Any</option>
          </select>
        </div>
        <button id="surpriseBtn" class="surprise-btn w-100" onclick="doSurprise()">🎲 Surprise Me!</button>
      </div>
      <div id="surpriseResults"></div>
    </div>
  </div>
</div>
<script>
function doSurprise() {
    const budget = document.getElementById('budget').value;
    const type   = document.querySelector('input[name="type"]:checked').value;
    const spice  = document.getElementById('spice').value;
    loadSurprise(budget, type, spice);
}
</script>
<?php require_once 'includes/footer.php'; ?>
