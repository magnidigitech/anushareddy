<?php
// Validate WhatsApp URL
$whatsapp_url = isset($_GET['whatsapp']) ? $_GET['whatsapp'] : '';

// Fallback to default contact WhatsApp if none provided
if (empty($whatsapp_url)) {
    $whatsapp_url = "https://wa.me/917702137501";
}

// Clean and sanitize the URL for JavaScript redirection
$whatsapp_url_clean = htmlspecialchars($whatsapp_url, ENT_QUOTES, 'UTF-8');

include 'header.php';
?>

<section class="section container" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center;">
    <div style="max-width: 600px; padding: var(--spacing-md); background: var(--bg-secondary); border: 1px solid var(--border-color); box-shadow: var(--shadow-premium); border-radius: 8px; margin: 2rem auto;">
        <div style="font-size: 3rem; color: var(--accent-gold); margin-bottom: var(--spacing-sm);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <h1 class="section-title" style="margin-bottom: var(--spacing-sm); font-size: 2.2rem; font-family: var(--font-serif); font-weight: 400;">Thank You</h1>
        <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: var(--spacing-md); font-weight: 300;">
            Your fitting request has been received. To complete your booking and coordinate specific timings, we are redirecting you to chat with our lead stylist on WhatsApp.
        </p>
        
        <div style="margin-bottom: var(--spacing-md); display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <div class="luxury-loader" style="width: 40px; height: 40px; border: 2px solid var(--border-color); border-top-color: var(--accent-gold); border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="font-size: 0.85rem; color: var(--accent-gold); font-family: var(--font-sans); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.5rem;">Redirecting to WhatsApp...</p>
        </div>

        <a href="<?php echo $whatsapp_url_clean; ?>" class="btn btn-maroon" style="display: inline-block; padding: 1rem 2rem; font-weight: 500;">
            Continue to WhatsApp Now
        </a>
    </div>
</section>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
.luxury-loader {
    display: inline-block;
}
</style>

<script>
// Automatically redirect the user to the WhatsApp link after 2.5 seconds
setTimeout(function() {
    window.location.href = "<?php echo addslashes($whatsapp_url); ?>";
}, 2500);
</script>

<?php
include 'footer.php';
?>
