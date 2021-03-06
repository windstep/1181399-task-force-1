<?php

namespace frontend\models\requests;

use frontend\models\Application;
use frontend\models\Feed;
use frontend\models\Task;
use frontend\services\notifications\NotificationService;
use Yii;
use yii\base\Model;

class ApplicationCreateForm extends Model
{
    public $comment;
    public $budget;
    public $task_id;

    public function rules()
    {
        return [
            [['budget', 'comment', 'task_id'], 'safe'],
            [['budget', 'comment', 'task_id'], 'required'],
            [['comment'], 'string', 'min' => 1],
            [['budget'], 'number', 'min' => '1'],
            [['task_id'], 'exist', 'targetClass' => Task::class, 'targetAttribute' => 'id'],
            [['task_id'], 'uniqueApplication'],
            [['task_id'], 'userIsExecutor']
        ];
    }

    public function attributeLabels()
    {
        return [
            'budget' => 'Бюджет',
            'comment'=> 'Комментарий'
        ];
    }
    /**
     * Выполнение всех действий по добавлению заявки к объявлению.
     *
     * @return bool
     */
    public function create()
    {
        $task = Task::findOne(['id' => $this->task_id]);

        if (!$task) {
            return false;
        }

        if ($task->author_id === Yii::$app->user->getId()) {
            return false;
        }

        if ($this->validate()) {

            $task = Task::findOne(['id' => $this->task_id]);
            $notification = new NotificationService();
            $notification->notify($task->author, Feed::APPLICATION, $task->id);

            return $this->createApplication();
        }
        return false;
    }


    /**
     * Валидатор уникальности заявки
     *
     * @return bool
     * @throws \Throwable
     */
    public function uniqueApplication()
    {
        if (Yii::$app->user->getIdentity()->applied($this->task_id)) {
            $this->addError('task_id', "Вы уже создавали заявку на эту задачу");
            return false;
        }

        return true;
    }

    /**
     * Проверка пользователя на то, что он не является "автором".
     *
     * @return bool
     * @throws \Throwable
     */
    public function userIsExecutor()
    {
        if (Yii::$app->user->getIdentity()->isAuthor()) {
            $this->addError('task_id', 'Вы должны являться исполнителем.');
            return false;
        }
        return true;
    }


    /**
     * Создание модели заявки
     *
     * @return bool
     */
    private function createApplication()
    {
        $application = new Application();
        $application->task_id = $this->task_id;
        $application->comment = $this->comment;
        $application->budget = $this->budget;
        $application->user_id = Yii::$app->user->getId();

        return $application->save();
    }
}
