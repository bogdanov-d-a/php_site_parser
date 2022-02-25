<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CollegeCardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'College Cards';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="college-card-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create College Card', ['create'], ['class' => 'btn btn-success']) ?>
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
                'urlCreator' => function ($action, CollegeCard $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
