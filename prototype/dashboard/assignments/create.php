<?php
/**
 * Δημιουργία Εργασίας
 * Μόνο για καθηγητές
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/rbac.php';

requireTeacher();

$db = getDB();
$userId = getUserId();

// Παίρνουμε τα μαθήματα του καθηγητή
$stmt = $db->prepare('SELECT id, name FROM courses WHERE teacher_id = ? ORDER BY name');
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();

if (empty($courses)) {
    setFlash('warning', 'Πρέπει πρώτα να δημιουργήσεις ένα μάθημα.');
    redirect(url('dashboard/courses/create.php'));
}

// Pre-selected course αν περάστηκε από URL
$selectedCourse = (int) get('course_id');

$pageTitle = 'Νέα Εργασία - Archimedes University';
$currentPage = 'assignments';

$errors = [];
$old = ['course_id' => $selectedCourse];

if (isPost()) {
    $courseId = (int) post('course_id');
    $title = post('title');
    $description = post('description');
    $dueDate = post('due_date');
    $dueTime = post('due_time');

    $old = compact('courseId', 'title', 'description', 'dueDate', 'dueTime');
    $old['course_id'] = $courseId;

    // Validation
    if (empty($courseId)) {
        $errors[] = 'Επίλεξε μάθημα';
    } else {
        // Έλεγχος ότι το μάθημα ανήκει στον καθηγητή
        $stmt = $db->prepare('SELECT id FROM courses WHERE id = ? AND teacher_id = ?');
        $stmt->execute([$courseId, $userId]);
        if (!$stmt->fetch()) {
            $errors[] = 'Μη έγκυρο μάθημα';
        }
    }

    if (empty($title)) {
        $errors[] = 'Ο τίτλος είναι υποχρεωτικός';
    }

    // Συνδυασμός date και time
    $dueDatetime = null;
    if (!empty($dueDate)) {
        $dueDatetime = $dueDate;
        if (!empty($dueTime)) {
            $dueDatetime .= ' ' . $dueTime;
        } else {
            $dueDatetime .= ' 23:59:59';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare('
                INSERT INTO assignments (course_id, title, description, due_date)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$courseId, $title, $description, $dueDatetime]);

            setFlash('success', 'Η εργασία δημιουργήθηκε επιτυχώς!');
            redirect(url('dashboard/assignments/'));
        } catch (PDOException $e) {
            $errors[] = 'Σφάλμα βάσης: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/dashboard_header.php';
?>

<div class="mb-4">
    <a href="<?= url('dashboard/assignments/') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω στις Εργασίες
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clipboard-plus me-2"></i>Δημιουργία Εργασίας</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-loading">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="course_id" class="form-label">Μάθημα *</label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <option value="">Επίλεξε μάθημα...</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>"
                                    <?= ($old['course_id'] ?? '') == $course['id']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= e($course['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Τίτλος *</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= e($old['title'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Περιγραφή / Οδηγίες</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="5"><?= e($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Προθεσμία (Ημερομηνία)</label>
                            <input type="date" class="form-control" id="due_date" name="due_date"
                                   value="<?= e($old['dueDate'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_time" class="form-label">Ώρα</label>
                            <input type="time" class="form-control" id="due_time" name="due_time"
                                   value="<?= e($old['dueTime'] ?? '23:59') ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Δημιουργία
                    </button>
                    <a href="<?= url(
                        'dashboard/assignments/',
                    ) ?>" class="btn btn-outline-secondary">Ακύρωση</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/dashboard_footer.php'; ?>
