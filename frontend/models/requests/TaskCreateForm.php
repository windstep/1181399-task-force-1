<?php

namespace frontend\models\requests;

use frontend\models\Attachment;
use frontend\models\Category;
use frontend\models\City;
use frontend\models\Task;
use frontend\services\YandexMapsApiService;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class TaskCreateForm extends Model
{
    const EXPIRE_TIME = 60 * 60 * 24; // Одни сутки
    const TAG = 'geocode';

    public $title;
    public $description;
    public $category;
    public $budget;
    public $until;
    public $files;
    public $address;

    public function rules()
    {
        return [
            [['title', 'description', 'category', 'budget', 'until'], 'safe'],
            [['title', 'description', 'category'], 'required'],
            [['title', 'description', 'address'], 'string', 'min' => 1],
            [['category'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['budget'], 'number', 'min' => '1'],
            [['until'], 'date', 'format' => 'Y-m-d'],
            [['files'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'Название',
            'description' => 'Описание задания',
            'category' => 'Категория',
            'budget' => 'Бюджет',
            'until' => 'Дата окончания',
            'files' => 'Файлы'
        ];
    }

    /**
     * Загрузка файлов к заданию.
     *
     * @param Task $task
     * @return bool
     */
    public function upload(Task $task)
    {
        if ($this->validate()) {
            $this->files = UploadedFile::getInstances($this, 'files');

            foreach ($this->files as $file) {
                $name = 'uploads/' . $file->baseName . '.' . $file->extension;
                $file->saveAs($name);
                $uploadedFile = new Attachment();
                $uploadedFile->name = $file->baseName . '.' . $file->extension;
                $uploadedFile->url = $name;
                $uploadedFile->save();

                $uploadedFile->link('tasks', $task);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Основной метод сохранения задания
     *
     * @return false|int
     * @throws \Throwable
     */
    public function saveTask()
    {
        if (!$this->validate()) {
            return false;
        }

        $task = new Task();
        $task->budget = $this->budget;
        $task->title = $this->title;
        $task->description = $this->description;
        $task->until = $this->until;
        $task->author_id = \Yii::$app->user->getId();
        $task->category_id = $this->category;

        if (isset($this->address)) {
            $task->address = $this->address;

            $service = new YandexMapsApiService(Yii::$app->params['mapsApiKey']);
            $response = $this->getGeocodeFromCache($service, $this->address);

            $point = $service->getCoordsFromResponse($response);
            $city = $service->getCityFromResponse($response);

            if (is_array($point)) {
                $task->map_w = $point['lat'];
                $task->map_h = $point['long'];
            }

            if ($city) {
                $cityId = City::find()->where(['like', 'name', $city])->one()->id ?? Yii::$app->user->getIdentity(
                    )->city_id ?? null;

                if ($cityId) {
                    $task->city_id = $cityId;
                }
            }
        }

        $task->save();

        $this->upload($task);

        return $task->id;
    }

    /**
     * Если вдруг у нас есть в кэше адрес - используем его.
     *
     * @param YandexMapsApiService $service
     * @param string $address
     * @return array|false
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getGeocodeFromCache(YandexMapsApiService $service)
    {
        try {
            $keyName = self::TAG . ":" . base64_encode($this->address);
            if (!$response = Yii::$app->redisCache->get($keyName)) {
                $response = $service->getPositionFromAddress($this->address);
                Yii::$app->redisCache->set($keyName, $response, self::EXPIRE_TIME);
            }
        } catch (\Exception $e) {
            $response = $service->getPositionFromAddress($this->address);
        }

        return $response;
    }
}
