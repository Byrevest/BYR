<?php

namespace app\controllers;

use app\models\User;
use app\models\UserSearch;
use Yii; // Добавляем use для Yii
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl; // Добавляем use для AccessControl

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [ // Добавляем поведение для контроля доступа
                    'class' => AccessControl::class, // Используем AccessControl
                    // Применяем правила доступа ко всем действиям, кроме 'signup'
                    // 'signup' должен быть доступен гостям
                    'only' => ['index', 'view', 'update', 'delete'], 
                    'rules' => [
                        [
                            'actions' => ['index', 'view', 'update', 'delete'],
                            'allow' => true,
                            'roles' => ['admin'], // Только пользователи с ролью 'admin' могут выполнять эти действия
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class, // Исправляем на ::class
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model (action for registration).
     * If creation is successful, the browser will be redirected to the 'login' page.
     * @return string|\yii\web\Response
     */
    public function actionSignup() // Переименовали actionCreate в actionSignup
    {
        $model = new User();
        $model->scenario = 'signup'; // Устанавливаем сценарий 'signup' для валидации пароля

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // Теперь, когда данные загружены, и поле 'password' заполнено
                // Хэшируем пароль из временного поля $model->password
                $model->setPassword($model->password); 
                
                // beforeSave в модели User сгенерирует auth_key и установит created_at/updated_at
                // А также установит status = 10 (активный)
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Вы успешно зарегистрированы! Теперь вы можете войти.');
                    return $this->redirect(['site/login']); // Перенаправляем на страницу входа
                } else {
                    // Логируем ошибки валидации модели для отладки
                    Yii::error('User registration failed: ' . json_encode($model->getErrors()));
                    Yii::$app->session->setFlash('error', 'Ошибка при регистрации. Проверьте введенные данные.');
                }
            } else {
                // Логируем ошибки загрузки данных
                Yii::error('User model load failed: ' . json_encode($model->getErrors()));
                Yii::$app->session->setFlash('error', 'Ошибка загрузки данных. Пожалуйста, попробуйте еще раз.');
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('signup', [ // Рендерим представление 'signup'
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        // Внимание: Если поле 'password' в форме не пустое, это означает, что пользователь хочет изменить пароль.
        // Поэтому нам нужно временно сохранить старый хэш, чтобы не потерять его,
        // если пользователь не ввел новый пароль при редактировании.
        $oldPasswordHash = $model->password_hash; // Сохраняем текущий хэш перед загрузкой данных из POST

        if ($this->request->isPost && $model->load($this->request->post())) {
            // Если поле 'password' (временное) было заполнено в форме
            if (!empty($model->password)) {
                $model->setPassword($model->password); // Хэшируем новый пароль из временного поля
            } else {
                // Если поле 'password' было пустое, оставляем старый хэш пароля
                $model->password_hash = $oldPasswordHash;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Данные пользователя обновлены.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error('User update failed: ' . json_encode($model->getErrors()));
                Yii::$app->session->setFlash('error', 'Ошибка при обновлении пользователя. Проверьте введенные данные.');
            }
        }

        // Для формы обновления: обнуляем временное поле password, чтобы оно не показывало хэш
        $model->password = ''; 
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Пользователь удален.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}