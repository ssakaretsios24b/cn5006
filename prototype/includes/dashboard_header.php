<?php
/**
 * Dashboard Header
 * Περιλαμβάνει navbar και sidebar για το dashboard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/rbac.php';

// Απαιτείται login
requireLogin();

$pageTitle = $pageTitle ?? 'Dashboard - Archimedes University';

// Ποιο menu item είναι active
$currentPage = $currentPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?= url('') ?>">
                <i class="bi bi-mortarboard-fill me-2"></i>
                <span>Archimedes</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash = getFlash()): ?>
    <div class="container-fluid mt-3">
        <div class="alert alert-<?= $flash['type'] === 'error'
            ? 'danger'
            : e($flash['type']) ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar d-none d-md-block">
            <ul class="sidebar-nav">
                <li>
                    <a href="<?= url('dashboard/') ?>" class="<?= $currentPage === 'dashboard'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-speedometer2"></i>Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= url('dashboard/courses/') ?>" class="<?= $currentPage === 'courses'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-book"></i>Μαθήματα
                    </a>
                </li>
                <li>
                    <a href="<?= url('dashboard/assignments/') ?>" class="<?= $currentPage ===
'assignments'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-clipboard-check"></i>Εργασίες
                    </a>
                </li>
                <?php if (isStudent()): ?>
                <li>
                    <a href="<?= url('dashboard/grades/') ?>" class="<?= $currentPage === 'grades'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-star"></i>Βαθμοί
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Mobile Bottom Nav -->
        <nav class="sidebar d-md-none">
            <ul class="sidebar-nav">
                <li>
                    <a href="<?= url('dashboard/') ?>" class="<?= $currentPage === 'dashboard'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('dashboard/courses/') ?>" class="<?= $currentPage === 'courses'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-book"></i>
                        <span>Μαθήματα</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('dashboard/assignments/') ?>" class="<?= $currentPage ===
'assignments'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Εργασίες</span>
                    </a>
                </li>
                <?php if (isStudent()): ?>
                <li>
                    <a href="<?= url('dashboard/grades/') ?>" class="<?= $currentPage === 'grades'
    ? 'active'
    : '' ?>">
                        <i class="bi bi-star"></i>
                        <span>Βαθμοί</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="dashboard-content">
