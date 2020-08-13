<?php

namespace Inewtonua\Upload\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Inewtonua\Upload\Models\Upload as UploadModel;
use Inewtonua\Upload\Contracts\Upload as UploadContract;

class UploadController extends Controller
{
    protected $upload;

    public function __construct(UploadContract $upload)
    {
        $this->upload = $upload;
    }

    public function index(Request $request)
    {

        $query = $this->upload->uploadModel->with('user')->sortable(['id' => 'desc']);

        if ($request->filled('filename')) {
            $query->where('file_name', 'like', '%' . $request->filename . '%');
        }

        if (!is_null($request->get('status'))) {
            $query->where('status', $request->get('status'));
        }

        if (!is_null($request->get('uploadable_type'))) {
            $query->where('uploadable_type', $request->get('uploadable_type'));
        }

        $models = $query->paginate(20);

        return view('uploads::index', compact('models'));

    }

    public function store(Request $request)
    {

        /* Прверить все ли параметры */

        if (!$request->filled('parent_model_name')) {
            return response()->json([
                'status' => 'error',
                'msg' => Lang::get('No parent model name specified.')
            ]);
        }

        if (!$request->filled('parent_model_entity')) {
            return response()->json([
                'status' => 'error',
                'msg' => Lang::get('No parent model entity specified.')
            ]);
        }

        if (!$request->has('parent_model_id')) {
            return response()->json([
                'status' => 'error',
                'msg' => Lang::get('No parent model id specified.')
            ]);
        }

        if (!$request->hasFile('file')) {
            return response()->json([
                'status' => 'error',
                'msg' => Lang::get('File not attached.')
            ]);
        }

        $parentModelName = $this->normalizeModelName($request->parent_model_name);

        if($parentModelName == 'App\Models\Service\Service\Type')
            $parentModelName = 'App\Models\Service\ServiceType';

        if (!method_exists($parentModelName, 'uploadableConfig')) {

            return response()->json([
                'status' => 'error',
                'msg' => Lang::get('Wrong model Config.')
            ]);

        }

        /* Конфиги загрузки из модели */

        $config = $parentModelName::config();

//        print_r($config);
//        die();

        if (is_null($config) || !is_array($config) || !isset($config[$request->parent_model_entity])) {

            return response()->json([
                'status' => 'error',
                'msg' => Lang::get('Invalid uploadable model Config.')
            ]);

        }

        /* Валидация файла */

        $rules = $config[$request->get('parent_model_entity')]['validation']['rules'] ?? [];

        $messages = $config[$request->get('parent_model_entity')]['validation']['messages'] ?? [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'msg' => $validator->errors()->first()
            ]);

        }


        /** --------------------------------------------------------------------- **/

        $upload = $this->upload->setParams(
            Auth::user()->id,
            $parentModelName,
            $request->parent_model_entity,
            $request->parent_model_id,
            $config[$request->parent_model_entity]
        )->saveFile($request->file('file'));

        if ($upload) {

            if(isset($config[$request->parent_model_entity]['replace']) && $config[$request->parent_model_entity]['replace'] && $request->current_file_id) {
                if($model = UploadModel::find($request->current_file_id)){
                    $model->markDeleted();
                }
            }

            return response()->json([
                'status' => 'success',
                'msg' => Lang::get('upload::upload.msg_up_updated'),
                'files' => [
                    'id' => $upload->id,
                    'styles' => $upload->getStyles(),
                    'name' => $upload->original_name,
                ]
            ]);

        } else {

            return [
                'status' => 'error',
                'msg' => Lang::get('upload::upload.msg_unable_to_save_picture')
            ];

        }

    }

    public function destroy(Request $request)
    {

        // Дописать условия и проверку прав

        $model = UploadModel::where([
            'id' => $request->file_id,
            'uploadable_entity' => $request->parent_model_entity,
            'uploadable_type' => $this->normalizeModelName($request->parent_model_name)
        ])->first();

        if (is_null($model)) {
            return [
                'status' => 'error',
                'msg' => Lang::get('upload::upload.msg_file_not_fount')
            ];
        }

        if (!Gate::forUser(Auth::user())->allows('delete-file', $model)) {
            return [
                'status' => 'error',
                'msg' => Lang::get('upload::upload.msg_dont_have_permission')
            ];
        }

        if ($model->markDeleted()) {
            return [
                'status' => 'success',
                'msg' => Lang::get('upload::upload.msg_file_deleted'),
            ];
        } else {
            return [
                'status' => 'error',
                'msg' => Lang::get('upload::upload.msg_unable_to_delete')
            ];
        }

    }

    public function rotate(Request $request)
    {

        $model = UploadModel::where([
            'id' => $request->file_id,
            'uploadable_entity' => $request->parent_model_entity,
            'uploadable_type' => $this->normalizeModelName($request->parent_model_name)
        ])->first();

        if (is_null($model)) {
            return [
                'status' => 'error',
                'msg' => Lang::get('upload::upload.msg_file_not_fount'),
            ];
        }

        if ($this->upload->rotate($model)) {
            return [
                'status' => 'success',
                'msg' => Lang::get('upload::upload.msg_image_rotated'),
                'files' => [
                    'id' => $model->id,
                    'styles' => $model->getStyles()
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'msg' => Lang::get('upload::upload.msg_unable_to_delete')
            ];
        }

    }

    /* Нормаизация класса модели */
    private function normalizeModelName($modelName)
    {

        $modelNameArr = explode("_", $modelName);
        $parentModelName = '';

        foreach ($modelNameArr as $item) {
            $parentModelName .= '\\' . Str::studly($item);
        }

        return substr($parentModelName, 1);
    }

//    public function download($id, $filename)
//    {
//
//        $file = UploadModel::find($id);
//
//        if($file->file_name == $filename) {
//
//            $modelClass = Config::get("upload.models.{$file->model}.class", null);
//
//            /**
//             * Для сообщений
//             */
//            if ($modelClass === '\App\Models\Talk\Messages\Message') {
//
//                $message = $modelClass::with('conversation')->find($file->model_id);
//
//                if ($message->conversation->user_one == \Auth::user()->id || $message->conversation->user_two == \Auth::user()->id) {
//
//                    $headers = array(
//                        'Content-Type' => $file->file_mime,
//                    );
//
//                    return response()->download(storage_path('app' . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . $file->path . DIRECTORY_SEPARATOR . $file->file_name),
//                        $file->original_name, $headers);
//                }
//
//            }
//
//            return abort(403);
//
//        }
//
//    }

//    public function checkConfig($config) :bool
//    {
//        return (isset($config['class']) && isset($config['validation']));
//    }

}
