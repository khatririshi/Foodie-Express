<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
$db = getDB();
$restaurants = $db->query("SELECT * FROM restaurants WHERE is_open=1 ORDER BY rating DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$topFoods = $db->query("SELECT f.*, r.name as restaurant_name FROM food_items f JOIN restaurants r ON f.restaurant_id=r.id WHERE f.is_available=1 ORDER BY f.rating DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$db->close();
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 hero-content">
        <div class="hero-badge">🚀 Smart Food Delivery Platform</div>
        <h1>Food That Fits Your <span class="highlight">Life & Goals</span></h1>
        <p>AI-powered recommendations, group ordering, emergency delivery and much more. Not just food — your complete dining experience.</p>
        <div class="hero-actions d-flex flex-wrap gap-3">
          <a href="<?= SITE_URL ?>/restaurants.php" class="btn-primary-fe">
            <i class="bi bi-compass"></i> Explore Restaurants
          </a>
          <a href="<?= SITE_URL ?>/ai-diet.php" class="btn-outline-fe">
            🧠 AI Diet Plan
          </a>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-num">50+</div>
            <div class="hero-stat-label">Restaurants</div>
          </div>
          <div>
            <div class="hero-stat-num">500+</div>
            <div class="hero-stat-label">Dishes</div>
          </div>
          <div>
            <div class="hero-stat-num">25min</div>
            <div class="hero-stat-label">Avg Delivery</div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 text-center position-relative">
        <div class="hero-image-wrap">
          <div style="font-size:14rem;line-height:1;filter:drop-shadow(0 30px 60px rgba(0,0,0,0.4))">🍱</div>
          <div class="hero-food-card card-1">
            <div class="d-flex align-items-center gap-2">
              <span style="font-size:2rem">🍕</span>
              <div>
                <div style="color:white;font-weight:700;font-size:0.85rem">Pizza Paradise</div>
                <div style="color:rgba(255,255,255,0.6);font-size:0.75rem">⭐ 4.8 · 25 mins</div>
              </div>
            </div>
          </div>
          <div class="hero-food-card card-2">
            <div style="text-align:center">
              <div style="color:#2ec4b6;font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:1px">🧠 AI Suggests</div>
              <div style="color:white;font-weight:600;font-size:0.85rem;margin-top:0.3rem">High Protein Bowl</div>
              <div style="color:rgba(255,255,255,0.6);font-size:0.75rem">420 cal · ₹280</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SEARCH -->
<section class="search-section">
  <div class="container">
    <div class="search-wrap">
      <i class="bi bi-search search-icon"></i>
      <input type="text" id="globalSearch" placeholder="Search restaurants, dishes, cuisines..." />
      <button class="btn-search" onclick="performSearch()">Search</button>
    </div>
    <div class="filter-chips">
      <div class="chip active" onclick="filterFood('all')">All</div>
      <div class="chip" onclick="filterFood('veg')">🟢 Veg</div>
      <div class="chip" onclick="filterFood('nonveg')">🔴 Non-Veg</div>
      <div class="chip" onclick="filterFood('healthy')">🥗 Healthy</div>
      <div class="chip" onclick="filterFood('fast')">⚡ Fast Food</div>
      <div class="chip" onclick="filterFood('under200')">Under ₹200</div>
    </div>
  </div>
</section>

<!-- SMART FEATURES -->
<section class="features-section bg-silver">
  <div class="container">
    <div class="section-title">
      <h2>Smart Features, Just for You</h2>
      <p>FoodieExpress goes beyond ordinary food ordering</p>
      <div class="title-line"></div>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="feature-card">
          <div class="feature-icon">🧠</div>
          <h4>AI Diet Assistant</h4>
          <p>Get meal recommendations based on your health goals, weight targets, and conditions like PCOS or diabetes.</p>
          <a href="<?= SITE_URL ?>/ai-diet.php" class="text-primary-fe fw-600 small">Try Now →</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card">
          <div class="feature-icon">🎲</div>
          <h4>Surprise Me</h4>
          <p>Can't decide what to eat? Tell us your budget and preferences, we'll pick the best meal for you!</p>
          <a href="<?= SITE_URL ?>/surprise.php" class="text-primary-fe fw-600 small">Surprise Me →</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card">
          <div class="feature-icon">👥</div>
          <h4>Group Ordering</h4>
          <p>Order together with friends! Everyone adds their items and we auto-split the bill. Perfect for hostels!</p>
          <a href="<?= SITE_URL ?>/group-order.php" class="text-primary-fe fw-600 small">Create Group →</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card">
          <div class="feature-icon">🚨</div>
          <h4>Emergency Mode</h4>
          <p>Late night hunger? Emergency mode shows only open restaurants with the fastest delivery times.</p>
          <a href="<?= SITE_URL ?>/emergency.php" class="text-primary-fe fw-600 small">Activate →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TOP RESTAURANTS -->
<section class="section-pad">
  <div class="container">
    <div class="section-title">
      <h2>Top Restaurants Near You</h2>
      <p>Handpicked for quality, taste, and fast delivery</p>
      <div class="title-line"></div>
    </div>
    <div class="row g-4">
      <?php foreach ($restaurants as $r): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-fe restaurant-card">
          <div style="position:relative">
            <div style="background:linear-gradient(135deg,#1a1a2e,#16213e);height:200px;display:flex;align-items:center;justify-content:center;font-size:5rem">
              <?= in_array($r['cuisine_type'], ['Healthy']) ? '🥗' : (in_array($r['cuisine_type'], ['Italian']) ? '🍕' : (in_array($r['cuisine_type'], ['Fast Food']) ? '🍔' : '🍛')) ?>
            </div>
            <span class="position-absolute top-0 start-0 m-2 <?= $r['is_open'] ? 'open-badge' : 'closed-badge' ?>">
              <?= $r['is_open'] ? '● Open' : '● Closed' ?>
            </span>
            <?php if ($r['is_late_night']): ?>
            <span class="position-absolute top-0 end-0 m-2" style="background:rgba(230,57,70,0.9);color:white;padding:0.2rem 0.6rem;border-radius:50px;font-size:0.75rem;font-weight:700">🌙 Late Night</span>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <div class="restaurant-name"><?= htmlspecialchars($r['name']) ?></div>
            <div class="cuisine-tag"><?= htmlspecialchars($r['cuisine_type']) ?></div>
            <div class="meta-row">
              <span class="rating-badge">⭐ <?= $r['rating'] ?></span>
              <span class="meta-item"><i class="bi bi-clock"></i> <?= $r['delivery_time'] ?> mins</span>
              <span class="meta-item"><i class="bi bi-bag"></i> ₹<?= number_format($r['min_order'], 0) ?> min</span>
            </div>
            <a href="<?= SITE_URL ?>/menu.php?restaurant=<?= $r['id'] ?>" class="btn-primary-fe mt-3 w-100 justify-content-center">
              View Menu
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= SITE_URL ?>/restaurants.php" class="btn-outline-fe" style="color:var(--text);border-color:var(--silver-light)">
        View All Restaurants <i class="bi bi-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- POPULAR DISHES -->
<section class="section-pad bg-silver">
  <div class="container">
    <div class="section-title">
      <h2>Most Loved Dishes</h2>
      <p>Crowd favorites with the highest ratings</p>
      <div class="title-line"></div>
    </div>
    <div class="row g-0">
      <div class="col-lg-8">
        <?php foreach (array_slice($topFoods, 0, 5) as $food): ?>
        <div class="food-card">
          <div style="background:linear-gradient(135deg,<?= $food['is_veg'] ? '#e8f5e9,#c8e6c9' : '#fce4ec,#f8bbd0' ?>);width:110px;height:100px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:3rem;flex-shrink:0">
            <?= $food['category'] === 'Pizza' ? '🍕' : ($food['category'] === 'Burgers' ? '🍔' : ($food['category'] === 'Bowls' ? '🥗' : ($food['category'] === 'Salads' ? '🥙' : '🍛'))) ?>
          </div>
          <div class="food-info">
            <div class="d-flex align-items-center gap-2 mb-1">
              <div class="<?= $food['is_veg'] ? 'veg-dot' : 'nonveg-dot' ?>"></div>
              <div class="food-name"><?= htmlspecialchars($food['name']) ?></div>
            </div>
            <div class="food-desc"><?= htmlspecialchars(substr($food['description'], 0, 80)) ?>...</div>
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="food-price">₹<?= number_format($food['price'], 0) ?></span>
                <span class="text-muted small ms-2"><?= $food['restaurant_name'] ?></span>
              </div>
              <button class="btn-add-cart" onclick="addToCart(<?= $food['id'] ?>, '<?= addslashes($food['name']) ?>', <?= $food['price'] ?>)">+ Add</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="col-lg-4 ps-lg-4">
        <!-- AI DIET PROMO -->
        <div class="diet-assistant mb-4">
          <div class="ai-badge">✨ AI Powered</div>
          <h4 style="color:white;font-family:'Playfair Display',serif;margin-bottom:0.5rem">Smart Diet Planner</h4>
          <p style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin-bottom:1.5rem">Tell us your health goals and we'll suggest the perfect meals for you — whether you're managing PCOS, diabetes, or just staying fit.</p>
          <a href="<?= SITE_URL ?>/ai-diet.php" class="btn-primary-fe">Get My Plan →</a>
        </div>
        <!-- SURPRISE ME PROMO -->
        <div class="surprise-section">
          <div style="font-size:3rem;margin-bottom:0.5rem">🎲</div>
          <h4 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem">Can't Decide?</h4>
          <p style="font-size:0.9rem;margin-bottom:1.5rem;opacity:0.9">Let us pick the perfect meal for you based on your budget!</p>
          <a href="<?= SITE_URL ?>/surprise.php" class="surprise-btn">Surprise Me!</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section-pad">
  <div class="container">
    <div class="section-title">
      <h2>How FoodieExpress Works</h2>
      <p>Order your favourite food in 3 simple steps</p>
      <div class="title-line"></div>
    </div>
    <div class="row g-4 text-center">
      <div class="col-md-4">
        <div style="font-size:4rem;margin-bottom:1rem">📍</div>
        <h4>1. Choose Restaurant</h4>
        <p class="text-muted">Browse top-rated restaurants near you and explore their full menus.</p>
      </div>
      <div class="col-md-4">
        <div style="font-size:4rem;margin-bottom:1rem">🛒</div>
        <h4>2. Add to Cart</h4>
        <p class="text-muted">Select your favourite dishes, customize and add them to your cart.</p>
      </div>
      <div class="col-md-4">
        <div style="font-size:4rem;margin-bottom:1rem">🚀</div>
        <h4>3. Fast Delivery</h4>
        <p class="text-muted">Track your order in real-time as it gets prepared and delivered.</p>
      </div>
    </div>
  </div>
</section>

<script>
function filterFood(type) {
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    // Redirect to menu with filter
    if (type !== 'all') window.location.href = '<?= SITE_URL ?>/menu.php?filter=' + type;
}
</script>

<?php require_once 'includes/footer.php'; ?>
