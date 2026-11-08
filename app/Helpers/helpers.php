<?php

if (!function_exists('maskNumber')) {
    function maskNumber($num)
    {
        if (!$num) return 'N/A';

        // Remove non-digit characters
        $num = preg_replace('/\D/', '', $num);

        // Mask number
        if (strlen($num) >= 11) {
            return substr($num, 0, 5) . '***' . substr($num, -3);
        }

        return $num;
    }
}