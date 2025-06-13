<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\User; // Подключаем модель User
use app\models\Topic; // Добавлено
use yii\data\ActiveDataProvider; // Добавлено
use app\models\Comment; 

class ProfileController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'update'], // Эти действия только для авторизованных пользователей
                'rules' => [
                    [
                        'actions' => ['index', 'update'],
                        'allow' => true,
                        'roles' => ['@'], // '@' означает аутентифицированные пользователи
                    ],
                ],
            ],
        ];
    }

    /**
     * Отображает страницу личного кабинета.
     * @return string
     */
    public function actionIndex()
{
    $model = Yii::$app->user->identity;

    if (!$model) {
        return $this->goHome();
    }

    $userTopicsProvider = new ActiveDataProvider([
        'query' => Topic::find()->where(['user_id' => $model->id])->orderBy(['created_at' => SORT_DESC]),
        'pagination' => [
            'pageSize' => 5,
        ],
    ]);

    return $this->render('index', [
        'model' => $model,
        'userTopicsProvider' => $userTopicsProvider, // <-- ДОБАВЬТЕ ЭТУ СТРОКУ
    ]);
}

    /**
     * Обновляет данные профиля пользователя.
     * @return string|\yii\web\Response
     */
    public function actionUpdate()
    {
        $model = Yii::$app->user->identity; // Получаем текущего аутентифицированного пользователя

        if (!$model) {
            return $this->goHome();
        }

        // Временно сохраняем старый пароль, если его не меняют
        $oldPasswordHash = $model->password_hash; 

        if ($model->load(Yii::$app->request->post())) {
            // Если поле 'password' (временное) было заполнено в форме
            if (!empty($model->password)) {
                $model->setPassword($model->password); // Хэшируем новый пароль
            } else {
                // Если пароль не был изменён, оставляем старый хэш
                $model->password_hash = $oldPasswordHash;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Профиль успешно обновлен!');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при обновлении профиля. Проверьте данные.');
                Yii::error('Profile update failed: ' . json_encode($model->getErrors()));
            }
        }

        // Очищаем поле пароля перед рендером, чтобы не показывать хэш
        $model->password = '';

        return $this->render('update', [
            'model' => $model,
        ]);
    }
}