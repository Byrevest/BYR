<?php

/** @var yii\web\View $this */
/** @var app\models\Topic $model */
/** @var app\models\Comment $commentModel */ // Объявляем новую переменную для формы комментария

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\User; // Добавлено для проверки isAdmin()

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Топики', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="topic-view">

    <h1><?= Html::encode($model->title) ?></h1>

    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-subtitle mb-3 text-muted">
                Автор: <?= Html::encode($model->user->username) ?> <small> (<?= Yii::$app->formatter->asDatetime($model->created_at) ?>)</small>
            </h6>

            <?php if ($model->image_url): ?>
                <div class="text-center mb-3">
                    <?= Html::img(Html::encode($model->image_url), [
                        'class' => 'img-fluid rounded',
                        'alt' => Html::encode($model->title),
                        'style' => 'max-width: 100%; height: auto; border: 1px solid #ddd;'
                    ]) ?>
                </div>
            <?php endif; ?>

            <p class="card-text"><?= Html::encode($model->description) ?></p>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <?= Html::a('К списку топиков', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>

            <?php if (!Yii::$app->user->isGuest && ($model->user_id === Yii::$app->user->id || User::isAdmin())): ?>
                <div class="btn-group" role="group">
                    <?php if ($model->user_id === Yii::$app->user->id): // Редактировать может только автор ?>
                        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']) ?>
                    <?php endif; ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'data' => [
                            'confirm' => 'Вы уверены, что хотите удалить этот топик?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <h3 id="comments">Комментарии (<?= count($model->comments) ?>)</h3>

    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="card mb-4">
            <div class="card-header">
                Оставить комментарий
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'action' => ['topic/comment', 'topic_id' => $model->id], // Указываем экшен для обработки формы
                ]); ?>

                <?= $form->field($commentModel, 'content')->textarea(['rows' => 3])->label(false) ?>

                <div class="form-group">
                    <?= Html::submitButton('Оставить комментарий', ['class' => 'btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php else: ?>
        <p class="alert alert-info">
            Пожалуйста, <?= Html::a('войдите', ['/site/login']) ?> или <?= Html::a('зарегистрируйтесь', ['/user/signup']) ?>, чтобы оставлять комментарии.
        </p>
    <?php endif; ?>

    <div class="comments-list mt-4">
        <?php if (!empty($model->comments)): ?>
            <?php foreach ($model->comments as $comment): ?>
                <div class="card mb-3">
                    <div class="card-body d-flex align-items-start">
                        <div class="comment-author-avatar me-3">
                            <?php if ($comment->user->profile_picture_url): ?>
                                <?= Html::img($comment->user->profile_picture_url, [
                                    'alt' => 'Аватар',
                                    'class' => 'img-thumbnail rounded-circle',
                                    'style' => 'width: 50px; height: 50px; object-fit: cover;'
                                ]) ?>
                            <?php else: ?>
                                <?= Html::img(Url::to('@web/img/default_avatar.png'), [ // Предполагаем, что у вас есть изображение default_avatar.png в web/img/
                                    'alt' => 'Аватар',
                                    'class' => 'img-thumbnail rounded-circle',
                                    'style' => 'width: 50px; height: 50px; object-fit: cover;'
                                ]) ?>
                            <?php endif; ?>
                        </div>
                        <div class="comment-content flex-grow-1">
                            <h6 class="mb-1">
                                <?= Html::encode($comment->user->username) ?> <small class="text-muted ms-2"><?= Yii::$app->formatter->asDatetime($comment->created_at) ?></small>
                            </h6>
                            <p class="mb-2"><?= Html::encode($comment->content) ?></p>

                            <?php if (!Yii::$app->user->isGuest && ($comment->user_id === Yii::$app->user->id || User::isAdmin())): ?>
                                <div class="d-flex justify-content-end">
                                    <?= Html::a('Удалить', ['topic/delete-comment', 'id' => $comment->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'Вы уверены, что хотите удалить этот комментарий?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>К этому топику пока нет комментариев.</p>
        <?php endif; ?>
    </div>

</div>