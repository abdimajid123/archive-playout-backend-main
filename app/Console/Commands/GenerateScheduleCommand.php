<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


use App\Models\{
    ChannelRule,
    ScheduleSlot,
    Content,
    ContentSchedule
};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateScheduleCommand extends Command
{
    protected $signature = 'schedule:generate';
    protected $description = 'Auto-generate schedule slots and fill them with content based on channel rules';

    public function handle()
    {
        $this->info("🔧 schedule:generate command is running...");
        
        $today = Carbon::today()->toDateString();

        $rules = ChannelRule::all();
        foreach ($rules as $rule) {
            DB::beginTransaction();
            try {
                $this->info("\n⏳ Processing channel: {$rule->channel}");

                $slot = ScheduleSlot::firstOrCreate(
                    ['channel' => $rule->channel, 'date' => $today],
                    [
                        'start_time' => '08:00:00',
                        'end_time' => Carbon::createFromTime(8, 0)->addMinutes($rule->slot_duration_minutes * $rule->max_content_per_day)->format('H:i:s')
                    ]
                );

                $currentTime = Carbon::createFromTimeString($slot->start_time);
                $slotEnd = Carbon::createFromTimeString($slot->end_time);

                $scheduledCount = ContentSchedule::where('slot_id', $slot->id)->count();

                if ($scheduledCount >= $rule->max_content_per_day) {
                    $this->warn("🔁 Slot already has maximum scheduled content");
                    DB::commit();
                    continue;
                }

                $availableContents = Content::where('channel', $rule->channel)
                    ->when($rule->preferred_content_types, function ($query) use ($rule) {
                        $query->whereIn('type', $rule->preferred_content_types);
                    })
                    ->get();

                $usedContentIds = ContentSchedule::whereDate('date', $today)->pluck('content_id')->toArray();

                foreach ($availableContents as $content) {
                    if (in_array($content->id, $usedContentIds)) {
                        continue; // skip already used
                    }

                    $duration = Carbon::createFromTimeString($content->duration);
                    $endTime = $currentTime->copy()->addHours($duration->hour)->addMinutes($duration->minute)->addSeconds($duration->second);

                    if ($endTime->gt($slotEnd)) {
                        break; // slot is full
                    }

                    ContentSchedule::create([
                        'content_id' => $content->id,
                        'slot_id' => $slot->id,
                        'channel' => $rule->channel,
                        'date' => $today,
                        'start_time' => $currentTime->format('H:i:s'),
                        'end_time' => $endTime->format('H:i:s'),
                    ]);

                    $currentTime = $endTime;
                    $scheduledCount++;

                    if ($scheduledCount >= $rule->max_content_per_day) {
                        break;
                    }
                }

                $slot->start_time = $currentTime->format('H:i:s');
                $slot->save();

                DB::commit();
                $this->info("✅ Schedule filled for {$rule->channel}");

            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("❌ Error generating for {$rule->channel}: " . $e->getMessage());
            }
        }
    }
}
