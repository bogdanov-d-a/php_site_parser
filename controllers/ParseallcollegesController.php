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
            $result .= $orderByUrlValue['universityName'] . '<br/>';
            $result .= $orderByUrlValue['universityLocation'] . '<br/>';
            $result .= $orderByUrlValue['universityImgUrl'] . '<br/>';
            $result .= '<br/>';
        }
    }

    private static function generateEchoText($orderByUrl, $pageCount)
    {
        $result = '';
        $result .= $pageCount . '<br/>';
        $result .= '<br/>';

        ParseallcollegesController::generateEchoOrderByUrlText($orderByUrl, $result);

        return $result;
    }

    public function actionIndex()
    {
        $parseResult = AllcollegesDataParser::parse();
        return $this->render('index', [
            'echoText' => ParseallcollegesController::generateEchoText(
                $parseResult['orderByUrl'],
                $parseResult['pageCount']),
        ]);
    }
}
