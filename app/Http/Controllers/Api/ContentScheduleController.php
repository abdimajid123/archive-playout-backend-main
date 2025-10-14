<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContentSchedule;

class ContentScheduleController extends Controller
{
    public function index()
    {
        return ContentSchedule::with(['content', 'slot'])->get();
    }





public function store(Request $request)
{
    $validated = $request->validate([
        'content_id' => 'required|exists:contents,id',
        'slot_id' => 'required|exists:schedule_slots,id',
        'cooldown' => 'nullable|integer|min:0',
    ]);

    $content = \App\Models\Content::findOrFail($validated['content_id']);
    $slot = \App\Models\ScheduleSlot::findOrFail($validated['slot_id']);

    $channel = $slot->channel;
    $date = $slot->date;

    // 1️⃣ Cooldown check
    $cooldown = \App\Models\ContentCooldown::where('content_id', $content->id)
        ->where('channel', $channel)
        ->first();

    if ($cooldown) {
        $cooldownEnd = \Carbon\Carbon::parse($cooldown->last_used_at)->addDays($cooldown->cooldown_days);
        if (now()->lt($cooldownEnd)) {
            return response()->json([
                'error' => "This content is in cooldown until " . $cooldownEnd->toDateString()
            ], 422);
        }
    }

    // 2️⃣ Time calculation
    $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $slot->start_time);
    $duration = \Carbon\CarbonInterval::createFromFormat('H:i:s', $content->duration);
    $endTime = $startTime->copy()->add($duration);
    $slotEnd = \Carbon\Carbon::createFromFormat('H:i:s', $slot->end_time);

    if ($endTime->gt($slotEnd)) {
        return response()->json(['error' => 'Content duration exceeds remaining slot time.'], 422);
    }

    // 3️⃣ Create schedule
    $schedule = \App\Models\ContentSchedule::create([
        'content_id' => $content->id,
        'slot_id' => $slot->id,
        'channel' => $channel,
        'date' => $date,
        'start_time' => $startTime->format('H:i:s'),
        'end_time' => $endTime->format('H:i:s'),
    ]);

    // 4️⃣ Update cooldown tracker
    \App\Models\ContentCooldown::updateOrCreate(
        ['content_id' => $content->id, 'channel' => $channel],
        [
            'last_used_at' => now(),
            'cooldown_days' => $validated['cooldown'] ?? 30,
        ]
    );

    // 5️⃣ Update slot's start_time
    $slot->start_time = $endTime->format('H:i:s');
    $slot->save();

    return response()->json($schedule->load('content', 'slot'), 201);
}







    public function show($id)
    {
        return ContentSchedule::with(['content', 'slot'])->findOrFail($id);
    }





