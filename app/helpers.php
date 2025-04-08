<?php

/**
 * Format file size to human-readable format
 *
 * @param int $bytes
 * @param int $precision
 * @return string
 */
if (!function_exists('human_filesize')) {
    function human_filesize($bytes, $precision = 2) 
    {
        if ($bytes === null) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
        
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }
}