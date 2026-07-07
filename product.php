<?php
include 'header.php';
include 'data/products.php';

// Validate and fetch product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($products[$product_id])) {
    // Elegant fallback if product does not exist
    echo "<div class='container section' style='text-align: center;'>
            <h2 class='section-title'>Product Not Found</h2>
            <p style='margin-bottom: 2rem;'>The product you are looking for is either unavailable or has been archived.</p>
            <a href='collections.php' class='btn btn-solid'>Back to Shop</a>
          </div>";
    include 'footer.php';
    exit;
}

$product = $products[$product_id];
$whatsapp_message = rawurlencode("Hello Anusha Reddy Couture, I am interested in inquiring about the product: '" . $product['name'] . "' (" . $product['category'] . "). Please let me know the customization options and availability.");
?>

<section class="section container">
    <!-- Breadcrumb -->
    <p style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: var(--spacing-md); color: var(--text-muted);">
        <a href="index.php">Home</a> &nbsp;/&nbsp; 
        <a href="collections.php">Shop</a> &nbsp;/&nbsp; 
        <span style="color: var(--accent-gold);"><?php echo htmlspecialchars($product['name']); ?></span>
    </p>

    <!-- Product Layout Grid -->
    <div class="product-detail-layout">
        <!-- Gallery / Multi-Image Showcase -->
        <div class="product-gallery">
            <div class="gallery-main-wrapper">
                <img id="mainProductImage" src="<?php echo htmlspecialchars($product['images'][0] ?? 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&q=80&w=800'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-main-image zoomable">
            </div>
            
            <?php if (isset($product['images']) && count($product['images']) > 1): ?>
                <div class="gallery-thumbnails">
                    <?php foreach ($product['images'] as $index => $img_url): ?>
                        <div class="thumb-wrapper <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeGalleryImage('<?php echo htmlspecialchars($img_url); ?>', this)">
                            <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Design detail view">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Details Info -->
        <div class="product-info-panel" style="font-family: var(--font-sans);">
            <p class="product-category" style="font-family: var(--font-sans);"><?php echo htmlspecialchars($product['category']); ?> Collection</p>
            <h1 class="product-name" style="font-family: var(--font-sans); font-weight: 500; font-size: 2rem; letter-spacing: 0.02em; margin-bottom: 0.8rem; line-height: 1.3; color: var(--text-primary);"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="product-price-block price-block-large" style="font-family: var(--font-sans);">
                <?php if ($product['price_numeric'] < $product['original_price_numeric']): 
                    $discount_pct = round((($product['original_price_numeric'] - $product['price_numeric']) / $product['original_price_numeric']) * 100);
                ?>
                    <div class="sale-price-row" style="font-family: var(--font-sans);">
                        <span class="discount-badge" style="font-family: var(--font-sans);">-<?php echo $discount_pct; ?>%</span>
                        <span class="current-price" style="font-family: var(--font-sans);"><span class="currency-symbol">₹</span><?php echo number_format($product['price_numeric']); ?></span>
                    </div>
                    <div class="original-price-row" style="font-family: var(--font-sans);">
                        Original Price: <span class="strikethrough-price" style="font-family: var(--font-sans);">₹<?php echo number_format($product['original_price_numeric']); ?></span>
                    </div>
                <?php else: ?>
                    <span class="normal-price" style="font-family: var(--font-sans);"><span class="currency-symbol">₹</span><?php echo number_format($product['price_numeric']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="product-description-text" style="font-family: var(--font-sans);">
                <p style="margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.8; font-weight: 300; color: var(--text-muted);">
                    <?php echo htmlspecialchars($product['description']); ?>
                </p>
            </div>

            <!-- Collapsible Premium Accordions Group -->
            <div class="product-accordions-group">
                <!-- 1. PRODUCT DETAILS ACCORDION -->
                <details class="product-accordion" open>
                    <summary>Product Details</summary>
                    <div class="accordion-content">
                        <?php if (isset($product['details']) && is_array($product['details'])): ?>
                            <ul>
                                <?php foreach ($product['details'] as $detail): ?>
                                    <li><?php echo htmlspecialchars($detail); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No specifications are currently available for this dress.</p>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- 2. SHIPPING, PACKAGING & RETURNS ACCORDION -->
                <details class="product-accordion">
                    <summary>Shipping, Packaging & Returns</summary>
                    <div class="accordion-content">
                        <p style="margin-bottom: 0.5rem;"><strong>Delivery Time:</strong> 8 to 12 weeks from purchase verification.</p>
                        <p>We ship worldwide. All custom bridal and bespoke apparel are packed in luxury boxes and handled by specialized clothing shipping partners. Returns are not accepted for custom-sized outfits.</p>
                    </div>
                </details>

                <!-- 3. DISCLAIMER ACCORDION -->
                <details class="product-accordion">
                    <summary>Disclaimer</summary>
                    <div class="accordion-content">
                        <p>Colors of the fabric might slightly vary due to professional photographic lighting setup and browser display calibrations. Handcrafted work features unique individual stitches and minor pattern changes, which celebrate authentic handwork craft.</p>
                    </div>
                </details>

                <!-- 4. LEGAL ACCORDION -->
                <details class="product-accordion">
                    <summary>Legal</summary>
                    <div class="accordion-content">
                        <p>All catalog designs and product photos are private intellectual property of Anusha Reddy Couture. Unauthorised distribution, printing copy designs, or duplication is subject to commercial copyright legal guidelines.</p>
                    </div>
                </details>
            </div>

            <!-- Conversion Call to Actions -->
            <div class="product-actions">
                <!-- WhatsApp Chat Button (Immediate Inquiry) -->
                <a href="https://wa.me/917702137501?text=<?php echo $whatsapp_message; ?>" target="_blank" class="btn btn-gold btn-full" style="display: flex; align-items: center; justify-content: center; gap: 0.8rem; font-weight: 500;">
                    <i class="fab fa-whatsapp" style="font-size: 1.3rem;"></i> Inquire on WhatsApp
                </a>
                
                <!-- Studio Booking Button (Custom Fitting / Trial) -->
                <a href="contact.php?product=<?php echo $product_id; ?>" class="btn btn-outline btn-full">
                    Book a Fitting
                </a>
            </div>
            
            <!-- Extra info -->
            <p style="font-size: 0.75rem; text-align: center; color: var(--text-muted); margin-top: 1rem; font-style: italic;">
                *For custom sizes, measurements, or other options, please contact us on WhatsApp or Book a Fitting.
            </p>
        </div>
    </div>
</section>

<script>
function changeGalleryImage(url, element) {
    document.getElementById('mainProductImage').src = url;
    const thumbs = document.querySelectorAll('.thumb-wrapper');
    thumbs.forEach(t => t.classList.remove('active'));
    element.classList.add('active');
}
</script>

<?php
include 'footer.php';
?>
