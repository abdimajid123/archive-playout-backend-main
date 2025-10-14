<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentCooldown;
use Illuminate\Http\Request;

class ContentCooldownController extends Controller
{
    // 🔍 GET all cooldowns
    public function index()
    {
        return ContentCooldown::all();
    }

    // 🔍 GET cooldown for specific content
    public function show(Request $request, $id = null)
    {
        if ($id) {
            return ContentCooldown::findOrFail($id);
        }
    
        $request->validate([
            'content_id' => 'required|integer',
            'channel' => 'required|string',
        ]);
    
        $cooldown = ContentCooldown::where('content_id', $request->content_id)
            ->where('channel', $request->channel)
            ->first();
    
        if (!$cooldown) {
            return response()->json(['message' => 'Not found'], 404);
        }
    
        return response()->json($cooldown);
    }


    

    // ✏️ Update cooldown days manually
    public function update(Request $request, $id)
    {
        $cooldown = ContentCooldown::findOrFail($id);

        $validated = $request->validate([
            'cooldown_days' => 'required|integer|min:0',
        ]);

        $cooldown->update($validated);

        return response()->json(['message' => 'Cooldown updated', 'data' => $cooldown]);
    }

    // ❌ Delete cooldown (optional)
    public function destroy($id)
    {
        ContentCooldown::findOrFail($id)->delete();
        return response()->json(['message' => 'Cooldown removed']);
    }
}