public function update(Request $request, $id)
{
    $schedule = \App\Models\ContentSchedule::findOrFail($id);
    $oldSlot = \App\Models\ScheduleSlot::findOrFail($schedule->slot_id);

    $validated = $request->validate([
        'content_id' => 'required|exists:contents,id',
        'slot_id' => 'required|exists:schedule_slots,id',
        'cooldown' => 'nullable|integer|min:0',
    ]);

    $newContent = \App\Models\Content::findOrFail($validated['content_id']);
    $newSlot = \App\Models\ScheduleSlot::findOrFail($validated['slot_id']);
    $channel = $newSlot->channel;

    // 🛡️ Check cooldown
    $cooldown = \App\Models\ContentCooldown::where('content_id', $newContent->id)
        ->where('channel', $channel)
        ->first();

    if ($cooldown) {
        $cooldownEnd = \Carbon\Carbon::parse($cooldown->last_used_at)->addDays($cooldown->cooldown_days);
        if (now()->lt($cooldownEnd)) {
            return response()->json([
                'error' => "This content is in cooldown until " . $cooldownEnd->toDateString()
            ], 422);
        }
    }

    $newDuration = \Carbon\CarbonInterval::createFromFormat('H:i:s', $newContent->duration);

    if ($schedule->slot_id != $newSlot->id) {
        // Reorder schedules in old slot
        $followingInOldSlot = \App\Models\ContentSchedule::where('slot_id', $oldSlot->id)
            ->where('start_time', '>', $schedule->start_time)
            ->orderBy('start_time')->get();

        $prevEnd = \Carbon\Carbon::createFromTimeString($schedule->start_time);

        foreach ($followingInOldSlot as $s) {
            $c = \App\Models\Content::findOrFail($s->content_id);
            $dur = \Carbon\CarbonInterval::createFromFormat('H:i:s', $c->duration);

            $s->update([
                'start_time' => $prevEnd->format('H:i:s'),
                'end_time' => $prevEnd->copy()->add($dur)->format('H:i:s'),
            ]);
            $prevEnd = $prevEnd->add($dur);
        }

        $lastInOld = \App\Models\ContentSchedule::where('slot_id', $oldSlot->id)->latest('end_time')->first();
        $oldSlot->start_time = $lastInOld ? $lastInOld->end_time : $oldSlot->end_time;
        $oldSlot->save();

        // Append to new slot
        $lastNewSchedule = \App\Models\ContentSchedule::where('slot_id', $newSlot->id)->latest('end_time')->first();
        $newStart = $lastNewSchedule
            ? \Carbon\Carbon::createFromTimeString($lastNewSchedule->end_time)
            : \Carbon\Carbon::createFromTimeString($newSlot->start_time);

        $newEnd = $newStart->copy()->add($newDuration);
        $slotEnd = \Carbon\Carbon::createFromTimeString($newSlot->end_time);

        if ($newEnd->gt($slotEnd)) {
            return response()->json(['error' => 'Not enough time in the new slot.'], 422);
        }

        $schedule->update([
            'content_id' => $newContent->id,
            'slot_id' => $newSlot->id,
            'start_time' => $newStart->format('H:i:s'),
            'end_time' => $newEnd->format('H:i:s'),
        ]);

        $newSlot->start_time = $newEnd->format('H:i:s');
        $newSlot->save();
    } else {
        // SAME SLOT update
        $start = \Carbon\Carbon::createFromTimeString($schedule->start_time);
        $end = $start->copy()->add($newDuration);

        $schedule->update([
            'content_id' => $newContent->id,
            'end_time' => $end->format('H:i:s'),
        ]);

        $previousEnd = $end;

        $laterSchedules = \App\Models\ContentSchedule::where('slot_id', $schedule->slot_id)
            ->where('start_time', '>', $schedule->start_time)
            ->orderBy('start_time')->get();

        foreach ($laterSchedules as $s) {
            $c = \App\Models\Content::findOrFail($s->content_id);
            $dur = \Carbon\CarbonInterval::createFromFormat('H:i:s', $c->duration);

            $s->update([
                'start_time' => $previousEnd->format('H:i:s'),
                'end_time' => $previousEnd->copy()->add($dur)->format('H:i:s'),
            ]);

            $previousEnd = $previousEnd->add($dur);
        }

        $oldSlot->start_time = $previousEnd->format('H:i:s');
        $oldSlot->save();
    }

    // Update cooldown tracker
    \App\Models\ContentCooldown::updateOrCreate(
        ['content_id' => $newContent->id, 'channel' => $channel],
        [
            'last_used_at' => now(),
            'cooldown_days' => $validated['cooldown'] ?? ($cooldown->cooldown_days ?? 30),
        ]
    );

    return response()->json([
        'message' => 'Schedule updated and times adjusted.',
        'updated' => $schedule->load('content', 'slot'),
    ]);
}











    public function destroy($id)
{
    $schedule = \App\Models\ContentSchedule::findOrFail($id);
    $slot = \App\Models\ScheduleSlot::findOrFail($schedule->slot_id);

    // Capture the start time of the schedule we're deleting
    $deletedStart = \Carbon\Carbon::createFromTimeString($schedule->start_time);

    // Delete the schedule
    $schedule->delete();

    // Get all schedules after the deleted one in this slot
    $laterSchedules = \App\Models\ContentSchedule::where('slot_id', $slot->id)
        ->where('start_time', '>', $deletedStart->format('H:i:s'))
        ->orderBy('start_time')
        ->get();

    $prevEnd = \Carbon\Carbon::createFromTimeString($schedule->start_time);

    foreach ($laterSchedules as $s) {
        $c = \App\Models\Content::findOrFail($s->content_id);
        $duration = \Carbon\CarbonInterval::createFromFormat('H:i:s', $c->duration);

        $s->update([
            'start_time' => $prevEnd->format('H:i:s'),
            'end_time' => $prevEnd->copy()->add($duration)->format('H:i:s'),
        ]);

        $prevEnd = $prevEnd->add($duration);
    }

    // Update the slot's start_time to the end of the last schedule
    $lastScheduled = \App\Models\ContentSchedule::where('slot_id', $slot->id)
        ->orderByDesc('end_time')
        ->first();

    $slot->start_time = $lastScheduled
        ? $lastScheduled->end_time
        : $slot->original_start_time ?? $slot->start_time; // fallback

    $slot->save();

    return response()->json(['message' => 'Schedule deleted and slot adjusted.']);
}

}
