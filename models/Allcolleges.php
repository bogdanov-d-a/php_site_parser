<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "allcolleges".
 *
 * @property int $id
 * @property string $imgurl
 * @property string $name
 * @property string $city
 * @property string $state
 */
class Allcolleges extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'allcolleges';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'imgurl', 'name', 'city', 'state'], 'required'],
            [['id'], 'integer'],
            [['imgurl', 'name', 'city', 'state'], 'string'],
            [['id'], 'unique'],
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
