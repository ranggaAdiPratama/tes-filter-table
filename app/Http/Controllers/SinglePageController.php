<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SinglePageController extends Controller
{
    public function index(): View
    {
        $machines   = DB::table('machines')->orderBy('name')->get();
        $operators  = DB::table('operators')->orderBy('name')->get();
        $sites      = DB::table('sites')->orderBy('code')->get();

        $query = DB::table('activities')
            ->join('machines', 'machines.id', '=', 'activities.machine_id')
            ->join('operators', 'operators.id', '=', 'activities.operator_id')
            ->join('sites', 'sites.id', '=', 'activities.site_id')
            ->select([
                'activities.*', 'machines.name as machine', 'operators.name as operator',
                'sites.code as site'
            ])
            ->get();

        $activities = [];

        foreach ($query as $row) {
            if (!in_array($row->activity, $activities)) {
                $activities[] = $row->activity;
            }
        }

        if (count($activities) > 0) {
            sort($activities);
        }

        $data = [
            'activities'    => $activities,
            'machines'      => $machines,
            'operators'     => $operators,
            'query'         => $query,
            'sites'         => $sites,
        ];

        return view('index', $data);
    }
}
