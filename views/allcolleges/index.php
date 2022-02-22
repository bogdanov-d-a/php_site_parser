<?php

use app\models\Allcolleges;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AllcollegesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Allcolleges';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="allcolleges-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Allcolleges', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'imgurl:ntext',
            'name:ntext',
            'city:ntext',
            'state:ntext',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Allcolleges $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
