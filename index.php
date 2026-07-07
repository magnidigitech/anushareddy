<?php
include_once 'header.php';
include_once 'data/products.php';
include_once 'data/db.php';
$hp_settings = get_homepage_settings();
$hero_images  = !empty($hp_settings['hero_images']) ? $hp_settings['hero_images'] : ['uploads/hero.jpg'];
$story_title  = $hp_settings['story_title'];
$story_intro  = explode("\n", $hp_settings['story_text'])[0]; // First paragraph only for homepage
?>

<!-- ============================================================
     HERO SECTION – Dynamic Slideshow
============================================================ -->
<section class="hero hero-slideshow">
    <!-- Slides -->
    <div class="hero-slides">
        <?php foreach ($hero_images as $i => $himg): ?>
        <div class="hero-slide<?php echo $i === 0 ? ' active' : ''; ?>" style="background-image: url('<?php echo htmlspecialchars($himg); ?>');"></div>
        <?php endforeach; ?>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="hero-subtitle">Welcome to Anusha Reddy Couture</p>
        <h1 class="hero-title">Beautiful Dresses. Handcrafted Elegance.</h1>
        <p style="color: #ccc; margin-bottom: 2rem; font-size: 1.1rem; font-weight: 300;">Explore custom bridal wear, ready to wear dresses, and festive wear.</p>
        <a href="collections.php" class="btn btn-gold">Shop All Products</a>
    </div>
</section>

<!-- ============================================================
     ABOUT THE LABEL – Dynamic Story Intro
============================================================ -->
<section class="section container" style="text-align: center; max-width: 800px; padding-bottom: 3rem;">
    <h2 class="section-title">Anusha Reddy</h2>
    <p style="margin-top: 1.5rem; font-size: 1.1rem; line-height: 1.8;">
        <?php echo htmlspecialchars($story_intro); ?>
    </p>
    <a href="story.php" class="btn btn-outline" style="margin-top: 2rem;">Read Our Story</a>
</section>

<!-- ============================================================
     CELEBRITY SHOWCASE (replaces Featured Products)
============================================================ -->
<?php
$celebrities = db_get_celebrities();
if (!empty($celebrities)):
    $display_celebrities = array_merge($celebrities, $celebrities, $celebrities, $celebrities);
?>
<section class="celebrity-section" style="border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container" style="text-align: center; margin-bottom: 2rem;">
        <h2 class="celebrity-title">IN ANUSHA REDDY</h2>
        <p class="celebrity-tagline">
            Where dreams are draped in elegance<br>and stars become muse.
        </p>
    </div>

    <div class="celebrity-marquee">
        <div class="celebrity-track" data-count="<?php echo count($celebrities); ?>">
            <?php foreach ($display_celebrities as $celeb): ?>
                <div class="celebrity-card">
                    <div class="celebrity-image-wrapper">
                        <img src="<?php echo htmlspecialchars($celeb['image']); ?>" alt="<?php echo htmlspecialchars($celeb['name']); ?>" loading="lazy">
                    </div>
                    <div class="celebrity-name"><?php echo htmlspecialchars($celeb['name']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div style="text-align: center; margin-top: 2rem;">
        <a href="collections.php" class="view-all-link">VIEW ALL</a>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     CALL TO ACTION – Book An Appointment
============================================================ -->
<section class="section container" style="padding: var(--spacing-xl) var(--spacing-md); text-align: center;">
    <div style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); padding: var(--spacing-lg); max-width: 900px; margin: 0 auto; box-shadow: var(--shadow-subtle);">
        <p class="section-subtitle">Personalized Experience</p>
        <h2 style="font-size: 2.2rem; margin-bottom: 1rem;">Design Your Dream Outfit</h2>
        <p style="max-width: 600px; margin: 0 auto 2rem auto; color: var(--text-muted);">
            Book an appointment at our Hyderabad shop or online to get custom bridal and festive dresses.
        </p>
        <a href="contact.php" class="btn btn-gold">Book An Appointment</a>
    </div>
</section>

<?php
include 'footer.php';
?>
