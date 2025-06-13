<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */ // Объявляем тип dataProvider
/** @var string $searchQuery */ // Добавляем объявление переменной для поискового запроса
/** @var app\models\Category[] $categories */ // <-- ДОБАВЛЕНО: Объявляем переменную для списка категорий
/** @var int $selectedCategoryId */ // <-- ДОБАВЛЕНО: Объявляем переменную для выбранной категории

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView; // Подключаем ListView
use yii\helpers\ArrayHelper; // <-- ДОБАВЛЕНО: Для удобной работы с массивами (ArrayHelper::map)
// use yii\grid\GridView; // Если вы используете GridView

$this->title = 'Топики форума';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="topic-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="search-and-filter-form">
                <?= Html::beginForm(['topic/index'], 'get', ['class' => 'form-inline d-flex']) ?>
                    <div class="input-group flex-grow-1 me-2">
                        <?= Html::textInput('q', $searchQuery ?? '', ['class' => 'form-control', 'placeholder' => 'Искать топики...']) ?>
                        <button type="submit" class="btn btn-primary">Поиск</button>
                    </div>

                    <!-- ДОБАВЛЕНО: Выпадающий список для фильтрации по категориям -->
                    <?php
                    $categoryItems = ArrayHelper::map($categories, 'id', 'name');
                    // Добавляем опцию "Все категории" в начало списка
                    $categoryItems = [null => 'Все категории'] + $categoryItems;

                    echo Html::dropDownList(
                        'category_id', // Имя параметра в URL
                        $selectedCategoryId, // Текущее выбранное значение
                        $categoryItems, // Список категорий
                        [
                            'class' => 'form-select me-2',
                            'onchange' => 'this.form.submit()', // Автоматическая отправка формы при изменении выбора
                        ]
                    );
                    ?>
                <?= Html::endForm() ?>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <?php if (!Yii::$app->user->isGuest): ?>
                <p class="mb-0">
                    <?= Html::a('Создать новый топик', ['create'], ['class' => 'btn btn-success']) ?>
                </p>
            <?php else: ?>
                <p class="alert alert-info mb-0">
                    Пожалуйста, <?= Html::a('войдите', ['/site/login']) ?> или <?= Html::a('зарегистрируйтесь', ['/user/signup']) ?>, чтобы создавать топики.
                </p>
            <?php endif; ?>
        </div>
    </div>


    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_topic_item', // Имя файла, который будет рендерить каждый топик
        'summary' => 'Показаны {begin}-{end} из {totalCount} топиков', // Пагинация
        'emptyText' => 'Пока нет ни одного топика.', // Текст, если топиков нет
        'options' => [
            'tag' => 'div',
            'class' => 'list-wrapper',
            'id' => 'topic-list',
        ],
        'itemOptions' => [
            'tag' => 'div',
            'class' => 'topic-item card mb-3', // Карточка для каждого топика
        ],
    ]); ?>

</div>
