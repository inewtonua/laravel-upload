<?php

namespace Inewtonua\Upload\Traits;

use Inewtonua\Upload\Models\Upload;
use Illuminate\Http\Request;

trait Uploadable
{

    abstract public function uploadableConfig(): array;

    public static function config(): array
    {
        $config = [];
        $uploadableConfig = self::uploadableConfig();
        $defaultEntityConfig = self::defaultEntityConfig();

        foreach ($uploadableConfig as $entity => $entityConfig){
            //$entityConfig['styles'] = array_merge($defaultEntityConfig['styles'], $entityConfig['styles']);
            $config[$entity] = array_merge($defaultEntityConfig, $entityConfig);
        }

        return $config;
    }

    private static function defaultEntityConfig(): array
    {
        return [
            'max' => 100,
            'mode' => 'simple', //'extended'
            //'format' =>  'jpg',
            'validation' => [
                'rules' => [
                    'file' => 'required|file|mimes:jpeg,jpg,png,gif,pdf,txt,doc,docx,xls,xlsx|max:4048'
                ]
            ]
//            ,
//            'styles' => [
//                'original' => [
//                ]
//            ],
        ];

    }

    public function uploads()
    {
        return $this->morphMany('Inewtonua\Upload\Models\Upload', 'uploadable')
            ->where('deleted', '<>', 1);
    }

    /*
     *  Разбиваем массив с загрузками на типы загрузок
     */
//    public function initializeAppendAttributeTrait()
//    {
//        $this->append('attachments');
//    }

    /**
     * Добавляет к модели атрибут с массивом всех прикрепленных файлов
     *
     * @return array
     */

//    public function getAttachmentsAttribute()
//    {
//        $attachments = [];
//
//        $entities = array_keys($this->uploadableConfig());
//
//        foreach ($entities as $entity) {
//
//            $attachments[$entity] = $this->uploads->filter(function ($value, $key) use ($entity) {
//
//                return $value->uploadable_entity == $entity && $value->status == 1 && $value->deleted == 0;
//
//            });
//
//        }
//
//        return $attachments;
//    }

    protected static function bootUploadable()
    {
        /**
         * Deleting Model-Associated Files
         */
        static::deleting(function ($model) {
            if ($model->uploads) {
                foreach ($model->uploads as $key => $upload) {
                    $upload->markDeleted();
                }
            }
        });
    }

}