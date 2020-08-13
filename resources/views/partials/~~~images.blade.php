@php
    $entity = $entity ?? 'entity_1';
@endphp

<div class="form-group" id="{{$entity}}">

    <a href="#" id="changeMode" class="mode col-md-12 pb-2 pl-1" style="display: block">
        @php $mode = $mode ?? 'simple' @endphp
        {{ $mode == 'extended' ? 'Простой режим' : 'Расширенный режим' }}
    </a>

    <div class="form-group row">

        <div class="col-md-12">

            <input type="file" name="files" id="file-btn" style="opacity: 0; position: absolute; z-index: 0;" multiple>
            <input type="hidden" id="model_id" name="model_id" value="{{ $model->id ?? null }}">
            <input type="hidden" id="sort" name="sort" value="" style="width: 100%">

            <div id="sortable" class="files-boxes {{ $mode }}">

                @if(!empty($model) && !is_array($model) && $model->uploads()->count())

                    @foreach($model->uploads as $upload)

                        <div class="file-box file-{{$upload->id}} border m-1 clearfix" data-id="{{$upload->id}}">

                            <a href="#" title="Удалить фото" class="op-a delete-photo-a">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </a>
                            <a href="#" title="Повернуть фото" class="op-a rotate-photo-a">
                                <i class="fa fa-repeat" aria-hidden="true"></i>
                            </a>

                            <div class="image-box p-1 float-left">
                                <img src="{{$upload->getStyle('th')}}" alt="" class="img-fluid">
                            </div>

                            <div class="col file-meta py-1 px-3">

                                <nav class="my-2">
                                    <div class="nav nav-tabs" id="nav-tab-{{ $upload->id }}" role="tablist">
                                        @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                                            <a class="nav-item nav-link {{$localeCode === 'ru' ? 'active' : '' }}"
                                               id="nav-{{$localeCode}}-tab-{{ $upload->id }}"
                                               data-toggle="tab"
                                               href="#nav-{{$localeCode}}-{{ $upload->id }}"
                                               role="tab" aria-controls="nav-{{$localeCode}}-{{ $upload->id }}"
                                               aria-selected="{{$localeCode === 'ru' ? 'true' : 'false'}}">
                                                {{ $properties['native'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </nav>

                                <div class="tab-content" id="nav-tabContent">
                                    @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                                        <div class="tab-pane fade {{ $localeCode === 'ru' ? 'show active' : '' }}"
                                             id="nav-{{$localeCode}}-{{ $upload->id }}" role="tabpanel"
                                             aria-labelledby="nav-{{$localeCode}}-tab-{{ $upload->id }}">
                                            <input type="text"
                                                   name="meta[{{ $upload->id }}][{{$localeCode}}][alt]"
                                                   value="{{old("meta.{$upload->id}.{$localeCode}.alt", $upload->meta[$localeCode]['alt'] ?? '')}}"
                                                   class="form-control mb-2"
                                                   placeholder="Alt">
                                            <input type="text"
                                                   name="meta[{{ $upload->id }}][{{$localeCode}}][title]"
                                                   value="{{old("meta.{$upload->id}.{$localeCode}.title", $upload->meta[$localeCode]['title'] ?? '')}}"
                                                   class="form-control mb-2"
                                                   placeholder="Title">
                                        </div>
                                    @endforeach
                                </div>

                            </div>

                        </div>

                    @endforeach

                @endif

                <div class="file-box attach-box py-5 text-center" id="attach-file">
                    <button type="button" class="btn btn-success">Добавить</button>
                </div>

            </div>

        </div>

    </div>

</div>


{{-- Шаблон загрузки изображения --}}

<script id="loading-box" type="template">
    <div class="loading-box border m-1" id="loading-box-@{{mark}}">
        <span class="spinner-border text-success spinner-border-sm" role="status" aria-hidden="true"></span>
    </div>
</script>

{{-- Шаблон поля изображения --}}

<script id="field-box" type="template">

    <div class="file-box box-empty border m-1" data-id="@{{id}}">

        <a href="#" title="Удалить фото" class="op-a delete-photo-a"><i class="fa fa-times" aria-hidden="true"></i></a>
        <a href="#" title="Повернуть фото" class="op-a rotate-photo-a"><i class="fa fa-repeat"
                                                                          aria-hidden="true"></i></a>

        <div class="image-box p-1 float-left">
            <img src="@{{src}}" alt="" class="img-fluid">
        </div>

        <div class="col file-meta py-1 px-3">

            <nav class="my-2">
                <div class="nav nav-tabs" id="nav-tab-@{{id}}" role="tablist">
                    @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                        <a class="nav-item nav-link {{$localeCode === 'ru' ? 'active' : '' }}"
                           id="nav-{{$localeCode}}-tab-@{{id}}"
                           data-toggle="tab"
                           href="#nav-{{$localeCode}}-@{{id}}"
                           role="tab" aria-controls="nav-{{$localeCode}}-@{{id}}"
                           aria-selected="{{$localeCode === 'ru' ? 'true' : 'false'}}">
                            {{ $properties['native'] }}
                        </a>
                    @endforeach
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                @foreach(LaravelLocalization::getLocalesOrder() as $localeCode => $properties)
                    <div class="tab-pane fade {{ $localeCode === 'ru' ? 'show active' : '' }}"
                         id="nav-{{$localeCode}}-@{{id}}" role="tabpanel"
                         aria-labelledby="nav-{{$localeCode}}-tab-@{{id}}">
                        <input type="text"
                               name="meta[@{{id}}][{{$localeCode}}][alt]"
                               value=""
                               class="form-control mb-2"
                               placeholder="Alt">
                        <input type="text"
                               name="meta[@{{id}}][{{$localeCode}}][title]"
                               value=""
                               class="form-control mb-2"
                               placeholder="Title">
                    </div>
                @endforeach
            </div>

        </div>

    </div>
</script>


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
            let entity = '{{$entity}}';

                    {{-- Spinner --}}
            let Spinner = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            $(document).ready(function (e) {

                {{-- Режим просмотра --}}

                $(document).on('click', '#changeMode', function (e) {
                    e.preventDefault();
                    if ($('#sortable').hasClass('extended')) {
                        $(this).text('Расширенный режим');
                        $('#sortable').addClass('simple').removeClass('extended');
                    } else {
                        $(this).text('Простой режим');
                        $('#sortable').addClass('extended').removeClass('simple');
                    }
                });

                {{-- Сортировка --}}

                $('#sortable').sortable({
                    items: '.file-box:not(.attach-box)',
                    update: function (event, ui) {
                        var idsInOrder = $("#sortable").sortable('toArray', {attribute: 'data-id'});
                        $('input#sort').val(idsInOrder);
                    },
                    create: function (event, ui) {
                        var idsInOrder = $("#sortable").sortable('toArray', {attribute: 'data-id'});
                        $('input#sort').val(idsInOrder);
                    }
                });

                $('#sortable').disableSelection();

                {{-- Загрузка изображения --}}

                $(document).on('click', '#attach-file', function (e) {
                    $("input#file-btn").trigger("click");
                });

                $(document).on('change', '#file-btn', function (e) {

                    e.preventDefault();

                    let file_data = this.files;

                    $(file_data).each(function (index, file) {

                        {{-- Валидация файлов --}}
                        if (file.size > 3500000) {  // 3.5Мб
                            sendNoty('Файл слишком большой.', 'error');
                            return false;
                        }

                                {{-- Временная метка блока --}}
                        let mark = Math.random().toString(36).substr(5, 6);

                                {{-- Отправка --}}
                        var form_data = new FormData();
                        form_data.append('_token', '{{ csrf_token() }}');
                        form_data.append('model_name', model_name);
                        form_data.append('entity', entity);
                        form_data.append('model_id', model_id);
                        form_data.append('file', file);

                        $.ajax({
                            url: "{{ route('upload') }}",
                            type: "POST",
                            data: form_data,
                            contentType: false,
                            cache: false,
                            processData: false,
                            beforeSend: function () {
                                let loadingBox = $.trim($('#loading-box').html());
                                $('#attach-file').before(loadingBox.replace(/@{{mark}}/ig, mark));
                            },
                            success: function (data) {
                                if (data.status == 'error') {
                                    setTimeout(function () {
                                        $('#loading-box-' + mark).remove();
                                    }, 500);
                                    sendNoty(data.msg, 'error');
                                } else {

                                    let field = $.trim($('#field-box').html());
                                    let str = field.replace(/@{{id}}/ig, data.files.id).replace(/@{{src}}/ig, data.files.styles.th);
                                    $('#attach-file').before(str);

                                    {{-- Добавить в список файлов --}}
                                        $ids = $('input#sort').val();

                                    if (!$ids.trim()) {
                                        $('input#sort').val(data.files.id);
                                    } else {
                                        $('input#sort').val($ids + ',' + data.files.id);
                                    }
                                }
                            },
                            error: function (xhr, status, error) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $('#loading-box-' + mark).remove();
                            }
                        });
                    });
                });

                {{-- Удаление --}}
                $(document).on('click', '.delete-photo-a', function (e) {

                    e.preventDefault();

                    let box = $(this).parent();
                    let file_id = box.data('id');

                    if (file_id) {

                        let form_data = new FormData();
                        form_data.append('_method', 'delete');
                        form_data.append('file_id', file_id);
                        form_data.append('model_name', model_name);
                        form_data.append('entity', entity);

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
                                    let ids = $('input#sort').val().split(',');
                                    ids = jQuery.grep(ids, function (value) {
                                        return value != file_id;
                                    });
                                    let str = ids.join(',')
                                    $('input#sort').val(str)
                                }
                            },
                            error: function (e) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $(box).find('.spinner-border').remove();
                            }
                        });

                    }
                });

                {{-- Поворот --}}
                $(document).on('click', '.rotate-photo-a', function (e) {

                    e.preventDefault();

                    let box = $(this).parent();
                    let file_id = box.data('id');

                    if (file_id) {

                        let form_data = new FormData();
                        form_data.append('_token', '{{ csrf_token() }}');
                        form_data.append('file_id', file_id);
                        form_data.append('model_name', model_name);
                        form_data.append('entity', entity);

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
                                    if (data.files.styles.th) {
                                        let src = data.files.styles.th + '?' + $.now();
                                        $(img).attr("src", src);
                                    }
                                    //sendNoty(data.msg);
                                }
                            },
                            error: function (xhr, status, error) {
                                if (xhr.statusText) {
                                    sendNoty(xhr.statusText, 'error', xhr.status);
                                }
                            },
                            complete: function () {
                                $(box).find('.spinner-border').remove();
                            }
                        });
                    }

                });

            });
        });
    </script>
