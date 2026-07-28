<?php
include 'data/products.php';

// Initialize submission flags and variables
$errors = [];
$success = false;
$name = $email = $phone = $message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = strip_tags(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST['phone'] ?? ''));
    $message = strip_tags(trim($_POST['message'] ?? ''));

    // Validate inputs
    if (empty($name)) $errors[] = "Please enter your name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (empty($phone)) $errors[] = "Please enter your phone number.";

    if (empty($errors)) {
        // Save submission details locally
        $booking_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'consultation_type' => 'Contact Inquiry',
            'preferred_date' => date('Y-m-d'),
            'inquired_product' => 'General Inquiry',
            'message' => $message
        ];

        // Save to booking log file
        $file = 'bookings_log.txt';
        $log_entry = "--- CONTACT INQUIRY (" . $booking_data['timestamp'] . ") ---\n" .
                     "Name: " . $booking_data['name'] . "\n" .
                     "Email: " . $booking_data['email'] . "\n" .
                     "Phone: " . $booking_data['phone'] . "\n" .
                     "Message: " . $booking_data['message'] . "\n" .
                     "---------------------------------------------\n\n";
        
        file_put_contents($file, $log_entry, FILE_APPEND | LOCK_EX);

        // Native PHP Mail simulation/attempt
        $to = "contact@anushareddycouture.com";
        $subject = "New Contact Inquiry from " . $name;
        $headers = "From: " . $email . "\r\n" .
                   "Reply-To: " . $email . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        @mail($to, $subject, $log_entry, $headers);

        // Submit to PostgreSQL database table
        db_create_booking($booking_data);

        // Format details into a structured WhatsApp message
        $whatsapp_text = "*New Contact Inquiry*\n\n" .
                         "*Name:* " . $name . "\n" .
                         "*Email:* " . $email . "\n" .
                         "*Phone:* " . $phone . "\n" .
                         "*Message:* " . ($message ? $message : 'None');

        // Redirect to Thank You page first to ensure Meta tracking triggers
        $whatsapp_url = "https://wa.me/917702137501?text=" . rawurlencode($whatsapp_text);
        header("Location: thank-you.php?whatsapp=" . urlencode($whatsapp_url));
        exit;
    }
}

include 'header.php';
?>

<section class="section container" style="min-height: 70vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding-top: 3rem; padding-bottom: 3rem;">
    <div style="width: 100%; max-width: 600px; text-align: center; margin-bottom: 2rem;">
        <p class="section-subtitle">Get in touch</p>
        <h1 class="section-title" style="margin-bottom: 1rem;">Contact Us</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 300; line-height: 1.6;">We would love to hear from you. Please fill out the form below to connect with our studio.</p>
    </div>

    <!-- Centered Form Card -->
    <div style="width: 100%; max-width: 600px; padding: 2.5rem; background: var(--bg-secondary); border: 1px solid var(--border-color); box-shadow: var(--shadow-premium); border-radius: 8px;">
        <?php if (!empty($errors)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const errMsg = <?php echo json_encode(implode("\n• ", $errors)); ?>;
                    showCoutureAlert('Please Correct Errors', '• ' + errMsg);
                });
            </script>
        <?php endif; ?>

        <form action="contact.php" method="POST" autocomplete="off">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="name" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; font-weight: 500;">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="mail@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="phone" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; font-weight: 500;">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="message" style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; font-weight: 500;">Your Message</label>
                <textarea id="message" name="message" class="form-control" placeholder="Tell us how we can help you..."><?php echo htmlspecialchars($message); ?></textarea>
            </div>

            <button type="submit" class="btn btn-maroon btn-full">Send Message</button>
        </form>
    </div>
</section>

<?php
include 'footer.php';
?>
