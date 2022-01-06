<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "form".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $desc
 */
class Form extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'desc' => 'Desc',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FormQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FormQuery(get_called_class());
    }
}
