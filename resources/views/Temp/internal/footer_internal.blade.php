
        </div>

        <div class="internal-footer">
            Internal BOD Dashboard • dark market theme • engineered for fast executive reading
        </div>
    </div>
</div>

<script>
    (function () {
        const layout = document.getElementById('internalLayout');
        const toggle = document.getElementById('internalSidebarToggle');
        const backdrop = document.getElementById('internalSidebarBackdrop');
        const navLinks = document.querySelectorAll('.internal-nav-link');

        if (toggle && layout) {
            toggle.addEventListener('click', function () {
                layout.classList.toggle('sidebar-open');
            });
        }

        if (backdrop && layout) {
            backdrop.addEventListener('click', function () {
                layout.classList.remove('sidebar-open');
            });
        }

        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 1200) {
                    layout.classList.remove('sidebar-open');
                }
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 1200) {
                layout.classList.remove('sidebar-open');
            }
        });
    })();
</script>
</body>
</html>
