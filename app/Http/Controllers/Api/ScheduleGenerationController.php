<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{ChannelRule, ScheduleSlot, Content, ContentSchedule, ContentCooldown};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleGenerationController extends Controller
{
    public function generate(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $log = [];

        $rules = ChannelRule::all();

        foreach ($rules as $rule) {
            DB::beginTransaction();

            try {
                $log[] = "⏳ Processing: {$rule->channel}";

                // Step 1: Create slot for the day
                $slot = ScheduleSlot::firstOrCreate(
                    ['channel' => $rule->channel, 'date' => $today],
                    [
                        'start_time' => '06:00:00',
                        'end_time' => '14:00:00'
                    ]
                );

                $currentTime = Carbon::createFromTimeString($slot->start_time);
                $slotEnd = Carbon::createFromTimeString($slot->end_time);
                $scheduledCount = ContentSchedule::where('slot_id', $slot->id)->count();

                if ($scheduledCount >= $rule->max_content_per_day) {
                    $log[] = "🔁 Max content already scheduled for {$rule->channel}";
                    DB::commit();
                    continue;
                }

                $contents = Content::where('channel', $rule->channel)
                    ->when($rule->preferred_content_types, function ($q) use ($rule) {
                        $q->whereIn('type', $rule->preferred_content_types);
                    })
                    ->get();

                $usedIds = ContentSchedule::whereDate('date', $today)->pluck('content_id')->toArray();

                foreach ($contents as $content) {
                    if (in_array($content->id, $usedIds)) continue;

                    // ✅ Cooldown check
                    $cooldown = ContentCooldown::where('content_id', $content->id)
                        ->where('channel', $rule->channel)
                        ->first();

                    if ($cooldown) {
                        $cooldownEnd = Carbon::parse($cooldown->last_used_at)
                            ->addDays($cooldown->cooldown_days ?? $rule->cooldown_days);

                        if (now()->lt($cooldownEnd)) {
                            $log[] = "⏸ Skipped (cooldown): {$content->title}";
                            continue;
                        }
                    }

                    // Timing calc
                    $duration = Carbon::createFromTimeString($content->duration);
                    $endTime = $currentTime->copy()
                        ->addHours($duration->hour)
                        ->addMinutes($duration->minute)
                        ->addSeconds($duration->second);

                    if ($endTime->gt($slotEnd)) {
                        $log[] = "⏹ No time left for {$content->title}";
                        break;
                    }

                    // Create schedule
                    ContentSchedule::create([
                        'content_id' => $content->id,
                        'slot_id' => $slot->id,
                        'channel' => $rule->channel,
                        'date' => $today,
                        'start_time' => $currentTime->format('H:i:s'),
                        'end_time' => $endTime->format('H:i:s'),
                    ]);

                    // ✅ Update cooldown
                    ContentCooldown::updateOrCreate(
                        ['content_id' => $content->id, 'channel' => $rule->channel],
                        [
                            'last_used_at' => now(),
                            'cooldown_days' => $rule->cooldown_days
                        ]
                    );

                    $currentTime = $endTime;
                    $scheduledCount++;

                    if ($scheduledCount >= $rule->max_content_per_day) {
                        $log[] = "✅ Max limit reached after {$content->title}";
                        break;
                    }
                }

                $slot->start_time = $currentTime->format('H:i:s');
                $slot->save();

                DB::commit();
                $log[] = "✅ Schedule filled for {$rule->channel}";

            } catch (\Throwable $e) {
                DB::rollBack();
                $log[] = "❌ Error for {$rule->channel}: " . $e->getMessage();
            }
        }

        return response()->json(['message' => 'Schedule generation complete', 'log' => $log]);
    }
}
