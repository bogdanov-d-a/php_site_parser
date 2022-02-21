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
                    $result[$url] = [];
                }
                $result[$url][] = [
                    'node' => $link,
                    'nodePath' => explode('/', $link->getNodePath()),
                ];
            }
        }
        return $result;
    }

    private static function fillAllNodePaths($orderByUrl)
    {
        $result = [];
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            foreach ($orderByUrlValue as $orderByUrlValueItem)
            {
                $result[] = $orderByUrlValueItem['nodePath'];
            }
        }
        return $result;
    }

    private static function stripNodePaths(&$orderByUrl, $nodePathsEqualItemCount)
    {
        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValue)
        {
            foreach ($orderByUrlValue as &$orderByUrlValueItem)
            {
                $orderByUrlValueItem['nodePath'] = Utils::StripArrayBeginning($orderByUrlValueItem['nodePath'], $nodePathsEqualItemCount);
            }
        }
    }

    private static function fillRootNodeNameToLinks($orderByUrl)
    {
        $result = [];
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            foreach ($orderByUrlValue as $orderByUrlValueItem)
            {
                $rootNodeName = $orderByUrlValueItem['nodePath'][0];
                if (!array_key_exists($rootNodeName, $result))
                {
                    $result[$rootNodeName] = [];
                }
                $result[$rootNodeName][$orderByUrlKey] = true;
            }
        }
        return $result;
    }

    private static function generateEchoOrderByUrlText($orderByUrl, &$result)
    {
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            $result .= $orderByUrlKey . '<br/>';
            foreach ($orderByUrlValue as $orderByUrlValueItem)
            {
                $result .= implode('/', $orderByUrlValueItem['nodePath']) . '<br/>';
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

    private static function generateEchoText($nodePathsCommonRoot, $orderByUrl, $rootNodeNameToLinks)
    {
        $result = '';
        $result .= implode('/', $nodePathsCommonRoot) . '<br/>';
        $result .= '<br/>';

        ParseallcollegesController::generateEchoOrderByUrlText($orderByUrl, $result);
        ParseallcollegesController::generateEchoRootNodeNameToLinksText($rootNodeNameToLinks, $result);

        return $result;
    }

    public function actionIndex()
    {
        $doc = Utils::ParseHtml(Utils::GetHtml('https://www.princetonreview.com/college-search?ceid=cp-1022984'));

        $orderByUrl = ParseallcollegesController::fillOrderByUrl($doc);
        $allNodePaths = ParseallcollegesController::fillAllNodePaths($orderByUrl);

        $nodePathsEqualItemCount = Utils::EqualItemCountMulti($allNodePaths);
        $nodePathsCommonRoot = Utils::TrimArray($allNodePaths[0], $nodePathsEqualItemCount);

        ParseallcollegesController::stripNodePaths($orderByUrl, $nodePathsEqualItemCount);

        $rootNodeNameToLinks = ParseallcollegesController::fillRootNodeNameToLinks($orderByUrl);

        return $this->render('index', [
            'echoText' => ParseallcollegesController::generateEchoText($nodePathsCommonRoot, $orderByUrl, $rootNodeNameToLinks),
        ]);
    }
}
