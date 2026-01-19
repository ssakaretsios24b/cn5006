    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <!-- About + Contact -->
                <div class="col-lg-5 col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-mortarboard-fill text-secondary me-2"></i>Archimedes University
                    </h5>
                    <p class="text-light small mb-3">
                        Βρισκόμαστε στην Πλατεία Συντάγματος, στο κέντρο της Αθήνας.
                        Εύκολη πρόσβαση με Μετρό και όλα τα μέσα μαζικής μεταφοράς.
                    </p>
                    <ul class="list-unstyled small text-light">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt text-secondary me-2"></i>Πλατεία Συντάγματος, Αθήνα 10563
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone text-secondary me-2"></i>210 123 4567
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope text-secondary me-2"></i>info@archimedes.edu.gr
                        </li>
                        <li>
                            <i class="bi bi-clock text-secondary me-2"></i>Δευ - Παρ: 09:00 - 21:00
                        </li>
                    </ul>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase mb-3">Σύνδεσμοι</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= url('') ?>">Αρχική</a></li>
                        <li><a href="<?= url('') ?>#about">Σχετικά</a></li>
                        <li><a href="<?= url('') ?>#programs">Προγράμματα</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?= url('dashboard/') ?>">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="<?= url('auth/login.php') ?>">Σύνδεση</a></li>
                            <li><a href="<?= url('auth/register.php') ?>">Εγγραφή</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Social -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-uppercase mb-3">Ακολουθήστε μας</h6>
                    <div class="social-links">
                        <a href="https://facebook.com" target="_blank" class="social-link" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-link" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://x.com" target="_blank" class="social-link" title="Twitter/X">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" class="social-link" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="social-link" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        Μείνετε ενημερωμένοι για νέα<br>και εκδηλώσεις.
                    </p>
                </div>
            </div>

            <!-- Bottom bar -->
            <hr class="my-4 border-secondary">
            <div class="text-center">
                <p class="text-white-50 small mb-0">
                    CN5006 - Web & Mobile Applications Development 2025-2026<br>
                    <a href="mailto:ssakaretsios24b@amcstudent.edu.gr" class="text-secondary">ssakaretsios24b@amcstudent.edu.gr</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leaflet JS - μόνο αν χρειάζεται χάρτης -->
    <?php if (!empty($needsMap)): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>

    <!-- Swiper JS - μόνο αν χρειάζεται slider -->
    <?php if (!empty($needsSwiper)): ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <?php endif; ?>

    <!-- App JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
