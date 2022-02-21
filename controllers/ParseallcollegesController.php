<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\Utils;

class ParseallcollegesController extends Controller
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
                throw new Exception('fillHeaderNodeIndices !$found');
            }
        }
    }

    private static function findOneNodeByPath($xpath, $nodePath)
    {
        $entries = $xpath->query('/' . implode('/', $nodePath));
        if (count($entries) != 1)
        {
            throw new Exception('findOneNodeByPath count($entries) != 1');
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

            $headerNodePath = array_merge($nodePathRoot, $orderByUrlValue['nodes'][$orderByUrlValue['headerNodeIndex']]['nodePath']);
            $headerNode = ParseallcollegesController::findOneNodeByPath($xpath, $headerNodePath);
            $orderByUrlValue['universityName'] = $headerNode->nodeValue;

            $locationNodePath = array_merge(array_slice($headerNodePath, 0, array_search('h2', $headerNodePath)), ['div[1]']);
            $locationNode = ParseallcollegesController::findOneNodeByPath($xpath, $locationNodePath);
            $orderByUrlValue['universityLocation'] = $locationNode->nodeValue;
        }
    }

    private static function generateEchoOrderByUrlText($orderByUrl, &$result)
    {
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            $result .= $orderByUrlKey . '<br/>';
            $result .= Utils::BoolToStr($orderByUrlValue['isFeatured']) . '<br/>';
            $result .= $orderByUrlValue['headerNodeIndex'] . '<br/>';
            $result .= $orderByUrlValue['universityName'] . '<br/>';
            $result .= $orderByUrlValue['universityLocation'] . '<br/>';
            foreach ($orderByUrlValue['nodes'] as $orderByUrlValueNode)
            {
                $result .= implode('/', $orderByUrlValueNode['nodePath']) . '<br/>';
            }
            $result .= '<br/>';
        }
    }

    private static function generateEchoRootNodeNameToLinksText($rootNodeNameToLinks, &$result)
    {
        foreach ($rootNodeNameToLinks as $rootNodeName => $links)
        {
            $result .= $rootNodeName . '<br/>';
            foreach ($links as $linksKey => $linksValue)
            {
                $result .= $linksKey . '<br/>';
            }
            $result .= '<br/>';
        }
    }

    private static function generateEchoText($nodePathsCommonRoot, $featuredNodePathsCommonRoot, $orderByUrl, $rootNodeNameToLinks)
    {
        $result = '';
        $result .= implode('/', $nodePathsCommonRoot) . '<br/>';
        $result .= implode('/', $featuredNodePathsCommonRoot) . '<br/>';
        $result .= '<br/>';

        ParseallcollegesController::generateEchoOrderByUrlText($orderByUrl, $result);
        ParseallcollegesController::generateEchoRootNodeNameToLinksText($rootNodeNameToLinks, $result);

        return $result;
    }

    public function actionIndex()
    {
        $doc = Utils::ParseHtml(Utils::GetHtml('https://www.princetonreview.com/college-search?ceid=cp-1022984'));
        $xpath = new \DOMXPath($doc);

        $orderByUrl = ParseallcollegesController::fillOrderByUrl($doc);
        $allNodePaths = ParseallcollegesController::fillAllNodePaths($orderByUrl, false);

        $nodePathsEqualItemCount = Utils::EqualItemCountMulti($allNodePaths);
        $nodePathsCommonRoot = Utils::TrimArray($allNodePaths[0], $nodePathsEqualItemCount);
        ParseallcollegesController::stripNodePaths($orderByUrl, false, $nodePathsEqualItemCount);

        $rootNodeNameToLinks = ParseallcollegesController::fillRootNodeNameToLinks($orderByUrl);
        ParseallcollegesController::fillFeaturedFromTop($orderByUrl, $rootNodeNameToLinks);

        $allFeaturedNodePaths = ParseallcollegesController::fillAllNodePaths($orderByUrl, true);
        $featuredNodePathsEqualItemCount = Utils::EqualItemCountMulti($allFeaturedNodePaths);
        $featuredNodePathsCommonRoot = Utils::TrimArray($allFeaturedNodePaths[0], $featuredNodePathsEqualItemCount);
        ParseallcollegesController::stripNodePaths($orderByUrl, true, $featuredNodePathsEqualItemCount);

        ParseallcollegesController::fillHeaderNodeIndices($orderByUrl);
        ParseallcollegesController::fillUniversityInfo($orderByUrl, $xpath, $nodePathsCommonRoot, $featuredNodePathsCommonRoot);

        return $this->render('index', [
            'echoText' => ParseallcollegesController::generateEchoText($nodePathsCommonRoot, $featuredNodePathsCommonRoot, $orderByUrl, $rootNodeNameToLinks),
        ]);
    }
}
