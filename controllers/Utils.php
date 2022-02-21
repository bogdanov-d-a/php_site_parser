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
        $doc->loadHTML($html);
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
            throw new Exception('EqualItemCountMulti $arraysCount < 2');
        }

        $result = Utils::EqualItemCount($arrays[0], $arrays[1]);

        for ($i = 2; $i < $arraysCount; $i++)
        {
            $result = min($result, Utils::EqualItemCount($arrays[0], $arrays[$i]));
        }

        return $result;
    }

    public static function StripArrayBeginning($array, $count)
    {
        $arrayCount = count($array);
        if ($arrayCount < $count)
        {
            throw new Exception('StripArrayBeginning $arrayCount < $count');
        }

        $result = [];
        for ($i = $count; $i < $arrayCount; $i++)
        {
            $result[] = $array[$i];
        }
        return $result;
    }

    public static function TrimArray($array, $count)
    {
        $arrayCount = count($array);
        if ($arrayCount < $count)
        {
            throw new Exception('TrimArray $arrayCount < $count');
        }

        $result = [];
        for ($i = 0; $i < $count; $i++)
        {
            $result[] = $array[$i];
        }
        return $result;
    }
}
