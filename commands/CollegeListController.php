<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\processors\CollegeListProcessor;

class CollegeListController extends Controller
{
    public function actionIndex(): int
    {
        CollegeListProcessor::process(function(string $msg): void {
            echo $msg . "\n";
        });
        return ExitCode::OK;
    }
}
