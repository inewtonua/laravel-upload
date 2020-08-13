<?php

namespace Inewtonua\Upload;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Inewtonua\Upload\Models\Upload as UploadModel;
use Inewtonua\Upload\Contracts\Upload as UploadContract;
use Inewtonua\Upload\Contracts\UploadModel as UploadModelContract;
use phpDocumentor\Reflection\Types\Integer;

class Upload implements UploadContract
{

    public $uploadModel;

    public $files = []; // Массив с адресами созданых файлов

    public $fileOriginalName;
    public $fileMime;
    public $fileName; // Уникальное имя файла

    private $disk = 'public';

    public $userId; // Id текущего пользователя
    public $parentModelName; // Класс родительской модели
    public $parentModelEntity; // Группа изображений/файлов относительно модели
    public $parentModelId; // Id родительской модели
    //public $config; // Конфиг для Entity
    //public $stylesConfig; // Отдельно конфиги стилей если это изображения
    public $storeBasePath; // Корневая директория для модели и entity
    public $fileStatus; // Сохранять как временный или постоянный

    public function __construct(UploadModelContract $uploadModel)
    {
        $this->uploadModel = $uploadModel;
    }

    public function setParams(int $userId, string $parentModelName, string $parentModelEntity, $parentModelId, array $config )
    {

        $this->userId = $userId;
        $this->parentModelName = $parentModelName;
        $this->parentModelEntity = $parentModelEntity;
        $this->parentModelId = $parentModelId;

        $this->storeBasePath = $this->makeStoreBasePath();
        $this->fileStatus = $this->parentModelId ? UploadModel::STATUS_ACTIVE : UploadModel::STATUS_TEMP;

//        $this->config = $config;
//        $this->stylesConfig = $config['styles'] ?? null;

        return $this;
    }

    private function makeStoreBasePath(){

        return implode(DIRECTORY_SEPARATOR, [
            $this->userId,
            Str::snake(class_basename($this->parentModelName)),
            $this->parentModelEntity
        ]);

    }

    public function saveFile(UploadedFile $file)
    {

        $this->fileOriginalName = $file->getClientOriginalName();
        $this->fileMime = $file->getClientMimeType();

        // Обработка и запись файлов на диск

//        if(!empty($this->stylesConfig)) {
//
//            foreach ($this->stylesConfig as $style => $options) {
//
//                if($path = $this->storeFile($file, $style, $options)){
//
//                    $this->files[$style] = $path;
//
//                } else {
//
//                    return false;
//
//                }
//            }
//
//        } else { // Если стилей нет - просто файл

            if($path = $this->storeFile($file)) {

                $this->files['thumb'] = $path;

            } else {

                return false;

            }

//        }

        // БД
        return $this->uploadModel->create([

            'user_id'  => $this->userId,
            'original_name' => $this->fileOriginalName,
            'file_mime' => $this->fileMime,
            'file_name' => $this->fileName,
//            'disk'     => $this->disk, // ?? UploadModel::DISK_DEFAULT,
            'path'     => $this->storeBasePath,  // ??????
            'status'   => $this->fileStatus,
//            'private'  => 0, //$this->visibility,
            'uploadable_type'    => $this->parentModelName,
            'uploadable_entity' => $this->parentModelEntity,
            'uploadable_id' => $this->parentModelId,
           // 'styles'   => $this->files

        ]);

    }

    /**
     * Запись файла на диск
     * @param UploadedFile $file
     * @param null $style
     * @param null $options
     * @return string
     */
    private function storeFile(UploadedFile $file) :string
    {

        $storePath = $this->storePath();

        $name = $this->getUniqueName($file, $storePath, $options['format'] ?? null);

        $this->fileName = implode('.', $name);

        /**
         * new
         */
//        if(is_null($options)) { // Как файл

            if($path = $file->storeAs($storePath, $this->fileName, ['disk' => $this->disk])) {
                return $path;
            } else {
                \Log::error('Can\'t save file.', [ $storePath.'/'.$this->fileName ]);
                return false;
            }

//        } else { // Как картинку
//
//            /**
//             * Применение стилей
//             */
//            $image = \Image::make($file);
//
//            if(!empty($options['operation'])) {
//
//                foreach ($options['operation'] as $operation => $value) {
//
//                    if(!isset($value['aspectRatio']) or $value['aspectRatio']){
//
//                        $image->$operation($value['width'] ?? null, $value['height'] ?? null, function ($constraint) {
//                            $constraint->aspectRatio();
//                            $constraint->upsize();
//                        });
//
//                    } else {
//
//                        $image->$operation($value['width'] ?? null, $value['height'] ?? null, function ($constraint) {
////                          $constraint->aspectRatio();
//                            $constraint->upsize();
//                        });
//
//                    }
//
//                }
//
//            }
//
//            $image = (string) $image->encode($options['format'] ?? null, $options['quality'] ?? 100); // params - format, quality
//
//            if( \Storage::disk($this->disk)->put($storePath.'/'.$this->fileName, $image) ) {
//
//                //    $this->fileMime = \Storage::disk($this->disk)->mimeType($storePath.DIRECTORY_SEPARATOR.$filename);
//                return $storePath.DIRECTORY_SEPARATOR.$this->fileName;
//
//            } else {
//
//                \Log::error('Can\'t save image file.', [ $storePath.'/'.$this->fileName]);
//                return false;
//
//            }

//        }

    }

//    /**
//     * Путь к папке модели относительно диска
//     * @return string
//     */
//    private function modelPath() :string
//    {
//        $sc = Str::snake($this->modelName);
//        return $this->userId.DIRECTORY_SEPARATOR.$sc;
//    }

    /**
     * Путь к папке в которой будет сохранён файл.
     * @param null $style
     * @return string
     */
    private function storePath() :string
    {
        // $modelPath = $this->modelPath();

//        if($style)
//            return $this->storeBasePath.DIRECTORY_SEPARATOR.$style;
//        else
            return $this->storeBasePath;

    }

    /**
     * Уникальное имя файла + транслитерация
     * @param UploadedFile $file
     * @param string $folderPath - относительно диска
     * @param null $format - формат файла если он будет изменён в дальнейшем
     * @return array [имя, расширение]
     */

    // TO-DO:  Передавать сюда только имя файла и расширение
    public function getUniqueName( $file, $path = '', $newExt = null ) :array
    {

        $ext = $file->getClientOriginalExtension();
        $name = basename($file->getClientOriginalName(), '.'.$ext);
        $uniqueFileName = $name = Str::slug(mb_substr($name, 0, 64), '-');

        $ext = $newExt ?? $ext; // Смена формата

        $counter = 0;

        do {

            if ($counter > 0) {
                $uniqueFileName = $name . '-' . $counter;
            }

            $checkFilePath = "{$path}/{$uniqueFileName}.{$ext}";

            ++$counter;

        } while (\Storage::disk($this->disk)->exists($checkFilePath));

        return [$uniqueFileName, $ext];

    }

    /**
     * Поворот картинки
     * @param UploadModel $model
     * @return bool
     */
    public function rotate(UploadModel $model ) :bool
    {

        foreach ($model->styles as $key => $path) {

            $dest = \Storage::disk('public')->path($path);

            try {

                \Image::make($dest)->rotate(-90)->save($dest, 100);

            } catch (Exception $e) {

                \Log::error('Не получилось повернуть изображение: '.$e->getMessage(), [ $model ]);

                return false;

            }

        }

        return true;

    }


}