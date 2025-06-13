<?php

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\data\ActiveDataProvider $userTopicsProvider */ // Объявляем тип userTopicsProvider

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView; // Добавлено

$this->title = 'Мой профиль';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="profile-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card mb-4">
        <div class="card-body">
            <div class="text-center mb-3">
                <?php if ($model->profile_picture_url): ?>
                    <?= Html::img($model->profile_picture_url, [
                        'alt' => 'Фото профиля',
                        'class' => 'img-thumbnail',
                        'style' => 'width: 150px; height: 150px; object-fit: cover; border-radius: 50%;'
                    ]) ?>
                <?php else: ?>
                    <?= Html::img(Url::to('@web/img/default_avatar.png'), [
                        'alt' => 'Нет фото',
                        'class' => 'img-thumbnail',
                        'style' => 'width: 150px; height: 150px; object-fit: cover; border-radius: 50%;'
                    ]) ?>
                <?php endif; ?>
            </div>

            <p><strong>Имя пользователя:</strong> <?= Html::encode($model->username) ?></p>
            <p><strong>Email:</strong> <?= Html::encode($model->email) ?></p>

            <div class="mt-3">
                <?= Html::a('Редактировать профиль', ['update'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4>Мои топики</h4>
            <?= ListView::widget([
                'dataProvider' => $userTopicsProvider,
                'itemView' => '@app/views/topic/_topic_item', // Используем то же частичное представление, что и для общего списка топиков
                'summary' => 'Показаны {begin}-{end} из {totalCount} ваших топиков',
                'emptyText' => 'Вы пока не создали ни одного топика.',
                'options' => [
                    'tag' => 'div',
                    'class' => 'list-wrapper',
                    'id' => 'user-topic-list',
                ],
                'itemOptions' => [
                    'tag' => 'div',
                    'class' => 'topic-item card mb-3',
                ],
            ]); ?>
        </div>
    </div>
</div>