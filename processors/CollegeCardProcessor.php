<?php

namespace app\processors;

use app\parsers\CollegeCardDataParser;
use app\models\CollegeCard;

class CollegeCardProcessor
{
    public static function process(callable $traceCallback): void
    {
        foreach (CollegeCard::find()->where('`needupd`')->all() as $collegeCard)
        {
            $url = $collegeCard->srcurl;
            call_user_func($traceCallback, 'CollegeCardProcessor::process parse ' . $url);
            $parseResult = CollegeCardDataParser::parse($url);
            sleep(1);  // reduce server request rate

            $collegeCard->needupd = false;
            $collegeCard->address = $parseResult['address'];
            $collegeCard->phone = $parseResult['phone'];
            $collegeCard->siteurl = $parseResult['siteurl'];
            $collegeCard->name = $parseResult['name'];
            $collegeCard->save();
        }
    }
}
