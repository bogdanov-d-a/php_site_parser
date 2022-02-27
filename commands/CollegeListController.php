<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\processors\CollegeListProcessor;

class CollegeListController extends Controller
{
    public function actionIndex()
    {
        CollegeListProcessor::process(function($msg) {
            echo $msg . "\n";
        });
        return ExitCode::OK;
    }
}
