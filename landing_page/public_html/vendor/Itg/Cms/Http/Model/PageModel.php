<?php

namespace Itg\Cms\Http\Model;


use Exception;
use Whiskey\Bourbon\App\Facade\Session;
use Whiskey\Bourbon\App\Http\MainModel;
use Itg\Buildr\Facade\Me;
use Itg\Buildr\Facade\User;
use Whiskey\Bourbon\App\Facade\Db;
use Whiskey\Bourbon\App\Facade\Utils;

/**
 * PageModel class
 * @package Whiskey\Bourbon\App\Http\Model
 */
class PageModel extends MainModel
{

    public function exportAttendees()
    {
        $data = $this->getCSV('attendance');
        $csv = Utils::arrayToCsv($data);
        $filename = 'dunelm-attendance-' . date('Y-m-d') . '.csv';

        header('Content-Type: application/csv');
        header("Content-Disposition: attachment; filename=$filename");
        echo $csv;
        exit();
    }

    public function exportNonattendees()
    {
        $data = $this->getCSV('nonattendance');
        $csv = Utils::arrayToCsv($data);
        $filename = 'dunelm-non-attendance-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: application/csv');
        header("Content-Disposition: attachment; filename=$filename");
        echo $csv;
        exit();
    }


    public function getCSV($table)
    {

        $result = Db::build()->table($table)
                            ->select();
        
        return $result;

    }

}