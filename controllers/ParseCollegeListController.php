<?php

namespace app\controllers;

use yii\web\Controller;
use app\processors\CollegeListProcessor;
use yii\BaseYii;

class ParseCollegeListController extends Controller
{
    public function actionIndex()
    {
        CollegeListProcessor::process(function($msg) {
            BaseYii::debug($msg);
        });
        return $this->render('index');
    }
}
