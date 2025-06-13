<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\Topic;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\models\Comment;
use yii\db\Expression;
use app\models\Category;

class TopicController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'index', 'view', 'update', 'delete', 'comment', 'delete-comment'],
                'rules' => [
                    [
                        'actions' => ['create', 'comment'],
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи могут создавать топики и комментировать
                    ],
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['?', '@'], // Index и View доступны всем
                    ],
                    [
                        'actions' => ['update'], // Редактировать топик может только его автор
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $topicId = Yii::$app->request->get('id');
                            if ($topicId) {
                                $topic = Topic::findOne($topicId);
                                return $topic && $topic->user_id === Yii::$app->user->id;
                            }
                            return false;
                        }
                    ],
                    [
                        'actions' => ['delete'], // Удалять топик может автор ИЛИ администратор
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Если текущий пользователь - администратор, всегда разрешаем
                            if (User::isAdmin()) {
                                return true;
                            }

                            // Иначе, разрешаем только автору топика
                            $topicId = Yii::$app->request->get('id');
                            if ($topicId) {
                                $topic = Topic::findOne($topicId);
                                return $topic && $topic->user_id === Yii::$app->user->id;
                            }
                            return false;
                        }
                    ],
                    [
                        'actions' => ['delete-comment'], // Удалять комментарий может автор ИЛИ администратор
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Если текущий пользователь - администратор, всегда разрешаем
                            if (User::isAdmin()) {
                                return true;
                            }

                            // Иначе, разрешаем только автору комментария
                            $commentId = Yii::$app->request->get('id');
                            if ($commentId) {
                                $comment = Comment::findOne($commentId);
                                return $comment && $comment->user_id === Yii::$app->user->id;
                            }
                            return false;
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Отображает список топиков с пагинацией и поиском.
     * @return string
     */
    public function actionIndex()
    {
        $searchQuery = Yii::$app->request->get('q'); // Получаем поисковый запрос
        $categoryId = Yii::$app->request->get('category_id'); // <-- ДОБАВЛЕНО: Получаем ID категории из запроса

        // ОБНОВЛЕНО: Добавлена жадная загрузка связанных категорий и пользователей для списка топиков
        // ИЗМЕНЕНО: 'posts.user' -> 'comments.user'
        $query = Topic::find()->with('user', 'comments.user', 'category')->orderBy(['created_at' => SORT_DESC]);

        if ($searchQuery) {
            $query->andFilterWhere(['or',
                ['like', 'title', $searchQuery],
                ['like', 'description', $searchQuery]
            ]);
        }

        // ДОБАВЛЕНО: Фильтрация по категории, если category_id передан и валиден
        if (!empty($categoryId) && is_numeric($categoryId)) {
            $query->andWhere(['category_id' => $categoryId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // Количество топиков на страницу
            ],
        ]);

        // ДОБАВЛЕНО: Получаем список всех категорий для фильтрации
        $categories = Category::find()->orderBy('name')->all();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchQuery' => $searchQuery, // Передаем поисковый запрос в представление
            'categories' => $categories, // <-- ДОБАВЛЕНО: Передаем список категорий
            'selectedCategoryId' => $categoryId, // <-- ДОБАВЛЕНО: Передаем выбранную категорию
        ]);
    }

    /**
     * Отображает один топик.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // ОБНОВЛЕНО: Добавлена жадная загрузка связанной категории для просмотра топика
        // ИЗМЕНЕНО: 'posts.user' -> 'comments.user'
        $model = Topic::find()->where(['id' => $id])->with('user', 'comments.user', 'category')->one();
        if ($model === null) {
            throw new NotFoundHttpException('Запрашиваемый топик не найден.');
        }

        $commentModel = new Comment(); // Создаем новую модель комментария для формы

        return $this->render('view', [
            'model' => $model,
            'commentModel' => $commentModel, // Передаем модель комментария
        ]);
    }

    /**
     * Создает новый топик.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Topic();
        $model->user_id = Yii::$app->user->id; // Автоматически устанавливаем ID текущего пользователя
        // Добавьте эти строки, если TimestampBehavior не используется в модели Topic
        $model->created_at = time();
        $model->updated_at = time();

        // ДОБАВЛЕНО: Получаем список всех категорий для выпадающего списка
        $categories = Category::find()->orderBy('name')->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Топик успешно создан!');
            return $this->redirect(['view', 'id' => $model->id]); // Перенаправляем на страницу созданного топика
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => $categories, // Передаем категории в представление
        ]);
    }

    /**
     * Обновляет существующий топик.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Topic::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Запрашиваемый топик не найден.');
        }

        if ($model->user_id !== Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для редактирования этого топика.');
        }
        
        $categories = Category::find()->orderBy('name')->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Топик успешно обновлен!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }

    /**
     * Удаляет существующий топик.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = Topic::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Запрашиваемый топик не найден.');
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Топик успешно удален.');

        return $this->redirect(['index']); // Перенаправляем на список топиков
    }

    /**
     * Добавляет комментарий к топику.
     * @param int $topic_id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the topic cannot be found
     */
    public function actionComment($topic_id)
    {
        $topic = Topic::findOne($topic_id);
        if ($topic === null) {
            throw new NotFoundHttpException('Запрашиваемый топик не найден.');
        }

        $commentModel = new Comment();
        $commentModel->topic_id = $topic_id;
        $commentModel->user_id = Yii::$app->user->id;
        $commentModel->created_at = time();
        $commentModel->updated_at = time();

        if ($commentModel->load(Yii::$app->request->post()) && $commentModel->save()) {
            Yii::$app->session->setFlash('success', 'Комментарий успешно добавлен.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при добавлении комментария.');
            Yii::error('Comment creation failed: ' . json_encode($commentModel->getErrors()));
        }

        return $this->redirect(['view', 'id' => $topic_id, '#' => 'comments']); // Перенаправляем обратно к топику
    }

    /**
     * Удаляет комментарий.
     * @param int $id ID комментария
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the comment cannot be found
     */
    public function actionDeleteComment($id)
    {
        $comment = Comment::findOne($id);
        if ($comment === null) {
            throw new NotFoundHttpException('Запрашиваемый комментарий не найден.');
        }

        $topicId = $comment->topic_id; // Сохраняем ID топика, чтобы вернуться к нему

        $comment->delete();
        Yii::$app->session->setFlash('success', 'Комментарий успешно удален.');

        return $this->redirect(['view', 'id' => $topicId, '#' => 'comments']); // Перенаправляем обратно к топику
    }
}
