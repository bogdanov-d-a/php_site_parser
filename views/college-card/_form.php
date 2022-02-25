<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CollegeCard */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="college-card-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'srcurl')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'needupd')->checkbox() ?>

    <?= $form->field($model, 'name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'address')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'phone')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'siteurl')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
