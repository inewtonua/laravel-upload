@php
    $max = $model::uploadableConfig()[$entity]['max'] ?? 100;
    $count = $model->attachments[$entity]->count();
@endphp

<div id="files" class="form-group clearfix">

    <input type="file" name="uploads[files][{{$entity}}]" @if($max > 1) multiple="multiple" @endif class="file-input">
    <input type="hidden" name="uploads[weight][{{$entity}}]" value="">
    <input type="hidden" name="uploads[entity][{{$entity}}]" value="{{$entity}}">

    <div class="sortable files-boxes files">

        @if($model->exists && $count)

            @foreach($model->attachments[$entity] as $upload)

                <div data-id="{{$upload->id}}" class="file-box has-background-white file-{{$upload->id}} border is-clearfix">

                    <span title="Удалить" class="op-btn deletePhotoBtn">
                        <i aria-hidden="true" class="fa fa-times"></i>
                    </span>
                    <div class="file-row">
                        <a href="{{$upload->getOriginalFileUrl()}}" target="_blank">{{$upload->original_name}}</a>
                    </div>

                    <div class="meta-box mt-1">
                        @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                            <div class="field">
                                <label for="uploads[meta][{{$entity}}][{{$upload->id}}][{{$localeCode}}][title]" class="label">Название ({{ $properties['native'] }})</label>
                                <input type="text" name="uploads[meta][files][{{$upload->id}}][{{$localeCode}}][title]" value="{{$upload->meta[$localeCode]['title'] ?? ''}}" class="input">
                            </div>
                        @endforeach
                    </div>

                </div>

                @if(!$loop->last)
                    <hr>
                    <hr>
                @endif

            @endforeach

        @endif
            <div class="file-box attachFileBtn attach-box ">
                <button type="button" class="button is-primary is-small">Добавить</button>
            </div>
    </div>


    <script type="template" class="loading-box-template">
        <div class="loading-box border" id="loading-box-!!!mark!!!">
            <span class="loader" role="status" aria-hidden="true">
                <span class="sr-only">Загрузка...</span>
            </span>
        </div>
    </script>
    <script type="template" class="file-box-template">

        <div class="file-box has-background-white box-new border clearfix" data-id="!!!id!!!">

            <span title="Удалить" class="op-btn deletePhotoBtn">
                <i class="fa fa-times" aria-hidden="true"></i>
            </span>

            <div class="file-row">
                <a href="!!!src!!!" target="_blank">!!!file_title!!!</a>
            </div>

            <div class="meta-box">
                @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                    <div class="field">
                        <label for="uploads[meta][{{$entity}}][!!!id!!!][{{$localeCode}}][title]" class="label">Название ({{ $properties['native'] }})</label>
                        <input type="text" name="uploads[meta][files][!!!id!!!][{{$localeCode}}][title]" value="{{$upload->meta[$localeCode]['title'] ?? ''}}" class="input">
                    </div>
                @endforeach
            </div>

        </div>
    </script>
</div>

