    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="uploads/logo.png" alt="Anusha Reddy Couture" class="footer-logo-img">
                <div class="social-links">
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://pinterest.com" target="_blank" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                    <a href="https://wa.me/917702137501" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h3>Explore</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="collections.php">Shop</a></li>
                    <li><a href="contact.php">Book Fitting</a></li>
                </ul>
            </div>
            
            <div class="footer-contact">
                <h3>Our Address</h3>
                <p><i class="fas fa-map-marker-alt"></i> Road No. 36, Jubilee Hills,<br>Hyderabad, Telangana 500033</p>
                <p><i class="fas fa-phone-alt"></i> +91 77021 37501</p>
                <p><i class="fas fa-envelope"></i> teamanushareddy@gmail.com</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Anusha Reddy Couture. All Rights Reserved. Crafted with elegance.</p>
        </div>
    </footer>

    <!-- Premium Image Lightbox Popup -->
    <div id="imageLightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <div class="lightbox-content-wrapper" onclick="event.stopPropagation()">
            <img id="lightboxImage" class="lightbox-img" src="" alt="Zoom view">
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script src="js/script.js"></script>
</body>
</html>
