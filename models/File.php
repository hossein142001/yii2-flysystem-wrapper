<?php

namespace hossein142001\flysystemwrapper\models;

use hiiran\api\v1\modules\user\models\User;
use Yii;


/**
 * This is the model class for table "{{%file}}".
 *
 * @property integer $id
 * @property string $file_name
 * @property string $path
 * @property integer $size
 * @property string $mime_type
 * @property string $context
 * @property integer $version
 * @property string $hash
 * @property string $created_at
 * @property integer $created_user_id
 * @property string $updated_at
 * @property integer $updated_user_id
 * @property string $deleted_at
 * @property integer $deleted_user_id
 *
 * @property User $createdUser
 * @property User $updatedUser
 * @property User $deletedUser
 * @property FileMetadata[] $fileMetadatas
 */
class File extends \hiiran\components\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_name', 'path', 'size', 'mime_type', 'hash'], 'required' , 'except' => 'getByParams'],
            [['size', 'version', 'created_user_id', 'updated_user_id', 'deleted_user_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['file_name', 'path'], 'string', 'max' => 255],
            [['mime_type'], 'string', 'max' => 25],
            [['context'], 'string', 'max' => 100],
            [['hash'], 'string', 'max' => 64],
            [['path'], 'unique'],
            [['hash'], 'unique'],
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
            'file_name' => Module::t('v1/app', 'File Name'),
            'path' => Module::t('v1/app', 'Path'),
            'size' => Module::t('v1/app', 'Size'),
            'mime_type' => Module::t('v1/app', 'Mime Type'),
            'context' => Module::t('v1/app', 'Context'),
            'version' => Module::t('v1/app', 'Version'),
            'hash' => Module::t('v1/app', 'Hash'),
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFileMetadatas()
    {
        return $this->hasMany(FileMetadata::className(), ['file_id' => 'id']);
    }
}