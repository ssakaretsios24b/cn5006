<?php
/**
 * Archimedes University - Αρχική Σελίδα
 * Δημόσια σελίδα με πληροφορίες campus και χάρτη
 */

$pageTitle = 'Archimedes University - Αρχική';
$needsMap = true; // φορτώνει το Leaflet
$needsSwiper = true; // φορτώνει το Swiper για gallery

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <i class="bi bi-mortarboard-fill hero-logo"></i>
                <h1>Archimedes University</h1>
                <p class="lead mb-4">
                    Πολυτεχνική Σχολή με έδρα στην καρδιά της Αθήνας.
                    Προετοιμάζουμε τους μηχανικούς του αύριο με σύγχρονες
                    τεχνολογίες και πρακτική εκπαίδευση.
                </p>
                <div class="mt-4">
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= url(
                            'auth/register.php',
                        ) ?>" class="btn btn-secondary btn-lg me-2">
                            <i class="bi bi-person-plus me-1"></i>Εγγραφή
                        </a>
                        <a href="<?= url('auth/login.php') ?>" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Σύνδεση
                        </a>
                    <?php else: ?>
                        <a href="<?= url('dashboard/') ?>" class="btn btn-secondary btn-lg">
                            <i class="bi bi-speedometer2 me-1"></i>Πήγαινε στο Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <a href="#about" class="scroll-indicator">
        <i class="bi bi-mouse"></i>
        <span>Περισσότερα</span>
        <i class="bi bi-chevron-down chevron-bounce"></i>
    </a>
</section>

<!-- About Section -->
<section id="about" class="pt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="text-primary mb-3">Γιατί Archimedes;</h2>
                <p class="text-muted">
                    Το Archimedes University προσφέρει προγράμματα σπουδών που συνδυάζουν
                    θεωρητική γνώση με πρακτική εφαρμογή. Οι φοιτητές μας αποκτούν
                    δεξιότητες που ζητά η αγορά εργασίας.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Campus Gallery -->
<section class="campus-gallery">
    <div class="container">
        <div class="swiper campus-swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="<?= asset('images/archuni1.jpg') ?>" alt="Campus 1">
                </div>
                <div class="swiper-slide">
                    <img src="<?= asset('images/archuni2.jpg') ?>" alt="Campus 2">
                </div>
                <div class="swiper-slide">
                    <img src="<?= asset('images/archuni3.jpg') ?>" alt="Campus 3">
                </div>
                <div class="swiper-slide">
                    <img src="<?= asset('images/archuni4.jpg') ?>" alt="Campus 4">
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.campus-swiper', {
        effect: 'coverflow',
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        loop: true,
        coverflowEffect: {
            rotate: 0,
            stretch: 0,
            depth: 100,
            modifier: 2,           
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        autoplay: {
            delay: 2000,
            disableOnInteraction: false
        }
    });
});
</script>

<!-- Features Cards -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-cpu text-secondary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Πληροφορική & Μηχανική</h5>
                        <p class="text-muted">
                            Προγράμματα σε Web Development, Mobile Apps,
                            Software Engineering και Data Science.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-people text-secondary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Εξειδικευμένοι Καθηγητές</h5>
                        <p class="text-muted">
                            Διδακτικό προσωπικό με εμπειρία στη βιομηχανία
                            και ακαδημαϊκό υπόβαθρο.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-briefcase text-secondary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Σύνδεση με την Αγορά</h5>
                        <p class="text-muted">
                            Πρακτική άσκηση και συνεργασίες με κορυφαίες
                            εταιρείες τεχνολογίας.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Campus Location -->
<section id="contact" class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <h2 class="text-primary mb-3">
                    <i class="bi bi-geo-alt me-2"></i>Το Campus μας
                </h2>
                <p>
                    Βρισκόμαστε στην Πλατεία Συντάγματος, στο κέντρο της Αθήνας.
                    Εύκολη πρόσβαση με Μετρό και όλα τα μέσα μαζικής μεταφοράς.
                </p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-geo-alt text-secondary me-2"></i>
                        Πλατεία Συντάγματος, Αθήνα 10563
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-telephone text-secondary me-2"></i>
                        210 123 4567
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-envelope text-secondary me-2"></i>
                        info@archimedes.edu.gr
                    </li>
                    <li>
                        <i class="bi bi-clock text-secondary me-2"></i>
                        Δευτ - Παρ: 09:00 - 21:00
                    </li>
                </ul>
            </div>
            <div class="col-lg-7">
                <div class="map-container">
                    <div id="campus-map"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Programs Section -->
<section id="programs" class="py-5">
    <div class="container">
        <h2 class="text-primary text-center mb-5">Προγράμματα Σπουδών</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle text-secondary mb-2">BSc</h6>
                        <h5 class="card-title">Computer Science</h5>
                        <p class="card-text small text-muted">
                            Αλγόριθμοι, δομές δεδομένων, λειτουργικά συστήματα
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle text-secondary mb-2">BSc</h6>
                        <h5 class="card-title">Web Development</h5>
                        <p class="card-text small text-muted">
                            Frontend, Backend, Full-stack, DevOps, Cloud Services
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle text-secondary mb-2">BSc</h6>
                        <h5 class="card-title">Mobile Apps</h5>
                        <p class="card-text small text-muted">
                            iOS, Android, React Native, Flutter, Cross-platform
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle text-secondary mb-2">MSc</h6>
                        <h5 class="card-title">Artificial Intelligence</h5>
                        <p class="card-text small text-muted">
                            Machine Learning, Deep Learning, NLP, Computer Vision
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-3">Έτοιμος να ξεκινήσεις;</h2>
        <p class="lead mb-4">
            Εγγράψου τώρα και γίνε μέρος της κοινότητας του Archimedes University.
        </p>
        <?php if (!isLoggedIn()): ?>
            <a href="<?= url('auth/register.php') ?>" class="btn btn-secondary btn-lg">
                <i class="bi bi-person-plus me-1"></i>Εγγραφή Τώρα
            </a>
        <?php else: ?>
            <a href="<?= url('dashboard/') ?>" class="btn btn-secondary btn-lg">
                <i class="bi bi-speedometer2 me-1"></i>Συνέχισε στο Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Initialize Map -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Συντεταγμένες Πλατείας Συντάγματος
    initCampusMap('campus-map', 37.9755, 23.7348);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
