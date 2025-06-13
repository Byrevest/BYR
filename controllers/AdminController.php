<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\User;
use app\models\Admin; // Добавлено, если нужно для других админ-функций
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные
                        'matchCallback' => function ($rule, $action) {
                            return User::isAdmin(); // Только если пользователь - админ
                        }
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->session->setFlash('error', 'У вас нет прав для доступа к этой странице.');
                    return $this->goHome();
                }
            ],
        ];
    }

    /**
     * Отображает список пользователей для управления.
     * @return string
     */
    public function actionUsers()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->orderBy(['id' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('users', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Банит пользователя.
     * @param int $id User ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionBanUser($id)
    {
        $user = User::findOne($id);

        if ($user === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        // Нельзя забанить самого себя или другого админа (для простоты)
        if ($user->id === Yii::$app->user->id || User::isAdmin($user->id)) {
            Yii::$app->session->setFlash('error', 'Невозможно забанить этого пользователя.');
            return $this->redirect(['users']);
        }

        // Допустим, мы используем поле 'status' в User модели (например, 9 для забаненных)
        // Если у вас нет поля 'status', вам нужно будет его добавить в таблицу 'user'
        // и обновить rules в User модели
        $user->status = User::STATUS_BANNED; // Предполагаем константу STATUS_BANNED = 0 или 9
        if ($user->save(false)) { // false, чтобы не запускать валидацию пароля и т.д.
            Yii::$app->session->setFlash('success', 'Пользователь ' . $user->username . ' забанен.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при бане пользователя.');
        }

        return $this->redirect(['users']);
    }

    /**
     * Разбанивает пользователя.
     * @param int $id User ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUnbanUser($id)
    {
        $user = User::findOne($id);

        if ($user === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        // Нельзя разбанить, если он не забанен (логично)
        if ($user->status === User::STATUS_ACTIVE) { // Предполагаем константу STATUS_ACTIVE = 10
            Yii::$app->session->setFlash('error', 'Пользователь не забанен.');
            return $this->redirect(['users']);
        }

        $user->status = User::STATUS_ACTIVE;
        if ($user->save(false)) {
            Yii::$app->session->setFlash('success', 'Пользователь ' . $user->username . ' разбанен.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при разбане пользователя.');
        }

        return $this->redirect(['users']);
    }
}