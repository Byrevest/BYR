<?php

/** @var yii\web\View $this */
/** @var app\models\Topic $model */
/** @var app\models\Category[] $categories */ // <-- ДОБАВЛЕНО: Объявляем переменную для категорий

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper; // <-- ДОБАВЛЕНО: для ArrayHelper::map

$this->title = 'Создать новый топик';
$this->params['breadcrumbs'][] = ['label' => 'Топики форума', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="topic-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="topic-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('Заголовок топика') ?>
        
        <?= $form->field($model, 'description')->textarea(['rows' => 6])->label('Описание топика') ?>
        
        <?= $form->field($model, 'image_url')->textInput(['maxlength' => true])->label('URL изображения') ?>
        <p class="text-muted">Введите URL изображения для топика (например, с Imgur, Pixabay и т.д.).</p>

        <?php
        // ДОБАВЛЕНО ПОЛЕ ВЫБОРА КАТЕГОРИИ
        // ArrayHelper::map преобразует массив объектов Category в массив key-value
        // (ID => Name) для использования в dropdownList.
        $categoryItems = ArrayHelper::map($categories, 'id', 'name');
        echo $form->field($model, 'category_id')->dropDownList(
            $categoryItems,
            ['prompt' => 'Выберите категорию...'] // Добавляет опцию "Выберите категорию..."
        )->label('Категория');
        ?>

        <div class="form-group">
            <?= Html::submitButton('Создать топик', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>