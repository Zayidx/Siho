<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $filename = 'contact_messages_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','name','email','subject','phone','message','ip','read_at','created_at']);
            $q = ContactMessage::query()->orderBy('id');
            if (request('status') === 'unread') {
                $q->whereNull('read_at');
            } elseif (request('status') === 'read') {
                $q->whereNotNull('read_at');
            }
            if ($s = request('search')) {
                $term = '%'.$s.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                       ->orWhere('email', 'like', $term)
                       ->orWhere('message', 'like', $term);
                });
            }
            if ($start = request('start')) {
                $q->whereDate('created_at', '>=', $start);
            }
            if ($end = request('end')) {
                $q->whereDate('created_at', '<=', $end);
            }

            $safe = static function ($v) {
                if (is_null($v)) return '';
                $s = (string) $v;
                $s = str_replace(["\r","\n"], ' ', $s);
                if ($s !== '' && in_array($s[0], ['=', '+', '-', '@'])) {
                    return "'".$s;
                }
                return $s;
            };

            $q->chunk(500, function ($chunk) use ($handle, $safe) {
                foreach ($chunk as $m) {
                    fputcsv($handle, [
                        $m->id,
                        $safe($m->name),
                        $safe($m->email),
                        $safe($m->subject),
                        $safe($m->phone),
                        $safe(str_replace(["\r","\n"], ' ', $m->message)),
                        $safe($m->ip),
                        optional($m->read_at)->toDateTimeString(),
                        $m->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
