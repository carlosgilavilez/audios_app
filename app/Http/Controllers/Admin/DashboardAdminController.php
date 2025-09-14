<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use App\Models\Autor;
use App\Models\Serie;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $autoresCount = Autor::count();
        $seriesCount = Serie::count();
        $audiosCount = Audio::count();
        $activityLogs = ActivityLog::with(['user', 'subject'])->orderBy('created_at', 'desc')->take(10)->get();

        return view('admin.dashboard', compact('autoresCount', 'seriesCount', 'audiosCount', 'activityLogs'));
    }

    public function logs()
    {
        $activityLogs = ActivityLog::with(['user', 'subject'])->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.logs', compact('activityLogs'));
    }
}