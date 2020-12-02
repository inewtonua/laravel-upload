<?php
//Segoe UI`
namespace Inewtonua\Upload\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Inewtonua\Upload\Contracts\UploadModel as UploadModelContract;

class Upload extends Model implements UploadModelContract
{
    use Sortable;

    const STATUS_TEMP  = 0;
    const STATUS_ACTIVE  = 1;

    protected $casts = [
        'status' => 'boolean',
        'meta' => 'array',
        'styles' => 'array'
    ];

    protected $fillable = [
        'user_id',
        'uploadable_type',
        'uploadable_id',
        'uploadable_entity',
        'original_name',
        'file_name',
        'file_mime',
        'path',
        'styles',
        'meta',
        'weight',
        'deleted',
        'status',
    ];

    protected $appends = [
//        'filepath',
        'title'
    ];

    public function uploadable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /*
     *  SCOPE
     */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     *
     * Binding files to a model.
     *
     * @param $id - integer - Model id.
     * @param $files - string - Sorted ids of upload models.
     * @param $meta - array - Array of Alt and Title for for files/images.
     * @param $entity - string / integer ?????
     */

    public static function setUploads($model, $uploads = []){
        
        if($model && !empty($uploads)) {

            if(!empty($uploads['weight'])) {

                foreach ($uploads['weight'] as $entity => $weights) {

                    if($weights){

                        $weightsArr = explode(',', $weights);

                        $result = self::whereIn('id', $weightsArr)->orderByRaw(\DB::raw("FIELD(id, $weights)"))->get();

                        if($result->count()) {

                            foreach ($result as $key => $file) {

                                $file->update([
                                    'uploadable_id' => $model->id,
                                    'status' => 1,
                                    'weight' => $key,
                                    'meta' => $uploads['meta'][$entity][$file->id] ?? '{}'
                                ]);

                            }

                        }

                    }

                }

            }

        } else {

            abort(500, 'No model id or files list.');

        }
    }

    /**
     * Mark record as deleted.
     */
    public function markDeleted() :bool
    {
        return $this->update([
            'status' => 0,
            'deleted' => 1
        ]);
    }

    /*
     * Returns an array of models names for the filter.
     */
    public static function getModels()
    {
        return self::orderBy('uploadable_type')
            ->distinct()
            ->getQuery()
            ->get(['uploadable_type'])
            ->toArray();
    }

    protected static function boot()
    {
        parent::boot();

        /**
         * Сортировка по умолчанию
         */
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy(\DB::raw('weight IS NULL, weight'), 'asc');
        });

        /**
         * Remove file on deletion
         */
        static::deleting(function ($model) {

            if(!empty($model->styles)) {

                foreach ($model->styles as $style => $path) {

                    if( !\Storage::disk('public')->delete($path)) {

                        \Log::error('Не удалось удалить файл ' . \Storage::disk('public')->path($path) .' для модели '.$model->id.'.');

                    }
                }

            }

            if(!\Storage::disk('public')->delete($model->path.DIRECTORY_SEPARATOR.$model->file_name) ) {

                \Log::error('Не удалось удалить файл ' . \Storage::disk('public')->path($model->path.DIRECTORY_SEPARATOR.$model->file_name) .' для модели '.$model->id);

                return false;

            }

        });

        /**
         *
         */
        static::created(function ($model) {

            $model->makeStyles();

        });

    }

    /*
     * Удаление файлов стилей
     */
    private function removeStyleFiles(){
        if(!empty($this->styles)) {
            foreach ($this->styles as $style => $path) {
                if( ! \Storage::disk('public')->delete($path) ) {
                    \Log::error('Не удалось удалить файл ' . \Storage::disk('public')->path($path) .' для модели '.$this->id.'.');
                }
            }
        }
    }

    /**
     * Возвращает полный путь к файлу
     * @return string
     */
//    public function getPathAttribute() : string
//    {
//        if(is_null($this->meta) || empty($this->styles['thumb'])) {
//
//            return \Storage::disk('public')->url($this->path.DIRECTORY_SEPARATOR.$this->file_name);
//
//        } else {
//
//            return $this->styles['thumb'];
//
//        }
//    }

    /**
     * Путь к файлу заданого стиля
     * @param $style
     * @return string|null
     */
    public function getStyle($style)
    {
        if($style == 'original' && !isset($this->styles[$style])) {
           return  $this->getOriginalFileUrl();
        }

        return isset($this->styles[$style]) ? \Storage::url($this->styles[$style]) : null;
    }

    /**
     * Возвращает урлы на файлы стилей
     * @return array
     */
    public function getStyles() :array
    {

        if(!empty($this->styles)) {

            $styles = $this->styles;

            foreach ($this->styles as $style => $path) {

                $styles[$style] = \Storage::disk('public')->url($path);

            }

            return $styles;

        } else {

            return [];

        }

    }

    public function makeStyles()
    {

        if ($this->uploadable) {
            $config = $this->uploadable->uploadableConfig();
        } else {
            $config = (new $this->uploadable_type)->uploadableConfig();
        }


        $stylesPaths = [];

        $this->removeStyleFiles();

        if(isset($config[$this->uploadable_entity])) {

            $entity = $this->uploadable_entity;

            if(isset($config[$this->uploadable_entity]['styles']) && !empty($config[$this->uploadable_entity]['styles'])) {

                $styles = $config[$this->uploadable_entity]['styles'];

                foreach ($styles as $styleName => $styleOptions) {

                    $stylesPaths[$styleName] = $this->makeStyleFile($styleName, $styleOptions) ?? '';

                }

            }
        }

        $this->styles = $stylesPaths;

        $this->save();

        //  dump($this->styles);

    }


    /*
     * Создание файла стиля
     */
    private function makeStyleFile($styleName, $styleOptions){

        $image = \Image::make($this->getOriginalFilePath());

        if($image){

            if(!empty($styleOptions['operation'])) {

                foreach ($styleOptions['operation'] as $operation => $value) {

                    if(!isset($value['aspectRatio']) or $value['aspectRatio']){

                        $image->$operation($value['width'] ?? null, $value['height'] ?? null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });

                    } else {

                        $image->$operation($value['width'] ?? null, $value['height'] ?? null, function ($constraint) {
//                          $constraint->aspectRatio();
                            $constraint->upsize();
                        });

                    }

                }

            }

            $fileExtension = $styleOptions['format'] ?? $image->extension;
            $fileName = "{$image->filename}.{$fileExtension}";
            $savePath = $this->path.DIRECTORY_SEPARATOR.$styleName.DIRECTORY_SEPARATOR.$fileName;

            $image = (string) $image->encode($styleOptions['format'] ?? null, $styleOptions['quality'] ?? 100); // params - format, quality

            if(\Storage::disk('public')->put($savePath, $image)) {
                return $savePath;
            } else {
                \Log::error('Can\'t save image file.', [ $storePath.'/'.$this->fileName]);
                return false;
            }

        }

        return false;
    }

    /**
     * Title
     * @return string
     */
    public function getTitleAttribute()
    {
        return $this->meta['title'] ?? '';
    }

    /**
     * Alt
     * @return string
     */
    public function getAltAttribute()
    {
        return $this->meta['alt'] ?? null;
    }

    /*
     * Абсолютный путь к оригинальному файлу
     */
    public function getOriginalFilePath(){

        return \Storage::disk('public')->path($this->path.DIRECTORY_SEPARATOR.$this->file_name);

    }

    /*
     * Относительный путь к оригинальному файлу
     */
    public function getOriginalFileUrl(){

        return \Storage::url($this->path.DIRECTORY_SEPARATOR.$this->file_name);

    }

}
