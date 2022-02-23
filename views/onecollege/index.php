<?php

use app\models\Onecollege;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\OnecollegeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Onecolleges';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="onecollege-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Onecollege', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'srcurl:ntext',
            'needupd:boolean',
            'name:ntext',
            'address:ntext',
            //'phone:ntext',
            //'siteurl:ntext',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Onecollege $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
