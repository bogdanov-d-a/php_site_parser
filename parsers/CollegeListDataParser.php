<?php

namespace app\parsers;

use app\utils\Utils;

class CollegeListDataParser
{
    private static function getUrl(int $page): string
    {
        $url = 'https://www.princetonreview.com/college-search?ceid=cp-1022984';
        if ($page != 1)
        {
            $url .= '&page=' . strval($page);
        }
        return $url;
    }

    private static function fillOrderByUrl(\DOMDocument $doc): array
    {
        $result = [];
        foreach ($doc->getElementsByTagName('a') as $link)
        {
            $url = Utils::removeUrlQuery($link->getAttribute('href'));
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

    private static function fillAllNodePaths(array $orderByUrl, bool $featuredOnly): array
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

    private static function stripNodePaths(array &$orderByUrl, bool $featuredOnly, int $nodePathsEqualItemCount): void
    {
        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValue)
        {
            if (!$featuredOnly || $orderByUrlValue['isFeatured'])
            {
                foreach ($orderByUrlValue['nodes'] as &$orderByUrlValueNode)
                {
                    $orderByUrlValueNode['nodePath'] = array_slice($orderByUrlValueNode['nodePath'], $nodePathsEqualItemCount);
                }
            }
        }
    }

    private static function fillRootNodeNameToLinks(array $orderByUrl): array
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

    private static function fillFeaturedFromTop(array &$orderByUrl, array $rootNodeNameToLinks): void
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

    private static function fillHeaderNodeIndices(array &$orderByUrl): void
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

    private static function parseUniversityLocation(string $location): array
    {
        $locationParts = explode(', ', $location);
        if (count($locationParts) != 2)
        {
            throw new \Exception('parseUniversityLocation count($locationParts) != 2');
        }
        return $locationParts;
    }

    private static function fillUniversityInfo(array &$orderByUrl, \DOMXPath $xpath, array $nodePathsCommonRoot, array $featuredNodePathsCommonRoot): void
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
            $headerNode = Utils::findOneNodeByPath($xpath, $headerNodePath);
            if ($headerNode === false)
            {
                throw new \Exception('fillUniversityInfo $headerNode === false');
            }
            $orderByUrlValue['universityName'] = $headerNode->nodeValue;

            $orderByUrlValue['universityCity'] = '';
            $orderByUrlValue['universityState'] = '';

            $locationNodePath = array_merge(array_slice($headerNodePath, 0, array_search('h2', $headerNodePath)), ['div[1]']);
            $locationNode = Utils::findOneNodeByPath($xpath, $locationNodePath);

            if ($locationNode !== false && $locationNode->getAttribute('class') == 'location')
            {
                $universityLocation = CollegeListDataParser::parseUniversityLocation($locationNode->nodeValue);
                $orderByUrlValue['universityCity'] = $universityLocation[0];
                $orderByUrlValue['universityState'] = $universityLocation[1];
            }

            $universityNodePathRoot = array_merge($nodePathRoot, [$headerNodePathRelative[0]]);
            $universityNodeRoot = Utils::findOneNodeByPath($xpath, $universityNodePathRoot);
            if ($universityNodeRoot === false)
            {
                throw new \Exception('fillUniversityInfo $universityNodeRoot === false');
            }

            $imgElements = $universityNodeRoot->getElementsByTagName('img');
            if (count($imgElements) > 1)
            {
                throw new \Exception('fillUniversityInfo count($imgElements) > 1');
            }
            $orderByUrlValue['universityImgUrl'] = (count($imgElements) == 1) ? $imgElements[0]->getAttribute('src') : '';
        }
    }

    private static function parsePageCount(string $str): int
    {
        if (preg_match('/^Page \d+ of (\d+)$/', $str, $matches) == false)
        {
            throw new \Exception('parsePageCount preg_match(...) == false');
        }
        return intval($matches[1]);
    }

    private static function findPageCount(array $nodePathsCommonRoot, \DOMXPath $xpath): int
    {
        $paginatorNodePath = array_merge($nodePathsCommonRoot, ['div[last()]', 'div']);
        $paginatorNode = Utils::findOneNodeByPath($xpath, $paginatorNodePath);
        if ($paginatorNode === false)
        {
            throw new \Exception('findPageCount $paginatorNode === false');
        }
        return CollegeListDataParser::parsePageCount($paginatorNode->nodeValue);
    }

    private static function removeTemporaryData(array &$orderByUrl): void
    {
        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValue)
        {
            unset($orderByUrlValue['isFeatured']);
            unset($orderByUrlValue['headerNodeIndex']);
            unset($orderByUrlValue['nodes']);
        }
    }

    private static function parsePage(int $page, callable $traceCallback): array
    {
        call_user_func($traceCallback, 'CollegeListDataParser parsePage ' . strval($page));

        $doc = Utils::parseHtml(Utils::getHtml(CollegeListDataParser::getUrl($page)));
        $xpath = new \DOMXPath($doc);

        $orderByUrl = CollegeListDataParser::fillOrderByUrl($doc);
        $allNodePaths = CollegeListDataParser::fillAllNodePaths($orderByUrl, false);

        $nodePathsEqualItemCount = Utils::equalItemCountMulti($allNodePaths);
        $nodePathsCommonRoot = array_slice($allNodePaths[0], 0, $nodePathsEqualItemCount);
        CollegeListDataParser::stripNodePaths($orderByUrl, false, $nodePathsEqualItemCount);

        $pageCount = ($page == 1) ? CollegeListDataParser::findPageCount($nodePathsCommonRoot, $xpath) : 0;

        $rootNodeNameToLinks = CollegeListDataParser::fillRootNodeNameToLinks($orderByUrl);
        CollegeListDataParser::fillFeaturedFromTop($orderByUrl, $rootNodeNameToLinks);

        $allFeaturedNodePaths = CollegeListDataParser::fillAllNodePaths($orderByUrl, true);
        $featuredNodePathsEqualItemCount = Utils::equalItemCountMulti($allFeaturedNodePaths);
        $featuredNodePathsCommonRoot = array_slice($allFeaturedNodePaths[0], 0, $featuredNodePathsEqualItemCount);
        CollegeListDataParser::stripNodePaths($orderByUrl, true, $featuredNodePathsEqualItemCount);

        CollegeListDataParser::fillHeaderNodeIndices($orderByUrl);
        CollegeListDataParser::fillUniversityInfo($orderByUrl, $xpath, $nodePathsCommonRoot, $featuredNodePathsCommonRoot);

        CollegeListDataParser::removeTemporaryData($orderByUrl);

        return [
            'orderByUrl' => $orderByUrl,
            'pageCount' => $pageCount,
        ];
    }

    public static function parse(callable $traceCallback): array
    {
        $parsePage1Result = CollegeListDataParser::parsePage(1, $traceCallback);
        $result = $parsePage1Result['orderByUrl'];
        $pageCount = $parsePage1Result['pageCount'];

        // for debugging purposes
        //$pageCount = 2;

        for ($pageNumber = 2; $pageNumber <= $pageCount; $pageNumber++)
        {
            sleep(1);  // reduce server request rate
            $result = array_merge($result, CollegeListDataParser::parsePage($pageNumber, $traceCallback)['orderByUrl']);
        }

        return $result;
    }
}
