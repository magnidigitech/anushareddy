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
            
            <nav class="nav-menu" id="navMenu">
                <ul>
                    <li><a href="index.php" class="<?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="collections.php" class="<?php echo ($current_page == 'collections.php' || $current_page == 'product.php') ? 'active' : ''; ?>">Shop</a></li>
                    <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Book Fitting</a></li>
                </ul>
            </nav>
            
            <div class="logo">
                <a href="index.php">
                    <img src="uploads/logo.png" alt="Anusha Reddy Couture" class="logo-img">
                </a>
            </div>

            <div class="header-actions">
                <a href="contact.php" class="btn btn-outline header-btn">Book Fitting</a>
                <a href="https://wa.me/917702137501" target="_blank" class="whatsapp-icon-btn" title="Chat on WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
    </header>
    <div class="header-spacer"></div>
