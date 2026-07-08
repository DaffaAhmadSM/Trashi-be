<?php

namespace App\Enums;

enum TimeSlot: string
{
    case Slot_8_10 = '8AM-10AM';
    case Slot_10_12 = '10AM-12PM';
    case Slot_13_15 = '1PM-3PM';
    case Slot_15_17 = '3PM-5PM';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Slot_8_10->value => '8:00 AM - 10:00 AM',
            self::Slot_10_12->value => '10:00 AM - 12:00 PM',
            self::Slot_13_15->value => '1:00 PM - 3:00 PM',
            self::Slot_15_17->value => '3:00 PM - 5:00 PM',
        ];
    }

    /** Time slots available for a given day of week (0=Sunday). */
    public static function availableFor(int $dayOfWeek): array
    {
        if ($dayOfWeek === 0) {
            return []; // Sunday: no slots
        }

        $slots = [self::Slot_8_10, self::Slot_10_12, self::Slot_13_15];

        if ($dayOfWeek !== 6) {
            $slots[] = self::Slot_15_17; // Sat: no 3PM-5PM
        }

        return $slots;
    }
}
