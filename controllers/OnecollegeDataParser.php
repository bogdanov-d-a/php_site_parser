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
        return false;
    }

    private static function findWebsiteLinkTextNode($doc)
    {
        foreach ($doc->getElementsByTagName('strong') as $elem)
        {
            if ($elem->nodeValue == 'Website')
            {
                return $elem;
            }
        }
        throw new \Exception('findWebsiteLinkTextNode not found');
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

    private static function parseCampusVisitsContactNodeRow($node)
    {
        $result = [];
        foreach ($node->childNodes as $elem)
        {
            if ($elem->nodeName == 'div')
            {
                $result[] = $elem;
            }
        }
        if (count($result) != 2)
        {
            throw new \Exception('parseCampusVisitsContactNodeRow count($result) != 2');
        }
        $result[0] = Utils::CleanupString($result[0]->nodeValue);
        return $result;
    }

    private static function parseCampusVisitsContactNode($node, &$result)
    {
        foreach ($node->childNodes as $rowNode)
        {
            if ($rowNode->nodeName == 'div')
            {
                $parseResult = OnecollegeDataParser::parseCampusVisitsContactNodeRow($rowNode);
                if ($parseResult[0] == 'Address')
                {
                    $result['address'] = OnecollegeDataParser::parseAddress($parseResult[1]);
                }
                elseif ($parseResult[0] == 'Phone')
                {
                    $result['phone'] = Utils::CleanupString($parseResult[1]->nodeValue);
                }
            }
        }
    }

    public static function parse($url)
    {
        $doc = Utils::ParseHtml(Utils::GetHtml($url));
        $xpath = new \DOMXPath($doc);
        $result = [];

        $result['address'] = '';
        $result['phone'] = '';

        $findCampusVisitsContactNodeResult = OnecollegeDataParser::findCampusVisitsContactNode($doc);
        if ($findCampusVisitsContactNodeResult !== false)
        {
            $campusVisitsContactNodePath = explode('/', $findCampusVisitsContactNodeResult->getNodePath());
            array_pop($campusVisitsContactNodePath);
            $popNode = array_pop($campusVisitsContactNodePath);
            $campusVisitsContactNodePath = array_merge($campusVisitsContactNodePath, [OnecollegeDataParser::getNextNode($popNode)]);

            OnecollegeDataParser::parseCampusVisitsContactNode(Utils::FindOneNodeByPath($xpath, $campusVisitsContactNodePath), $result);
        }

        $websiteLinkTextNodePath = explode('/', OnecollegeDataParser::findWebsiteLinkTextNode($doc)->getNodePath());
        array_pop($websiteLinkTextNodePath);
        $result['siteurl'] = Utils::FindOneNodeByPath($xpath, $websiteLinkTextNodePath)->getAttribute('href');

        $nameNodePath = array_merge(array_slice($websiteLinkTextNodePath, 0, -3), ['h1', 'span']);
        $result['name'] = Utils::FindOneNodeByPath($xpath, $nameNodePath)->nodeValue;

        return $result;
    }
}
