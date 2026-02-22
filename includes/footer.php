    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">ট্রাক কই</h3>
                    <p class="footer-text">বাংলাদেশের সবচেয়ে বিশ্বস্ত ট্রাক ট্র্যাকিং সিস্টেম</p>
                </div>
                <div class="footer-section">
                    <h4 class="footer-heading">দ্রুত লিংক</h4>
                    <ul class="footer-links">
                        <li><a href="#about">আমাদের সম্পর্কে</a></li>
                        <li><a href="#features">ফিচার সমূহ</a></li>
                        <li><a href="#pricing">মূল্য তালিকা</a></li>
                        <li><a href="#contact">যোগাযোগ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-heading">সহায়তা</h4>
                    <ul class="footer-links">
                        <li><a href="#help">সাহায্য কেন্দ্র</a></li>
                        <li><a href="#terms">শর্তাবলী</a></li>
                        <li><a href="#privacy">গোপনীয়তা নীতি</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-heading">যোগাযোগ করুন</h4>
                    <ul class="footer-contact">
                        <li>📞 +৮৮০ ১৭xxxxxxxx</li>
                        <li>✉️ info@truckkoi.com</li>
                        <li>📍 ঢাকা, বাংলাদেশ</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ট্রাক কই। সর্বস্বত্ব সংরক্ষিত।</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php if (isset($current_page)): ?>
        <?php if ($current_page == 'index.php'): ?>
            <script src="script.js"></script>
            <script src="tracker.js"></script>
        <?php endif; ?>
        
        <?php if ($current_page == 'dashboard.php'): ?>
            <script src="dashboard.js"></script>
        <?php endif; ?>
        
        <?php if ($current_page == 'login.php' || $current_page == 'register.php'): ?>
            <script src="auth.js"></script>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Common Scripts (Dropdowns, etc.) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Re-initialize dropdowns if needed since header is now dynamic
             const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });
                
                document.addEventListener('click', function(e) {
                    if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>
