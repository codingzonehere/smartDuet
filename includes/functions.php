<?php
/**
 * Smart DUET Admission Management System
 *
 * File: includes/functions.php
 *
 * Purpose:
 * Common reusable functions for the whole project.
 */


// --------------------------------------------------
// Load Configuration
// --------------------------------------------------

require_once __DIR__ . '/../config/config.php';


// --------------------------------------------------
// Escape HTML Output
// --------------------------------------------------

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// --------------------------------------------------
// Redirect to Another Page
// --------------------------------------------------

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}


// --------------------------------------------------
// Get Current User ID
// --------------------------------------------------

function getUserId(): ?int
{
    if (isset($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }

    return null;
}


// --------------------------------------------------
// Check Login Status
// --------------------------------------------------

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}


// --------------------------------------------------
// Set Flash Message
// --------------------------------------------------

function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}


// --------------------------------------------------
// Get Flash Message
// --------------------------------------------------

function getFlashMessage(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];

    // Remove after reading
    unset($_SESSION['flash']);

    return $flash;
}