<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\OnecollegeDataParser;
use yii\BaseYii;

class ParseonecollegeController extends Controller
{
    public function actionIndex()
    {
        $parseResult = OnecollegeDataParser::parse('https://www.princetonreview.com/college/harvard-college-1022984?ceid=cp-1022984');
        BaseYii::debug('ParseonecollegeController::actionIndex');
        BaseYii::debug($parseResult);
        return $this->render('index');
    }
}
