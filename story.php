<?php
include_once 'header.php';
include_once 'data/db.php';
$hp_settings = get_homepage_settings();
$story_title  = htmlspecialchars($hp_settings['story_title'] ?? 'The Anusha Reddy Story');
$story_text   = $hp_settings['story_text'] ?? '';
$story_gallery = $hp_settings['story_gallery'] ?? [];

// Break the story text into paragraphs by blank lines
$paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $story_text)));
?>

<!-- ============================================================
     STORY HERO
============================================================ -->
<section class="story-hero">
    <div class="container">
        <p class="section-subtitle" style="margin-bottom: 0.5rem;">The Label</p>
        <h1><?php echo $story_title; ?></h1>
        <p style="max-width: 600px; margin: 0 auto; font-size: 1rem; color: var(--text-muted);">A story of heritage, craftsmanship, and grace — told one thread at a time.</p>
    </div>
</section>

<!-- ============================================================
     STORY BODY
============================================================ -->
<section class="section container">
    <div class="story-body">
        <?php if (!empty($paragraphs)): ?>
            <?php foreach ($paragraphs as $para): ?>
            <p><?php echo nl2br(htmlspecialchars($para)); ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Our story is coming soon. Check back later.</p>
        <?php endif; ?>
    </div>

    <!-- CTA inside story body -->
    <div style="text-align: center; margin-top: var(--spacing-lg);">
        <p class="section-subtitle" style="margin-bottom: 0.5rem;">Experience It</p>
        <h2 style="font-size: 2rem; margin-bottom: 1.2rem;">Visit Our Studio</h2>
        <a href="contact.php" class="btn btn-gold" style="margin-right: 1rem;">Book a Fitting</a>
        <a href="collections.php" class="btn btn-outline">View Collections</a>
    </div>
</section>

<!-- ============================================================
     STORY PAGE GALLERY (Admin-managed, not on home page)
============================================================ -->
<?php if (!empty($story_gallery)): ?>
<section class="section" style="background-color: #F4F1ED; border-top: 1px solid var(--border-color);">
    <div class="container">
        <p class="section-subtitle" style="text-align: center;">From the Studio</p>
        <h2 class="section-title">Gallery</h2>

        <div class="story-gallery-grid">
            <?php foreach ($story_gallery as $item): ?>
            <div class="story-gallery-item" onclick="openLightbox('<?php echo htmlspecialchars($item['image']); ?>')">
                <img src="<?php echo htmlspecialchars($item['image']); ?>"
                     alt="<?php echo htmlspecialchars($item['caption'] ?? 'Gallery Image'); ?>"
                     class="zoomable"
                     loading="lazy">
                <?php if (!empty($item['caption'])): ?>
                <div class="story-gallery-caption"><?php echo htmlspecialchars($item['caption']); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'footer.php'; ?>
