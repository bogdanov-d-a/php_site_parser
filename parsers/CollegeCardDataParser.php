<?php

namespace app\parsers;

use app\utils\Utils;

class CollegeCardDataParser
{
    private static function findCampusVisitsContactNode(\DOMDocument $doc): \DOMElement|false
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

    private static function findWebsiteLinkTextNode(\DOMDocument $doc): \DOMElement
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

    private static function getNextNode(string $str): string
    {
        $parseResult = Utils::parseNthNode($str);
        return Utils::buildNthNode($parseResult['name'], $parseResult['index'] + 1);
    }

    private static function parseAddress(\DOMElement $node): string
    {
        $result = '';
        foreach ($node->childNodes as $elem)
        {
            if ($elem->nodeType == XML_TEXT_NODE)
            {
                $result .= Utils::cleanupString($elem->nodeValue) . "\n";
            }
        }
        return $result;
    }

    private static function parseCampusVisitsContactNodeRow(\DOMElement $node): array
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
        $result[0] = Utils::cleanupString($result[0]->nodeValue);
        return $result;
    }

    private static function parseCampusVisitsContactNode(\DOMElement $node, array &$result): void
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
                    $result['phone'] = Utils::cleanupString($parseResult[1]->nodeValue);
                }
            }
        }
    }

    public static function parse(string $url): array
    {
        $doc = Utils::parseHtml(Utils::getHtml($url));
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

            $campusVisitsContactNode = Utils::findOneNodeByPath($xpath, $campusVisitsContactNodePath);
            if ($campusVisitsContactNode === false)
            {
                throw new \Exception('parse $campusVisitsContactNode === false');
            }
            CollegeCardDataParser::parseCampusVisitsContactNode($campusVisitsContactNode, $result);
        }

        $websiteLinkTextNodePath = explode('/', CollegeCardDataParser::findWebsiteLinkTextNode($doc)->getNodePath());
        array_pop($websiteLinkTextNodePath);
        $websiteLinkTextNode = Utils::findOneNodeByPath($xpath, $websiteLinkTextNodePath);
        if ($websiteLinkTextNode === false)
        {
            throw new \Exception('parse $websiteLinkTextNode === false');
        }
        $result['siteurl'] = $websiteLinkTextNode->getAttribute('href');

        $nameNodePath = array_merge(array_slice($websiteLinkTextNodePath, 0, -3), ['h1', 'span']);
        $nameNode = Utils::findOneNodeByPath($xpath, $nameNodePath);
        if ($nameNode === false)
        {
            throw new \Exception('parse $nameNode === false');
        }
        $result['name'] = $nameNode->nodeValue;

        return $result;
    }
}
