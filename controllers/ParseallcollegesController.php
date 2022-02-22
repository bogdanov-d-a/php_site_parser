<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\AllcollegesDataParser;

class ParseallcollegesController extends Controller
{
    private static function generateEchoOrderByUrlText($orderByUrl, &$result)
    {
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            $result .= $orderByUrlKey . '<br/>';
            $result .= Utils::BoolToStr($orderByUrlValue['isFeatured']) . '<br/>';
            $result .= $orderByUrlValue['headerNodeIndex'] . '<br/>';
            $result .= $orderByUrlValue['universityName'] . '<br/>';
            $result .= $orderByUrlValue['universityLocation'] . '<br/>';
            $result .= $orderByUrlValue['universityImgUrl'] . '<br/>';
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

    private static function generateEchoText($nodePathsCommonRoot, $featuredNodePathsCommonRoot, $orderByUrl, $rootNodeNameToLinks, $pageCount)
    {
        $result = '';
        $result .= implode('/', $nodePathsCommonRoot) . '<br/>';
        $result .= implode('/', $featuredNodePathsCommonRoot) . '<br/>';
        $result .= $pageCount . '<br/>';
        $result .= '<br/>';

        ParseallcollegesController::generateEchoOrderByUrlText($orderByUrl, $result);
        ParseallcollegesController::generateEchoRootNodeNameToLinksText($rootNodeNameToLinks, $result);

        return $result;
    }

    public function actionIndex()
    {
        $parseResult = AllcollegesDataParser::parse();
        return $this->render('index', [
            'echoText' => ParseallcollegesController::generateEchoText(
                $parseResult['nodePathsCommonRoot'],
                $parseResult['featuredNodePathsCommonRoot'],
                $parseResult['orderByUrl'],
                $parseResult['rootNodeNameToLinks'],
                $parseResult['pageCount']),
        ]);
    }
}
