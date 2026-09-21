<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class AttendaceExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $collection = collect(); // Inisialisasi koleksi kosong

        foreach ($this->data as $item) {
            $collection->push([
                'name' => $item['user']['name'] ?? null,
                'userName' => $item['user']['userName'] ?? null,
                'full_address' => $item['full_address'] ?? null,
                'Project Name' => $item['project']['name'] ?? null,
                'Project Number' => $item['project']['projectNo'] ?? null,
                'latitude' => $item['latitude'] ?? null,
                'longitude' => $item['longitude'] ?? null, // Perbaikan typo
                'date' => $item['date'] ?? null,
                'time' => $item['time'] ?? null,
                'type' => $item['type'] ?? null,
                'status' => $item['status'] ?? null,
                'project Id' => $item['projectId'] ?? null,
            ]);
        }

        // Tambahkan header sebagai baris pertama dalam koleksi
        $withHeaders = collect([
            [
                'name',
                'userName',
                'full_address',
                'Project Name',
                'Project Number',
                'latitude',
                'longitude',
                'date',
                'time',
                'type',
                'status',
                'projectId',
            ],
        ])->merge($collection);

        return $withHeaders;
    }
}
