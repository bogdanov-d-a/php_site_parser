<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CollegeList */

$this->title = 'Create College List';
$this->params['breadcrumbs'][] = ['label' => 'College Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="college-list-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
