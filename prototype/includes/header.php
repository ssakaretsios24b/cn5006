<?php
/**
 * Common Header
 * Περιλαμβάνει HTML head, navbar και flash messages
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Τίτλος σελίδας - μπορεί να οριστεί πριν το include
$pageTitle = $pageTitle ?? 'Archimedes University';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Leaflet CSS - μόνο αν χρειάζεται χάρτης -->
    <?php if (!empty($needsMap)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?php endif; ?>

    <!-- Swiper CSS - μόνο αν χρειάζεται slider -->
    <?php if (!empty($needsSwiper)): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <?php endif; ?>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= url('') ?>">
                <i class="bi bi-mortarboard-fill me-2"></i>
                <span>Archimedes University</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('dashboard/') ?>">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= e(getUsername()) ?>
                                <span class="badge bg-secondary ms-1">
                                    <?= isTeacher() ? 'Καθηγητής' : 'Φοιτητής' ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= url('auth/logout.php') ?>">
                                        <i class="bi bi-box-arrow-right me-2"></i>Αποσύνδεση
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('auth/login.php') ?>">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Σύνδεση
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('auth/register.php') ?>">
                                <i class="bi bi-person-plus me-1"></i>Εγγραφή
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash = getFlash()): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flash['type'] === 'error'
            ? 'danger'
            : e($flash['type']) ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
