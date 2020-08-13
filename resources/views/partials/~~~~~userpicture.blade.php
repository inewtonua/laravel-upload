@if($user->id !== auth()->id())
    <div class="userPicture">
        <div class="userPictureBox">
            <img class="img-fluid rounded-circle" src="{{ $user->profile_image }}" alt="@lang('upload::upload.up_alt', ['name' => $user->name])">
        </div>
    </div>
@else
    <div class="userPicture">
        <div class="userPictureBox">
            <button id="up" type="button" class="btn btn-link p-0" title="@lang('upload::upload.up_change')">
                <img id="upImg" class="img-fluid rounded-circle" src="{{ $user->profile_image }}" alt="@lang('upload::upload.up_alt', ['name' => $user->name])">
            </button>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="upModal" tabindex="-1" role="dialog" aria-labelledby="upModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h3 class="col-12 modal-title text-center" id="upModalTitle">
                        @lang('upload::upload.up_change')
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </h3>
                </div>
                <div class="modal-body pt-0">
                    <ul class="list-group list-group-flush text-center p-2">
                        <li class="list-group-item list-group-item-light">
                            <button id="upLoad" type="button" class="btn btn-link"><strong>@lang('upload::upload.link_upload')</strong></button>
                        </li>
                        <li class="list-group-item list-group-item-light p-2">
                            <button id="upDelete" type="button" class="btn btn-link"><strong>@lang('upload::upload.link_delete')</strong></button>
                        </li>
                        <li class="list-group-item border-bottom-0 p-2">
                            <span class="modal-link-close btn btn-link" data-dismiss="modal" aria-label="Close">@lang('Cancel')</span>
                        </li>
                    </ul>
                    <form enctype="multipart/form-data" method="POST" role="presentation">
                        <input id="upField" name="file" type="file" class="upField" accept="image/jpeg,image/png">
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')

        <script type="text/javascript">

            $(function () {

                let model_name = "{!! Illuminate\Support\Str::snake(get_class($user)) !!}";
                let model_id = '{{$user->id}}';
                let has_up = '{{$user->image ? true : false}}';
                let up_id = '{{$user->image->id ?? null}}';
                let up_default = "{{\Storage::disk('public')->url($user->uploadable()['default_image'])}}";

                $('button#up').on('click', function (e) {

                    if(has_up){
                        $('div#upModal').modal('show');
                    } else {
                        $("input#upField").trigger("click");
                    }

                });

                $('button#upLoad').on('click', function (e) {
                    $("input#upField").trigger("click");
                });

                /**
                 * Удаление картинки пользователя
                 */
                $('button#upDelete').on('click', function (e) {

                    e.preventDefault();

                    if(has_up && up_id){

                        var form_data = new FormData();
                        form_data.append('_method', 'delete');
                        form_data.append('file_id', up_id);
                        form_data.append('model_name', model_name);

                        $.ajax({
                            url: "{{ route('upload.destroy') }}",
                            type: "POST",
                            data:  form_data,
                            contentType: false,
                            cache: false,
                            processData:false,
                            beforeSend : function() {
                                $('div#upModal').modal('hide');
                                $('div.userPictureBox').addClass('spinner-grow text-light');
                                $('div.userPictureBox').attr('role', 'status');
                            },
                            success: function(data) {
                                if(data.status == 'success') {
                                    $('img#upImg, img#upNav').attr('src', up_default);
                                    has_up = false;
                                }
                                sendNoty(data.msg, data.status);
                            },
                            error: function(e) {
                                sendNoty(e.statusText, 'error', e.status);
                            }
                        });

                        $('div.userPictureBox').removeClass('spinner-grow text-light');
                        $('div.userPictureBox').removeAttr('role');

                    }
                });


                /**
                 * Смена картинки пользователя
                 */

                $('input#upField').on('change', function (e) {

                    e.preventDefault();

                    var form_data = new FormData();
                    form_data.append('_token', '{{ csrf_token() }}');
                    form_data.append('model_name', model_name);
                    form_data.append('model_id', model_id);
                    form_data.append('file', this.files[0]);

                    // var file_data = this.files;
                    // $(file_data).each( function( index, file ) {
                    //     form_data.append('files[]', file);
                    // });

                    $.ajax({
                        url: "{{ route('upload') }}",
                        type: "POST",
                        data:  form_data,
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend : function() {
                            $('div#upModal').modal('hide');
                            $('div.userPictureBox').addClass('spinner-grow text-light');
                            $('div.userPictureBox').attr('role', 'status');
                        },
                        success: function(data) {

                            if(data.status == 'error') {
                                sendNoty(data.msg, data.status);
                            } else {
                               // console.log(data.files.styles.big);

                                $('img#upImg').attr('src', data.files.styles.p);
                                $('img#upNav').attr('src', data.files.styles.n);

                                has_up = true;
                                up_id = data.files.id;
                                sendNoty(data.msg, data.status);
                            }
                        },
                        error: function(e) {
                            sendNoty(e.statusText, 'error', e.status);
                        }
                    });
                    setTimeout(function() {
                        $('div.userPictureBox').removeClass('spinner-grow text-light');
                        $('div.userPictureBox').removeAttr('role');
                    }, 1000);
                });
            });
        </script>
    @endpush
@endif
