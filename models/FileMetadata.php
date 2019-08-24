<?php

namespace hossein142001\flysystemwrapper\models;

use hiiran\api\v1\modules\user\models\User;
use Yii;


/**
 * This is the model class for table "{{%file_metadata}}".
 *
 * @property integer $id
 * @property integer $file_id
 * @property string $metadata
 * @property string $value
 * @property string $created_at
 * @property integer $created_user_id
 * @property string $updated_at
 * @property integer $updated_user_id
 * @property string $deleted_at
 * @property integer $deleted_user_id
 *
 * @property File $file
 * @property User $createdUser
 * @property User $updatedUser
 * @property User $deletedUser
 */
class FileMetadata extends \hiiran\components\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%file_metadata}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_id', 'metadata', 'value'], 'required' , 'except' => 'getByParams'],
            [['file_id', 'created_user_id', 'updated_user_id', 'deleted_user_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['metadata', 'value'], 'string', 'max' => 255],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['file_id' => 'id']],
            [['created_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_user_id' => 'id']],
            [['updated_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_user_id' => 'id']],
            [['deleted_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['deleted_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('v1/app', 'ID'),
            'file_id' => Module::t('v1/app', 'File ID'),
            'metadata' => Module::t('v1/app', 'Metadata'),
            'value' => Module::t('v1/app', 'Value'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_user_id' => Yii::t('app', 'Created User ID'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_user_id' => Yii::t('app', 'Updated User ID'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
            'deleted_user_id' => Yii::t('app', 'Deleted User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'file_id']);
    }

	/**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'deleted_user_id']);
    }
}