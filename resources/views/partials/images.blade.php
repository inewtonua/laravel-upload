@php
    $max = $model::uploadableConfig()[$entity]['max'] ?? 100;
    $mode = $model::uploadableConfig()[$entity]['mode'] ?? 'simple';
    $uploads = $model->uploads->filter(function ($image, $key) use ($entity) {
       return $image->uploadable_entity == $entity && $image->status == 1 && $image->deleted == 0;
    });
    $count = $uploads->count();
@endphp

<div id="{{$entity}}" class="form-group clearfix">

    <a href="#" class="changeModeBtn mode mb-2" style="display: block">
        {{ $mode == 'extended' ? 'Перейти в простой режим' : 'Перейти в расширенный режим' }}
    </a>

    <input type="file" name="uploads[files][{{$entity}}]" class="file-input" @if($max > 1) multiple @endif>
    <input type="hidden" name="uploads[weight][{{$entity}}]" value="">
    <input type="hidden" name="uploads[entity][{{$entity}}]" value="{{$entity}}">

    <div class="sortable files-boxes {{ $mode }}">

        @if($model->exists && $count)

            @foreach($uploads as $upload)

                <div class="file-box has-background-white file-{{$upload->id}} border is-clearfix" data-id="{{$upload->id}}">

                    <span title="Удалить фото" class="op-btn deletePhotoBtn">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </span>

                    <span title="Повернуть фото" class="op-btn rotatePhotoBtn">
                        <i class="fa fa-repeat" aria-hidden="true"></i>
                    </span>

                    <div class="image-box">
                        <img src="{{$upload->getStyle('thumb')}}">
                    </div>

                    <div class="meta-box">
                        @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                            <div class="field">
                                <label for="uploads[meta][{{$entity}}][{{$upload->id}}][{{$localeCode}}][alt]" class="label">Alt ({{ $properties['native'] }})</label>
                                <input type="text" name="uploads[meta][{{$entity}}][{{$upload->id}}][{{$localeCode}}][alt]" value="{{old("uploads.meta.{$entity}.{$upload->id}.{$localeCode}.alt", $upload->meta[$localeCode]['alt'] ?? '')}}" class="input">
                            </div>
                            <div class="field">
                                <label for="uploads[meta][{{$entity}}][{{$upload->id}}][{{$localeCode}}][title]" class="label">Title ({{ $properties['native'] }})</label>
                                <input type="text" name="uploads[meta][{{$entity}}][{{$upload->id}}][{{$localeCode}}][title]" value="{{old("uploads.meta.{$entity}.{$upload->id}.{$localeCode}.title", $upload->meta[$localeCode]['title'] ?? '')}}" class="input">
                            </div>
                        @endforeach
                    </div>

                </div>

            @endforeach

        @endif

        <div class="file-box attachFileBtn attach-box @if($count >= $max) attach-btn-hidden @endif">
            <button type="button" class="button is-primary is-small">Добавить</button>
        </div>

    </div>

    {{--Шаблон загрузки изображения--}}

    <script class="loading-box-template" type="template">
        <div class="loading-box border" id="loading-box-!!!mark!!!">
            <span class="spinner-border text-success" role="status" aria-hidden="true">
                <span class="sr-only">Загрузка...</span>
            </span>
        </div>
    </script>

    {{-- Шаблон поля изображения --}}

    <script class="field-box-template" type="template">

        <div class="file-box box-new border is-clearfix" data-id="!!!id!!!">

            <span title="Удалить фото" class="op-btn deletePhotoBtn">
                <i class="fa fa-times" aria-hidden="true"></i>
            </span>
            <span title="Повернуть фото" class="op-btn rotatePhotoBtn">
                <i class="fa fa-repeat" aria-hidden="true"></i>
            </span>

            <div class="image-box">
                <img src="!!!src!!!" class="img-fluid">
            </div>

            <div class="meta-box">
                @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                    <div class="field">
                        <label for="uploads[meta][{{$entity}}][!!!id!!!][{{$localeCode}}][alt]" class="label">Alt ({{ $properties['native'] }})</label>
                        <input type="text" name="uploads[meta][{{$entity}}][!!!id!!!][{{$localeCode}}][alt]" value="" class="input">
                    </div>
                    <div class="field">
                        <label for="uploads[meta][{{$entity}}][!!!id!!!][{{$localeCode}}][title]" class="label">Title ({{ $properties['native'] }})</label>
                        <input type="text" name="uploads[meta][{{$entity}}][!!!id!!!][{{$localeCode}}][title]" value="" class="input">
                    </div>
                @endforeach
            </div>

        </div>

    </script>

