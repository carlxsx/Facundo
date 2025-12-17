<?php
require_once 'config/init.php';

// Get featured cars
$stmt = $db->prepare("
    SELECT c.*, ci.image_path, u.full_name as seller_name
    FROM cars c
    LEFT JOIN car_images ci ON c.car_id = ci.car_id AND ci.is_primary = 1
    LEFT JOIN users u ON c.seller_id = u.user_id
    WHERE c.status = 'approved' AND c.is_featured = 1
    ORDER BY c.created_at DESC
    LIMIT 4
");
$stmt->execute();
$featured_cars = $stmt->fetchAll();

// Get recent cars
$stmt = $db->prepare("
    SELECT c.*, ci.image_path, u.full_name as seller_name
    FROM cars c
    LEFT JOIN car_images ci ON c.car_id = ci.car_id AND ci.is_primary = 1
    LEFT JOIN users u ON c.seller_id = u.user_id
    WHERE c.status = 'approved'
    ORDER BY c.created_at DESC
    LIMIT 8
");
$stmt->execute();
$recent_cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Buy & Sell Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Find Your Dream Car Today</h1>
                    <p class="lead mb-4">Browse thousands of quality cars from trusted sellers. Buy, sell, or trade with confidence.</p>
                    <a href="browse.php" class="btn btn-lg me-2" style="background-color: #fff; color: #000; border: 2px solid #fff; font-weight: 700;">Browse Cars</a>
                    <a href="sell.php" class="btn btn-lg" style="background-color: transparent; color: #fff; border: 2px solid #fff; font-weight: 700;">Sell Your Car</a>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/hero-car.png" alt="Cars" class="img-fluid" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section py-5 bg-light">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="browse.php" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <select name="brand" class="form-select">
                                    <option value="">All Brands</option>
                                    <option value="Toyota">Toyota</option>
                                    <option value="Honda">Honda</option>
                                    <option value="Ford">Ford</option>
                                    <option value="Mitsubishi">Mitsubishi</option>
                                    <option value="Mazda">Mazda</option>
                                    <option value="Nissan">Nissan</option>
                                    <option value="Hyundai">Hyundai</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price Range</label>
                                <select name="price" class="form-select">
                                    <option value="">Any Price</option>
                                    <option value="0-500000">Under ₱500,000</option>
                                    <option value="500000-1000000">₱500k - ₱1M</option>
                                    <option value="1000000-1500000">₱1M - ₱1.5M</option>
                                    <option value="1500000-2000000">₱1.5M - ₱2M</option>
                                    <option value="2000000-999999999">Above ₱2M</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Condition</label>
                                <select name="condition" class="form-select">
                                    <option value="">Any</option>
                                    <option value="New">New</option>
                                    <option value="Used">Used</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn w-100" style="background-color: #000; color: #fff; border: 2px solid #000; font-weight: 700;">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Cars -->
    <section class="featured-section py-5">
        <div class="container">
            <h2 class="text-center mb-5">Featured Cars</h2>
            <div class="row g-4">
                <?php if (count($featured_cars) > 0): ?>
                    <?php foreach ($featured_cars as $car): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="car-card">
                                <div class="car-image">
                                    <?php if ($car['image_path']): ?>
                                        <img src="<?php echo $car['image_path']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                                    <?php else: ?>
                                        <img src="assets/images/no-image.jpg" alt="No image">
                                    <?php endif; ?>
                                    <span class="badge-featured">Featured</span>
                                </div>
                                <div class="car-details p-3">
                                    <h5><?php echo $car['brand'] . ' ' . $car['model']; ?></h5>
                                    <p class="text-muted mb-2"><?php echo $car['year']; ?> • <?php echo number_format($car['mileage']); ?> km</p>
                                    <p class="price"><?php echo formatPrice($car['price']); ?></p>
                                    <div class="d-flex gap-2">
                                        <a href="car-details.php?id=<?php echo $car['car_id']; ?>" class="btn btn-sm flex-fill" style="background-color: #000; color: #fff; border: 2px solid #000; font-weight: 700;">View Details</a>
                                        <button class="btn btn-sm" onclick="toggleFavorite(<?php echo $car['car_id']; ?>)" style="background-color: #fff; color: #000; border: 2px solid #e0e0e0;">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No featured cars available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Recent Listings -->
    <section class="recent-section py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Recent Listings</h2>
            <div class="row g-4">
                <?php foreach ($recent_cars as $car): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="car-card">
                            <div class="car-image">
                                <?php if ($car['image_path']): ?>
                                    <img src="<?php echo $car['image_path']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                                <?php else: ?>
                                    <img src="assets/images/no-image.jpg" alt="No image">
                                <?php endif; ?>
                            </div>
                            <div class="car-details p-3">
                                <h5><?php echo $car['brand'] . ' ' . $car['model']; ?></h5>
                                <p class="text-muted mb-2"><?php echo $car['year']; ?> • <?php echo number_format($car['mileage']); ?> km</p>
                                <p class="price"><?php echo formatPrice($car['price']); ?></p>
                                <a href="car-details.php?id=<?php echo $car['car_id']; ?>" class="btn btn-sm w-100" style="background-color: #000; color: #fff; border: 2px solid #000; font-weight: 700;">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="browse.php" class="btn" style="background-color: transparent; color: #000; border: 2px solid #000; font-weight: 700; padding: 12px 30px;">View All Cars</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <i class="fas fa-shield-alt fa-3x mb-3" style="color: #000;"></i>
                    <h4>Trusted Sellers</h4>
                    <p>All sellers are verified for your safety and security</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-comments fa-3x mb-3" style="color: #000;"></i>
                    <h4>Live Chat Support</h4>
                    <p>Get instant help from our support team</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-car fa-3x mb-3" style="color: #000;"></i>
                    <h4>Quality Cars</h4>
                    <p>Every car is inspected before listing</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/chat-widget.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>