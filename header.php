<?php
// Determine the current page for active nav states
$current_page = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/data/products.php';

// Default SEO Metadata
$page_title = "Anusha Reddy Couture | Premium Bridal & Festive Wear";
$page_desc = "Anusha Reddy Couture - Premium Bridal & Festive Wear. Handcrafted bespoke designer lehengas, wedding sarees, and luxury Indian garments in Hyderabad.";
$page_keywords = "Anusha Reddy Couture, designer bridal wear, luxury lehenga, wedding sarees, custom fittings Hyderabad, Jubilee Hills boutique";

// Page-specific SEO Metadata Overrides
if ($current_page === 'contact.php') {
    $page_title = "Book a Fitting | Anusha Reddy Couture";
    $page_desc = "Schedule a private bridal fitting or virtual consultation at Anusha Reddy Couture in Jubilee Hills, Hyderabad. Customize your dream outfit.";
    $page_keywords = "book designer fitting, bridal consultation, virtual styling, luxury custom clothing, Hyderabad boutique appointment";
} elseif ($current_page === 'collections.php') {
    $selected_category = isset($_GET['category']) ? trim($_GET['category']) : 'All';
    $category_title = ($selected_category !== 'All') ? htmlspecialchars($selected_category) : 'All Collections';
    $page_title = "Shop {$category_title} | Anusha Reddy Couture";
    $page_desc = "Explore our premium {$category_title} collection. Handcrafted custom lehengas, luxury sarees, Anarkalis, and fusion wear.";
    $page_keywords = "shop " . htmlspecialchars($selected_category) . ", designer clothing, custom lehengas online, luxury sarees shopping, bridal wear catalog";
} elseif ($current_page === 'product.php') {
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (isset($products[$product_id])) {
        $product = $products[$product_id];
        $page_title = "{$product['name']} | Anusha Reddy Couture";
        $page_desc = "Discover {$product['name']}. " . strip_tags($product['description']);
        $page_keywords = "{$product['name']}, {$product['category']}, custom bridal wear, bespoke couture, luxury Indian clothing";
    }
} elseif ($current_page === 'story.php') {
    $page_title = "Our Story | Anusha Reddy Couture";
    $page_desc = "Learn about the journey of Anusha Reddy Couture. From two sewing machines to a premier luxury boutique in Hyderabad celebrating Indian craftsmanship.";
    $page_keywords = "Anusha Reddy Story, fashion designer journey, Indian craftsmanship, luxury bridal boutique, Hyderabad couture house history";
} elseif ($current_page === 'thank-you.php') {
    $page_title = "Thank You | Anusha Reddy Couture";
    $page_desc = "Thank you for contacting Anusha Reddy Couture. We are redirecting you to complete your custom fitting details.";
    $page_keywords = "thank you, booking submission, redirecting";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1701135914496775');
    fbq('track', 'PageView');

    // Page-specific Event Tracking
    <?php if ($current_page === 'thank-you.php'): ?>
        fbq('track', 'Lead', { content_name: 'Book a Fitting' });
    <?php elseif ($current_page === 'collections.php'): ?>
        fbq('track', 'ViewContent', { 
            content_category: 'Collections',
            content_name: '<?php echo isset($_GET['category']) ? addslashes($_GET['category']) : 'Shop All'; ?>' 
        });
    <?php elseif ($current_page === 'product.php'): 
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $product_name = isset($products[$product_id]) ? $products[$product_id]['name'] : 'Unknown Product';
    ?>
        fbq('track', 'ViewContent', { 
            content_type: 'product',
            content_ids: ['<?php echo $product_id; ?>'],
            content_name: '<?php echo addslashes($product_name); ?>'
        });
    <?php elseif ($current_page === 'story.php'): ?>
        fbq('track', 'ViewContent', { content_name: 'Our Story' });
    <?php endif; ?>

    // Global WhatsApp click listener
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            var target = e.target.closest('a');
            if (target && target.href && target.href.indexOf('wa.me') !== -1) {
                fbq('trackCustom', 'WhatsAppClick');
            }
        });
    });
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1701135914496775&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="nav-toggle" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            
<?php
$categories_list = db_get_categories();
$parent_categories = [];
$subcategories = [];
foreach ($categories_list as $cat) {
    if ($cat['parent'] === null || $cat['parent'] === '') {
        $parent_categories[] = $cat;
    } else {
        $subcategories[$cat['parent']][] = $cat;
    }
}
$is_collections_active = false;
if (isset($_GET['category'])) {
    $cat_name = $_GET['category'];
    if (strcasecmp($cat_name, 'Collections') === 0) {
        $is_collections_active = true;
    } else {
        foreach ($categories_list as $c) {
            if (strcasecmp($c['name'], $cat_name) === 0 && $c['parent'] === 'Collections') {
                $is_collections_active = true;
                break;
            }
        }
    }
}
?>
            <nav class="nav-menu" id="navMenu">
                <ul>
                    <li><a href="index.php" class="<?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>">Home</a></li>
                    
                    <li class="dropdown-item">
                        <div class="dropdown-header-link">
                            <a href="collections.php" class="shop-trigger-link <?php echo ($current_page == 'collections.php' || $current_page == 'product.php') ? 'active' : ''; ?>">Shop</a>
                            <span class="menu-toggle-btn" data-target="shop-submenu"><i class="fas fa-chevron-down toggle-icon"></i></span>
                        </div>
                        <ul class="dropdown-menu" id="shop-submenu">
                            <?php foreach ($parent_categories as $parent): ?>
                                <li class="dropdown-submenu">
                                    <div class="submenu-header-link">
                                        <a href="collections.php?category=<?php echo urlencode($parent['name']); ?>"><?php echo htmlspecialchars($parent['name']); ?></a>
                                        <?php if (isset($subcategories[$parent['name']])): ?>
                                            <span class="menu-toggle-btn" data-target="sub-<?php echo urlencode($parent['name']); ?>"><i class="fas fa-chevron-down toggle-icon"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (isset($subcategories[$parent['name']])): ?>
                                        <ul class="submenu" id="sub-<?php echo urlencode($parent['name']); ?>">
                                            <?php foreach ($subcategories[$parent['name']] as $sub): ?>
                                                <li><a href="collections.php?category=<?php echo urlencode($sub['name']); ?>"><?php echo htmlspecialchars($sub['name']); ?></a></li>
                                            <?php endforeach; ?>
                                            <li><a href="collections.php?category=<?php echo urlencode($parent['name']); ?>" style="font-weight: 500;">Shop All <?php echo htmlspecialchars($parent['name']); ?></a></li>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="dropdown-item">
                        <div class="dropdown-header-link">
                            <a href="collections.php?category=Collections" class="shop-trigger-link <?php echo $is_collections_active ? 'active' : ''; ?>">Collections</a>
                            <span class="menu-toggle-btn" data-target="collections-submenu"><i class="fas fa-chevron-down toggle-icon"></i></span>
                        </div>
                        <ul class="dropdown-menu" id="collections-submenu">
                            <?php 
                            if (isset($subcategories['Collections'])) {
                                foreach ($subcategories['Collections'] as $sub) {
                                    echo '<li><a href="collections.php?category=' . urlencode($sub['name']) . '">' . htmlspecialchars($sub['name']) . '</a></li>';
                                }
                            }
                            ?>
                            <li><a href="collections.php?category=Collections" style="font-weight: 500; border-top: 1px solid var(--border-color); margin-top: 0.5rem; padding-top: 0.5rem;">Shop All Collections</a></li>
                        </ul>
                    </li>
                    <li><a href="story.php" class="<?php echo ($current_page == 'story.php') ? 'active' : ''; ?>">Our Story</a></li>
                </ul>
            </nav>
            
            <div class="logo">
                <a href="index.php">
                    <img src="uploads/logo.png" alt="Anusha Reddy Couture" class="logo-img">
                </a>
            </div>

            <div class="header-actions">
                <a href="contact.php" class="btn btn-maroon header-btn">Book Fitting</a>
                <a href="https://wa.me/917702137501" target="_blank" class="whatsapp-icon-btn" title="Chat on WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </header>
    <div class="header-spacer"></div>
