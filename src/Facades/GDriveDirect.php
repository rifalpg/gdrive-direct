<?php
/**
 * Created by NYXLab.
 * User: Rifal Pramadita G
 * Date: 05/12/2019
 * Time: 14.17
 */

namespace Rifalpg\GDriveDirect\Facades;

use Illuminate\Support\Facades\Facade;

class GDriveDirect extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'GDriveDirect';
    }
}