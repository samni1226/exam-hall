<?php
/**
 * Shared helper functions used across the project.
 */

// Always start the session once, from a single place.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape output safely for HTML */
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect helper */
function redirect($url) {
    header("Location: $url");
    exit;
}

/** Require an admin to be logged in - used at the top of every admin page */
function require_admin() {
    if (empty($_SESSION['admin_id'])) {
        redirect('../login.php');
    }
}

/** Require a student to be logged in - used at the top of every student page */
function require_student() {
    if (empty($_SESSION['student_id'])) {
        redirect('../student_login.php');
    }
}

/** Simple flash-message helper (stored in session, shown once) */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Basic required-field validator. Returns array of errors. */
function validate_required(array $fields) {
    $errors = [];
    foreach ($fields as $label => $value) {
        if ($value === null || trim((string)$value) === '') {
            $errors[] = "$label is required.";
        }
    }
    return $errors;
}
