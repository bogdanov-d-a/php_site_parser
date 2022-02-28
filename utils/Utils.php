<?php

namespace app\utils;

class Utils
{
    public static function GetHtml(string $url): string
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);

        $data = curl_exec($handle);
        if ($data === false)
        {
            throw new \Exception('GetHtml $data === false');
        }
        return $data;
    }

    public static function ParseHtml(string $html): \DOMDocument
    {
        libxml_use_internal_errors(true); // Prevent HTML errors from displaying
        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        return $doc;
    }

    public static function RemoveUrlQuery(string $url): string
    {
        return preg_replace('/\?.*/', '', $url);
    }

    public static function EqualItemCount(array $array1, array $array2): int
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

    public static function EqualItemCountMulti(array $arrays): int
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

    public static function BoolToStr(bool $bool): string
    {
        return $bool ? 'true' : 'false';
    }

    public static function ParseNthNode(string $str): array
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

    public static function BuildNthNode(string $name, int $index): string
    {
        return $name . '[' . strval($index) . ']';
    }

    public static function FindOneNodeByPath(\DOMXPath $xpath, array $nodePath): \DOMElement|false
    {
        $entries = $xpath->query('/' . implode('/', $nodePath));
        if (count($entries) != 1)
        {
            return false;
        }
        return $entries[0];
    }

    public static function CleanupString(string $str): string
    {
        return preg_replace('/[\r\n\t\f\v]+/', '', $str);
    }
}
