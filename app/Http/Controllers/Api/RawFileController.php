<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



use App\Models\Rawfile;


class RawFileController extends Controller
{
    // 📌 GET all raw files with filters
    public function index(Request $request)
    {
        $query = Rawfile::query();

        // Apply filters if provided
        if ($request->has('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->has('path')) {
            $query->where('path', 'LIKE', '%' . $request->path . '%');
        }

        if ($request->has('channel')) {
            $query->where('channel', 'LIKE', '%' . $request->channel . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Get results with pagination
        $results = $query->paginate(10);

        return response()->json([
            'message' => 'Raw files fetched successfully',
            'data' => $results
        ]);
    }

    ///geting and searching method
    public function search(Request $request)
{
    // If there's an ID in the request, show just that record
    if ($request->has('id')) {
        $rawFile = Rawfile::find($request->id);
        if (!$rawFile) {
            return response()->json(['message' => 'Raw data not found'], 404);
        }

        return response()->json([
            'message' => 'Raw data retrieved successfully',
            'data' => $rawFile
        ]);
    }

    // Start building the query
    $query = Rawfile::query();

    // Apply filters if provided
    if ($request->has('name')) {
        $query->where('name', 'LIKE', '%' . $request->name . '%');
    }

    if ($request->has('path')) {
        $query->where('path', 'LIKE', '%' . $request->path . '%');
    }

    if ($request->has('channel')) {
        $query->where('channel', 'LIKE', '%' . $request->channel . '%');
    }

    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    // Get results
    $results = $query->get();

    return response()->json([
        'message' => 'Raw files fetched successfully',
        'data' => $results
    ]);
}

    
    
    
    
    
    
    
    
    ///storing method
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'path' => 'required|string',
            'description' => 'nullable|string', // ✅ Add this line
            'channel' => 'required|string',
            'status' => 'in:unedited,edited',
        ]);

        $rawData = Rawfile::create($validated);

        return response()->json([
            'message' => 'Raw data path stored successfully.',
            'data' => $rawData,
        ]);
    }

    /// update method
    public function update(Request $request, $id)
{
    $rawData = Rawfile::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string',
        'path' => 'required|string',
        'description' => 'nullable|string', // ✅ Add this line
        'channel' => 'required|string',
        'status' => 'required|in:unedited,edited',
    ]);


    $rawData->update($validated);

    return response()->json([
        'message' => 'Raw data updated successfully',
        'data' => $rawData
    ]);
}
    /// deletion method
    public function destroy($id)
    {
        Rawfile::findOrFail($id)->delete();

        return response()->json(['message' => 'Path deleted successfully.']);
    }
}
