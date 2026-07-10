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
        <p class="hero-subtitle" style="text-transform: uppercase; letter-spacing: 0.25em; font-size: 0.8rem; font-weight: 500; margin-bottom: 1rem; color: #FDFBF7;">Anusha Reddy Couture</p>
        <h1 class="hero-title" style="font-family: var(--font-serif); font-size: 3.5rem; margin-bottom: 1rem; color: #FDFBF7;">Handcrafted Elegance</h1>
        <p style="color: #EBE0D5; margin-bottom: 2.5rem; font-size: 1.15rem; font-weight: 300; letter-spacing: 0.05em; max-width: 600px; margin-left: auto; margin-right: auto;">Bespoke Indian Couture &amp; Custom Bridal Wear</p>
        <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
            <a href="contact.php" class="btn btn-maroon" style="padding: 0.9rem 2.2rem; font-size: 0.8rem; letter-spacing: 0.15em;">Book Fitting</a>
            <a href="collections.php" class="btn btn-outline" style="padding: 0.9rem 2.2rem; border-color: #FDFBF7; color: #FDFBF7; font-size: 0.8rem; letter-spacing: 0.15em;">View Collections</a>
        </div>
    </div>
</section>

<!-- ============================================================
     ABOUT THE LABEL – Dynamic Story Intro
============================================================ -->
<section class="section container" style="text-align: center; max-width: 800px; padding-bottom: 4rem;">
    <h2 class="section-title" style="font-size: 2rem; color: var(--text-primary); font-weight: 400; margin-bottom: 1.5rem;">Anusha Reddy</h2>
    <p style="font-size: 1.05rem; line-height: 1.9; color: var(--text-muted); font-weight: 300;">
        <?php echo htmlspecialchars($story_intro); ?>
    </p>
    <a href="story.php" class="btn btn-outline" style="margin-top: 2rem; font-size: 0.8rem; letter-spacing: 0.15em; padding: 0.8rem 2rem;">Read Our Story</a>
</section>

<!-- ============================================================
     BENTO-GRID PRODUCT SHOWCASE
============================================================ -->
<section class="section container" style="padding-top: var(--spacing-sm); padding-bottom: var(--spacing-lg);">
    <div style="text-align: center; margin-bottom: 3.5rem;">
        <p class="section-subtitle" style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.75rem; color: var(--accent-gold); font-weight: 600; margin-bottom: 0.5rem;">Signature Lines</p>
        <h2 style="font-family: var(--font-serif); font-size: 2.2rem; color: var(--text-primary); font-weight: 400; letter-spacing: 0.03em;">The Collections</h2>
    </div>
    
    <div class="bento-grid">
        <!-- Card 1: Bridal Wear (Double width / large) -->
        <a href="collections.php?category=Bridal+Wear" class="bento-card bento-large" style="background-image: url('https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&q=80&w=800');">
            <div class="bento-card-overlay"></div>
            <div class="bento-card-content">
                <span class="bento-tag">Bespoke Bridal</span>
                <h3>The Bridal Edit</h3>
                <span class="bento-link">Explore Collection <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>
        
        <!-- Card 2: Festive Wear (Standard size) -->
        <a href="collections.php?category=Festive+Collection" class="bento-card bento-standard" style="background-image: url('https://images.unsplash.com/photo-1610030469668-93535c17b6b3?auto=format&fit=crop&q=80&w=800');">
            <div class="bento-card-overlay"></div>
            <div class="bento-card-content">
                <span class="bento-tag">Traditional Heritage</span>
                <h3>Festive Wear</h3>
                <span class="bento-link">Explore Collection <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>
        
        <!-- Card 3: Pret-A-Porter (Standard size) -->
        <a href="collections.php?category=Pret-A-Porter" class="bento-card bento-standard" style="background-image: url('https://images.unsplash.com/photo-1609357605129-26f69add5d6e?auto=format&fit=crop&q=80&w=800');">
            <div class="bento-card-overlay"></div>
            <div class="bento-card-content">
                <span class="bento-tag">Ready To Wear</span>
                <h3>Pret-A-Porter</h3>
                <span class="bento-link">Explore Collection <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <!-- Card 4: Bespoke Couture (Wide size) -->
        <a href="collections.php?category=Bespoke+Couture" class="bento-card bento-large" style="background-image: url('https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&q=80&w=800');">
            <div class="bento-card-overlay"></div>
            <div class="bento-card-content">
                <span class="bento-tag">Handcrafted Masterpieces</span>
                <h3>Bespoke Couture</h3>
                <span class="bento-link">Explore Collection <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>
    </div>
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
                <a href="collections.php?search=<?php echo urlencode($celeb['name']); ?>" class="celebrity-card">
                    <div class="celebrity-image-wrapper">
                        <img src="<?php echo htmlspecialchars($celeb['image']); ?>" alt="<?php echo htmlspecialchars($celeb['name']); ?>" loading="lazy">
                    </div>
                    <div class="celebrity-name"><?php echo htmlspecialchars($celeb['name']); ?></div>
                </a>
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
        <a href="contact.php" class="btn btn-maroon">Book An Appointment</a>
    </div>
</section>

<?php
include 'footer.php';
?>
