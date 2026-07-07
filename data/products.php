<?php
// Anusha Reddy Couture - Product Catalog Database Array

$products = [
    1 => [
        'id' => 1,
        'name' => 'The Gulnar Lehenga',
        'category' => 'Bridal',
        'price_numeric' => 285000,
        'price' => '₹2,85,000',
        'original_price_numeric' => 285000,
        'images' => [
            'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&q=80&w=800'
        ],
        'description' => 'A traditional crimson raw silk lehenga, hand-embroidered with classic zardozi, gold threadwork, and delicate pearls. Paired with a heavily embellished blouse and a sheer tissue dupatta.',
        'details' => [
            'Fabric: Raw Silk, Net & Organza',
            'Embroidery: Hand-done Zardozi, Aari and Pearl accents',
            'Delivery Time: 8 to 12 weeks',
            'Fit: Custom fitted to measurements'
        ]
    ],
    2 => [
        'id' => 2,
        'name' => 'The Ivory Noor Anarkali',
        'category' => 'Festive',
        'price_numeric' => 145000,
        'price' => '₹1,45,000',
        'original_price_numeric' => 145000,
        'images' => [
            'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&q=80&w=800'
        ],
        'description' => 'An ethereal ivory georgette Anarkali gown featuring fine Lucknowi chikankari embroidery, paired with a sheer organza dupatta highlighted with badla work.',
        'details' => [
            'Fabric: Georgette & Organza',
            'Embroidery: Hand-embroidered Chikankari & Badla work',
            'Delivery Time: 6 to 8 weeks',
            'Fit: Tailored to standard sizes or custom measurement'
        ]
    ],
    3 => [
        'id' => 3,
        'name' => 'Meera Velvet Kaftan',
        'category' => 'Pret',
        'price_numeric' => 75000,
        'price' => '₹75,000',
        'original_price_numeric' => 75000,
        'images' => [
            'https://images.unsplash.com/photo-1609357605129-26f69add5d6e?auto=format&fit=crop&q=80&w=800'
        ],
        'description' => 'Luxurious royal emerald velvet kaftan, detailed with delicate tilla embroidery along the neckline and sleeves. Flowy silhouette suitable for festive hosting.',
        'details' => [
            'Fabric: Premium Silk Velvet',
            'Embroidery: Fine metallic Tilla work',
            'Delivery Time: 3 to 4 weeks',
            'Fit: Relaxed free-size drape'
        ]
    ],
    4 => [
        'id' => 4,
        'name' => 'The Seerat Sharara Set',
        'category' => 'Bridal',
        'price_numeric' => 195000,
        'price' => '₹1,95,000',
        'original_price_numeric' => 195000,
        'images' => [
            'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&q=80&w=800'
        ],
        'description' => 'A modern blush pink georgette sharara set adorned with floral sequins, silver gota patti, and fine thread border details.',
        'details' => [
            'Fabric: Silk Georgette & Crepe',
            'Embroidery: Gota Patti & Sequins',
            'Delivery Time: 8 weeks',
            'Fit: Custom sizing available'
        ]
    ],
    5 => [
        'id' => 5,
        'name' => 'The Zara Silk Saree',
        'category' => 'Festive',
        'price_numeric' => 115000,
        'price' => '₹1,15,000',
        'original_price_numeric' => 115000,
        'images' => [
            'https://images.unsplash.com/photo-1610030469668-93535c17b6b3?auto=format&fit=crop&q=80&w=800'
        ],
        'description' => 'A timeless handwoven Banarasi silk saree in midnight blue, featuring intricate gold zari borders and heavy pallu detail. Includes unstitched blouse piece.',
        'details' => [
            'Fabric: 100% pure Banarasi Silk',
            'Weave: Traditional Kadhwa weave',
            'Delivery Time: 2 to 3 weeks',
            'Fit: Unstitched (Blouse tailoring optional)'
        ]
    ],
    6 => [
        'id' => 6,
        'name' => 'Gulabi Organza Cape',
        'category' => 'Pret',
        'price_numeric' => 55000,
        'price' => '₹55,000',
        'original_price_numeric' => 55000,
        'images' => [
            'https://images.unsplash.com/photo-1549064482-6779ba3292fe?auto=format&fit=crop&q=80&w=800'
        ],
        'description' => 'A contemporary organza cape draped set in pastel rose, finished with minimal pearl borders and floral printed inner straight pants.',
        'details' => [
            'Fabric: Silk Organza & Satin',
            'Embroidery: Hand-sewn Pearls',
            'Delivery Time: 3 weeks',
            'Fit: Comfort straight fit'
        ]
    ]
];

// Integrate with PostgreSQL. Fallback to local JSON database or array.
require_once __DIR__ . '/db.php';
$db_products = db_get_products();
if ($db_products !== null && !empty($db_products)) {
    $products = $db_products;
} else {
    // Load from local JSON database
    $json_file = __DIR__ . '/products.json';
    if (file_exists($json_file)) {
        $json_content = file_get_contents($json_file);
        $decoded_json = json_decode($json_content, true);
        if (is_array($decoded_json) && !empty($decoded_json)) {
            $products = $decoded_json;
        }
    }
}
