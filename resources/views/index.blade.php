@extends('system.layouts.app')

@section('page_title', __('Uploads') .' | '.__('System'))

@section('breadcrumbs', Breadcrumbs::render('system.home'))

@section('top')
    <nav class="level my-4">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">@lang('Uploads')</h1>
            </div>

            @if($models->count())
                <div class="level-item">
                    <p class="subtitle is-size-6 has-text-grey">
                        {{ $models->count() }} @lang('uploads')
                    </p>
                </div>
            @endif
        </div>
    </nav>
@endsection


@section('content')

    <div class="filters-box bg-light p-3 mb-5">

        <form class="form-inline mb-1" action="{{ route(Route::currentRouteName()) }}" method="get">

            @csrf

            @method('get')

            <div class="field is-grouped">

                <div class="control" style="width: 75px">
                    <input value="{{ request()->get('id') ?? '' }}" name="id" placeholder="№" type="text" class="input" id="inputNo">
                </div>

                <div class="control" style="width: 350px">
                    <input value="{{ request()->get('title')??'' }}" name="title" placeholder="@lang('Title contains')" type="text" class="input">
                </div>

                <div class="control">
                    <div class="select is-fullwidth">
                        <select id="published" name="published">
                            <option value="">@lang('Status') (@lang('all'))</option>
                            <option value="0" @if (request()->get('published') == 0 && !is_null(request()->get('published'))) selected="" @endif>
                                Не опубликовано
                            </option>
                            <option value="1" @if (request()->get('published') == 1) selected="" @endif>
                                Опубликовано
                            </option>
                        </select>
                    </div>
                </div>

                <div class="control">
                    <button type="submit" class="button is-info">@lang('Find')</button>
                </div>

                <div class="control">
                    <a class="button is-text" href="{{ route(Route::currentRouteName()) }}">@lang('Reset')</a>
                </div>
            </div>

        </form>
    </div>

    <table class="table bg-light table-hover table-sm data_rows">

        <thead>
        <tr class="table-primary">
            <th class="text-center">#</th>
            <th class="text-center"><span>@sortablelink('created_at', 'Дата')</span></th>
            {{--<th class="text-center">Оригинальное</th>--}}
            <th class="text-left"><span>@sortablelink('filename', 'Имя')</span></th>
            {{--<th class="text-center"><span>@sortablelink('disk', 'Диск')</span></th>--}}
            {{--<th class="text-center">Путь</th>--}}
            {{--<th class="text-center">MIME</th>--}}
            <th class="text-center"><span>@sortablelink('uploadable_entity', 'Тип')</span></th>
            <th class="text-center"><span>@sortablelink('styles', 'Стили')</span></th>
            {{--<th class="text-center"><span>@sortablelink('styles', 'Мета')</span></th>--}}
            <th class="text-center"><span>@sortablelink('uploadable_type', 'Модель')</span></th>
            <th class="text-center"><span>@sortablelink('uploadable_id', 'id')</span></th>
            <th class="text-center"><span>@sortablelink('user_id', 'Пользователь')</span></th>
            <th class="text-center"><span>@sortablelink('status', 'Статус')</span></th>
            {{--<th class="text-center"><span>@sortablelink('private', 'Приватный')</span></th>--}}
            {{--<th class="text-center"></th>--}}
        </tr>
        </thead>

        <tbody>

        @forelse ($models as $file)
            <tr class="data_row_{{ $file->getKey() }}">
                <td class="text-center">
                    {{ $file->id }}
                </td>
                <td class=" text-center text-center">
                    {{ $file->created_at }}
                </td>
                <td class="text-left">
                    <a href="{{$file->getOriginalFileUrl()}}" target="_blank">{{ $file->file_name }}</a>
                </td>
                {{--<td class="text-center">--}}
                {{--{{ $file->disk }}--}}
                {{--</td>--}}
                {{--<td>--}}
                {{--{{ $file->path }}--}}
                {{--</td>--}}
                <td class="text-center">
                    {{$file->uploadable_entity}}
                    {{--<a href="{{route('profile.index', $file->user->id )}}" target="_blank">{{ $file->user->name }}</a>--}}
                </td>
                <td class="text-center">
                                <span style="white-space: nowrap">
                                    @if(is_array($file->styles))
                                        {{ implode(', ', array_keys($file->styles)) }}
                                    @else
                                        Нет
                                    @endif
                                </span>
                </td>
                {{--<td class="text-center">--}}
                {{--@if(is_array($file->meta))--}}
                {{--{{ implode(', ', array_keys($file->meta)) }}--}}
                {{--@else--}}
                {{--Нет--}}
                {{--@endif--}}
                {{--</td>--}}
                <td class="text-center">
                    {{ $file->uploadable_type }}
                </td>
                <td class="text-center">
                    {{ $file->uploadable_id }}
                </td>
                {{--<td>--}}
                {{--@if( mb_strlen($file->filemime) > 12 )--}}
                {{--<span data-toggle="tooltip" data-placement="right" title="{{ $file->filemime }}">--}}
                {{--{{ str_limit($file->filemime, 12) }}--}}
                {{--</span>--}}
                {{--@else--}}
                {{--{{ $file->filemime }}--}}
                {{--@endif--}}
                {{--</td>--}}
                <td class="text-center">
                    {{$file->user->name}}
                    {{--<a href="{{route('profile.index', $file->user->id )}}" target="_blank">{{ $file->user->name }}</a>--}}
                </td>
                <td class="text-center">
                    @if($file->status)
                        Опуб.
                    @else
                        Не опуб.
                    @endif
                </td>
                {{--<td class="text-center">--}}
                {{--@if($file->private)--}}
                {{--Да--}}
                {{--@else--}}
                {{--Нет--}}
                {{--@endif--}}
                {{--</td>--}}
                {{--<td class="text-center">--}}
                {{--{{ $file->created_at }}<br/>--}}
                {{--</td>--}}
                {{--<td class="text-center td_actions">--}}
                {{--<a class="btn" href="{{ route('profile.index', $file) }}" target="_blank"><i class="fa fa-eye"></i></a>--}}
                {{--@can('delete-file', $file)--}}
                {{--<button class="deleteFile delete-record" type="submit" data-id="{{ $file->id }}">--}}
                {{--<i class="fa fa-trash-o"></i>--}}
                {{--</button>--}}
                {{--<a title="Удалить" data-id="{{ $file->id }}" data-entity="{{ $file->uploadable_entity }}" data-model="{{ Illuminate\Support\Str::snake($file->uploadable_type) }}" class="btn delete-record"><i class="fa fa-trash-o"></i></a>--}}
                {{--@endcan--}}
                {{--</td>--}}
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">Файлы отсутствуют</td>
            </tr>
        @endforelse

        </tbody>

    </table>

    {{--@if( $models->hasPages() )--}}
    {{--<tfoot>--}}
    {{--<tr>--}}
    {{--<td colspan="8">--}}
    {{--<ul class="pagination pull-right">--}}
    {{--{{ $models->links() }}--}}
    {{--</ul>--}}
    {{--</td>--}}
    {{--</tr>--}}
    {{--</tfoot>--}}
    {{--@endif--}}

    </div>

    </div>

    {{--@push('scripts')--}}

    {{--<script>--}}

    {{--$('.delete-record').on('click', function (event) {--}}

    {{--event.preventDefault();--}}

    {{--var id = $(this).data("id");--}}
    {{--var model = $(this).data("model");--}}
    {{--var entity = $(this).data("entity");--}}

    {{--var n = new Noty({--}}
    {{--text   : 'Do you want to continue?',--}}
    {{--type   : 'error',--}}
    {{--layout : 'center',--}}
    {{--theme  : 'sunset',--}}
    {{--buttons: [--}}

    {{--Noty.button('YES', 'btn btn-danger', function () {--}}

    {{--$.ajax({--}}
    {{--url:  "{{ route('upload.destroy') }}",--}}
    {{--type: 'DELETE',--}}
    {{--cache: false,--}}
    {{--data: {--}}
    {{--"_token": "{{ csrf_token() }}",--}}
    {{--'_method': 'delete',--}}
    {{--'file_id': id,--}}
    {{--"parent_model_entity": entity,--}}
    {{--'parent_model_name': model--}}

    {{--},--}}
    {{--beforeSend : function() {--}}
    {{--$('.data_rows .data_row_' + id).css('opacity', .3);--}}
    {{--},--}}
    {{--success: function (data){--}}
    {{--if(data.status == 'success') {--}}
    {{--$('.data_rows .data_row_' + id).remove();--}}
    {{--sendNoty(data.msg, data.status);--}}
    {{--} else {--}}
    {{--sendNoty(data.msg, data.status);--}}
    {{--$('.data_rows .data_row_' + id).css('opacity', 1);--}}
    {{--}--}}
    {{--},--}}
    {{--error: function (e) {--}}
    {{--$('.data_rows .data_row_' + id).css('opacity', 1);--}}
    {{--sendNoty(e.statusText, 'error', e.status);--}}
    {{--}--}}
    {{--});--}}

    {{--n.close();--}}

    {{--}, {id: 'button1', 'data-status': 'ok'}),--}}

    {{--Noty.button('NO', 'btn btn-error', function () {--}}
    {{--//console.log('button 2 clicked');--}}
    {{--n.close();--}}
    {{--})--}}
    {{--]--}}
    {{--});--}}
    {{--n.show();--}}

    {{--});--}}
    {{--</script>--}}
    {{--@endpush--}}

@endsection
