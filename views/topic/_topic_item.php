<?php
/** @var $model app\models\Topic */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User; // <-- Убедитесь, что эта строка присутствует

?>

<div class="card-body">
    <h2 class="card-title"><?= Html::a(Html::encode($model->title), ['topic/view', 'id' => $model->id]) ?></h2>
    <h6 class="card-subtitle mb-2 text-muted">
        Автор: <?= Html::encode($model->user->username) ?> <small> (<?= Yii::$app->formatter->asDatetime($model->created_at) ?>)</small>
    </h6>

    <?php if ($model->image_url): ?>
        <?= Html::img(Html::encode($model->image_url), [
            'class' => 'img-fluid mb-3 rounded',
            'alt' => Html::encode($model->title),
            'style' => 'max-height: 200px; object-fit: cover;' // Ограничиваем размер изображения
        ]) ?>
    <?php endif; ?>

    <p class="card-text"><?= Html::encode(mb_substr($model->description, 0, 200)) ?>...</p>
    <div class="d-flex justify-content-between align-items-center">
        <?= Html::a('Читать далее', ['topic/view', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>

        <?php if (!Yii::$app->user->isGuest && ($model->user_id === Yii::$app->user->id || User::isAdmin())): ?>
            <div class="btn-group" role="group">
                <?php if ($model->user_id === Yii::$app->user->id): // Редактировать может только автор ?>
                    <?= Html::a('Редактировать', ['topic/update', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']) ?>
                <?php endif; ?>
                <?= Html::a('Удалить', ['topic/delete', 'id' => $model->id], [
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