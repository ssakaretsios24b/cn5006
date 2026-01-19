/**
 * Archimedes University - Main JS
 * Form enhancements και utilities
 */

document.addEventListener('DOMContentLoaded', function () {
    // Auto-loading σε forms με class 'needs-loading'
    document.querySelectorAll('form.needs-loading').forEach((form) => {
        form.addEventListener('submit', function () {
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
            }
        });
    });

    // Confirm dialogs σε delete links/buttons
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', function (e) {
            const message = this.dataset.confirm || 'Είσαι σίγουρος;';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Password visibility toggle
    document.querySelectorAll('.password-toggle').forEach((toggle) => {
        toggle.addEventListener('click', function () {
            const input = document.querySelector(this.dataset.target);
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                this.innerHTML =
                    type === 'password'
                        ? '<i class="bi bi-eye"></i>'
                        : '<i class="bi bi-eye-slash"></i>';
            }
        });
    });

    // File input custom label
    document.querySelectorAll('.custom-file-input').forEach((input) => {
        input.addEventListener('change', function () {
            const label = this.nextElementSibling;
            if (label && this.files.length > 0) {
                label.textContent = this.files[0].name;
            }
        });
    });

    // Auto-dismiss alerts μετά από 5 δευτερόλεπτα
    document.querySelectorAll('.alert.auto-dismiss').forEach((alert) => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Tooltips initialization
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach((el) => new bootstrap.Tooltip(el));

    // Form validation styling
    document.querySelectorAll('form.needs-validation').forEach((form) => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});

/**
 * Helper για να φορτώνει το χάρτη Leaflet
 */
function initCampusMap(elementId, lat, lng) {
    if (typeof L === 'undefined') {
        console.error('Leaflet not loaded');
        return null;
    }

    const map = L.map(elementId).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    // Marker για το campus
    const marker = L.marker([lat, lng]).addTo(map);
    marker
        .bindPopup(
            `
        <strong>Archimedes University</strong><br>
        Πολυτεχνική Σχολή<br>
        Πλατεία Συντάγματος
    `
        )
        .openPopup();

    return map;
}

/**
 * Confirm modal helper
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Φορματάρει ημερομηνία για display
 */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('el-GR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
