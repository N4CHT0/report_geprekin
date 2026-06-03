</main>
</div>

@stack('scripts')

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('temp/lte/dist/js/adminlte.js') }}"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleBtn = document.getElementById("auditSidebarToggle");
        const searchInput = document.getElementById("sidebarSearchInput");
        const navigation = document.getElementById("navigation");

        if (toggleBtn) {
            toggleBtn.addEventListener("click", function (e) {
                e.preventDefault();
                if (window.innerWidth <= 991.98) {
                    document.body.classList.toggle("sidebar-open");
                    document.body.classList.remove("sidebar-collapse");
                } else {
                    document.body.classList.toggle("sidebar-collapse");
                    document.body.classList.remove("sidebar-open");
                }
            });
        }

        if (searchInput && navigation) {
            searchInput.addEventListener("input", function () {
                let q = this.value.toLowerCase();
                navigation.querySelectorAll(".nav-item").forEach(li => {
                    let text = li.innerText.toLowerCase();
                    li.style.display = text.includes(q) ? "" : "none";
                });
            });
        }
    });
</script>
</body>

</html>