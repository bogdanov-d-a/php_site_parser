<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\processors\CollegeCardProcessor;

class CollegeCardController extends Controller
{
    public function actionIndex(): int
    {
        CollegeCardProcessor::process(function(string $msg): void {
            echo $msg . "\n";
        });
        return ExitCode::OK;
    }
}
