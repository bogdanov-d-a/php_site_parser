<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\Utils;

class ParseallcollegesController extends Controller
{
    public function actionIndex()
    {
        $doc = Utils::ParseHtml(Utils::GetHtml('http://acid3.acidtests.org'));
        $link = $doc->getElementsByTagName('a')->item(0);

        return $this->render('index', [
            'linkText' => $link->nodeValue,
            'linkURL' => $link->getAttribute('href'),
        ]);
    }
}
