<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Imports\ContentImport;
use Maatwebsite\Excel\Facades\Excel;

class ContentController extends Controller
{
    // 📌 GET all content with filters
    public function index(Request $request)
    {
        $query = Content::query();

        // Apply filters if present in the request
        if ($request->has('title')) $query->where('title', 'like', '%' . $request->title . '%');
        if ($request->has('description')) $query->where('description', 'like', '%' . $request->description . '%');
        if ($request->has('channel')) $query->where('channel', 'like', '%' . $request->channel . '%');
        if ($request->has('season')) $query->where('season', $request->season);
        if ($request->has('episode')) $query->where('episode', $request->episode);
        if ($request->has('type')) $query->where('type', 'like', '%' . $request->type . '%');
        if ($request->has('category')) {$query->whereJsonContains('category', $request->category);}
        if ($request->has('year')) $query->where('year', $request->year);
        if ($request->has('duration')) $query->where('duration', $request->duration);
        if ($request->has('country')) $query->where('country', 'like', '%' . $request->country . '%');

        // Optional pagination
        $contents = $query->paginate(10);

        return response()->json($contents);
    }

    // 📌 Store new content
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'channel' => 'required|string',
            'season' => 'nullable|integer',
            'episode' => 'nullable|integer',
            'type' => 'required|string',
            'category' => 'required|array',
            'category.*' => 'string',
            'year' => 'nullable|integer',
            'duration' => 'nullable|string|date_format:H:i:s',
            'country' => 'nullable|string',
        ]);

        

        $content = Content::create($validated);

        return response()->json($content, 201);
    }

    // 📌 Update existing content
    public function update(Request $request, $id)
    {
        $content = Content::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'channel' => 'sometimes|string',
            'season' => 'nullable|integer',
            'episode' => 'nullable|integer',
            'type' => 'sometimes|string',
            'category' => 'sometimes|array',
            'category.*' => 'string',
            'year' => 'nullable|integer',
            'duration' => 'nullable|string|date_format:H:i:s',
            'country' => 'nullable|string',
        ]);

        $content->update($validated);

        return response()->json($content);
    }

    // 📌 Delete content
    public function destroy($id)
    {
        $content = Content::findOrFail($id);
        $content->delete();

        return response()->json(['message' => 'Content deleted successfully.']);
    }






    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,csv,xls',
    ]);

    try {
        Excel::import(new ContentImport, $request->file('file'));

        return response()->json(['message' => 'Contents imported successfully.']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
