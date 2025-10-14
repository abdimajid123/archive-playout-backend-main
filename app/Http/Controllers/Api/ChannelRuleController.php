<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelRule;
use Illuminate\Http\Request;

class ChannelRuleController extends Controller
{
    public function index()
    {
        return ChannelRule::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|string|unique:channel_rules,channel',
            'min_content_per_day' => 'required|integer|min:1',
            'max_content_per_day' => 'required|integer|min:1',
            'slot_duration_minutes' => 'required|integer|min:1',
            'preferred_content_types' => 'nullable|array',
            'scheduling_algorithm' => 'nullable|string',
            'cooldown_days' => 'required|integer|min:0', // ✅ changed here
        ]);

        $rule = ChannelRule::create($validated);
        return response()->json($rule, 201);
    }

    public function show($id)
    {
        return ChannelRule::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $rule = ChannelRule::findOrFail($id);

        $validated = $request->validate([
            'min_content_per_day' => 'integer|min:1',
            'max_content_per_day' => 'integer|min:1',
            'slot_duration_minutes' => 'integer|min:1',
            'preferred_content_types' => 'nullable|array',
            'scheduling_algorithm' => 'nullable|string',
            'cooldown_days' => 'integer|min:0', // ✅ changed here
        ]);

        $rule->update($validated);
        return response()->json($rule);
    }

    public function destroy($id)
    {
        ChannelRule::findOrFail($id)->delete();
        return response()->json(['message' => 'Rule deleted.']);
    }
}
