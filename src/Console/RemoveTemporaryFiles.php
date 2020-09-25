<?php

namespace Inewtonua\Upload\Console;

use Illuminate\Console\Command;
//use Inewtonua\Upload\Upload;
//use Inewtonua\Upload\Facades\Upload as UploadFacade;
use Inewtonua\Upload\Models\Upload as UploadModel;
use Illuminate\Support\Carbon;

class RemoveTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removing temporary files.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ban service.
     *
     * @var \Inewtonua\Upload\Upload
     */
    protected $service;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        // TODO: Добавить в условие Or deleted = 1

        $models = UploadModel::where('status', 0)
            ->where('created_at', '<=', Carbon::now()->addMinutes(-1)->toDateTimeString())
            ->take(100)
            ->get();

        $x = $y = 0;

        foreach($models as $model) {

            if($model->delete()) {
                $x++;
            } else {
                $y++;
                \Log::error('Не удалось удалить модель и файлы: ', [ $model ]);
            }
        }

        if($x or $y)
            \Log::info("Удалено uploads: {$x}, не удалено uploads: {$y}.");

    }

}
