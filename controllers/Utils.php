<?php

namespace app\controllers;

class Utils
{
    public static function GetHtml($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($handle);
    }

    public static function ParseHtml($html)
    {
        libxml_use_internal_errors(true); // Prevent HTML errors from displaying
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        return $doc;
    }
}
