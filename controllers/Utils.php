<?php

namespace app\controllers;

class Utils
{
    public static function GetHtml($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
        return curl_exec($handle);
    }

    public static function ParseHtml($html)
    {
        libxml_use_internal_errors(true); // Prevent HTML errors from displaying
        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        return $doc;
    }

    public static function RemoveUrlQuery($url)
    {
        return preg_replace('/\?.*/', '', $url);
    }

    public static function EqualItemCount($array1, $array2)
    {
        $maxResult = min(count($array1), count($array2));

        for ($i = 0; $i < $maxResult; $i++)
        {
            if ($array1[$i] != $array2[$i])
            {
                return $i;
            }
        }

        return $maxResult;
    }

    public static function EqualItemCountMulti($arrays)
    {
        $arraysCount = count($arrays);
        if ($arraysCount < 2)
        {
            throw new \Exception('EqualItemCountMulti $arraysCount < 2');
        }

        $result = Utils::EqualItemCount($arrays[0], $arrays[1]);

        for ($i = 2; $i < $arraysCount; $i++)
        {
            $result = min($result, Utils::EqualItemCount($arrays[0], $arrays[$i]));
        }

        return $result;
    }

    public static function BoolToStr($bool)
    {
        return $bool ? 'true' : 'false';
    }

    public static function ParseNthNode($str)
    {
        if (preg_match('/^(\w+)\[(\w+)\]$/', $str, $matches) == false)
        {
            throw new \Exception('ParseNthNode preg_match(...) == false');
        }
        return [
            'name' => $matches[1],
            'index' => intval($matches[2]),
        ];
    }

    public static function BuildNthNode($name, $index)
    {
        return $name . '[' . strval($index) . ']';
    }

    public static function FindOneNodeByPath($xpath, $nodePath)
    {
        $entries = $xpath->query('/' . implode('/', $nodePath));
        if (count($entries) != 1)
        {
            return false;
        }
        return $entries[0];
    }

    public static function CleanupString($str)
    {
        return preg_replace('/[\r\n\t\f\v]+/', '', $str);
    }
}
