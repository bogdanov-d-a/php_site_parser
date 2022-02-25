<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CollegeList */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="college-list-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'imgurl')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'city')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'state')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
