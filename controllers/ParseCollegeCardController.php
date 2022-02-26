<?php

namespace app\controllers;

use yii\web\Controller;
use app\parsers\CollegeCardDataParser;
use app\models\CollegeCard;
use yii\BaseYii;

class ParseCollegeCardController extends Controller
{
    public function actionIndex()
    {
        foreach (CollegeCard::find()->where('`needupd`')->all() as $collegeCard)
        {
            $url = $collegeCard->srcurl;
            BaseYii::debug('ParseCollegeCardController::actionIndex parse ' . $url);
            $parseResult = CollegeCardDataParser::parse($url);
            sleep(1);  // reduce server request rate

            $collegeCard->needupd = false;
            $collegeCard->address = $parseResult['address'];
            $collegeCard->phone = $parseResult['phone'];
            $collegeCard->siteurl = $parseResult['siteurl'];
            $collegeCard->name = $parseResult['name'];
            $collegeCard->save();
        }

        return $this->render('index');
    }
}
