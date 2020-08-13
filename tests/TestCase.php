<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12/28/2019
 * Time: 7:03 AM
 */

namespace Inewtonua\Upload\Tests;

use Inewtonua\Upload\UploadServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            UploadServiceProvider::class
        ];
    }

}