@push('scripts')
    <script>
        $(function () {

            let model_name = "{!! Illuminate\Support\Str::snake(get_class($model)) !!}";
            let model_id = '{{$model->id ?? ''}}';

            // Spinner
            let Spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            $(document).ready(function (e) {

                // Сортировка

                $('#files .sortable').sortable({
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

                $('#files .sortable').disableSelection();

                // Загрузка изображения

                $(document).on('click', '#files .attachFileBtn', function (e) {
                    $("input[name='uploads[files][{{$entity}}]']").trigger("click");
                });

                $(document).on('change', "input[name='uploads[files][{{$entity}}]']", function (event) {

                    event.preventDefault();

                    let filesUploaded = $('#files .files-boxes .file-box:not(.attach-box)').length;
                    let filesAllowed = '10' - $('#files .files-boxes .file-box:not(.attach-box)').length;
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
                        form_data.append('_token', 'BLv7kWH7RNsFDvy456JP0KMGsoVHp15AiG4xTUuk');
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
                                let loadingBox = $.trim($('#files .loading-box-template').html()); // ? Зачем тут trim?
                                $('#files .attachFileBtn').before(loadingBox.replace(/!!!mark!!!/ig, mark));
                            },

                            success: function (data) {

                                if (data.status == 'error') {
                                    setTimeout(function () {
                                        $('#files #loading-box-' + mark).remove();
                                    }, 500);
                                    sendNoty(data.msg, 'error');

                                } else {

                                    let file = $.trim($('#files .file-box-template').html());

                                    let str = file
                                        .replace(/!!!id!!!/ig, data.files.id)
                                        .replace(/!!!src!!!/ig, data.files.styles.thumb)
                                        .replace(/!!!file_title!!!/ig, data.files.name);

                                    $('#files .attachFileBtn').before(str);

                                    // Добавить в список файлов
                                    $ids = $("input[name='uploads[weight][{{$entity}}]']").val();

                                    if (!$ids.trim()) {
                                        $("input[name='uploads[weight][{{$entity}}]']").val(data.files.id);
                                    } else {
                                        $("input[name='uploads[weight][{{$entity}}]']").val($ids + ',' + data.files.id);
                                    }

                                    if ($('#files .files-boxes .file-box:not(.attach-box)').length >= '10') {
                                        $('#files .attachFileBtn').addClass('attach-btn-hidden');
                                    }

                                }
                            },
                            error: function (xhr, status, error) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $('#files #loading-box-' + mark).remove();
                            }
                        });
                    });
                });

                // Удаление

                $(document).on('click', '#files .deletePhotoBtn', function (e) {

                    e.preventDefault();

                    let box = $(this).parent();
                    let file_id = box.data('id');

                    if (file_id) {

                        let form_data = new FormData();
                        form_data.append('_method', 'delete');
                        form_data.append('file_id', file_id);
                        form_data.append('_token', '{{ csrf_token() }}');
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

                                    if ($('#files .files-boxes .file-box:not(.attach-box)').length < '10') {
                                        $('#files .attachFileBtn').removeClass('attach-btn-hidden');
                                    }

                                }

                            },
                            error: function (e) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $(box).find('#files .spinner-border').remove();
                            }
                        });

                    }
                });

            });
        });
    </script>


    <style type="text/css" lang="css">

        .files-boxes.files .file-box .meta-box {
            width: 100%;
        }

    </style>


@endpush




{{-- JS, jQuery --}}

{{--@push('scripts')--}}

{{-- jquery ui для Sortable --}}
{{--<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"--}}
{{--integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>--}}

{{-- Основной JS --}}
{{--<script type="text/javascript">--}}

{{--$(function () {--}}

{{--let model_name = "{!! Illuminate\Support\Str::snake(get_class($model)) !!}";--}}
{{--let model_id = '{{$model->id ?? ''}}';--}}

{{-- Spinner --}}
{{--let Spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';--}}

{{--$(document).ready(function (e) {--}}

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

{{--let file = $.trim($('#{{$entity}} .file-box-template').html());--}}

{{--let str = file--}}
{{--.replace(/!!!id!!!/ig, data.files.id)--}}
{{--.replace(/!!!src!!!/ig, data.files.styles.thumb)--}}
{{--.replace(/!!!file_title!!!/ig, data.files.name);--}}

{{--$('#{{$entity}} .attachFileBtn').before(str);--}}

{{--// Добавить в список файлов--}}
{{--$ids = $("input[name='uploads[weight][{{$entity}}]']").val();--}}

{{--if (!$ids.trim()) {--}}
{{--$("input[name='uploads[weight][{{$entity}}]']").val(data.files.id);--}}
{{--} else {--}}
{{--$("input[name='uploads[weight][{{$entity}}]']").val($ids + ',' + data.files.id);--}}
{{--}--}}

{{--if($('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length >= '{{$max}}') {--}}
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

{{--if($('#{{$entity}} .files-boxes .file-box:not(.attach-box)').length < '{{$max}}') {--}}
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

{{--});--}}
{{--});--}}
{{--</script>--}}


{{--@endpush--}}