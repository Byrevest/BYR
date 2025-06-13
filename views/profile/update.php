<?php

/** @var yii\web\View $this */
/** @var app\models\User $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Редактировать профиль: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Мой профиль', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактировать';
?>

<div class="profile-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="user-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput(['value' => '']) // Поле для нового пароля, изначально пустое ?>
        <p class="text-muted">Оставьте пустым, если не хотите менять пароль.</p>

        <?= $form->field($model, 'profile_picture_url')->textInput(['maxlength' => true]) ?>
        <p class="text-muted">Введите URL вашей фотографии (например, с Imgur, Pixabay и т.д.).</p>

        <div class="form-group">
            <?= Html::submitButton('Сохранить изменения', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>