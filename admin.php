<?php
session_start();

// 1. Configure Admin Password
define('ADMIN_PASSWORD', 'couture2026'); 

// Require products and database operations
include_once 'data/products.php';
include_once 'data/db.php';

// Authentication Check
$is_authenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle Login Request
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $password = $_POST['password'] ?? '';
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $is_authenticated = true;
    } else {
        $login_error = "Invalid credential. Please try again.";
    }
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION['admin_logged_in'] = false;
    session_destroy();
    header("Location: admin.php");
    exit;
}

$alert_message = '';
$alert_type = ''; // 'success' or 'error'

// Handle Operations (Only if authenticated)
if ($is_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // A. ADD PRODUCT
    if ($action === 'add') {
        $name = strip_tags(trim($_POST['name'] ?? ''));
        $category = strip_tags(trim($_POST['category'] ?? 'Couture'));
        $price_numeric = intval($_POST['price_numeric'] ?? 0);
        $description = strip_tags(trim($_POST['description'] ?? ''));
        $details_raw = strip_tags(trim($_POST['details'] ?? ''));
        
        $details = array_filter(array_map('trim', explode("\n", $details_raw)));
        $uploaded_images = [];

        // Handle file uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['images']['name'][$key];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($ext, $allowed)) {
                        $new_file = uniqid('design_', true) . '.' . $ext;
                        if (move_uploaded_file($tmp_name, $upload_dir . $new_file)) {
                            $uploaded_images[] = 'uploads/' . $new_file;
                        }
                    }
                }
            }
        }

        if (empty($uploaded_images)) {
            $uploaded_images[] = 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&q=80&w=800';
        }

        if (!empty($name) && $price_numeric > 0) {
            // Generate new ID
            $new_id = empty($products) ? 1 : max(array_keys($products)) + 1;
            
            $new_product = [
                'id' => $new_id,
                'name' => $name,
                'category' => $category,
                'price_numeric' => $price_numeric,
                'price' => '₹' . number_format($price_numeric),
                'original_price_numeric' => $price_numeric,
                'images' => $uploaded_images,
                'description' => $description,
                'details' => $details
            ];

            // Update local array and save to products.json
            $products[$new_id] = $new_product;
            if (save_local_products($products)) {
                // Try to create in PostgreSQL
                db_create_product([
                    'name' => $name,
                    'category' => $category,
                    'price_numeric' => $price_numeric,
                    'price' => '₹' . number_format($price_numeric),
                    'original_price_numeric' => $price_numeric,
                    'images' => $uploaded_images,
                    'description' => $description,
                    'details' => $details
                ]);
                $alert_message = "Product added successfully!";
                $alert_type = 'success';
            } else {
                $alert_message = "Failed to save product locally.";
                $alert_type = 'error';
            }
        } else {
            $alert_message = "Please enter a valid product name and numerical price.";
            $alert_type = 'error';
        }
    }

    // B. EDIT PRODUCT
    if ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        if (isset($products[$id])) {
            $name = strip_tags(trim($_POST['name'] ?? ''));
            $category = strip_tags(trim($_POST['category'] ?? 'Couture'));
            $price_numeric = intval($_POST['price_numeric'] ?? 0);
            $description = strip_tags(trim($_POST['description'] ?? ''));
            $details_raw = strip_tags(trim($_POST['details'] ?? ''));
            
            $details = array_filter(array_map('trim', explode("\n", $details_raw)));

            $uploaded_images = $products[$id]['images'] ?? [];
            if (!empty($_POST['image_order'])) {
                $uploaded_images = json_decode($_POST['image_order'], true);
                if (!is_array($uploaded_images)) {
                    $uploaded_images = [];
                }
            }

            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $new_uploaded = [];
                $upload_dir = __DIR__ . '/uploads/';
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES['images']['name'][$key];
                        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (in_array($ext, $allowed)) {
                            $new_file = uniqid('design_', true) . '.' . $ext;
                            if (move_uploaded_file($tmp_name, $upload_dir . $new_file)) {
                                $new_uploaded[] = 'uploads/' . $new_file;
                            }
                        }
                    }
                }
                if (!empty($new_uploaded)) {
                    $uploaded_images = array_merge($uploaded_images, $new_uploaded);
                }
            }

            $products[$id]['name'] = $name;
            $products[$id]['category'] = $category;
            $products[$id]['price_numeric'] = $price_numeric;
            $products[$id]['price'] = '₹' . number_format($price_numeric);
            // If editing, also reset original price to match unless currently on sale
            if (!isset($_POST['is_on_sale'])) {
                $products[$id]['original_price_numeric'] = $price_numeric;
            }
            $products[$id]['images'] = $uploaded_images;
            $products[$id]['description'] = $description;
            $products[$id]['details'] = $details;

            if (save_local_products($products)) {
                // Try updating in PostgreSQL
                db_update_product($id, [
                    'name' => $name,
                    'category' => $category,
                    'price_numeric' => $price_numeric,
                    'price' => '₹' . number_format($price_numeric),
                    'original_price_numeric' => $products[$id]['original_price_numeric'] ?? $price_numeric,
                    'images' => $uploaded_images,
                    'description' => $description,
                    'details' => $details
                ]);
                $alert_message = "Product details updated successfully!";
                $alert_type = 'success';
            } else {
                $alert_message = "Failed to save updates.";
                $alert_type = 'error';
            }
        }
    }

    // C. DELETE PRODUCT
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (isset($products[$id])) {
            unset($products[$id]);
            if (save_local_products($products)) {
                // Try deleting from PostgreSQL
                db_delete_product($id);
                $alert_message = "Product deleted successfully.";
                $alert_type = 'success';
            } else {
                $alert_message = "Error deleting product.";
                $alert_type = 'error';
            }
        }
    }

    // D. BULK DISCOUNT ENGINE
    if ($action === 'bulk_discount') {
        $target_category = $_POST['discount_category'] ?? 'All';
        $discount_pct = intval($_POST['discount_percentage'] ?? 0);

        if ($discount_pct > 0 && $discount_pct <= 90) {
            foreach ($products as $id => $product) {
                // Check if product belongs to targeted category
                if ($target_category === 'All' || strcasecmp($product['category'], $target_category) === 0) {
                    $orig = $product['original_price_numeric'] ?? $product['price_numeric'];
                    $discounted = round($orig * (1 - ($discount_pct / 100)));
                    
                    $products[$id]['original_price_numeric'] = $orig;
                    $products[$id]['price_numeric'] = $discounted;
                    $products[$id]['price'] = '₹' . number_format($discounted) . ' (' . $discount_pct . '% Off)';
                    
                    // Attempt remote update
                    db_update_product($id, $products[$id]);
                }
            }

            if (save_local_products($products)) {
                $alert_message = "Successfully applied a " . $discount_pct . "% discount campaign to " . htmlspecialchars($target_category) . " products!";
                $alert_type = 'success';
            }
        }
    }

    // E. REVERT ALL PRICES
    if ($action === 'revert_discount') {
        foreach ($products as $id => $product) {
            $orig = $product['original_price_numeric'] ?? $product['price_numeric'];
            $products[$id]['price_numeric'] = $orig;
            $products[$id]['price'] = '₹' . number_format($orig);
            
            db_update_product($id, $products[$id]);
        }

        if (save_local_products($products)) {
            $alert_message = "All products reverted back to their original retail prices successfully.";
            $alert_type = 'success';
        }
    }

    // E. ADD CELEBRITY
    if ($action === 'add_celebrity') {
        $name = strip_tags(trim($_POST['name'] ?? ''));
        $image_url = '';

        // Handle image upload
        if (isset($_FILES['celebrity_photo']) && $_FILES['celebrity_photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['celebrity_photo']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['celebrity_photo']['name']);
            $upload_dir = __DIR__ . '/uploads/';
            
            // Create uploads folder if missing
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $dest_path = $upload_dir . $file_name;
            if (move_uploaded_file($file_tmp, $dest_path)) {
                $image_url = 'uploads/' . $file_name;
            }
        }
        
        if (empty($name)) {
            $alert_message = "Please enter the celebrity name.";
            $alert_type = 'error';
        } elseif (empty($image_url)) {
            $alert_message = "Please select/upload a celebrity photo.";
            $alert_type = 'error';
        } else {
            $celebrities = db_get_celebrities();
            $new_id = empty($celebrities) ? 1 : max(array_keys($celebrities)) + 1;
            
            $new_celeb = [
                'id' => $new_id,
                'name' => $name,
                'image' => $image_url
            ];
            
            $celebrities[$new_id] = $new_celeb;
            
            // Sync to database if available
            db_create_celebrity($new_celeb);
            
            if (save_local_celebrities($celebrities)) {
                $alert_message = "Celebrity showcase added successfully.";
                $alert_type = 'success';
            } else {
                $alert_message = "Failed to save celebrity data locally.";
                $alert_type = 'error';
            }
        }
    }

    // F. DELETE CELEBRITY
    if ($action === 'delete_celebrity') {
        $id = intval($_POST['id'] ?? 0);
        $celebrities = db_get_celebrities();
        
        if (isset($celebrities[$id])) {
            // Delete image file if exists
            $img_path = __DIR__ . '/' . $celebrities[$id]['image'];
            if (file_exists($img_path) && is_file($img_path)) {
                @unlink($img_path);
            }
            
            unset($celebrities[$id]);
            db_delete_celebrity($id);
            
            if (save_local_celebrities($celebrities)) {
                $alert_message = "Celebrity showcase removed successfully.";
                $alert_type = 'success';
            } else {
                $alert_message = "Failed to update local celebrity records.";
                $alert_type = 'error';
            }
        } else {
            $alert_message = "Celebrity record not found.";
            $alert_type = 'error';
        }
    }

    // G. UPDATE STORY INFO
    if ($action === 'update_story_info') {
        $hp = get_homepage_settings();
        $hp['story_title'] = strip_tags(trim($_POST['story_title'] ?? $hp['story_title']));
        $hp['story_text']  = trim($_POST['story_text'] ?? $hp['story_text']);
        if (save_homepage_settings($hp)) {
            $alert_message = 'Story text updated successfully.';
            $alert_type = 'success';
        } else {
            $alert_message = 'Failed to save story info.';
            $alert_type = 'error';
        }
    }

    // H. ADD HERO IMAGE
    if ($action === 'add_hero_image') {
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext      = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed)) {
                $new_name = 'hero_' . time() . '_' . uniqid() . '.' . $ext;
                $dest     = $upload_dir . $new_name;
                if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $dest)) {
                    $hp = get_homepage_settings();
                    $hp['hero_images'][] = 'uploads/' . $new_name;
                    if (save_homepage_settings($hp)) {
                        $alert_message = 'Hero image added.';
                        $alert_type = 'success';
                    } else {
                        $alert_message = 'Image uploaded but settings could not be saved.';
                        $alert_type = 'error';
                    }
                } else {
                    $alert_message = 'Failed to move uploaded file.';
                    $alert_type = 'error';
                }
            } else {
                $alert_message = 'Invalid file type. Use jpg, jpeg, png or webp.';
                $alert_type = 'error';
            }
        } else {
            $alert_message = 'No image file provided.';
            $alert_type = 'error';
        }
    }

    // I. DELETE HERO IMAGE
    if ($action === 'delete_hero_image') {
        $img = $_POST['image'] ?? '';
        $hp  = get_homepage_settings();
        $hp['hero_images'] = array_values(array_filter($hp['hero_images'], fn($i) => $i !== $img));
        // Delete actual file if not the original hero.jpg
        if ($img && $img !== 'uploads/hero.jpg') {
            $fp = __DIR__ . '/../' . $img;
            if (file_exists($fp)) @unlink($fp);
        }
        if (save_homepage_settings($hp)) {
            $alert_message = 'Hero image removed.';
            $alert_type = 'success';
        } else {
            $alert_message = 'Failed to update hero images.';
            $alert_type = 'error';
        }
    }

    // J. ADD STORY GALLERY IMAGE
    if ($action === 'add_story_gallery_image') {
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext     = strtolower(pathinfo($_FILES['gallery_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed)) {
                $new_name = 'story_' . time() . '_' . uniqid() . '.' . $ext;
                $dest     = $upload_dir . $new_name;
                if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $dest)) {
                    $hp = get_homepage_settings();
                    $caption = strip_tags(trim($_POST['gallery_caption'] ?? ''));
                    $hp['story_gallery'][] = ['image' => 'uploads/' . $new_name, 'caption' => $caption];
                    if (save_homepage_settings($hp)) {
                        $alert_message = 'Gallery image added.';
                        $alert_type = 'success';
                    } else {
                        $alert_message = 'Image uploaded but settings could not be saved.';
                        $alert_type = 'error';
                    }
                } else {
                    $alert_message = 'Failed to move uploaded gallery file.';
                    $alert_type = 'error';
                }
            } else {
                $alert_message = 'Invalid file type.';
                $alert_type = 'error';
            }
        } else {
            $alert_message = 'No image provided.';
            $alert_type = 'error';
        }
    }

    // K. DELETE STORY GALLERY IMAGE
    if ($action === 'delete_story_gallery_image') {
        $idx = intval($_POST['index'] ?? -1);
        $hp  = get_homepage_settings();
        if ($idx >= 0 && isset($hp['story_gallery'][$idx])) {
            $img_path = __DIR__ . '/../' . $hp['story_gallery'][$idx]['image'];
            if (file_exists($img_path)) @unlink($img_path);
            array_splice($hp['story_gallery'], $idx, 1);
            if (save_homepage_settings($hp)) {
                $alert_message = 'Gallery image removed.';
                $alert_type = 'success';
            } else {
                $alert_message = 'Failed to update gallery.';
                $alert_type = 'error';
            }
        } else {
            $alert_message = 'Invalid gallery index.';
            $alert_type = 'error';
        }
    }

    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => ($alert_type === 'success') ? 'success' : 'error',
            'message' => $alert_message
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Couture Admin Dashboard | Anusha Reddy</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-login-wrapper {
            max-width: 400px;
            margin: 100px auto;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            padding: var(--spacing-md);
            box-shadow: var(--shadow-subtle);
        }
        .admin-nav {
            background-color: #111;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav h1 {
            font-size: 1.3rem;
            letter-spacing: 0.1em;
        }
        .admin-nav a {
            color: var(--accent-gold);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: var(--spacing-md);
            gap: 1rem;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 1rem 0.5rem;
            font-family: var(--font-sans);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            color: var(--text-muted);
            transition: all 0.3s;
        }
        .tab-btn.active {
            border-color: var(--accent-gold);
            color: var(--text-primary);
            font-weight: 500;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: var(--bg-secondary);
        }
        .admin-table th, .admin-table td {
            border: 1px solid var(--border-color);
            padding: 1rem;
            text-align: left;
            font-size: 0.9rem;
        }
        .admin-table th {
            background-color: var(--bg-primary);
            font-family: var(--font-sans);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
        }
        .admin-alert {
            padding: 1rem;
            margin-bottom: var(--spacing-md);
            border-left: 4px solid;
            font-size: 0.9rem;
        }
        .admin-alert.success {
            background-color: #E8F5E9;
            color: #2E7D32;
            border-color: #4CAF50;
        }
        .admin-alert.error {
            background-color: #FFEBEE;
            color: #C62828;
            border-color: #D32F2F;
        }
    </style>
