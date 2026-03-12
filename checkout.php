<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];

// Get cart items
$cartItems = $db->query("SELECT c.*, f.name, f.price, f.image, r.name as restaurant_name, r.delivery_fee 
                          FROM cart c 
                          JOIN food_items f ON c.food_id=f.id 
                          JOIN restaurants r ON f.restaurant_id=r.id 
                          WHERE c.user_id=$uid")->fetch_all(MYSQLI_ASSOC);

if (empty($cartItems)) {
    header('Location: ' . SITE_URL . '/menu.php');
    exit;
}

$subtotal    = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$deliveryFee = $cartItems[0]['delivery_fee'] ?? 30;
$total       = $subtotal + $deliveryFee;

$user = $db->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = sanitize($_POST['address']);
    $payment = sanitize($_POST['payment_method']);
    $special = sanitize($_POST['special_instructions'] ?? '');
    
    if (!$address) {
        $error = 'Please enter delivery address';
    } else {
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, delivery_fee, final_amount, delivery_address, payment_method, special_instructions) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("idddsss", $uid, $subtotal, $deliveryFee, $total, $address, $payment, $special);
        $stmt->execute();
        $orderId = $db->insert_id;
        
        // Insert order items
        foreach ($cartItems as $item) {
            $stmt2 = $db->prepare("INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?,?,?,?)");
            $stmt2->bind_param("iiid", $orderId, $item['food_id'], $item['quantity'], $item['price']);
            $stmt2->execute();
        }
        
        // Clear cart
        $db->query("DELETE FROM cart WHERE user_id=$uid");
        $db->close();
        
        setFlash('success', "Order #$orderId placed successfully! 🎉 Track your order in My Orders.");
        header('Location: ' . SITE_URL . '/orders.php?new=' . $orderId);
        exit;
    }
}
$db->close();
?>
<meta name="site-url" content="<?= SITE_URL ?>">

<div class="container py-5" style="max-width:900px">
  <h2 style="font-family:'Playfair Display',serif;margin-bottom:2rem">🛒 Checkout</h2>
  
  <?php if (isset($error)): ?>
  <div class="alert alert-danger rounded-3 mb-3"><?= $error ?></div>
  <?php endif; ?>
  
  <form method="POST">
    <div class="row g-4">
      <!-- Left: Details -->
      <div class="col-lg-7">
        <!-- Delivery Address -->
        <div class="card-fe p-4 mb-3">
          <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem">📍 Delivery Address</h5>
          <div class="form-group mb-3">
            <label>Full Address</label>
            <textarea name="address" class="form-control-fe" rows="3" placeholder="House/Flat no, Street, Area, City, Pincode" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Special Instructions (Optional)</label>
            <input type="text" name="special_instructions" class="form-control-fe" placeholder="e.g. Ring the bell twice, Leave at door...">
          </div>
        </div>
        
        <!-- Payment -->
        <div class="card-fe p-4">
          <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem">💳 Payment Method</h5>
          <div class="d-flex flex-column gap-2">
            <?php foreach (['cod'=>['💵','Cash on Delivery','Pay when food arrives'],'online'=>['💳','Online Payment','Debit/Credit Card'],'upi'=>['📱','UPI Payment','GPay, PhonePe, Paytm']] as $val=>[$icon,$title,$sub]): ?>
            <label style="cursor:pointer;border:2px solid var(--silver-light);border-radius:var(--radius-sm);padding:1rem;transition:all 0.2s;display:flex;align-items:center;gap:1rem" 
                   onclick="this.style.borderColor='var(--primary)'">
              <input type="radio" name="payment_method" value="<?= $val ?>" <?= $val==='cod'?'checked':'' ?> style="margin:0">
              <span style="font-size:1.5rem"><?= $icon ?></span>
              <div>
                <div style="font-weight:600"><?= $title ?></div>
                <div style="font-size:0.8rem;color:var(--text-muted)"><?= $sub ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      
      <!-- Right: Order Summary -->
      <div class="col-lg-5">
        <div class="card-fe p-4" style="position:sticky;top:80px">
          <h5 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem">📝 Order Summary</h5>
          
          <?php foreach ($cartItems as $item): ?>
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom:1px solid var(--silver-light)">
            <div>
              <div style="font-weight:500;font-size:0.9rem"><?= htmlspecialchars($item['name']) ?></div>
              <div style="font-size:0.78rem;color:var(--text-muted)">x<?= $item['quantity'] ?></div>
            </div>
            <div style="font-weight:600">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
          </div>
          <?php endforeach; ?>
          
          <div class="mt-3">
            <div class="d-flex justify-content-between mb-2" style="font-size:0.9rem">
              <span class="text-muted">Subtotal</span>
              <span>₹<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2" style="font-size:0.9rem">
              <span class="text-muted">Delivery Fee</span>
              <span>₹<?= number_format($deliveryFee, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between pt-2 mt-2" style="border-top:2px solid var(--silver-light);font-weight:800;font-size:1.1rem">
              <span>Total</span>
              <span style="color:var(--primary)">₹<?= number_format($total, 2) ?></span>
            </div>
          </div>
          
          <div class="mt-3 p-3 rounded" style="background:rgba(46,196,182,0.08);border:1px solid rgba(46,196,182,0.2);font-size:0.82rem">
            <span style="color:#2ec4b6;font-weight:700">⏱ Estimated Delivery: 25-35 minutes</span>
          </div>
          
          <button type="submit" class="btn-primary-fe w-100 justify-content-center mt-4" style="padding:1rem">
            Place Order ₹<?= number_format($total, 2) ?> →
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
