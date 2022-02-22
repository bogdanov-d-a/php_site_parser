<?php

namespace app\controllers;

use app\controllers\Utils;

class AllcollegesDataParser
{
    private static function fillOrderByUrl($doc)
    {
        $result = [];
        foreach ($doc->getElementsByTagName('a') as $link)
        {
            $url = Utils::RemoveUrlQuery($link->getAttribute('href'));
            if (str_starts_with($url, '/college/'))
            {
                if (!array_key_exists($url, $result))
                {
                    $result[$url] = ['nodes' => []];
                }
                $result[$url]['nodes'][] = [
                    'node' => $link,
                    'nodePath' => explode('/', $link->getNodePath()),
                ];
            }
        }
        return $result;
    }

    private static function fillAllNodePaths($orderByUrl, $featuredOnly)
    {
        $result = [];
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            if (!$featuredOnly || $orderByUrlValue['isFeatured'])
            {
                foreach ($orderByUrlValue['nodes'] as $orderByUrlValueNode)
                {
                    $result[] = $orderByUrlValueNode['nodePath'];
                }
            }
        }
        return $result;
    }

    private static function stripNodePaths(&$orderByUrl, $featuredOnly, $nodePathsEqualItemCount)
    {
        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValue)
        {
            if (!$featuredOnly || $orderByUrlValue['isFeatured'])
            {
                foreach ($orderByUrlValue['nodes'] as &$orderByUrlValueNode)
                {
                    $orderByUrlValueNode['nodePath'] = Utils::StripArrayBeginning($orderByUrlValueNode['nodePath'], $nodePathsEqualItemCount);
                }
            }
        }
    }

    private static function fillRootNodeNameToLinks($orderByUrl)
    {
        $result = [];
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            foreach ($orderByUrlValue['nodes'] as $orderByUrlValueNode)
            {
                $rootNodeName = $orderByUrlValueNode['nodePath'][0];
                if (!array_key_exists($rootNodeName, $result))
                {
                    $result[$rootNodeName] = [];
                }
                $result[$rootNodeName][$orderByUrlKey] = true;
            }
        }
        return $result;
    }

    private static function fillFeaturedFromTop(&$orderByUrl, $rootNodeNameToLinks)
    {
        foreach ($rootNodeNameToLinks as $rootNodeName => $links)
        {
            $isFeatured = count($links) > 1;
            foreach ($links as $linksKey => $linksValue)
            {
                $orderByUrl[$linksKey]['isFeatured'] = $isFeatured;
            }
        }
    }

    private static function fillHeaderNodeIndices(&$orderByUrl)
    {
        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValue)
        {
            $orderByUrlValueNodes = $orderByUrlValue['nodes'];
            $orderByUrlValueNodesCount = count($orderByUrlValueNodes);
            $found = false;

            for ($orderByUrlValueNodeIndex = 0; $orderByUrlValueNodeIndex < $orderByUrlValueNodesCount; $orderByUrlValueNodeIndex++)
            {
                $orderByUrlValueNode = $orderByUrlValueNodes[$orderByUrlValueNodeIndex];
                $orderByUrlValueNodePath = $orderByUrlValueNode['nodePath'];

                if (array_search('h2', $orderByUrlValueNodePath) !== false)
                {
                    $orderByUrlValue['headerNodeIndex'] = $orderByUrlValueNodeIndex;
                    $found = true;
                    break;
                }
            }

            if (!$found)
            {
                throw new \Exception('fillHeaderNodeIndices !$found');
            }
        }
    }

    private static function findOneNodeByPath($xpath, $nodePath)
    {
        $entries = $xpath->query('/' . implode('/', $nodePath));
        if (count($entries) != 1)
        {
            throw new \Exception('findOneNodeByPath count($entries) != 1');
        }
        return $entries[0];
    }

    private static function fillUniversityInfo(&$orderByUrl, $xpath, $nodePathsCommonRoot, $featuredNodePathsCommonRoot)
    {
        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValue)
        {
            $nodePathRoot = $nodePathsCommonRoot;
            if ($orderByUrlValue['isFeatured'])
            {
                $nodePathRoot = array_merge($nodePathRoot, $featuredNodePathsCommonRoot);
            }

            $headerNodePathRelative = $orderByUrlValue['nodes'][$orderByUrlValue['headerNodeIndex']]['nodePath'];
            $headerNodePath = array_merge($nodePathRoot, $headerNodePathRelative);
            $headerNode = AllcollegesDataParser::findOneNodeByPath($xpath, $headerNodePath);
            $orderByUrlValue['universityName'] = $headerNode->nodeValue;

            $locationNodePath = array_merge(array_slice($headerNodePath, 0, array_search('h2', $headerNodePath)), ['div[1]']);
            $locationNode = AllcollegesDataParser::findOneNodeByPath($xpath, $locationNodePath);
            $orderByUrlValue['universityLocation'] = $locationNode->nodeValue;

            $universityNodePathRoot = array_merge($nodePathRoot, [$headerNodePathRelative[0]]);
            $universityNodeRoot = AllcollegesDataParser::findOneNodeByPath($xpath, $universityNodePathRoot);

            $imgElements = $universityNodeRoot->getElementsByTagName('img');
            if (count($imgElements) > 1)
            {
                throw new \Exception('fillUniversityInfo count($imgElements) > 1');
            }
            $orderByUrlValue['universityImgUrl'] = (count($imgElements) == 1) ? $imgElements[0]->getAttribute('src') : '';
        }
    }

    private static function parsePageCount($str)
    {
        if (preg_match('/^Page \d+ of (\d+)$/', $str, $matches) == false)
        {
            throw new \Exception('parsePageCount preg_match(...) == false');
        }
        return intval($matches[1]);
    }

    private static function findPageCount($nodePathsCommonRoot, $xpath)
    {
        $paginatorNodePath = array_merge($nodePathsCommonRoot, ['div[last()]', 'div']);
        $paginatorNode = AllcollegesDataParser::findOneNodeByPath($xpath, $paginatorNodePath);
        return AllcollegesDataParser::parsePageCount($paginatorNode->nodeValue);
    }

    private static function removeTemporaryData(&$orderByUrl)
    {
        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValue)
        {
            unset($orderByUrlValue['isFeatured']);
            unset($orderByUrlValue['headerNodeIndex']);
            unset($orderByUrlValue['nodes']);
        }
    }

    public static function parse()
    {
        $doc = Utils::ParseHtml(Utils::GetHtml('https://www.princetonreview.com/college-search?ceid=cp-1022984'));
        $xpath = new \DOMXPath($doc);

        $orderByUrl = AllcollegesDataParser::fillOrderByUrl($doc);
        $allNodePaths = AllcollegesDataParser::fillAllNodePaths($orderByUrl, false);

        $nodePathsEqualItemCount = Utils::EqualItemCountMulti($allNodePaths);
        $nodePathsCommonRoot = Utils::TrimArray($allNodePaths[0], $nodePathsEqualItemCount);
        AllcollegesDataParser::stripNodePaths($orderByUrl, false, $nodePathsEqualItemCount);

        $pageCount = AllcollegesDataParser::findPageCount($nodePathsCommonRoot, $xpath);

        $rootNodeNameToLinks = AllcollegesDataParser::fillRootNodeNameToLinks($orderByUrl);
        AllcollegesDataParser::fillFeaturedFromTop($orderByUrl, $rootNodeNameToLinks);

        $allFeaturedNodePaths = AllcollegesDataParser::fillAllNodePaths($orderByUrl, true);
        $featuredNodePathsEqualItemCount = Utils::EqualItemCountMulti($allFeaturedNodePaths);
        $featuredNodePathsCommonRoot = Utils::TrimArray($allFeaturedNodePaths[0], $featuredNodePathsEqualItemCount);
        AllcollegesDataParser::stripNodePaths($orderByUrl, true, $featuredNodePathsEqualItemCount);

        AllcollegesDataParser::fillHeaderNodeIndices($orderByUrl);
        AllcollegesDataParser::fillUniversityInfo($orderByUrl, $xpath, $nodePathsCommonRoot, $featuredNodePathsCommonRoot);

        AllcollegesDataParser::removeTemporaryData($orderByUrl);

        return [
            'orderByUrl' => $orderByUrl,
            'pageCount' => $pageCount,
        ];
    }
}
