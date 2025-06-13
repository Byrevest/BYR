<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\rbac\DbManager; // Добавляем этот use

/**
 * Rbac controller
 */
class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); // Очищаем все старые данные RBAC

        // Создаем роль "admin"
        $admin = $auth->createRole('admin');
        $auth->add($admin);

        // Создаем роль "user" (по умолчанию для всех зарегистрированных)
        $user = $auth->createRole('user');
        $auth->add($user);

        // Теперь назначим роль "admin" конкретному пользователю
        // Предположим, что пользователь с username 'admin' будет админом.
        // Вам нужно будет зарегистрировать такого пользователя через форму регистрации.
        // После регистрации узнайте его ID из таблицы `user` в phpMyAdmin.
        // Например, если ID администратора = 1, то:
        // $auth->assign($admin, 1);

        $this->stdout('Done!' . PHP_EOL);
    }

    public function actionAssignAdmin($userId)
    {
        $auth = Yii::$app->authManager;
        $adminRole = $auth->getRole('admin');

        if (!$adminRole) {
            $this->stdout("Role 'admin' does not exist. Run rbac/init first." . PHP_EOL);
            return Controller::EXIT_CODE_ERROR;
        }

        $auth->assign($adminRole, $userId);
        $this->stdout("Role 'admin' assigned to user ID: $userId" . PHP_EOL);
        return Controller::EXIT_CODE_NORMAL;
    }
}