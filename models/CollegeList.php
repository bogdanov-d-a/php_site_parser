<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "college_list".
 *
 * @property int $id
 * @property string|null $imgurl
 * @property string $name
 * @property string|null $city
 * @property string|null $state
 */
class CollegeList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'college_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['imgurl', 'name', 'city', 'state'], 'string'],
            [['name'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'imgurl' => 'Imgurl',
            'name' => 'Name',
            'city' => 'City',
            'state' => 'State',
        ];
    }
}
