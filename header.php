<?php
// Determine the current page for active nav states
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Anusha Reddy Couture - Premium Bridal & Festive Wear. Custom Clothing Designs.">
    <title>Anusha Reddy Couture | Beautiful Dresses</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
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
require_once __DIR__ . '/data/db.php';
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
                    <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Book Fitting</a></li>
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
