<?php

namespace app\controllers;

use yii\web\Controller;
use app\processors\CollegeCardProcessor;
use yii\BaseYii;

class ParseCollegeCardController extends Controller
{
    public function actionIndex()
    {
        CollegeCardProcessor::process(function($msg) {
            BaseYii::debug($msg);
        });
        return $this->render('index');
    }
}
