<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "college_card".
 *
 * @property int $id
 * @property string $srcurl
 * @property bool|null $needupd
 * @property string|null $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $siteurl
 */
class CollegeCard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'college_card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['srcurl'], 'required'],
            [['srcurl', 'name', 'address', 'phone', 'siteurl'], 'string'],
            [['needupd'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'srcurl' => 'Srcurl',
            'needupd' => 'Needupd',
            'name' => 'Name',
            'address' => 'Address',
            'phone' => 'Phone',
            'siteurl' => 'Siteurl',
        ];
    }
}