</head>
<body>

<?php if (!$is_authenticated): ?>
    <!-- Luxury Login Screen -->
    <div class="admin-login-wrapper">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="letter-spacing: 0.1em; font-size: 1.6rem; font-family: var(--font-serif);">ANUSHA REDDY</h2>
            <p style="font-size: 0.65rem; letter-spacing: 0.3em; color: var(--accent-gold); text-transform: uppercase; margin-top: 0.2rem;">Couture Dashboard</p>
        </div>
        
        <?php if (isset($login_error)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showCoutureAlert('Authentication Error', <?php echo json_encode($login_error); ?>);
                });
            </script>
        <?php endif; ?>
        
        <form action="admin.php" method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="password">Enter Security Key</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autofocus>
            </div>
            <button type="submit" class="btn btn-solid btn-full">Authenticate</button>
        </form>
    </div>
<?php else: ?>
    <!-- Authenticated Admin Workspace -->
    <header class="admin-nav">
        <h1>ANUSHA REDDY <span style="font-size: 0.75rem; font-family: var(--font-sans); color: var(--accent-gold); margin-left: 0.5rem; letter-spacing: 0.2em; font-weight: 300;">Couture Admin</span></h1>
        <a href="admin.php?action=logout"><i class="fas fa-sign-out-alt"></i> Exit Workspace</a>
    </header>

    <main class="container section">
        <!-- Display Alert message -->
        <?php if (!empty($alert_message)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const title = "<?php echo ($alert_type === 'success') ? 'Success' : 'Notice'; ?>";
                    const message = <?php echo json_encode($alert_message); ?>;
                    showCoutureAlert(title, message);
                });
            </script>
        <?php endif; ?>

        <?php
        $edit_id = isset($_GET['edit']) ? $_GET['edit'] : '';
        $edit_product = null;
        if (!empty($edit_id) && isset($products[$edit_id])) {
            $edit_product = $products[$edit_id];
        }
        ?>

        <!-- Tabs Headers -->
        <div class="tabs">
            <button class="tab-btn <?php echo !$edit_product ? 'active' : ''; ?>" onclick="switchTab('manage-products', this)">Manage Products</button>
            <button class="tab-btn" onclick="switchTab('add-product', this)">Add New Design</button>
            <button class="tab-btn" onclick="switchTab('sales-manager', this)">Bulk Sales Engine</button>
            <button class="tab-btn" onclick="switchTab('celebrity-manager', this)">Celebrity Showcase</button>
            <button class="tab-btn" onclick="switchTab('homepage-settings', this)">Home &amp; Story Settings</button>
            <?php if ($edit_product): ?>
                <button class="tab-btn active" onclick="switchTab('edit-product', this)">Edit Design</button>
            <?php endif; ?>
        </div>

        <!-- TAB 1: MANAGE PRODUCTS -->
        <div id="manage-products" class="tab-content <?php echo !$edit_product ? 'active' : ''; ?>">
            <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Current Catalog Ensembles</h2>
            <p style="margin-bottom: 2rem;">Overview of all live products on the website storefront.</p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Thumbnail</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Price</th>
                        <th>Original Retail</th>
                        <th style="width: 150px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">No products registered in the database catalog.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $id => $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($product['images'][0] ?? 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&q=80&w=800'); ?>" style="width: 50px; height: 60px; object-fit: cover; border: 1px solid var(--border-color);" alt="">
                                </td>
                                <td style="font-family: var(--font-serif); font-weight: 600; font-size: 1.05rem;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </td>
                                <td>
                                    <span style="font-size: 0.75rem; text-transform: uppercase; background-color: var(--border-color); padding: 3px 8px; letter-spacing: 0.05em; font-weight: 500;">
                                        <?php echo htmlspecialchars($product['category']); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 600; color: var(--accent-gold);">
                                    <?php echo htmlspecialchars($product['price']); ?>
                                </td>
                                <td style="color: var(--text-muted);">
                                    ₹<?php echo number_format($product['original_price_numeric'] ?? $product['price_numeric']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="admin.php?edit=<?php echo $id; ?>" style="color: var(--accent-gold); margin-right: 1.2rem; font-size: 0.95rem; font-weight: 500;"><i class="fas fa-edit"></i> Edit</a>
                                    <!-- Delete form shortcut -->
                                    <form action="admin.php" method="POST" class="confirm-action-form" data-confirm-title="Archive Design" data-confirm-msg="Are you sure you want to archive/delete this design?" data-confirm-btn="Delete" data-cancel-btn="Cancel" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                                        <button type="submit" style="background: none; border: none; color: #D32F2F; cursor: pointer; font-size: 0.95rem;" title="Archive Product"><i class="fas fa-trash-alt"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TAB 2: ADD PRODUCT -->
        <div id="add-product" class="tab-content">
            <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Add New Product</h2>
            <p style="margin-bottom: 2rem;">Add a new product to your online store catalog.</p>

            <form action="admin.php" method="POST" enctype="multipart/form-data" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: var(--spacing-md);">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label for="new_name">Product Name</label>
                        <input type="text" id="new_name" name="name" class="form-control" placeholder="e.g. Silk Organza Lehenga" required>
                    </div>
                    <div>
                        <label for="new_category">Category</label>
                        <select id="new_category" name="category" class="form-control">
                            <option value="Bridal">Bridal Wear</option>
                            <option value="Festive">Festive Collection</option>
                            <option value="Pret">Pret-A-Porter (Ready to Wear)</option>
                            <option value="Bespoke">Bespoke Couture</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label for="new_price_numeric">Price (₹)</label>
                        <input type="number" id="new_price_numeric" name="price_numeric" class="form-control" placeholder="e.g. 240000" required>
                    </div>
                    <div>
                        <label>Product Images (Select multiple)</label>
                        <input type="file" id="new_images" name="images[]" class="form-control" multiple accept="image/*" required style="display: none;" onchange="previewAddImages(this)">
                        <div class="image-manager-grid" id="newImagesPreviewGrid" style="margin-top: 0.2rem; min-height: 110px;">
                            <div class="add-image-tile" onclick="document.getElementById('new_images').click()">
                                <i class="fas fa-plus" style="font-size: 1.3rem; color: var(--accent-gold);"></i>
                                <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Select</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_description">Product Description</label>
                    <textarea id="new_description" name="description" class="form-control" style="min-height: 80px;" placeholder="Enter details about materials, design elements, fit, and origin."></textarea>
                </div>

                <div class="form-group">
                    <label for="new_details">Product Details (one bullet point per line)</label>
                    <textarea id="new_details" name="details" class="form-control" style="min-height: 100px;" placeholder="Fabric: Silk & Organza&#10;Embroidery: Hand-done Zardozi&#10;Delivery: 6 to 8 weeks"></textarea>
                </div>

                <button type="submit" class="btn btn-solid btn-full" style="padding: 1rem;">Add Product</button>
            </form>
        </div>

        <!-- TAB 4: EDIT PRODUCT (Conditional) -->
        <?php if ($edit_product): ?>
        <div id="edit-product" class="tab-content active">
            <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Edit Product: <?php echo htmlspecialchars($edit_product['name']); ?></h2>
            <p style="margin-bottom: 2rem;">Modify details, delete photos, or drag-and-drop to rearrange image orders.</p>

            <form action="admin.php" method="POST" enctype="multipart/form-data" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: var(--spacing-md);">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                <input type="hidden" name="image_order" id="edit_image_order" value="<?php echo htmlspecialchars(json_encode($edit_product['images'] ?? [])); ?>">
                
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label for="edit_name">Product Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                    </div>
                    <div>
                        <label for="edit_category">Category</label>
                        <select id="edit_category" name="category" class="form-control">
                            <option value="Bridal" <?php echo ($edit_product['category'] === 'Bridal') ? 'selected' : ''; ?>>Bridal Wear</option>
                            <option value="Festive" <?php echo ($edit_product['category'] === 'Festive') ? 'selected' : ''; ?>>Festive Collection</option>
                            <option value="Pret" <?php echo ($edit_product['category'] === 'Pret') ? 'selected' : ''; ?>>Pret-A-Porter (Ready to Wear)</option>
                            <option value="Bespoke" <?php echo ($edit_product['category'] === 'Bespoke') ? 'selected' : ''; ?>>Bespoke Couture</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                    <div>
                        <label for="edit_price_numeric">Price (₹)</label>
                        <input type="number" id="edit_price_numeric" name="price_numeric" class="form-control" value="<?php echo htmlspecialchars($edit_product['price_numeric']); ?>" required style="max-width: 50%;">
                    </div>
                </div>

                <!-- Visual Image Organizer Grid -->
                <div class="form-group">
                    <label style="font-weight: 600; color: var(--accent-gold);">Product Images (Drag to rearrange, click &times; to delete, click + to add)</label>
                    <input type="file" id="edit_images" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewEditImages(this)">
                    <div class="image-manager-grid" id="imageManagerGrid">
                        <?php 
                        $current_images = $edit_product['images'] ?? [];
                        foreach ($current_images as $index => $img_path): 
                        ?>
                            <div class="draggable-thumb" draggable="true" data-path="<?php echo htmlspecialchars($img_path); ?>" data-index="<?php echo $index; ?>">
                                <img src="<?php echo htmlspecialchars($img_path); ?>" alt="">
                                <span class="delete-thumb-btn" onclick="removeThumbnail(this)">&times;</span>
                            </div>
                        <?php endforeach; ?>
                        <!-- Visual Plus Tile -->
                        <div class="add-image-tile" onclick="document.getElementById('edit_images').click()" title="Add More Images">
                            <i class="fas fa-plus" style="font-size: 1.3rem; color: var(--accent-gold);"></i>
                            <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Add More</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_description">Product Description</label>
                    <textarea id="edit_description" name="description" class="form-control" style="min-height: 80px;"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_details">Product Details (one bullet point per line)</label>
                    <textarea id="edit_details" name="details" class="form-control" style="min-height: 100px;"><?php echo htmlspecialchars(implode("\n", $edit_product['details'] ?? [])); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-solid" style="flex-grow: 1; padding: 1rem;">Save Changes</button>
                    <a href="admin.php" class="btn btn-outline" style="padding: 1rem; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- TAB 3: BULK SALES MANAGER -->
        <div id="sales-manager" class="tab-content">
            <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Bulk Pricing Campaigns</h2>
            <p style="margin-bottom: 2rem;">Run seasonal promotions, adjust collection prices globally, and revert campaigns with one click.</p>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: var(--spacing-md);">
                <!-- Bulk discount apply card -->
                <div style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: var(--spacing-md);">
                    <h3 style="font-size: 1.2rem; margin-bottom: 1rem; font-family: var(--font-sans); font-weight: 500; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; color: var(--accent-gold);">Launch Promotional Campaign</h3>
                    
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="action" value="bulk_discount">
                        
                        <div class="form-group">
                            <label for="discount_category">Target Campaign Collection</label>
                            <select id="discount_category" name="discount_category" class="form-control">
                                <option value="All">All Collections (Storewide)</option>
                                <option value="Bridal">Bridal Collection Only</option>
                                <option value="Festive">Festive Collection Only</option>
                                <option value="Pret">Pret (Ready-to-Wear) Only</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="discount_percentage">Discount Percentage</label>
                            <select id="discount_percentage" name="discount_percentage" class="form-control">
                                <option value="10">10% Off</option>
                                <option value="15">15% Off</option>
                                <option value="20" selected>20% Off</option>
                                <option value="25">25% Off</option>
                                <option value="30">30% Off</option>
                                <option value="50">50% Half Price Sale</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-gold btn-full" style="padding: 1rem;">Apply Discount Campaign</button>
                    </form>
                </div>

                <!-- Revert campaign status card -->
                <div style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: var(--spacing-md); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 1.2rem; margin-bottom: 1rem; font-family: var(--font-sans); font-weight: 500; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; color: #111;">Restore Catalog Values</h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1rem;">
                            Click below to immediately revert all retail listings back to their original registered prices. This will cancel any active bulk discount campaigns storewide.
                        </p>
                    </div>
                    
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="action" value="revert_discount">
                        <button type="submit" class="btn btn-outline btn-full" style="padding: 1rem; border-color: #111; color: #111; font-weight: 600;">Revert to Original Prices</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 5: CELEBRITY SHOWCASE MANAGER -->
        <div id="celebrity-manager" class="tab-content">
            <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Celebrity Showcase Manager</h2>
            <p style="margin-bottom: 2rem;">Manage the images and names of celebrities featured wearing Anusha Reddy Couture designs.</p>

            <div style="display: grid; grid-template-columns: 1.2fr 2fr; gap: 3rem;">
                <!-- Add Celebrity Form -->
                <div style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: var(--spacing-md); height: fit-content;">
                    <h3 style="font-size: 1.2rem; margin-bottom: 1.5rem; font-family: var(--font-sans); font-weight: 500; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; color: #111;">Add Featured Celebrity</h3>
                    
                    <form action="admin.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_celebrity">
                        
                        <div class="form-group" style="margin-bottom: 1.2rem;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Celebrity Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Kareena Kapoor" required style="width: 100%; padding: 0.75rem;">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Celebrity Photo</label>
                            <input type="file" name="celebrity_photo" accept="image/*" required style="width: 100%;">
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.4rem;">Select a high-resolution vertical/portrait photo.</p>
                        </div>

                        <button type="submit" class="btn btn-gold btn-full" style="padding: 1rem;">Add Celebrity</button>
                    </form>
                </div>

                <!-- Existing Celebrity List -->
                <div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 1.5rem; font-family: var(--font-sans); font-weight: 500; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; color: #111;">Current Celebrity Showcase</h3>
                    
                    <?php 
                    $celebrity_list = db_get_celebrities();
                    if (empty($celebrity_list)): 
                    ?>
                        <p style="color: var(--text-muted); font-style: italic; font-size: 0.9rem;">No celebrities added yet. Seed default cards or upload photos.</p>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1.5rem;">
                            <?php foreach ($celebrity_list as $celeb): ?>
                                <div style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: 0.8rem; border-radius: 8px; text-align: center; position: relative; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <div style="width: 100%; height: 160px; overflow: hidden; border-radius: 4px; background-color: #ddd; margin-bottom: 0.8rem;">
                                            <img src="<?php echo htmlspecialchars($celeb['image']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <p style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-primary); margin-bottom: 0.8rem;"><?php echo htmlspecialchars($celeb['name']); ?></p>
                                    </div>
                                    
                                    <form action="admin.php" method="POST" class="confirm-action-form" data-confirm-title="Remove Celebrity" data-confirm-msg="Are you sure you want to delete this celebrity card?" data-confirm-btn="Delete" data-cancel-btn="Cancel">
                                        <input type="hidden" name="action" value="delete_celebrity">
                                        <input type="hidden" name="id" value="<?php echo $celeb['id']; ?>">
                                        <button type="submit" class="btn btn-outline" style="padding: 0.4rem; font-size: 0.7rem; color: #d32f2f; border-color: #d32f2f; width: 100%;">Delete</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB 5: HOME & STORY SETTINGS -->
        <?php
        $hp_settings = get_homepage_settings();
        ?>
        <div id="homepage-settings" class="tab-content">
            <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Home &amp; Story Settings</h2>
            <p style="margin-bottom: 2.5rem;">Manage the homepage hero slideshow images, the "Read Our Story" text, and the story page gallery.</p>

            <!-- SUB-SECTION A: HERO SLIDESHOW IMAGES -->
            <div style="border: 1px solid var(--border-color); padding: 1.5rem; margin-bottom: 2rem; background: var(--bg-secondary);">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.4rem;">Homepage Hero Slideshow</h3>
                <p style="margin-bottom: 1.5rem; font-size: 0.9rem;">These images will cycle on the home page hero background. Upload multiple images for a smooth cross-fade slideshow.</p>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <?php foreach ($hp_settings['hero_images'] as $himg): ?>
                    <div style="position: relative; border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($himg); ?>" style="width: 100%; height: 120px; object-fit: cover; display: block;" alt="">
                        <form action="admin.php" method="POST" style="position: absolute; bottom: 0; right: 0; margin: 0;">
                            <input type="hidden" name="action" value="delete_hero_image">
                            <input type="hidden" name="image" value="<?php echo htmlspecialchars($himg); ?>">
                            <button type="submit" onclick="return confirm('Remove this hero image?')" style="background: rgba(211,47,47,0.85); border: none; color: #fff; font-size: 0.7rem; padding: 0.3rem 0.6rem; cursor: pointer; letter-spacing: 0.05em;">&#215; Remove</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($hp_settings['hero_images'])): ?>
                    <p style="color: var(--text-muted); font-style: italic; font-size: 0.9rem;">No hero images. Upload at least one below.</p>
                    <?php endif; ?>
                </div>

                <form action="admin.php" method="POST" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="add_hero_image">
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 240px;">
                        <label for="hero_image_upload">Upload New Hero Slide</label>
                        <input type="file" id="hero_image_upload" name="hero_image" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-solid" style="height: 46px; padding: 0 1.5rem; white-space: nowrap;"><i class="fas fa-plus"></i> Add Hero Image</button>
                </form>
            </div>

            <!-- SUB-SECTION B: STORY TEXT -->
            <div style="border: 1px solid var(--border-color); padding: 1.5rem; margin-bottom: 2rem; background: var(--bg-secondary);">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.4rem;">"Read Our Story" Page Content</h3>
                <p style="margin-bottom: 1.5rem; font-size: 0.9rem;">This title and text will appear both on the homepage intro section and the dedicated Story page.</p>

                <form action="admin.php" method="POST">
                    <input type="hidden" name="action" value="update_story_info">
                    <div class="form-group">
                        <label for="story_title_input">Story Page Title</label>
                        <input type="text" id="story_title_input" name="story_title" class="form-control" value="<?php echo htmlspecialchars($hp_settings['story_title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="story_text_input">Story Text (use blank lines to create paragraphs)</label>
                        <textarea id="story_text_input" name="story_text" class="form-control" rows="10" style="font-family: var(--font-sans); line-height: 1.7;"><?php echo htmlspecialchars($hp_settings['story_text']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-solid"><i class="fas fa-save"></i> Save Story Content</button>
                </form>
            </div>

            <!-- SUB-SECTION C: STORY PAGE GALLERY -->
            <div style="border: 1px solid var(--border-color); padding: 1.5rem; background: var(--bg-secondary);">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.4rem;">Story Page Gallery</h3>
                <p style="margin-bottom: 1.5rem; font-size: 0.9rem;">These images appear in the gallery section on the Story page only (not visible on the homepage).</p>

                <?php if (!empty($hp_settings['story_gallery'])): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <?php foreach ($hp_settings['story_gallery'] as $gi => $gitem): ?>
                    <div style="position: relative; border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($gitem['image']); ?>" style="width: 100%; height: 140px; object-fit: cover; display: block;" alt="">
                        <?php if (!empty($gitem['caption'])): ?>
                        <div style="padding: 0.4rem 0.6rem; font-size: 0.75rem; color: var(--text-muted); background: var(--bg-primary); border-top: 1px solid var(--border-color);"><?php echo htmlspecialchars($gitem['caption']); ?></div>
                        <?php endif; ?>
                        <form action="admin.php" method="POST" style="position: absolute; top: 0; right: 0; margin: 0;">
                            <input type="hidden" name="action" value="delete_story_gallery_image">
                            <input type="hidden" name="index" value="<?php echo $gi; ?>">
                            <button type="submit" onclick="return confirm('Remove this gallery image?')" style="background: rgba(211,47,47,0.85); border: none; color: #fff; font-size: 0.7rem; padding: 0.3rem 0.6rem; cursor: pointer; letter-spacing: 0.05em;">&#215;</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color: var(--text-muted); font-style: italic; font-size: 0.9rem; margin-bottom: 1.5rem;">No gallery images yet. Upload photos below.</p>
                <?php endif; ?>

                <form action="admin.php" method="POST" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="add_story_gallery_image">
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                        <label for="gallery_image_upload">Photo</label>
                        <input type="file" id="gallery_image_upload" name="gallery_image" class="form-control" accept="image/*" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                        <label for="gallery_caption_input">Caption (optional)</label>
                        <input type="text" id="gallery_caption_input" name="gallery_caption" class="form-control" placeholder="e.g. Artisan at work">
                    </div>
                    <button type="submit" class="btn btn-solid" style="height: 46px; padding: 0 1.5rem; white-space: nowrap;"><i class="fas fa-plus"></i> Add to Gallery</button>
                </form>
            </div>
        </div>

    </main>
<?php endif; ?>

<script>
    function switchTab(tabId, element) {
        // Toggle tab headers
        const tabBtns = document.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');

        // Toggle tab content panels
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => content.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
    }

    // Visual Drag & Drop Image Manager
    let dragSrcEl = null;

    function handleDragStart(e) {
        this.style.opacity = '0.4';
        dragSrcEl = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        e.dataTransfer.setData('text/plain', this.getAttribute('data-path'));
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        this.classList.add('drag-over');
    }

    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        if (dragSrcEl !== this) {
            // Swap innerHTML
            const tempHTML = this.innerHTML;
            this.innerHTML = dragSrcEl.innerHTML;
            dragSrcEl.innerHTML = tempHTML;
            
            // Swap data-path attributes
            const tempPath = this.getAttribute('data-path');
            this.setAttribute('data-path', dragSrcEl.getAttribute('data-path'));
            dragSrcEl.setAttribute('data-path', tempPath);
            
            // Re-update the input value
            updateImageOrder();
        }
        return false;
    }

    function handleDragEnd(e) {
        this.style.opacity = '1';
        const items = document.querySelectorAll('.draggable-thumb');
        items.forEach(item => {
            item.classList.remove('drag-over');
        });
    }

    function updateImageOrder() {
        const items = document.querySelectorAll('.draggable-thumb');
        const order = [];
        items.forEach(item => {
            const path = item.getAttribute('data-path');
            if (path) {
                order.push(path);
            }
        });
        const orderInput = document.getElementById('edit_image_order');
        if (orderInput) {
            orderInput.value = JSON.stringify(order);
        }
    }

    function removeThumbnail(btn) {
        const thumb = btn.closest('.draggable-thumb');
        if (thumb) {
            thumb.remove();
            updateImageOrder();
        }
    }

    function initDragAndDrop() {
        const items = document.querySelectorAll('.draggable-thumb');
        items.forEach(item => {
            item.addEventListener('dragstart', handleDragStart, false);
            item.addEventListener('dragenter', handleDragEnter, false);
            item.addEventListener('dragover', handleDragOver, false);
            item.addEventListener('dragleave', handleDragLeave, false);
            item.addEventListener('drop', handleDrop, false);
            item.addEventListener('dragend', handleDragEnd, false);
        });
    }

    // Dynamic Image Previews & AJAX publishing
    function previewAddImages(input) {
        const grid = document.getElementById('newImagesPreviewGrid');
        // Clear existing previews (except the Select button itself)
        const oldThumbs = grid.querySelectorAll('.draggable-thumb');
        oldThumbs.forEach(t => t.remove());

        if (input.files && input.files.length > 0) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const thumb = document.createElement('div');
                    thumb.className = 'draggable-thumb preview-only-thumb';
                    thumb.innerHTML = `<img src="${e.target.result}" alt="">`;
                    grid.insertBefore(thumb, grid.querySelector('.add-image-tile'));
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function previewEditImages(input) {
        const grid = document.getElementById('imageManagerGrid');
        // Clear any previous new preview cards first (re-run selections)
        const oldPreviews = grid.querySelectorAll('.preview-only-thumb');
        oldPreviews.forEach(p => p.remove());

        if (input.files && input.files.length > 0) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const thumb = document.createElement('div');
                    thumb.className = 'draggable-thumb preview-only-thumb';
                    thumb.innerHTML = `<img src="${e.target.result}" alt="">`;
                    grid.insertBefore(thumb, grid.querySelector('.add-image-tile'));
                }
                reader.readAsDataURL(file);
            });
        }
    }

    // Bind AJAX Form Submissions on load
    document.addEventListener('DOMContentLoaded', () => {
        initDragAndDrop();

        // 1. ADD PRODUCT FORM
        const addForm = document.querySelector('#add-product form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = addForm.querySelector('button[type="submit"]');
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';

                const formData = new FormData(addForm);
                formData.append('ajax', '1');

                fetch('admin.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        showCoutureAlert('Publish Success', data.message || 'Product published successfully!').then(() => {
                            window.location.href = 'admin.php';
                        });
                    } else {
                        showCoutureAlert('Publish Error', data.message).then(() => {
                            btn.disabled = false;
                            btn.innerHTML = origText;
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    showCoutureAlert('Error', 'Network error occurred.').then(() => {
                        btn.disabled = false;
                        btn.innerHTML = origText;
                    });
                });
            });
        }

        // 2. EDIT PRODUCT FORM
        const editForm = document.querySelector('#edit-product form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = editForm.querySelector('button[type="submit"]');
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                const formData = new FormData(editForm);
                formData.append('ajax', '1');

                fetch('admin.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        showCoutureAlert('Save Success', data.message || 'Product saved successfully!').then(() => {
                            window.location.href = 'admin.php';
                        });
                    } else {
                        showCoutureAlert('Save Error', data.message).then(() => {
                            btn.disabled = false;
                            btn.innerHTML = origText;
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    showCoutureAlert('Error', 'Network error occurred.').then(() => {
                        btn.disabled = false;
                        btn.innerHTML = origText;
                    });
                });
            });
        }
    });
</script>
</body>
</html>
