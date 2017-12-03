<?php

namespace whc\flysystemwrapper;

use Integral\Flysystem\Adapter\PDOAdapter;
use whc\common\components\Query;
use whc\flysystemwrapper\models\File;
use whc\flysystemwrapper\models\FileMetadata;
use whc\flysystemwrapper\models\FileStorage;
use Yii;
use yii\db\ActiveQuery;
use yii\i18n\PhpMessageSource;

class FlysystemWrapper extends \yii\base\Widget
{
    public function init()
    {
        if (!isset(Yii::$app->get('i18n')->translations['message*'])) {
            Yii::$app->get('i18n')->translations['message*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => Yii::$app->language
            ];
        }

        parent::init();
    }

    /**
     * @param $files
     * @param $data
     * @return bool
     */
    public static function upload($files, $data)
    {
        $config = new \League\Flysystem\Config;

        foreach ((array)$files as $file)
        {
            $filePath = Yii::getAlias($data['path']) . '/' . $file->name;
            $fileContent = file_get_contents($file->tempName);
            if(Yii::$app->fs->write($filePath, $fileContent, $config) !== false)
            {
                $fileModel = new File;
                $fileModel->file_name = $file->name;
                $fileModel->path = $filePath;
                $fileModel->size = $file->size;
                $fileModel->mime_type = $file->type;
                $fileModel->context = isset($data['context'])? $data['context'] : null;
                $fileModel->version = isset($data['version'])? $data['version'] : null;
                $fileModel->hash = sha1(uniqid(rand(), true));
                $fileModel->save();

                if($fileModel->save())
                {
                    foreach ((array)$data['metadata'] as $metadata => $value)
                    {
                        $fileMetadataModel = new FileMetadata();
                        $fileMetadataModel->file_id = $fileModel->id;
                        $fileMetadataModel->metadata = $metadata;
                        $fileMetadataModel->value = (string)$value;
                        $fileMetadataModel->save();
                    }
                }
            }
            else
            {
                return false;
            }
        }
        return true;
    }

    /**
     * get file by hash key
     * @param $hash
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getByHash($hash)
    {
        return File::find()
            ->alias('f')
            ->innerJoinWith('fileMetadatas')
            ->where(['f.hash' => $hash, 'f.deleted_time' => null])
            ->asArray()
            ->all();
    }

    /**
     * read file by hash key
     * @param $hash
     * @return bool
     */
    public function readByHash($hash)
    {
        $fileModel = File::find()->where(['hash' => $hash, 'deleted_time' => null])->one();
        $fileStorageModel = FileStorage::find()->where(['path' => $fileModel->path])->one();

        if($fileModel !== false && $fileStorageModel !== false)
        {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $fileModel->mime_type);
            header('Content-Disposition: inline; filename="' . $fileModel->file_name);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $fileModel->size);

            echo Yii::$app->fs->read($fileStorageModel->path);
        }
        return false;
    }

    /**
     * search by metadata or special file model fields
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function searchByParams($params)
    {
        // special fields is fields that have exist in file model.
        $specialFields = ['context', 'version'];

        $fileModel = File::find()
            ->distinct()
            ->select('hash')
            ->alias('f');

        $i = 1;

        foreach ($params as $meta => $value)
        {
            if(in_array($meta, $specialFields))
            {
                $fileModel->andWhere([$meta => $value]);
                continue;
            }

            $fmAlais = 'fm_' . $i++;
            $fileModel->innerJoin([$fmAlais => FileMetadata::tableName()], "f.id={$fmAlais}.file_id AND {$fmAlais}.metadata=:meta_param AND {$fmAlais}.value=:meta_value", ['meta_param' => $meta, 'meta_value' => $value]);
        }
        $fileModel->andWhere(['f.deleted_time' => null]);

        return $fileModel->all();
    }

    /**
     * delete a file by hash key
     * @param $hash
     */
    public static function deleteByHash($hash)
    {
        $fileModel = File::find()->where(['hash' => $hash, 'deleted_time' => null])->one();
        if($fileModel !== null)
        {
            return Yii::$app->fs->delete($fileModel);
        }
        return false;
    }
}
