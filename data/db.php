<?php
// PostgreSQL Database Integration Utility (PDO-based)
// Designed to be fast, secure with prepared statements, and fallback gracefully if DB is offline.

// Load environment variables from .env file if it exists (useful for local development)
function load_env_file($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue; // Skip comments and empty lines
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            // Strip surrounding quotes
            if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Execute environment loader for .env in project root
load_env_file(__DIR__ . '/../.env');

// 1. Configure your PostgreSQL Connection Credentials (e.g. from Coolify)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '5432');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: getenv('DB_DATABASE') ?: ''); // Add your database name here
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: getenv('DB_USERNAME') ?: 'postgres');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

/**
 * Establish a PDO PostgreSQL Connection with a short timeout.
 */
function get_db_connection() {
    if (empty(DB_NAME)) {
        return null; // Gracefully bypass if credentials are not configured yet
    }
    
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $options = [
            PDO::ATTR_TIMEOUT => 2, // 2-second timeout to keep the site fast
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        return new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    } catch (PDOException $e) {
        // Log error locally if desired (suppress in production to avoid leaks)
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Fetch products from PostgreSQL.
 */
function db_get_products() {
    $pdo = get_db_connection();
    if (!$pdo) {
        return null; // Trigger fallback to products.json
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        
        if (empty($rows)) {
            return [];
        }
        
        $formatted_products = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            
            // Handle details array mapping from JSON or comma-separated string
            $details = [];
            if (!empty($row['details'])) {
                $decoded = json_decode($row['details'], true);
                if (is_array($decoded)) {
                    $details = $decoded;
                } else {
                    $details = array_filter(array_map('trim', explode("\n", $row['details'])));
                }
            }
            
            // Parse images list (support arrays and legacy single image strings)
            $images = [];
            if (!empty($row['image'])) {
                $decoded_imgs = json_decode($row['image'], true);
                if (is_array($decoded_imgs)) {
                    $images = $decoded_imgs;
                } else {
                    $images = [$row['image']];
                }
            }
            if (empty($images)) {
                $images = ['https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&q=80&w=800'];
            }

            $formatted_products[$id] = [
                'id' => $id,
                'name' => $row['name'],
                'category' => $row['category'],
                'price_numeric' => intval($row['price_numeric']),
                'price' => $row['price'],
                'original_price_numeric' => intval($row['original_price_numeric']),
                'images' => $images,
                'description' => $row['description'] ?? '',
                'details' => $details
            ];
        }
        return $formatted_products;
    } catch (PDOException $e) {
        error_log("Query products failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Submit booking request to PostgreSQL 'bookings' table.
 */
function db_create_booking($booking_data) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO bookings (name, email, phone, type, date, product_name, message) 
                VALUES (:name, :email, :phone, :type, :date, :product_name, :message)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $booking_data['name'] ?? '',
            ':email' => $booking_data['email'] ?? '',
            ':phone' => $booking_data['phone'] ?? '',
            ':type' => $booking_data['consultation_type'] ?? '',
            ':date' => $booking_data['preferred_date'] ?? '',
            ':product_name' => $booking_data['inquired_product'] ?? null,
            ':message' => $booking_data['message'] ?? null
        ]);
    } catch (PDOException $e) {
        error_log("Create booking failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Save products back to the local products.json file.
 */
function save_local_products($products_array) {
    $json_file = __DIR__ . '/products.json';
    return (file_put_contents($json_file, json_encode($products_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false);
}

/**
 * Create a product in PostgreSQL.
 */
function db_create_product($product_data) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO products (name, category, price_numeric, price, original_price_numeric, image, description, details) 
                VALUES (:name, :category, :price_numeric, :price, :original_price_numeric, :image, :description, :details)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $product_data['name'],
            ':category' => $product_data['category'],
            ':price_numeric' => intval($product_data['price_numeric']),
            ':price' => $product_data['price'],
            ':original_price_numeric' => intval($product_data['original_price_numeric']),
            ':image' => json_encode($product_data['images'] ?? []),
            ':description' => $product_data['description'] ?? null,
            ':details' => json_encode($product_data['details'] ?? [])
        ]);
    } catch (PDOException $e) {
        error_log("Create product failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a product in PostgreSQL.
 */
function db_update_product($id, $product_data) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $sql = "UPDATE products 
                SET name = :name, category = :category, price_numeric = :price_numeric, price = :price, 
                    original_price_numeric = :original_price_numeric, image = :image, description = :description, details = :details
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id' => intval($id),
            ':name' => $product_data['name'],
            ':category' => $product_data['category'],
            ':price_numeric' => intval($product_data['price_numeric']),
            ':price' => $product_data['price'],
            ':original_price_numeric' => intval($product_data['original_price_numeric']),
            ':image' => json_encode($product_data['images'] ?? []),
            ':description' => $product_data['description'] ?? null,
            ':details' => json_encode($product_data['details'] ?? [])
        ]);
    } catch (PDOException $e) {
        error_log("Update product failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a product in PostgreSQL.
 */
function db_delete_product($id) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => intval($id)]);
    } catch (PDOException $e) {
        error_log("Delete product failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch all celebrities from PostgreSQL or fallback to local JSON database.
 */
function db_get_celebrities() {
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            // Check if table exists, if not create it
            $pdo->exec("CREATE TABLE IF NOT EXISTS celebrities (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                image VARCHAR(255) NOT NULL
            )");
            
            $stmt = $pdo->query("SELECT * FROM celebrities ORDER BY id ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $celebrities = [];
                foreach ($rows as $row) {
                    $celebrities[$row['id']] = [
                        'id' => intval($row['id']),
                        'name' => $row['name'],
                        'image' => $row['image']
                    ];
                }
                return $celebrities;
            }
        } catch (PDOException $e) {
            error_log("Get celebrities failed, falling back: " . $e->getMessage());
        }
    }
    
    // Fallback to local JSON database
    $json_file = __DIR__ . '/celebrities.json';
    if (file_exists($json_file)) {
        $json_content = file_get_contents($json_file);
        $decoded = json_decode($json_content, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return [];
}

/**
 * Save celebrities back to the local celebrities.json file.
 */
function save_local_celebrities($celebrities_array) {
    $json_file = __DIR__ . '/celebrities.json';
    return (file_put_contents($json_file, json_encode($celebrities_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false);
}

/**
 * Create a celebrity card in PostgreSQL database.
 */
function db_create_celebrity($celebrity_data) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO celebrities (id, name, image) VALUES (:id, :name, :image)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id' => intval($celebrity_data['id']),
            ':name' => $celebrity_data['name'],
            ':image' => $celebrity_data['image']
        ]);
    } catch (PDOException $e) {
        error_log("Create celebrity failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a celebrity card in PostgreSQL database.
 */
function db_delete_celebrity($id) {
    $pdo = get_db_connection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM celebrities WHERE id = :id");
        return $stmt->execute([':id' => intval($id)]);
    } catch (PDOException $e) {
        error_log("Delete celebrity failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Returns the default homepage settings structure.
 */
function get_default_homepage_settings() {
    return [
        'hero_images'   => ['uploads/hero.jpg'],
        'story_title'   => 'The Anusha Reddy Story',
        'story_text'    => 'Anusha Reddy Couture celebrates the richness of Indian craftsmanship with a modern aesthetic. Each piece is meticulously handcrafted by master artisans, blending heritage zardozi, handwoven silks, and delicate embroideries into silhouettes that tell a story of grace and elegance.',
        'story_gallery' => [],
    ];
}

/**
 * Read homepage settings from the local JSON file, falling back to defaults.
 */
function get_homepage_settings() {
    $file = __DIR__ . '/homepage_settings.json';
    if (file_exists($file)) {
        $decoded = json_decode(file_get_contents($file), true);
        if (is_array($decoded)) {
            return array_merge(get_default_homepage_settings(), $decoded);
        }
    }
    return get_default_homepage_settings();
}

/**
 * Save homepage settings back to the local JSON file.
 */
function save_homepage_settings($settings) {
    $file = __DIR__ . '/homepage_settings.json';
    return (file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !== false);
}
