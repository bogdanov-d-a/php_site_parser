<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\OnecollegeDataParser;

class ParseonecollegeController extends Controller
{
    public function actionIndex()
    {
        OnecollegeDataParser::parse('https://www.princetonreview.com/college/harvard-college-1022984?ceid=cp-1022984');
        return $this->render('index');
    }
}
