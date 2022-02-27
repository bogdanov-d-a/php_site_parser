<?php

namespace app\processors;

use app\parsers\CollegeListDataParser;
use app\models\CollegeList;

class CollegeListProcessor
{
    public static function process($traceCallback)
    {
        $parseResult = CollegeListDataParser::parse($traceCallback);

        CollegeList::getDb()->createCommand('TRUNCATE `college_list`')->execute();

        foreach ($parseResult as $parseResultKey => $parseResultValue)
        {
            $ac = new CollegeList();
            $ac->cardurl = $parseResultKey;
            $ac->imgurl = $parseResultValue['universityImgUrl'];
            $ac->name = $parseResultValue['universityName'];
            $ac->city = $parseResultValue['universityCity'];
            $ac->state = $parseResultValue['universityState'];
            $ac->save();
        }
    }
}
