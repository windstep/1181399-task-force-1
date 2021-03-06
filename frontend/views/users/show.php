<?php

/* @var $this yii\web\View */

/* @var $user frontend\models\User */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Task Force';

?>

<section class="content-view">
    <div class="user__card-wrapper">
        <div class="user__card">
            <img src="/<?= $user->avatar_url ?>" width="120" height="120" alt="Аватар пользователя">
            <div class="content-view__headline">
                <h1><?= Html::encode($user->name) ?></h1>
                <p><?= $user->city->name ?? '' ?>, <?= $user->getAge() ?> лет</p>
                <div class="profile-mini__name five-stars__rate">
                    <span></span><span></span><span></span><span></span><span class="star-disabled"></span>
                    <b><?= $user->getSelfFeedbacks()->sum('rating') ?? 0 ?></b>
                </div>
                <b class="done-task">Выполнил <?= $user->getExecutorTasks()->count() ?> заказов</b><b
                        class="done-review">Получил <?= $user->getSelfFeedbacks()->count() ?> отзывов</b>
            </div>
            <div class="content-view__headline user__card-bookmark user__card-bookmark--current">
                <span><?= Yii::$app->formatter->asRelativeTime($user->last_visit) ?></span>
                <a href="<?= Url::toRoute(['users/bookmark', 'id' => $user->id]) ?>"><b></b></a>
            </div>
        </div>
        <div class="content-view__description">
            <p><?= Html::encode($user->description) ?></p>
        </div>
        <div class="user__card-general-information">
            <div class="user__card-info">
                <h3 class="content-view__h3">Специализации</h3>
                <div class="link-specialization">
                    <?php
                    foreach ($user->categories as $category): ?>
                        <a href="<?= Url::toRoute(['tasks/index', 'categories[]' => $category->id]) ?>"
                           class="link-regular"><?= $category->name ?></a>
                    <?php
                    endforeach; ?>
                </div>
                <?php if ($user->private_contacts === 1): ?>
                <h3 class="content-view__h3">Контакты</h3>
                <div class="user__card-link">
                    <a class="user__card-link--tel link-regular" href="#"><?= Html::encode($user->phone) ?></a>
                    <a class="user__card-link--email link-regular" href="#"><?= Html::encode($user->email) ?></a>
                    <a class="user__card-link--skype link-regular" href="#"><?= Html::encode($user->skype) ?></a>
                </div>
                <?php endif; ?>
            </div>
            <div class="user__card-photo">
                <h3 class="content-view__h3">Фото работ</h3>
                <?php
                foreach ($user->attachments as $attachment): ?>
                    <a href="<?= $attachment->getAbsoluteUrl() ?>" download><img
                                src="<?= $attachment->getAbsoluteUrl() ?>" width="85" height="86"
                                alt="Фото работы"></a>
                <?php
                endforeach; ?>
            </div>
        </div>
    </div>
    <?php if ($user->getSelfFeedbacks()->count() > 0): ?>
    <div class="content-view__feedback">
        <h2>Отзывы<span>(<?= $user->getSelfFeedbacks()->count() ?>)</span></h2>
        <div class="content-view__feedback-wrapper reviews-wrapper">
            <?php
            foreach ($user->selfFeedbacks as $feedback): ?>
                <div class="feedback-card__reviews">
                    <p class="link-task link">Задание <a
                                href="<?= Url::toRoute(['tasks/view', 'id' => $feedback->task_id]) ?>"
                                class="link-regular">«<?= Html::encode($feedback->task->title) ?>»</a></p>
                    <div class="card__review">
                        <a href="#"><img src="/<?= $feedback->author->avatar_url ?? 'img/man-glasses.jpg' ?>" width="55"
                                         height="54"></a>
                        <div class="feedback-card__reviews-content">
                            <p class="link-name link"><a
                                        href="<?= Url::toRoute(['users/view', 'id' => $feedback->author_id]) ?>"
                                        class="link-regular"><?= Html::encode($feedback->author->name) ?></a></p>
                            <p class="review-text">
                                <?= Html::encode($feedback->comment) ?>
                            </p>
                        </div>
                        <div class="card__review-rate">
                            <p class="five-rate big-rate"><?= $feedback->rating ?><span></span></p>
                        </div>
                    </div>
                </div>
            <?php
            endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
