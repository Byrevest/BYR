<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface; // <-- Убедитесь, что эта строка есть!

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $access_token
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $profile_picture_url // <-- Gii должен был добавить это, но мы добавляем вручную на всякий случай

 * @property string $password // <-- Это свойство добавляем вручную
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface // <-- Убедитесь, что implements IdentityInterface есть!
{

    const STATUS_DELETED = 0;
const STATUS_ACTIVE = 10;
const STATUS_BANNED = 0; 
    public $password; // <-- Объявляем временное свойство для пароля

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // ОБЯЗАТЕЛЬНЫЕ ПОЛЯ:
            // Обратите внимание: 'password_hash' убран из required для основного сценария,
            // т.к. он будет заполняться из $this->password.
            // Но Gii по умолчанию мог бы его поставить как required.
            // Если вы не используете сценарии, можете его оставить как required,
            // но тогда нужно, чтобы setPassword() вызывался до валидации, 
            // или поле было не required.
            // В нашем случае, со сценарием 'signup', все будет хорошо.
            [['username', 'email', 'auth_key', 'created_at', 'updated_at'], 'required'],
           ['status', 'default', 'value' => self::STATUS_ACTIVE],
        ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_BANNED]],
            // ДЛИНА СТРОКОВЫХ ПОЛЕЙ. profile_picture_url тоже должно быть здесь.
            [['username', 'email', 'password_hash', 'profile_picture_url'], 'string', 'max' => 255], 
            [['auth_key', 'access_token'], 'string', 'max' => 32],
            // УНИКАЛЬНЫЕ ЗНАЧЕНИЯ:
            [['username'], 'unique'],
            [['email'], 'unique'],

            // -- Ваши ручные изменения для функционала --
            // Это правило делает 'password' обязательным только при сценарии 'signup'
            [['password'], 'required', 'on' => 'signup'], 
            ['email', 'email'], // Проверка формата email
            ['password', 'string', 'min' => 6], // Минимальная длина пароля

            // Правила для нового поля profile_picture_url
            [['profile_picture_url'], 'url'], // Проверка, что это валидный URL
            [['profile_picture_url'], 'default', 'value' => null], // Разрешаем NULL
            // --------------------------------------------------
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Имя пользователя',
            'email' => 'Email',
            'password_hash' => 'Хэш пароля',
            'auth_key' => 'Ключ аутентификации',
            'access_token' => 'Токен доступа',
            'status' => 'Статус',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
            'profile_picture_url' => 'URL фото профиля', // <-- Метка для нового поля
            'password' => 'Пароль', // <-- Метка для временного поля
        ];
    }
    
    /**
     * {@inheritdoc}
     * Определяем сценарии для модели. Сценарий 'signup' используется для регистрации.
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        // В сценарии 'signup' мы ожидаем username, email, password и profile_picture_url
        $scenarios['signup'] = ['username', 'email', 'password', 'profile_picture_url']; 
        return $scenarios;
    }

    /*
    * Методы IdentityInterface и вспомогательные методы
    * Они должны быть здесь, чтобы авторизация работала.
    */

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
   /**
 * {@inheritdoc}
 */
public static function findIdentity($id)
{
    return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
}

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @param int $type the type of the token. The value of this parameter depends on the implementation.
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // Для простоты, пока не используем access_token для авторизации
        return static::findOne(['access_token' => $token]);
    }

    /**
 * Finds user by username
 *
 * @param string $username
 * @return static|null
 */
public static function findByUsername($username)
{
    return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
}

    // ... (после findIdentity() или findByUsername())

/**
 * Проверяет, является ли пользователь администратором.
 * @param int|null $userId ID пользователя. Если null, используется текущий авторизованный пользователь.
 * @return bool
 */
public static function isAdmin($userId = null)
{
    if ($userId === null) {
        $userId = Yii::$app->user->id;
    }
    if ($userId === null) { // Если пользователь не авторизован
        return false;
    }
    return Admin::find()->where(['user_id' => $userId])->exists();
}

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Перед сохранением новой записи устанавливаем created_at, updated_at, auth_key и status.
     * Для существующих записей обновляем updated_at.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_at = time();
                $this->updated_at = time();
                $this->generateAuthKey(); // Генерируем auth_key при создании нового пользователя
                $this->status = 10; // Активный пользователь (по умолчанию)
            } else {
                $this->updated_at = time();
            }
            return true;
        }
        return false;
    }
}