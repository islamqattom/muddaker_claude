<?php
/**
 * Laravel redirect - points to public directory
 * Used when DocumentRoot cannot be changed (e.g. shared hosting, default XAMPP)
 */
header('Location: public/');
exit;
