

<div class="image-field">
    <div class="image-field-box">
        <span id="user_image" title="@lang('Replace image')">
            <img src="{{ $model->attachments[$entity]->isNotEmpty() ? $model->attachments[$entity]->last()->getStyle('image') : '/storage/user-default.png' }}" alt="">
        </span>
    </div>
</div>

<div class="modal" id="modal_user_image">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head has-text-centered has-text-white-bis">
            <p class="modal-card-title">@lang('Change profile photo')</p>
            <button class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body has-text-centered">
            <ul class="list-group list-group-flush text-center p-2">
                <li class="list-group-item list-group-item-light p-05">
                    <a id="load_user_image" class="">@lang('Select image')</a>
                </li>
                <li class="list-group-item list-group-item-light p-05">
                    <a id="remove_user_image" class="">@lang('Remove image')</a>
                </li>
                <li class="list-group-item p-05">
                    <a id="cancel_user_image" class="">@lang('Cancel')</a>
                </li>
            </ul>
            <form enctype="multipart/form-data" method="POST" role="presentation">
                <input id="user_image_input" name="file" type="file" class="image-field" accept="image/jpeg,image/png">
            </form>
        </section>
        <footer class="modal-card-foot p-0">
        </footer>
    </div>
</div>

@push('scripts')

    {{-- jquery ui для Sortable --}}
    <script
            src="https://code.jquery.com/jquery-3.5.0.min.js"
            integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ="
            crossorigin="anonymous"></script>

    <script type="text/javascript">

        // user_image.onclick = function (event) {
        //     document.getElementById("modal_user_image").classList.add("is-active");
        // }
        //
        // cancel_user_image.onclick = function (event) {
        //     document.getElementById("modal_user_image").classList.remove("is-active");
        // }

        $(function () {

            let model_name = "{!! Illuminate\Support\Str::snake(get_class($model)) !!}";
            let model_id = '{{ $model->id }}';
            let has_image = '{{ $model->attachments[$entity]->first() ? true : false }}';
            let image_id = '{{ $model->attachments[$entity]->first()->id ?? null }}';
            let default_image = "/storage/user-default.png";

            $('#load_user_image').on('click', function (e) {
                $("#user_image_input").trigger("click");
            });

            $('#user_image').on('click', function (e) {
                if (has_image) {
                    document.getElementById("modal_user_image").classList.add("is-active");
                } else {
                    $("#user_image_input").trigger("click");
                }
            });

            // $('button#image-load').on('click', function (e) {
            //     $("input#one-image-input").trigger("click");
            // });

            /**
             * Удаление картинки пользователя
             */
            $('#remove_user_image').on('click', function (e) {

                e.preventDefault();

                if (has_image && image_id) {

                    {{--var form_data = new FormData();--}}
                    {{--form_data.append('_method', 'delete');--}}
                    {{--form_data.append('file_id', image_id);--}}
                    {{--form_data.append('model_name', model_name);--}}

                    var form_data = new FormData();
                    form_data.append('_token', '{{ csrf_token() }}');
                    form_data.append('_method', 'delete');
                    form_data.append('parent_model_name', model_name);
                    form_data.append('parent_model_entity', '{{$entity}}');
                    form_data.append('file_id', image_id);

                    $.ajax({
                        url: "{{ route('upload.destroy') }}",
                        type: "POST",
                        data: form_data,
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function () {
                            document.getElementById("modal_user_image").classList.remove("is-active");
                        },
                        success: function (data) {
                            if (data.status == 'success') {
                                $('#user_image img').attr('src', default_image);
                                has_image = false;
                                image_id = 0;
                            }
                            sendNoty(data.msg, data.status);
                        },
                        error: function (e) {
                            sendNoty(e.statusText, 'error', e.status);
                        }
                    });

                    {{--$('div.image-field-box').removeClass('spinner-grow text-light');--}}
                    {{--$('div.image-field-box').removeAttr('role');--}}

                }
            });

            /**
             * Смена картинки пользователя
             */
            $('#user_image_input').on('change', function (e) {

                e.preventDefault();

                var form_data = new FormData();
                form_data.append('_token', '{{ csrf_token() }}');
                form_data.append('parent_model_name', model_name);
                form_data.append('parent_model_entity', '{{$entity}}');
                form_data.append('parent_model_id', model_id);
                form_data.append('current_file_id', image_id);
                form_data.append('file', this.files[0]);

                $.ajax({
                    url: "{{ route('upload') }}",
                    type: "POST",
                    data: form_data,
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
                        // $('div#one-image-modal').modal('hide');
                        // $('div.image-field-box').addClass('spinner-grow text-light');
                        // $('div.image-field-box').attr('role', 'status');

                        document.getElementById("modal_user_image").classList.remove("is-active");
                    },
                    success: function (data) {
                        if (data.status == 'error') {
                            sendNoty(data.msg, data.status);
                        } else {
                            $('#user_image img').attr('src', data.files.styles.image);
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
                    // $('div.image-field-box').removeClass('spinner-grow text-light');
                    // $('div.image-field-box').removeAttr('role');
                }, 1000);
            });

            // $('#image-item').on('change', function (e) {
            //     alert('Элемент foo был изменен.');
            // });

        });

    </script>
@endpush

@push('styles')
    <style type="text/css" lang="css">
        #user_image_input {
            opacity: 0;
            position: absolute;
        }
    </style>
@endpush







