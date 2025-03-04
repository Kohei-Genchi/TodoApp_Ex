<?php

/**
 * Convert a hex color to rgba with transparency
 *
 * @param string $hex The hex color code (e.g. #FF5733)
 * @param float $alpha The alpha transparency value (0-1)
 * @return string The rgba color value
 */
if (!function_exists('hexToRgba')) {
    function hexToRgba($hex, $alpha = 1) {
        if (empty($hex)) {
            return "rgba(156, 163, 175, {$alpha})"; // Default gray color
        }

        // Remove # if present
        $hex = ltrim($hex, '#');

        // Parse the hex values
        $r = hexdec(strlen($hex) == 6 ? substr($hex, 0, 2) : substr($hex, 0, 1).substr($hex, 0, 1));
        $g = hexdec(strlen($hex) == 6 ? substr($hex, 2, 2) : substr($hex, 1, 1).substr($hex, 1, 1));
        $b = hexdec(strlen($hex) == 6 ? substr($hex, 4, 2) : substr($hex, 2, 1).substr($hex, 2, 1));

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }
}
