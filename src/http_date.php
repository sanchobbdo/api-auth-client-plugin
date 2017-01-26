<?php

/**
 * http_date helper if not installaed by PECL.
 *
 * PHP version 5
 *
 * @category Helpers
 * @package  ApiAuthPlugin
 * @author   Camilo Aguilar <camiloaguilar@sanchobbdo.com.co>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://
 */

if (!function_exists('http_date')) {
    /**
     * Compose HTTP RFC compliant date.
     *
     * @param int $timestamp Unix timestamp; current time if omitted
     *
     * @return string HTTP date as string.
     */
    function http_date($timestamp = null)
    {
        $timestamp = empty($timestamp) ? time() : $timestamp;
        return gmdate("D, d M Y H:i:s", $timestamp)." GMT";
    }
}
