<?php

namespace app\controllers;

use yii\web\Controller;

class ParseallcollegesController extends Controller
{
    public function actionIndex()
    {
        $url = 'http://acid3.acidtests.org';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($handle);
        libxml_use_internal_errors(true); // Prevent HTML errors from displaying
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $link = $doc->getElementsByTagName('a')->item(0);

        return $this->render('index', [
            'linkText' => $link->nodeValue,
            'linkURL' => $link->getAttribute('href'),
        ]);
    }
}
