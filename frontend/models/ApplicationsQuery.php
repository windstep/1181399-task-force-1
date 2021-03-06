<?php

namespace frontend\models;

/**
 * This is the ActiveQuery class for [[Application]].
 *
 * @see Application
 */
class ApplicationsQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Application[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Application|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
