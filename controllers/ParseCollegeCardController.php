<?php

namespace app\controllers;

use yii\web\Controller;
use app\processors\CollegeCardProcessor;
use yii\BaseYii;

class ParseCollegeCardController extends Controller
{
    public function actionIndex(): string
    {
        CollegeCardProcessor::process(function(string $msg): void {
            BaseYii::debug($msg);
        });
        return $this->render('index');
    }
}
