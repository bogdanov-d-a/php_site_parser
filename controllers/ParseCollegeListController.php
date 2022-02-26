<?php

namespace app\controllers;

use yii\web\Controller;
use app\parsers\CollegeListDataParser;
use app\models\CollegeList;

class ParseCollegeListController extends Controller
{
    public function actionIndex()
    {
        $parseResult = CollegeListDataParser::parse();

        CollegeList::getDb()->createCommand('TRUNCATE `college_list`')->execute();

        foreach ($parseResult as $parseResultKey => $parseResultValue)
        {
            $ac = new CollegeList();
            $ac->imgurl = $parseResultValue['universityImgUrl'];
            $ac->name = $parseResultValue['universityName'];
            $ac->city = $parseResultValue['universityCity'];
            $ac->state = $parseResultValue['universityState'];
            $ac->save();
        }

        return $this->render('index');
    }
}
