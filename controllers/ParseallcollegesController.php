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

    private static function generateEchoOrderByUrlText($orderByUrl, &$result)
    {
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            $result .= $orderByUrlKey . '<br/>';
            $result .= Utils::BoolToStr($orderByUrlValue['isFeatured']) . '<br/>';
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

        return $this->render('index', [
            'echoText' => ParseallcollegesController::generateEchoText($nodePathsCommonRoot, $featuredNodePathsCommonRoot, $orderByUrl, $rootNodeNameToLinks),
        ]);
    }
}
