<?php
/**
 * Logout Handler
 * VILLA BINTARO REGENCY RT/RW Digital
 */

require_once __DIR__ . '/includes/auth.php';

logout();
header('Location: login.php');
exit;