</div>

{{-- JS, jQuery --}}

@push('scripts')

    {{-- jquery ui для Sortable --}}
    <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
            integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>

    {{-- Основной JS --}}
    <script type="text/javascript">

        $(function () {

            let model_name = "{!! Illuminate\Support\Str::snake(get_class($model)) !!}";
            let model_id = '{{$model->id ?? ''}}';

            let Spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            $(document).ready(function (e) {

                $(document).on('click', '#{{$entity}} .changeModeBtn', function (e) {
                    e.preventDefault();
                    if ($('#{{$entity}} .sortable').hasClass('extended')) {
                        $(this).text('Расширенный режим');
                        $('#{{$entity}} .sortable').addClass('simple').removeClass('extended');
                    } else {
                        $(this).text('Простой режим');
                        $('#{{$entity}} .sortable').addClass('extended').removeClass('simple');
                    }
                });

                $('#{{$entity}} .sortable').sortable({
                    items: '.file-box:not(.attach-box)',
                    update: function (event, ui) {
                        var idsInOrder = $(this).sortable('toArray', {attribute: 'data-id'});
                        $("input[name='uploads[weight][{{$entity}}]']").val(idsInOrder);
                    },
                    create: function (event, ui) {
                        var idsInOrder = $(this).sortable('toArray', {attribute: 'data-id'});
                        $("input[name='uploads[weight][{{$entity}}]']").val(idsInOrder);
                    }
                });

                $('#{{$entity}} .sortable').disableSelection();

                $(document).on('click', '#{{$entity}} .attachFileBtn', function (e) {
                    $("input[name='uploads[files][{{$entity}}]']").trigger("click");
                });

                $(document).on('change', "input[name='uploads[files][{{$entity}}]']", function (event) {

                    event.preventDefault();

                    let filesUploaded = $('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length;
                    let filesAllowed = '25' - $('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length;
                    let files = $(this.files).slice(0, filesAllowed);

                    $(files).each(function (index, file) {

                        // Валидация файлов
                        if (file.size > 3500000) {  // 3.5Мб
                            sendNoty('Файл слишком большой.', 'error');
                            return false;
                        }

                        // Временная метка блока
                        let mark = Math.random().toString(36).substr(5, 6);

                        // Отправка
                        var form_data = new FormData();
                        form_data.append('_token', '{{ csrf_token() }}');
                        form_data.append('parent_model_name', model_name);
                        form_data.append('parent_model_entity', $("input[name='uploads[entity][{{$entity}}]']").val());
                        form_data.append('parent_model_id', model_id);
                        form_data.append('file', file);

                        $.ajax({
                            url: "{{ route('upload') }}",
                            type: "POST",
                            data: form_data,
                            contentType: false,
                            cache: false,
                            processData: false,

                            beforeSend: function () {
                                let loadingBox = $.trim($('#{{$entity}} .loading-box-template').html()); // ? Зачем тут trim?
                                $('#{{$entity}} .attachFileBtn').before(loadingBox.replace(/!!!mark!!!/ig, mark));
                            },

                            success: function (data) {

                                if (data.status == 'error') {
                                    setTimeout(function () {
                                        $('#{{$entity}} #loading-box-' + mark).remove();
                                    }, 500);
                                    sendNoty(data.msg, 'error');

                                } else {

                                    let field = $.trim($('#{{$entity}} .field-box-template').html());

                                    let str = field.replace(/!!!id!!!/ig, data.files.id).replace(/!!!src!!!/ig, data.files.styles.thumb);

                                    $('#{{$entity}} .attachFileBtn').before(str);

                                    // Добавить в список файлов
                                    $ids = $("input[name='uploads[weight][{{$entity}}]']").val();

                                    if (!$ids.trim()) {
                                        $("input[name='uploads[weight][{{$entity}}]']").val(data.files.id);
                                    } else {
                                        $("input[name='uploads[weight][{{$entity}}]']").val($ids + ',' + data.files.id);
                                    }

                                    if ($('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length >= '25') {
                                        $('#{{$entity}} .attachFileBtn').addClass('attach-btn-hidden');
                                    }

                                }
                            },
                            error: function (xhr, status, error) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $('#{{$entity}} #loading-box-' + mark).remove();
                            }
                        });
                    });
                });



                $(document).on('click', '#{{$entity}} .deletePhotoBtn', function (e) {

                    e.preventDefault();

                    let box = $(this).parent();
                    let file_id = box.data('id');

                    if (file_id) {

                        let form_data = new FormData();
                        form_data.append('_method', 'delete');
                        form_data.append('_token', '{{ csrf_token() }}');
                        form_data.append('file_id', file_id);
                        form_data.append('parent_model_name', model_name);
                        form_data.append('parent_model_entity', $("input[name='uploads[entity][{{$entity}}]']").val());
                        form_data.append('parent_model_id', model_id);

                        $.ajax({

                            url: "{{ route('upload.destroy') }}",
                            type: "POST",
                            data: form_data,
                            contentType: false,
                            cache: false,
                            processData: false,

                            beforeSend: function () {
                                $(box).find('.image-box').prepend(Spinner);
                            },

                            success: function (data) {

                                if (data.status == 'error') {

                                    sendNoty(data.msg, 'error');

                                } else {

                                    box.remove();

                                    let ids = $("input[name='uploads[weight][{{$entity}}]']").val().split(',');
                                    ids = jQuery.grep(ids, function (value) {
                                        return value != file_id;
                                    });

                                    let str = ids.join(',')
                                    $("input[name='uploads[weight][{{$entity}}]']").val(str)

                                    if ($('#images .files-boxes .file-box:not(.attach-box)').length < '25') {
                                        $('#images .attachFileBtn').removeClass('attach-btn-hidden');
                                    }

                                }

                            },
                            error: function (e) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $(box).find('#{{$entity}} .spinner-border').remove();
                            }
                        });

                    }
                });



                $(document).on('click', '#{{$entity}} .rotatePhotoBtn', function (e) {

                    e.preventDefault();

                    let box = $(this).parent();
                    let file_id = box.data('id');

                    if (file_id) {

                        let form_data = new FormData();
                        form_data.append('_token', '{{ csrf_token() }}');
                        form_data.append('file_id', file_id);
                        form_data.append('parent_model_name', model_name);
                        form_data.append('parent_model_entity', $("input[name='uploads[entity][{{$entity}}]']").val());

                        $.ajax({
                            url: "{{ route('upload.rotate') }}",
                            type: "POST",
                            data: form_data,
                            contentType: false,
                            cache: false,
                            processData: false,

                            beforeSend: function () {
                                $(box).find('.image-box').prepend(Spinner);
                            },
                            success: function (data) {
                                if (data.status == 'error') {
                                    sendNoty(data.msg, 'error');
                                } else {
                                    let img = $(box).find('img');
                                    if (data.files.styles.thumb) {
                                        let src = data.files.styles.thumb + '?' + $.now();
                                        $(img).attr("src", src);
                                    }
                                }
                            },
                            error: function (xhr, status, error) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $(box).find('.image-box .spinner-border').remove();
                            }
                        });
                    }

                });

            });
        });



        {{--$(function () {--}}

            {{--let model_name = "{!! Illuminate\Support\Str::snake(get_class($model)) !!}";--}}
            {{--let model_id = '{{$model->id ?? ''}}';--}}

                {{-- Spinner --}}
            {{--let Spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';--}}

            {{--$(document).ready(function (e) {--}}

                {{-- Режим просмотра --}}

                {{--$(document).on('click', '#{{$entity}} .changeModeBtn', function (e) {--}}
                    {{--e.preventDefault();--}}
                    {{--if ($('#{{$entity}} .sortable').hasClass('extended')) {--}}
                        {{--$(this).text('Расширенный режим');--}}
                        {{--$('#{{$entity}} .sortable').addClass('simple').removeClass('extended');--}}
                    {{--} else {--}}
                        {{--$(this).text('Простой режим');--}}
                        {{--$('#{{$entity}} .sortable').addClass('extended').removeClass('simple');--}}
                    {{--}--}}
                {{--});--}}

                {{-- Сортировка --}}
                {{--$('#{{$entity}} .sortable').sortable({--}}
                    {{--items: '.file-box:not(.attach-box)',--}}
                    {{--update: function (event, ui) {--}}
                        {{--var idsInOrder = $(this).sortable('toArray', {attribute: 'data-id'});--}}
                        {{--$("input[name='uploads[weight][{{$entity}}]']").val(idsInOrder);--}}
                    {{--},--}}
                    {{--create: function (event, ui) {--}}
                        {{--var idsInOrder = $(this).sortable('toArray', {attribute: 'data-id'});--}}
                        {{--$("input[name='uploads[weight][{{$entity}}]']").val(idsInOrder);--}}
                    {{--}--}}
                {{--});--}}

                {{--$('#{{$entity}} .sortable').disableSelection();--}}

                {{-- Загрузка изображения --}}
                {{--$(document).on('click', '#{{$entity}} .attachFileBtn', function (e) {--}}
                    {{--$("input[name='uploads[files][{{$entity}}]']").trigger("click");--}}
                {{--});--}}

                {{--$(document).on('change', "input[name='uploads[files][{{$entity}}]']", function (event) {--}}

                    {{--event.preventDefault();--}}

                    {{--let filesUploaded = $('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length;--}}
                    {{--let filesAllowed = '{{$max}}' - $('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length;--}}
                    {{--let files = $(this.files).slice(0, filesAllowed);--}}

                    {{--$(files).each(function (index, file) {--}}

                        {{--// Валидация файлов--}}
                        {{--if (file.size > 3500000) {  // 3.5Мб--}}
                            {{--sendNoty('Файл слишком большой.', 'error');--}}
                            {{--return false;--}}
                        {{--}--}}

                        {{--// Временная метка блока--}}
                        {{--let mark = Math.random().toString(36).substr(5, 6);--}}

                        {{--// Отправка--}}
                        {{--var form_data = new FormData();--}}
                        {{--form_data.append('_token', '{{ csrf_token() }}');--}}
                        {{--form_data.append('parent_model_name', model_name);--}}
                        {{--form_data.append('parent_model_entity', $("input[name='uploads[entity][{{$entity}}]']").val());--}}
                        {{--form_data.append('parent_model_id', model_id);--}}
                        {{--form_data.append('file', file);--}}

                        {{--$.ajax({--}}
                            {{--url: "{{ route('upload') }}",--}}
                            {{--type: "POST",--}}
                            {{--data: form_data,--}}
                            {{--contentType: false,--}}
                            {{--cache: false,--}}
                            {{--processData: false,--}}

                            {{--beforeSend: function () {--}}
                                {{--let loadingBox = $.trim($('#{{$entity}} .loading-box-template').html()); // ? Зачем тут trim?--}}
                                {{--$('#{{$entity}} .attachFileBtn').before(loadingBox.replace(/!!!mark!!!/ig, mark));--}}
                            {{--},--}}

                            {{--success: function (data) {--}}

                                {{--if (data.status == 'error') {--}}
                                    {{--setTimeout(function () {--}}
                                        {{--$('#{{$entity}} #loading-box-' + mark).remove();--}}
                                    {{--}, 500);--}}
                                    {{--sendNoty(data.msg, 'error');--}}

                                {{--} else {--}}

                                    {{--let field = $.trim($('#{{$entity}} .field-box-template').html());--}}

                                    {{--let str = field.replace(/!!!id!!!/ig, data.files.id).replace(/!!!src!!!/ig, data.files.styles.thumb);--}}

                                    {{--$('#{{$entity}} .attachFileBtn').before(str);--}}

                                    {{--// Добавить в список файлов--}}
                                    {{--$ids = $("input[name='uploads[weight][{{$entity}}]']").val();--}}

                                    {{--if (!$ids.trim()) {--}}
                                        {{--$("input[name='uploads[weight][{{$entity}}]']").val(data.files.id);--}}
                                    {{--} else {--}}
                                        {{--$("input[name='uploads[weight][{{$entity}}]']").val($ids + ',' + data.files.id);--}}
                                    {{--}--}}

                                    {{--if ($('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length >= '{{$max}}') {--}}
                                        {{--$('#{{$entity}} .attachFileBtn').addClass('attach-btn-hidden');--}}
                                    {{--}--}}

                                {{--}--}}
                            {{--},--}}
                            {{--error: function (xhr, status, error) {--}}
                                {{--if (xhr.statusText) {--}}
                                    {{--sendNoty(xhr.statusText, 'error', xhr.status);--}}
                                {{--}--}}
                            {{--},--}}
                            {{--complete: function () {--}}
                                {{--$('#{{$entity}} #loading-box-' + mark).remove();--}}
                            {{--}--}}
                        {{--});--}}
                    {{--});--}}
                {{--});--}}

                {{-- Удаление --}}

                {{--$(document).on('click', '#{{$entity}} .deletePhotoBtn', function (e) {--}}

                    {{--e.preventDefault();--}}

                    {{--let box = $(this).parent();--}}
                    {{--let file_id = box.data('id');--}}

                    {{--if (file_id) {--}}

                        {{--let form_data = new FormData();--}}
                        {{--form_data.append('_method', 'delete');--}}
                        {{--form_data.append('file_id', file_id);--}}
                        {{--form_data.append('parent_model_name', model_name);--}}
                        {{--form_data.append('parent_model_entity', $("input[name='uploads[entity][{{$entity}}]']").val());--}}
                        {{--form_data.append('parent_model_id', model_id);--}}

                        {{--$.ajax({--}}

                            {{--url: "{{ route('upload.destroy') }}",--}}
                            {{--type: "POST",--}}
                            {{--data: form_data,--}}
                            {{--contentType: false,--}}
                            {{--cache: false,--}}
                            {{--processData: false,--}}

                            {{--beforeSend: function () {--}}
                                {{--$(box).find('.image-box').prepend(Spinner);--}}
                            {{--},--}}

                            {{--success: function (data) {--}}

                                {{--console.log(data);--}}

                                {{--if (data.status == 'error') {--}}

                                    {{--sendNoty(data.msg, 'error');--}}

                                {{--} else {--}}

                                    {{--box.remove();--}}
                                    {{--let ids = $("input[name='uploads[weight][{{$entity}}]']").val().split(',');--}}
                                    {{--ids = jQuery.grep(ids, function (value) {--}}
                                        {{--return value != file_id;--}}
                                    {{--});--}}
                                    {{--let str = ids.join(',')--}}
                                    {{--$("input[name='uploads[weight][{{$entity}}]']").val(str)--}}

                                    {{--if ($('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length < '{{$max}}') {--}}
                                        {{--$('#{{$entity}} .attachFileBtn').removeClass('attach-btn-hidden');--}}
                                    {{--}--}}

                                {{--}--}}

                            {{--},--}}
                            {{--error: function (e) {--}}
                                {{--if (xhr.statusText) {--}}
                                    {{--sendNoty(xhr.statusText, 'error', xhr.status);--}}
                                {{--}--}}
                            {{--},--}}
                            {{--complete: function () {--}}
                                {{--$(box).find('#{{$entity}} .spinner-border').remove();--}}
                            {{--}--}}
                        {{--});--}}

                    {{--}--}}
                {{--});--}}

                {{-- Поворот --}}

                {{--$(document).on('click', '#{{$entity}} .rotatePhotoBtn', function (e) {--}}

                    {{--e.preventDefault();--}}

                    {{--let box = $(this).parent();--}}
                    {{--let file_id = box.data('id');--}}

                    {{--if (file_id) {--}}

                        {{--let form_data = new FormData();--}}
                        {{--form_data.append('_token', '{{ csrf_token()}}');--}}
                        {{--form_data.append('file_id', file_id);--}}
                        {{--form_data.append('parent_model_name', model_name);--}}
                        {{--form_data.append('parent_model_entity', $("input[name='uploads[entity][{{$entity}}]']").val());--}}

                        {{--$.ajax({--}}

                            {{--url: "{{ route('upload.rotate') }}",--}}
                            {{--type: "POST",--}}
                            {{--data: form_data,--}}
                            {{--contentType: false,--}}
                            {{--cache: false,--}}
                            {{--processData: false,--}}

                            {{--beforeSend: function () {--}}
                                {{--$(box).find('.image-box').prepend(Spinner);--}}
                            {{--},--}}
                            {{--success: function (data) {--}}
                                {{--if (data.status == 'error') {--}}
                                    {{--sendNoty(data.msg, 'error');--}}
                                {{--} else {--}}
                                    {{--let img = $(box).find('img');--}}
                                    {{--if (data.files.styles.thumb) {--}}
                                        {{--let src = data.files.styles.thumb + '?' + $.now();--}}
                                        {{--$(img).attr("src", src);--}}
                                    {{--}--}}
                                {{--}--}}
                            {{--},--}}
                            {{--error: function (xhr, status, error) {--}}
                                {{--if (xhr.statusText) {--}}
                                    {{--sendNoty(xhr.statusText, 'error', xhr.status);--}}
                                {{--}--}}
                            {{--},--}}
                            {{--complete: function () {--}}
                                {{--$(box).find('.image-box .spinner-border').remove();--}}
                            {{--}--}}
                        {{--});--}}
                    {{--}--}}

                {{--});--}}

            {{--});--}}
        {{--});--}}
    </script>


    <style type="text/css" lang="css">

        .files-boxes {
            vertical-align: top;
            position: relative;
        }
        .changeModeBtn {
            font-size: 0.82rem
        }

        /*
        * Кнопки
        */
        .files-boxes .file-box:not(.attach-box) {
            background-color: #f8f9fa;
        }
        .files-boxes .file-box, .files-boxes .loading-box {
            position: relative;
            padding: 3px;
            width: 100%;
            margin-bottom: 10px;
        }

        .files-boxes .file-box.box-new {
            background-color: #f8f9fa;
        }

        .files-boxes .file-box.attachFileBtn {
            text-align: left;
            padding: 15px;
        }

        .files-boxes .file-box .op-btn {
            display: none;
            position: absolute;
            top: -10px;
            border-radius: 25px;
            width: 25px;
            height: 25px;
            background-color: #db5c4c;
            font-size: 16px;
            padding: 4px 3px 2px 6px;
            color: #fff;
            line-height: 100%;
            z-index: 999;
            cursor: pointer;
        }

        .files-boxes .file-box:hover .op-btn {
            display: block;
        }

        .files-boxes .file-box .deletePhotoBtn {
            right: -7px;
            font-size: 17px;
            padding: 3px 3px 2px 6px;
        }

        .files-boxes .file-box .rotatePhotoBtn {
            left: -10px;
            background-color: #256799;
        }

        .files-boxes .file-box .image-box {
            width: 12%;
            float: left;
            margin-right: 1%;
            position: relative;
        }

        .files-boxes .file-box .meta-box {
            width: 86%;
            float: left;
            padding: 5px 0;
            padding-left: 10px;
        }

        .files-boxes .loading-box {
            padding: 15px;
            text-align: center;
        }
        .file-box.attach-box.attach-btn-hidden {
            display: none;
        }
        .file-input {
            opacity: 0;
            position: relative;
        }
        .files-boxes .form-group label {
            font-weight: normal;
        }

        /*
        *  Упрощенный
        */
        .files-boxes.simple .file-box, .files-boxes.simple .loading-box {
            width: 160px;
            height: 158px;
            float: left;
            margin: 3px;
            padding: 1px;
        }

        .files-boxes.simple .file-box .image-box {
            width: auto;
        }

        .files-boxes.simple .loading-box {
            padding-top: 60px;
        }

        .files-boxes.simple .file-box.attach-box {
            padding-top: 60px;
            text-align: center;
        }

        .files-boxes .file-box .image-box .spinner-border {
            width: 28%;
            height: 28%;
            text-align: center;
            position: absolute;
            color: #fff;
            top: 52px;
            left: 52px;
        }

        .files-boxes.simple .file-box .meta-box {
            display: none;
        }
        .files-boxes .file-box {
            border-bottom: 1px solid  #eee;
            margin-bottom: 15px;
            padding-bottom: 15px;
        }
        .extended .files-boxes .file-box:last-of-type {
            border-bottom: none;
        }

    </style>
@endpush

