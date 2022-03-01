<?php

namespace app\utils;

class Utils
{
    public static function getHtml(string $url): string
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);

        $data = curl_exec($handle);
        if ($data === false)
        {
            throw new \Exception('getHtml $data === false');
        }
        return $data;
    }

    public static function parseHtml(string $html): \DOMDocument
    {
        libxml_use_internal_errors(true); // Prevent HTML errors from displaying
        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        return $doc;
    }

    public static function removeUrlQuery(string $url): string
    {
        return preg_replace('/\?.*/', '', $url);
    }

    public static function equalItemCount(array $array1, array $array2): int
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

    public static function equalItemCountMulti(array $arrays): int
    {
        $arraysCount = count($arrays);
        if ($arraysCount < 2)
        {
            throw new \Exception('equalItemCountMulti $arraysCount < 2');
        }

        $result = Utils::equalItemCount($arrays[0], $arrays[1]);

        for ($i = 2; $i < $arraysCount; $i++)
        {
            $result = min($result, Utils::equalItemCount($arrays[0], $arrays[$i]));
        }

        return $result;
    }

    public static function boolToStr(bool $bool): string
    {
        return $bool ? 'true' : 'false';
    }

    public static function parseNthNode(string $str): array
    {
        if (preg_match('/^(\w+)\[(\w+)\]$/', $str, $matches) == false)
        {
            throw new \Exception('parseNthNode preg_match(...) == false');
        }
        return [
            'name' => $matches[1],
            'index' => intval($matches[2]),
        ];
    }

    public static function buildNthNode(string $name, int $index): string
    {
        return $name . '[' . strval($index) . ']';
    }

    public static function findOneNodeByPath(\DOMXPath $xpath, array $nodePath): \DOMElement|false
    {
        $entries = $xpath->query('/' . implode('/', $nodePath));
        if (count($entries) != 1)
        {
            return false;
        }
        return $entries[0];
    }

    public static function cleanupString(string $str): string
    {
        return preg_replace('/[\r\n\t\f\v]+/', '', $str);
    }
}
