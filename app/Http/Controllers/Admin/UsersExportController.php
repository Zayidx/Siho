<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UsersExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $filename = 'users_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','username','email','role','full_name','phone','created_at']);
            $q = User::with('role')->orderBy('id');
            if ($s = request('search')) {
                $term = '%'.$s.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('username','like',$term)
                       ->orWhere('email','like',$term)
                       ->orWhere('full_name','like',$term);
                });
            }
            $q->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $u) {
                    fputcsv($handle, [
                        $u->id,
                        $u->username,
                        $u->email,
                        optional($u->role)->name,
                        $u->full_name,
                        $u->phone,
                        $u->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

