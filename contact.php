<?php
include 'data/products.php';

// Initialize submission flags and variables
$errors = [];
$success = false;
$name = $email = $phone = $type = $date = $message = $product_name = '';

// Pre-fill product if passed in URL query
$selected_product_id = isset($_GET['product']) ? intval($_GET['product']) : 0;
if (isset($products[$selected_product_id])) {
    $product_name = $products[$selected_product_id]['name'];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = strip_tags(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST['phone'] ?? ''));
    $type = strip_tags(trim($_POST['type'] ?? ''));
    $date = strip_tags(trim($_POST['date'] ?? ''));
    $product_name = strip_tags(trim($_POST['product_name'] ?? ''));
    $message = strip_tags(trim($_POST['message'] ?? ''));

    // Validate inputs
    if (empty($name)) $errors[] = "Please enter your name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (empty($phone)) $errors[] = "Please enter your phone number.";
    if (empty($date)) $errors[] = "Please select a preferred date.";

    if (empty($errors)) {
        // Save submission details locally so it works without SMTP configured (excellent for local zip & testing)
        $booking_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'consultation_type' => $type,
            'preferred_date' => $date,
            'inquired_product' => $product_name,
            'message' => $message
        ];

        // Save to booking file
        $file = 'bookings_log.txt';
        $log_entry = "--- BOOKING REQUEST (" . $booking_data['timestamp'] . ") ---\n" .
                     "Name: " . $booking_data['name'] . "\n" .
                     "Email: " . $booking_data['email'] . "\n" .
                     "Phone: " . $booking_data['phone'] . "\n" .
                     "Type: " . $booking_data['consultation_type'] . "\n" .
                     "Preferred Date: " . $booking_data['preferred_date'] . "\n" .
                     "Inquired Design: " . ($booking_data['inquired_product'] ?: 'None') . "\n" .
                     "Message: " . $booking_data['message'] . "\n" .
                     "---------------------------------------------\n\n";
        
        file_put_contents($file, $log_entry, FILE_APPEND | LOCK_EX);

        // Native PHP Mail simulation/attempt
        $to = "contact@anushareddycouture.com";
        $subject = "Couture Consultation Request: " . $name;
        $headers = "From: " . $email . "\r\n" .
                   "Reply-To: " . $email . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        // Attempt to mail (suppressing error if mail host is not configured)
        @mail($to, $subject, $log_entry, $headers);

        // Submit to PostgreSQL database table
        db_create_booking($booking_data);

        // Format details into a structured WhatsApp message
        $whatsapp_text = "*New Couture Fitting Request*\n\n" .
                         "*Name:* " . $name . "\n" .
                         "*Email:* " . $email . "\n" .
                         "*Phone:* " . $phone . "\n" .
                         "*Appointment Type:* " . $type . "\n" .
                         "*Preferred Date:* " . $date . "\n" .
                         "*Inquired Design:* " . ($product_name ? $product_name : 'None') . "\n" .
                         "*Message:* " . ($message ? $message : 'None');

        // Redirect to WhatsApp
        $whatsapp_url = "https://wa.me/917702137501?text=" . rawurlencode($whatsapp_text);
        header("Location: " . $whatsapp_url);
        exit;

        $success = true;
        // Reset form fields on success
        $name = $email = $phone = $type = $date = $message = $product_name = '';
    }
}

include 'header.php';
?>

<section class="section container">
    <p class="section-subtitle">Get in touch</p>
    <h1 class="section-title">Book a Fitting</h1>

    <div class="consultation-layout" style="margin-top: var(--spacing-md);">
        <!-- Side Details Panel -->
        <div class="consultation-info">
            <h2>Our Studio</h2>
            <p>Step into our studio shop. Let us help you find the right fit and design for your special day.</p>
            
            <div class="info-item">
                <i class="fas fa-gem"></i>
                <div>
                    <h4 style="color: var(--accent-gold); font-family: var(--font-sans); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.1em; margin-bottom: 0.2rem;">Bridal Fitting</h4>
                    <p style="font-size: 0.85rem; color: #aaa;">Private fittings, styling, fabric selections, and measurement layouts.</p>
                </div>
            </div>

            <div class="info-item">
                <i class="fas fa-video"></i>
                <div>
                    <h4 style="color: var(--accent-gold); font-family: var(--font-sans); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.1em; margin-bottom: 0.2rem;">Video Call Fitting</h4>
                    <p style="font-size: 0.85rem; color: #aaa;">For global clients, book a video-consultation with our lead stylists.</p>
                </div>
            </div>

            <div class="info-item">
                <i class="fas fa-clock"></i>
                <div>
                    <h4 style="color: var(--accent-gold); font-family: var(--font-sans); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.1em; margin-bottom: 0.2rem;">Opening Hours</h4>
                    <p style="font-size: 0.85rem; color: #aaa;">Monday to Saturday: 10:00 AM - 7:00 PM<br>Sunday: By Prior Appointment Only</p>
                </div>
            </div>
        </div>

        <!-- Consultation Request Form -->
        <div class="consultation-form-wrapper">
            <?php if ($success): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        showCoutureAlert('Booking Success', 'Thank you! Your fitting request has been received. We will reach out to you within 24 hours to confirm your booking.');
                    });
                </script>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const errMsg = <?php echo json_encode(implode("\n• ", $errors)); ?>;
                        showCoutureAlert('Please Correct Errors', '• ' + errMsg);
                    });
                </script>
            <?php endif; ?>

            <form action="contact.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group form-grid-2">
                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@domain.com" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div>
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                </div>

                <div class="form-group form-grid-2">
                    <div>
                        <label for="type">Appointment Type</label>
                        <select id="type" name="type" class="form-control">
                            <option value="Studio Appointment" <?php echo ($type === 'Studio Appointment') ? 'selected' : ''; ?>>Visit Shop in Person</option>
                            <option value="Virtual Video Call" <?php echo ($type === 'Virtual Video Call') ? 'selected' : ''; ?>>Video Call Meeting</option>
                        </select>
                    </div>
                    <div>
                        <label for="date">Preferred Date</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="product_name">Product Name (Optional)</label>
                    <input type="text" id="product_name" name="product_name" class="form-control" placeholder="e.g. The Gulnar Lehenga" value="<?php echo htmlspecialchars($product_name); ?>">
                </div>

                <div class="form-group">
                    <label for="message">Your Requirements / Message</label>
                    <textarea id="message" name="message" class="form-control" placeholder="Mention size specifications, occasion date, custom requirements, etc."><?php echo htmlspecialchars($message); ?></textarea>
                </div>

                <button type="submit" class="btn btn-maroon btn-full" style="padding: 1.1rem; font-weight: 500;">Book Appointment</button>
            </form>
        </div>
    </div>
</section>

<?php
include 'footer.php';
?>