@endpush



<style>
    .files-boxes .nav-item {
        padding: 3px 15px;
        text-decoration: none;
    }

    .files-boxes {
        vertical-align: top;
    }

    .files-boxes .file-box {
        position: relative;
    }

    .files-boxes.simple .file-box,
    .files-boxes .attach-box {
        width: 144px;
        height: 144px;
        float: left;
    }

    .files-boxes.simple .file-box .file-meta {
        display: none;
    }

    .files-boxes .attach-box {
        cursor: pointer;
    }

    .files-boxes .file-box .spinner-border {
        width: 28%;
        height: 28%;
        text-align: center;
        position: absolute;
        color: #fff;
        top: 47px;
        left: 44px;
    }

    .files-boxes .file-box .image-box {
        position: relative;
        width: 142px;
        height: 142px;
    }

    .files-boxes .file-box .file-meta {
        width: 80%;
        display: inline-block;
    }

    .files-boxes .box-empty {
        height: 140px;
        background-color: #f8f9fa;
    }

    .files-boxes .file-box .op-a {
        display: none;
        position: absolute;
        top: -10px;
        border-radius: 25px;
        width: 25px;
        height: 25px;
        background-color: #db5c4c;
        font-size: 16px;
        padding: 5px 3px 2px 6px;
        color: #fff;
        line-height: 100%;
        z-index: 999;
    }

    .files-boxes .file-box:hover .op-a {
        display: block;
    }

    .files-boxes .file-box .delete-photo-a {
        right: -10px;
        font-size: 16px;
        padding: 4px 3px 2px 7px;
    }

    .files-boxes .file-box .rotate-photo-a {
        left: -10px;
        background-color: #256799;
    }

    .loading-box {
        padding: 15px;
    }
</style>
