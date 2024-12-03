<?php
session_start();

// Disable error display to prevent image corruption
ini_set('display_errors', 0);
error_reporting(0);

// CAPTCHA Generation Function
function generate_captcha() {
    $captcha_text = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 6);
    $_SESSION['captcha'] = $captcha_text;
    return $captcha_text;
}

// CAPTCHA Validation Function
function validate_captcha($input) {
    if (!isset($_SESSION['captcha'])) {
        return false;
    }

    $is_valid = strtolower($input) === strtolower($_SESSION['captcha']);
    unset($_SESSION['captcha']); // One-time use
    return $is_valid;
}

// Generate CAPTCHA Image
if (isset($_GET['action']) && $_GET['action'] === 'image') {
    // Ensure no output before headers
    if (ob_get_length()) ob_clean();

    header('Content-Type: image/png');

    $captcha_text = generate_captcha();

    // Image dimensions
    $width = 150;
    $height = 50;

    // Create image
    $image = imagecreatetruecolor($width, $height);

    if (!$image) {
        error_log('Failed to create image.');
        exit;
    }

    // Colors
    $bg_color = imagecolorallocate($image, 255, 255, 255); // White
    imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

    // Randomized font colors and sizes
    $fonts = glob(__DIR__ . '/fonts/*.ttf');

    if (empty($fonts)) {
        error_log('No fonts found in fonts directory.');
        exit;
    }

    $text_colors = [
        imagecolorallocate($image, 27, 78, 181),
        imagecolorallocate($image, 22, 163, 35),
        imagecolorallocate($image, 214, 36, 7),
        imagecolorallocate($image, 123, 46, 208),
        imagecolorallocate($image, 255, 165, 0),
    ];

    // Add noise
    for ($i = 0; $i < 50; $i++) {
        $noise_color = $text_colors[array_rand($text_colors)];
        imageline(
            $image,
            mt_rand(0, $width),
            mt_rand(0, $height),
            mt_rand(0, $width),
            mt_rand(0, $height),
            $noise_color
        );
    }

    // Add captcha text
    $x = 15;
    for ($i = 0; $i < strlen($captcha_text); $i++) {
        $font = $fonts[array_rand($fonts)];
        $font_size = mt_rand(18, 24);
        $angle = mt_rand(-15, 15);
        $y = mt_rand(30, 40);
        $text_color = $text_colors[array_rand($text_colors)];
        $char = $captcha_text[$i];
        imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font, $char);
        $x += 20;
    }

    // Output image
    imagepng($image);
    imagedestroy($image);
    exit;
}
?>