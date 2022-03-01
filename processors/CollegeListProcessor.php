<?php

namespace app\processors;

use app\parsers\CollegeListDataParser;
use app\models\CollegeList;

class CollegeListProcessor
{
    public static function process(callable $traceCallback): void
    {
        $parseResult = CollegeListDataParser::parse($traceCallback);

        CollegeList::getDb()->createCommand('TRUNCATE `college_list`')->execute();

        foreach ($parseResult as $parseResultKey => $parseResultValue)
        {
            $collegeList = new CollegeList();
            $collegeList->cardurl = $parseResultKey;
            $collegeList->imgurl = $parseResultValue['universityImgUrl'];
            $collegeList->name = $parseResultValue['universityName'];
            $collegeList->city = $parseResultValue['universityCity'];
            $collegeList->state = $parseResultValue['universityState'];
            $collegeList->save();
        }
    }
}
