<?php
/**
 * Lịch tập PT - redirect về route chuẩn  
 * Route chính: /member/booking (MemberController::ptBooking)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
startSession();
if (!isLoggedIn()) redirect('/login');
redirect('/member/booking');
