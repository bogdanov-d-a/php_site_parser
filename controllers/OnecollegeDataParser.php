<?php

namespace app\controllers;

use app\controllers\Utils;

class OnecollegeDataParser
{
    private static function findCampusVisitsContactNode($doc)
    {
        foreach ($doc->getElementsByTagName('h2') as $elem)
        {
            if ($elem->getAttribute('class') == 'box-subtitle' && $elem->nodeValue == 'Campus Visits Contact')
            {
                return $elem;
            }
        }
    }

    private static function findWebsiteLinkText($doc)
    {
        foreach ($doc->getElementsByTagName('strong') as $elem)
        {
            if ($elem->nodeValue == 'Website')
            {
                return $elem;
            }
        }
    }

    private static function getNextNode($str)
    {
        $parseResult = Utils::ParseNthNode($str);
        return Utils::BuildNthNode($parseResult['name'], $parseResult['index'] + 1);
    }

    private static function parseAddress($node)
    {
        $result = '';
        foreach ($node->childNodes as $elem)
        {
            if ($elem->nodeType == XML_TEXT_NODE)
            {
                $result .= Utils::CleanupString($elem->nodeValue) . "\n";
            }
        }
        return $result;
    }

    public static function parse($url)
    {
        $doc = Utils::ParseHtml(Utils::GetHtml($url));
        $xpath = new \DOMXPath($doc);
        $result = [];

        $campusVisitsContactNodePath = explode('/', OnecollegeDataParser::findCampusVisitsContactNode($doc)->getNodePath());
        array_pop($campusVisitsContactNodePath);
        $popNode = array_pop($campusVisitsContactNodePath);
        $campusVisitsContactNodePath = array_merge($campusVisitsContactNodePath, [OnecollegeDataParser::getNextNode($popNode)]);

        $addressNodePath = array_merge($campusVisitsContactNodePath, [Utils::BuildNthNode('div', 1), Utils::BuildNthNode('div', 2)]);
        $result['address'] = OnecollegeDataParser::parseAddress(Utils::FindOneNodeByPath($xpath, $addressNodePath));

        $phoneNodePath = array_merge($campusVisitsContactNodePath, [Utils::BuildNthNode('div', 2), Utils::BuildNthNode('div', 2)]);
        $result['phone'] = Utils::CleanupString(Utils::FindOneNodeByPath($xpath, $phoneNodePath)->nodeValue);

        return $result;
    }
}
