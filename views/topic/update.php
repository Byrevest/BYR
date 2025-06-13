<?php

/** @var yii\web\View $this */
/** @var app\models\Topic $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Редактировать топик: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Топики форума', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактировать';
?>

<div class="topic-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="topic-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'image_url')->textInput(['maxlength' => true]) ?>
        <p class="text-muted">Введите URL изображения для топика (например, с Imgur, Pixabay и т.д.).</p>

        <div class="form-group">
            <?= Html::submitButton('Сохранить изменения', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>