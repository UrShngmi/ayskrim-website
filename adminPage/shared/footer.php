<?php
// Shared admin footer
$currentYear = date('Y');
?>

<footer class="admin-footer">
    <div class="footer-content">
        <div class="footer-copyright">
            &copy; <?php echo $currentYear; ?> Ayskrim Ice Cream. All rights reserved.
        </div>
        <div class="footer-links">
            <a href="#" class="footer-link">Privacy Policy</a>
            <a href="#" class="footer-link">Terms of Service</a>
            <a href="#" class="footer-link">Help Center</a>
        </div>
    </div>
</footer>

<!-- Load JavaScript libraries and scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="/ayskrimWebsite/adminPage/shared/admin.js"></script>

<!-- Page-specific scripts can be added here -->
<?php if (isset($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?php echo $script; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
