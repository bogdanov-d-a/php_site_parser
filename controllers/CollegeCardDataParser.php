<?php

namespace app\controllers;

use app\controllers\Utils;

class CollegeCardDataParser
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
                $parseResult = CollegeCardDataParser::parseCampusVisitsContactNodeRow($rowNode);
                if ($parseResult[0] == 'Address')
                {
                    $result['address'] = CollegeCardDataParser::parseAddress($parseResult[1]);
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

        $findCampusVisitsContactNodeResult = CollegeCardDataParser::findCampusVisitsContactNode($doc);
        if ($findCampusVisitsContactNodeResult !== false)
        {
            $campusVisitsContactNodePath = explode('/', $findCampusVisitsContactNodeResult->getNodePath());
            array_pop($campusVisitsContactNodePath);
            $popNode = array_pop($campusVisitsContactNodePath);
            $campusVisitsContactNodePath = array_merge($campusVisitsContactNodePath, [CollegeCardDataParser::getNextNode($popNode)]);

            $campusVisitsContactNode = Utils::FindOneNodeByPath($xpath, $campusVisitsContactNodePath);
            if ($campusVisitsContactNode === false)
            {
                throw new \Exception('parse $campusVisitsContactNode === false');
            }
            CollegeCardDataParser::parseCampusVisitsContactNode($campusVisitsContactNode, $result);
        }

        $websiteLinkTextNodePath = explode('/', CollegeCardDataParser::findWebsiteLinkTextNode($doc)->getNodePath());
        array_pop($websiteLinkTextNodePath);
        $websiteLinkTextNode = Utils::FindOneNodeByPath($xpath, $websiteLinkTextNodePath);
        if ($websiteLinkTextNode === false)
        {
            throw new \Exception('parse $websiteLinkTextNode === false');
        }
        $result['siteurl'] = $websiteLinkTextNode->getAttribute('href');

        $nameNodePath = array_merge(array_slice($websiteLinkTextNodePath, 0, -3), ['h1', 'span']);
        $nameNode = Utils::FindOneNodeByPath($xpath, $nameNodePath);
        if ($nameNode === false)
        {
            throw new \Exception('parse $nameNode === false');
        }
        $result['name'] = $nameNode->nodeValue;

        return $result;
    }
}
