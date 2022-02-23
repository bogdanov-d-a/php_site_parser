<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Onecollege */

$this->title = 'Create Onecollege';
$this->params['breadcrumbs'][] = ['label' => 'Onecolleges', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="onecollege-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
