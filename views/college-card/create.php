<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CollegeCard */

$this->title = 'Create College Card';
$this->params['breadcrumbs'][] = ['label' => 'College Cards', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="college-card-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
