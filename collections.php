<?php
include 'header.php';
include 'data/products.php';

$selected_category = isset($_GET['category']) ? trim($_GET['category']) : 'All';

if ($selected_category !== 'All') {
    $filtered_products = [];
    foreach ($products as $id => $product) {
        $prod_categories = array_map('trim', explode(',', $product['category'] ?? ''));
        $match = false;
        foreach ($prod_categories as $cat) {
            if (strcasecmp($cat, $selected_category) === 0) {
                $match = true;
                break;
            }
        }
        if ($match) {
            $filtered_products[$id] = $product;
        }
    }
    $products = $filtered_products;
}
?>

<section class="section container-fluid">
    <p class="section-subtitle">Our Catalog</p>
    <h1 class="section-title"><?php echo ($selected_category !== 'All') ? htmlspecialchars($selected_category) : 'Our Products'; ?></h1>
    
    <!-- Premium Interactive Search -->
    <div class="search-container">
        <div class="search-input-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by name, category (e.g. Bridal, Pret, Velvet)..." autocomplete="off" aria-label="Search designs">
            <button class="search-btn" type="button" aria-label="Search Button"><i class="fas fa-search"></i></button>
        </div>
        <div id="resultsSummary" class="search-results-summary"></div>
    </div>

    <!-- No Results Placeholder Message (Hidden by default) -->
    <div id="noResultsMessage" class="no-results" style="display: none;">
        <i class="fas fa-search" style="font-size: 2rem; color: var(--accent-gold); margin-bottom: 1rem; display: block;"></i>
        <p>No products match your search query. Please try searching for another style or category.</p>
    </div>

    <!-- Collections Product Grid -->
    <div class="collections-grid" id="productsGrid">
        <?php foreach ($products as $id => $product): ?>
            <a href="product.php?id=<?php echo $id; ?>" class="collection-card" data-category="<?php echo htmlspecialchars($product['category']); ?>">
                <div class="card-image-wrapper">
                    <img src="<?php echo htmlspecialchars($product['images'][0] ?? 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&q=80&w=800'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-image primary" loading="lazy">
                    <?php if (isset($product['images'][1])): ?>
                        <img src="<?php echo htmlspecialchars($product['images'][1]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-image secondary" loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="card-content">
                    <p class="card-category"><?php echo htmlspecialchars($product['category']); ?></p>
                    <h3 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="product-price-block">
                        <?php if ($product['price_numeric'] < $product['original_price_numeric']): 
                            $discount_pct = round((($product['original_price_numeric'] - $product['price_numeric']) / $product['original_price_numeric']) * 100);
                        ?>
                            <div class="sale-price-row">
                                <span class="discount-badge">-<?php echo $discount_pct; ?>%</span>
                                <span class="current-price"><span class="currency-symbol">₹</span><?php echo number_format($product['price_numeric']); ?></span>
                            </div>
                            <div class="original-price-row">
                                Original: <span class="strikethrough-price">₹<?php echo number_format($product['original_price_numeric']); ?></span>
                            </div>
                        <?php else: ?>
                            <span class="normal-price"><span class="currency-symbol">₹</span><?php echo number_format($product['price_numeric']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php
include 'footer.php';
?>
