<?php

namespace app\controllers;

use yii\web\Controller;
use app\processors\CollegeListProcessor;
use yii\BaseYii;

class ParseCollegeListController extends Controller
{
    public function actionIndex(): string
    {
        CollegeListProcessor::process(function(string $msg): void {
            BaseYii::debug($msg);
        });
        return $this->render('index');
    }
}
