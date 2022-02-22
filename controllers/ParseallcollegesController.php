<?php

namespace app\controllers;

use yii\web\Controller;
use app\controllers\AllcollegesDataParser;
use app\models\Allcolleges;

class ParseallcollegesController extends Controller
{
    public function actionIndex()
    {
        $parseResult = AllcollegesDataParser::parse();

        foreach ($parseResult as $parseResultKey => $parseResultValue)
        {
            $ac = new Allcolleges();
            $ac->imgurl = $parseResultValue['universityImgUrl'];
            $ac->name = $parseResultValue['universityName'];
            $ac->city = $parseResultValue['universityCity'];
            $ac->state = $parseResultValue['universityState'];
            $ac->save();
        }

        return $this->render('index');
    }
}
