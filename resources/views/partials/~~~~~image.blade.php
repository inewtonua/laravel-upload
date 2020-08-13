@php
    $entity = $uploadable_entity = $entity ?? 'image_1';
@endphp

<div class="image-field">
    <div class="image-field-box">
        <button id="one-image" type="button" class="btn btn-link" title="@lang('Replace image')">
            <img id="image-item" class="img-fluid" src="{{ isset($model->image) ? $model->image->getStyle('p') : '/storage/user-default.png' }}" alt="">
        </button>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="one-image-modal" tabindex="-1" role="dialog" aria-labelledby="imageModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h3 class="col-12 modal-title text-center" id="upModalTitle">
                    @lang('Replace image')
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </h3>
            </div>
            <div class="modal-body pt-0">
                <ul class="list-group list-group-flush text-center p-2">
                    <li class="list-group-item list-group-item-light">
                        <button id="image-load" type="button" class="btn btn-link">
                            <strong>@lang('Select image')</strong></button>
                    </li>
                    <li class="list-group-item list-group-item-light p-2">
                        <button id="image-remove" type="button" class="btn btn-link">
                            <strong>@lang('Remove image')</strong></button>
                    </li>
                    <li class="list-group-item border-bottom-0 p-2">
                        <span class="modal-link-close btn btn-link" data-dismiss="modal"
                              aria-label="Close">@lang('Cancel')</span>
                    </li>
                </ul>
                <form enctype="multipart/form-data" method="POST" role="presentation">
                    <input id="one-image-input" name="file" type="file" class="image-field" accept="image/jpeg,image/png">
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')

    <script type="text/javascript">

        $(function () {

            let model_name = "{!! Illuminate\Support\Str::snake(get_class($model)) !!}";
            let model_id = '{{ $model->id }}';
            let has_image = '{{ isset($model->image->filepath) ? true : false }}';
            let image_id = '{{ $model->image->id ?? null }}';
            let default_image = "{{\Storage::disk('public')->url($model->uploadable()['default_image'])}}";
            console.log(default_image);

            $('button#one-image').on('click', function (e) {
                if (has_image) {
                    $('div#one-image-modal').modal('show');
                } else {
                    $("input#one-image-input").trigger("click");
                }

            });

            $('button#image-load').on('click', function (e) {
                $("input#one-image-input").trigger("click");
            });

            /**
             * Удаление картинки пользователя
             */
            $('button#image-remove').on('click', function (e) {

                e.preventDefault();

                if (has_image && image_id) {

                    var form_data = new FormData();
                    form_data.append('_method', 'delete');
                    form_data.append('file_id', image_id);
                    form_data.append('model_name', model_name);

                    $.ajax({
                        url: "{{ route('upload.destroy') }}",
                        type: "POST",
                        data: form_data,
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            $('div#one-image-modal').modal('hide');
                            $('div.image-field-box').addClass('spinner-grow text-light');
                            $('div.image-field-box').attr('role', 'status');
                        },
                        success: function (data) {
                            if (data.status == 'success') {
                                $('img#image-item').attr('src', default_image);
                                has_image = false;
                                image_id = 0;
                            }
                            sendNoty(data.msg, data.status);
                        },
                        error: function (e) {
                            sendNoty(e.statusText, 'error', e.status);
                        }
                    });

                    $('div.image-field-box').removeClass('spinner-grow text-light');
                    $('div.image-field-box').removeAttr('role');

                }
            });


            /**
             * Смена картинки пользователя
             */
            $('input#one-image-input').on('change', function (e) {

                e.preventDefault();

                var form_data = new FormData();
                form_data.append('_token', '{{ csrf_token() }}');
                form_data.append('model_name', model_name);
                form_data.append('model_id', model_id);
                form_data.append('file', this.files[0]);

                $.ajax({
                    url: "{{ route('upload') }}",
                    type: "POST",
                    data: form_data,
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
                        $('div#one-image-modal').modal('hide');
                        $('div.image-field-box').addClass('spinner-grow text-light');
                        $('div.image-field-box').attr('role', 'status');
                    },
                    success: function (data) {

                        if (data.status == 'error') {
                            sendNoty(data.msg, data.status);
                        } else {

                            $('img#image-item').attr('src', data.files.styles.p);
                            //$('img#upNav').attr('src', data.files.styles.n);

                            has_image = true;
                            image_id = data.files.id;

                            sendNoty(data.msg, data.status);
                        }
                    },
                    error: function (e) {
                        sendNoty(e.statusText, 'error', e.status);
                    }
                });
                setTimeout(function () {
                    $('div.image-field-box').removeClass('spinner-grow text-light');
                    $('div.image-field-box').removeAttr('role');
                }, 1000);
            });

            $('#image-item').on('change', function (e) {
                alert('Элемент foo был изменен.');
            });

        });


        var app = new Vue({
            el: '#app',
            data: {
                message: '<strong>Hello Vue!</strong>'
            },
            methods : {
                changeuserpic : function() {
                    alert(5555);
                }
            },
        })

    </script>
@endpush


<div id="app">
    <span v-html="message" v-on:click = "changeuserpic">@{{ message }}</span>
</div>
