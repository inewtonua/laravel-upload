# Laravel file upload package

## Package is under development, do not use it

## В контроллер:

    ```php
    use Inewtonua\Upload\Models\Upload;
    ```
    
    ```php
    Upload::setUploads($model, $request->uploads);
    ```
    
## В view
    ```php    
    <div class="c-image">
        @if($model->attachments['images_1']->first() && $model->attachments['images_1']->first()->getStyle('thumb'))
            <img data-src="{{$model->attachments['images_1']->first()->getStyle('original')}}" class="img-fluid" src="{{$model->attachments['images_1']->first()->getStyle('teaser')}}" alt="{{ $model->attachments['images_1']->first()->getAltAttribute() }}">
        @endif
    </div>
    ```    
    
## Регенерация изображенией:

1. Модель и entity (можно без entity)
    ```bash
    artisan images:regenerate App/Models/Example/Example images
    ```
    
1. Ссылка на стиль:

    ```php
    $model->attachments['head']->first()->getStyle('thumb');
    ```
    
1. Ссылка на оригинал:
    ```php
    $model->attachments['head']->first()->getOriginalFileUrl();
    ```
    
1. Путь к оригиналу:
    ```php
    ->getOriginalFilePath()
    ->getOriginalFileUrl()
    ```
    
TD:
Размер файла в БД
Диски
Переделать так:
При загрузке файл только загружается, один оригинальный, стили генерируются опционально.
т.е. вынести генерацию стилей в отделный подпроцесс к которому можно обращаться в любой время на основе основного файла.
Тогда в основной базе будут хранится данные оригинального файла, а в колонке styles его дочерние стили.


php artisan upload:remove
