</main>

<link rel="stylesheet" href="/ayskrimWebsite/shared/footer/footer.css">

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3 class="footer-title">Sweet Scoops Catering</h3>
                <p class="footer-text">Making your events sweeter with premium ice cream catering services.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#" class="footer-link">Our Packages</a></li>
                    <li><a href="#" class="footer-link">Flavors</a></li>
                    <li><a href="#" class="footer-link">Book an Event</a></li>
                    <li><a href="#" class="footer-link">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3 class="footer-title">Contact Us</h3>
                <address class="footer-address">
                    <p>123 Ice Cream Lane</p>
                    <p>Sweet City, SC 12345</p>
                    <p>Phone: (555) 123-4567</p>
                    <p>Email: hello@sweetscoops.com</p>
                </address>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="copyright">© <?php echo date('Y'); ?> Sweet Scoops Catering. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php if(isset($pageJS)): ?>
<script src="<?php echo $pageJS; ?>"></script>
<?php endif; ?>
</body>
</html>