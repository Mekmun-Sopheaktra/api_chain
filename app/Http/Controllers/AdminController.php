<?php

namespace App\Http\Controllers;

use App\Constants\ConstUserRole;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //dashboard method
    public function dashboard()
    {
        //total Patient count
        $users = User::where('role', ConstUserRole::PATIENT)->count();

        //total hospital count
        $hospitals = User::where('role', ConstUserRole::HOSPITAL)->count();

        //total admin count
        $admins = User::where('role', ConstUserRole::ADMIN)->count();

        //create Patient graph data for last 7 days
        $patientGraphData = User::where('role', ConstUserRole::PATIENT)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get();

        //create hospital graph data for last 7 days
        $hospitalGraphData = User::where('role', ConstUserRole::HOSPITAL)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get();

        $data = [
            'total_patients' => $users,
            'total_hospitals' => $hospitals,
            'total_admins' => $admins,
            'patient_graph_data' => $patientGraphData,
            'hospital_graph_data' => $hospitalGraphData,
        ];

        return $this->success($data, 'Admin Dashboard', 'Welcome to the Admin Dashboard');
    }
}
