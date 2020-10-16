<?php

namespace frontend\controllers;

use frontend\models\Category;
use frontend\models\City;
use frontend\models\requests\AccountForm;
use frontend\models\User;
use Yii;

class AccountController extends SecuredController
{
    public function beforeAction($action)
    {
        if ($this->action->id == 'photos') {
            $this->enableCsrfValidation = false;
        }

        return true;
    }


    public function actionIndex()
    {
        $model = AccountForm::findOne(Yii::$app->user->getId());
        $model->load(Yii::$app->request->post());

        if (Yii::$app->request->isPost && $model->validate()) {
            $model->save();
        }

        $cities = City::find()->all();
        $categories = Category::find()->all();

        return $this->render('account',
             [
                 'model' => $model,
                 'cities' => $cities,
                 'categories' => $categories
             ]
        );
    }

    public function actionPhotos()
    {
        if (Yii::$app->request->getIsAjax()) {
            Yii::$app->getUser()->getIdentity()->uploadPhotos();
        }
    }
}