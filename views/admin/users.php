<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\grid\GridView; // Подключаем GridView
use app\models\User; // Для констант статусов

$this->title = 'Управление пользователями';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-users">

    <h1><?= Html::encode($this->title) ?></h1>

    <p class="alert alert-info">
        Здесь вы можете управлять пользователями форума.
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'email:email',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    if ($model->status === User::STATUS_ACTIVE) {
                        return 'Активный';
                    } elseif ($model->status === User::STATUS_BANNED) {
                        return 'Забанен';
                    } elseif ($model->status === User::STATUS_DELETED) { // Если вы используете DELETED
                        return 'Удален';
                    }
                    return 'Неизвестно';
                },
                'filter' => [ // Опционально: фильтр по статусу
                    User::STATUS_ACTIVE => 'Активный',
                    User::STATUS_BANNED => 'Забанен',
                    User::STATUS_DELETED => 'Удален',
                ],
            ],
            'created_at:datetime',
            'updated_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{ban} {unban}', // Только кнопки бана/разбана
                'buttons' => [
                    'ban' => function ($url, $model, $key) {
                        if ($model->status === User::STATUS_ACTIVE && $model->id !== Yii::$app->user->id && !User::isAdmin($model->id)) {
                            return Html::a('Бан', ['ban-user', 'id' => $model->id], [
                                'class' => 'btn btn-danger btn-xs',
                                'data-confirm' => 'Вы уверены, что хотите забанить этого пользователя?',
                                'data-method' => 'post',
                            ]);
                        }
                        return '';
                    },
                    'unban' => function ($url, $model, $key) {
                        if ($model->status === User::STATUS_BANNED) {
                            return Html::a('Разбан', ['unban-user', 'id' => $model->id], [
                                'class' => 'btn btn-success btn-xs',
                                'data-confirm' => 'Вы уверены, что хотите разбанить этого пользователя?',
                                'data-method' => 'post',
                            ]);
                        }
                        return '';
                    },
                ],
                'visibleButtons' => [ // Показываем кнопки только админу (хотя AccessControl уже это делает)
                    'ban' => function ($model) {
                        return User::isAdmin();
                    },
                    'unban' => function ($model) {
                        return User::isAdmin();
                    },
                ]
            ],
        ],
    ]); ?>

</div>