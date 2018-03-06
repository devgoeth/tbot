<?php

namespace devgoeth\tbot\models;

use Yii;

/**
 * This is the model class for table "state".
 *
 * @property integer $id_user
 * @property string $state
 * @property string $menu
 * @property string $date
 */
class State extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'state';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_user', 'state', 'menu'], 'required'],
            [['id_user'], 'integer'],
            [['date'], 'safe'],
            [['state'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_user' => 'Id User',
            'state' => 'State',
            'date' => 'Date',
        ];
    }
}
