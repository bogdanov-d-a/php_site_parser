<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Allcolleges */

$this->title = 'Create Allcolleges';
$this->params['breadcrumbs'][] = ['label' => 'Allcolleges', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="allcolleges-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
