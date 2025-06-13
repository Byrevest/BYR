<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\Category;
use app\models\Comment; // <-- ДОБАВЛЕНО: Подключаем модель Comment

/**
 * This is the model class for table "topic".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $image_url
 * @property int $user_id
 * @property int $category_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property Comment[] $comments // <-- ИЗМЕНЕНО: Ссылка на Comment, если это ваша модель сообщений
 * @property Category $category
 */
class Topic extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'topic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description', 'user_id', 'category_id'], 'required'],
            
            [['description'], 'string'], 
            
            [['user_id', 'category_id', 'created_at', 'updated_at'], 'integer'],
            
            [['title', 'image_url'], 'string', 'max' => 255],
            
            [['image_url'], 'url', 'skipOnEmpty' => true],

            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'description' => 'Описание',
            'image_url' => 'URL изображения',
            'user_id' => 'ID Пользователя',
            'category_id' => 'Категория',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Comments]]. // <-- ИЗМЕНЕНО: Имя метода теперь getComments
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments() // <-- ИЗМЕНЕНО: Метод теперь getComments
    {
        return $this->hasMany(Comment::class, ['topic_id' => 'id']); // <-- ИЗМЕНЕНО: Используем Comment::class
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * {@inheritdoc}
     * Если вы используете TimestampBehavior, убедитесь, что он подключен.
     * Если вы вручную устанавливаете created_at и updated_at, то этот метод не нужен.
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
