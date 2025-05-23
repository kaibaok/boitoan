<?php
/**
 * Zalo © 2019
 *
 */

namespace App\Zalo\Url;

/**
 * Interface UrlDetectionInterface
 *
 * @package Zalo
 */
interface UrlDetectionInterface
{
    /**
     * Get the currently active URL.
     *
     * @return string
     */
    public function getCurrentUrl();
}
