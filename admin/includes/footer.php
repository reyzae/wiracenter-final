</div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="assets/js/admin-script.js"></script>
    <script src="assets/js/tinymce-functions.js"></script>
    <script src="assets/js/tinymce-init.js"></script>
    
    <script>
        // Toggle the sidebar
        var menuToggle = document.getElementById('menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                document.getElementById('wrapper').classList.toggle('toggled');
            });
        }

        // Select all checkboxes functionality
        function setupSelectAll(checkboxId, itemClass) {
            var selectAllCheckbox = document.getElementById(checkboxId);
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    var itemCheckboxes = document.querySelectorAll(itemClass);
                    for (var i = 0; i < itemCheckboxes.length; i++) {
                        itemCheckboxes[i].checked = this.checked;
                    }
                });
            }
        }

        setupSelectAll('select-all-articles', '.article-checkbox');
        setupSelectAll('select-all-projects', '.project-checkbox');
        setupSelectAll('select-all-tools', '.tool-checkbox');

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text)
                .then(function() {
                    alert('URL copied to clipboard!');
                })
                .catch(function(err) {
                    console.error('Could not copy text: ', err);
                });
        }

        
    </script>

    <?php
    if (function_exists('getSetting')) {
        $google_analytics_id = getSetting('google_analytics_id', '');
        if (!empty($google_analytics_id)):
    ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($google_analytics_id); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo htmlspecialchars($google_analytics_id); ?>');
    </script>
    <?php
        endif;
    }
    ?>
</body>
</html>