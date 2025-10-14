<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScheduleSlot;

class ScheduleSlotController extends Controller
{
    public function index()
    {
        return ScheduleSlot::with('schedules')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
        ]);

        // Prevent duplicate slot on same date & channel
        $exists = ScheduleSlot::where('channel', $validated['channel'])
            ->where('date', $validated['date'])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'A slot already exists for this channel and date.'], 409);
        }

        $slot = ScheduleSlot::create($validated);

        return response()->json($slot, 201);
    }

    public function show($id)
    {
        return ScheduleSlot::with('schedules')->findOrFail($id);
    }





    public function update(Request $request, $id)
{
    $slot = ScheduleSlot::with('schedules')->findOrFail($id);

    $validated = $request->validate([
        'start_time' => 'sometimes|date_format:H:i:s',
        'end_time' => 'sometimes|date_format:H:i:s|after:start_time',
        'date' => 'sometimes|date',
        'channel' => 'sometimes|string', // assuming channel is a string
    ]);

    $hasSchedules = $slot->schedules->count() > 0;

    if ($hasSchedules) {
        // ❌ Block start_time update if schedules exist
        if (array_key_exists('start_time', $validated)) {
            return response()->json([
                'error' => 'You cannot update the start_time because content has already been scheduled in this slot.'
            ], 422);
        }

        // Allow updating end_time, date, or channel only if no conflict
        $newDate = $validated['date'] ?? $slot->date;
        $newChannel = $validated['channel'] ?? $slot->channel;

        $conflictExists = ScheduleSlot::where('id', '!=', $slot->id)
            ->where('date', $newDate)
            ->where('channel', $newChannel)
            ->exists();

        if ($conflictExists) {
            return response()->json([
                'error' => 'You cannot update the slot because another slot exists on the same date and channel.'
            ], 422);
        }
    }

    // ✅ No schedules: update everything
    $slot->update($validated);

    return response()->json($slot->load('schedules'));
}








    public function destroy($id)
    {
        $slot = ScheduleSlot::findOrFail($id);

        if ($slot->schedules()->count()) {
            return response()->json(['error' => 'Cannot delete slot with scheduled content.'], 403);
        }

        $slot->delete();

        return response()->json(['message' => 'Slot deleted successfully.']);
    }
}
