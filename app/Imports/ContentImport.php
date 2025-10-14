<?php

namespace App\Imports;

use App\Models\Content;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ContentImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Content([
            'title' => $row['title'],
            'description' => $row['description'],
            'channel' => $row['channel'],
            'season' => $row['season'],
            'episode' => $row['episode'],
            'type' => $row['type'],
            'category' => array_map('trim', explode(',', $row['category'])), // e.g., '["Drama", "Action"]'
            'year' => $row['year'],
            'duration' => $row['duration'], // must be in H:i:s format
            'country' => $row['country'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.title' => 'required|string',
            '*.description' => 'required|string',
            '*.channel' => 'required|string',
            '*.type' => 'required|string',
            '*.category' => 'required|string', // Must be valid JSON string
            '*.duration' => 'nullable|date_format:H:i:s',
        ];
    }
}
