<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\Utils;

class ParseallcollegesController extends Controller
{
    public function actionIndex()
    {
        $doc = Utils::ParseHtml(Utils::GetHtml('https://www.princetonreview.com/college-search?ceid=cp-1022984'));

        $orderByUrl = [];
        foreach ($doc->getElementsByTagName('a') as $link)
        {
            $url = Utils::RemoveUrlQuery($link->getAttribute('href'));
            if (str_starts_with($url, '/college/'))
            {
                if (!array_key_exists($url, $orderByUrl))
                {
                    $orderByUrl[$url] = [];
                }
                $orderByUrl[$url][] = [
                    'node' => $link,
                    'nodePath' => explode('/', $link->getNodePath()),
                ];
            }
        }

        $allNodePaths = [];
        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            foreach ($orderByUrlValue as $orderByUrlValueItem)
            {
                $allNodePaths[] = $orderByUrlValueItem['nodePath'];
            }
        }

        $nodePathsEqualItemCount = Utils::EqualItemCountMulti($allNodePaths);
        $nodePathsCommonRoot = implode('/', Utils::TrimArray($allNodePaths[0], $nodePathsEqualItemCount));

        foreach ($orderByUrl as $orderByUrlKey => &$orderByUrlValueRef)
        {
            foreach ($orderByUrlValueRef as &$orderByUrlValueItemRef)
            {
                $orderByUrlValueItemRef['nodePath'] = Utils::StripArrayBeginning($orderByUrlValueItemRef['nodePath'], $nodePathsEqualItemCount);
            }
            unset($orderByUrlValueItemRef);
        }
        unset($orderByUrlValueRef);

        $echoText = '';
        $echoText .= strval($nodePathsCommonRoot) . '<br/>';
        $echoText .= '<br/>';

        foreach ($orderByUrl as $orderByUrlKey => $orderByUrlValue)
        {
            $echoText .= $orderByUrlKey . '<br/>';

            foreach ($orderByUrlValue as $orderByUrlValueItem)
            {
                $echoText .= implode('/', $orderByUrlValueItem['nodePath']) . '<br/>';
            }

            $echoText .= '<br/>';
        }

        return $this->render('index', [
            'echoText' => $echoText,
        ]);
    }
}
