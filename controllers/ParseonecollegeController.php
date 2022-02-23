<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\OnecollegeDataParser;
use app\models\Onecollege;
use yii\BaseYii;

class ParseonecollegeController extends Controller
{
    public function actionIndex()
    {
        foreach (Onecollege::find()->where('`needupd`')->all() as $onecollege)
        {
            $url = $onecollege->srcurl;
            BaseYii::debug('ParseonecollegeController::actionIndex parse ' . $url);
            $parseResult = OnecollegeDataParser::parse($url);
            sleep(1);  // reduce server request rate

            $onecollege->needupd = false;
            $onecollege->address = $parseResult['address'];
            $onecollege->phone = $parseResult['phone'];
            $onecollege->siteurl = $parseResult['siteurl'];
            $onecollege->name = $parseResult['name'];
            $onecollege->save();
        }

        return $this->render('index');
    }
}
