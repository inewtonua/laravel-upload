<?php

namespace Inewtonua\Upload\Console;

//use App\Models\Example\Example;
use Illuminate\Console\Command;
use Inewtonua\Upload\Models\Upload as UploadModel;
//use Spatie\Sitemap\SitemapGenerator;
//use Carbon\Carbon;
//use Spatie\Sitemap\Sitemap;
//use Spatie\Sitemap\Tags\Url;
//
//use App\Models\Ceiling\Ceiling;
//use App\Models\Ceiling\CeilingType;
//use App\Models\Example\ExampleType;
//use app\Models\Page;

class ReGenerateImages extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'images:regenerate {model} {entity?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re Generate Images';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $entities = [];

        $model_type = $this->argument('model');
        $model_type = str_replace("/", "\\", $model_type);

        $entity = $this->argument('entity') ?? null;

        //$style = $this->argument('style') ?? null;
        //$this->info($model_type);
        //$this->info($entity);
        //$this->info($style);

        $query = UploadModel::where('uploadable_type', $model_type)->active();

        if($entity){

            $query->where('uploadable_entity', $entity);

        }

        $models = $query->get();

        if($models->isNotEmpty()) {

            //$config = $model_type::uploadableConfig();
            //$this->info($models->count());

            foreach ($models as $key => $model) {

              $model->makeStyles();

            }

        } else {

            $this->error('Нет моделей удовлетворяющих запросу.');

        }

    }
}
