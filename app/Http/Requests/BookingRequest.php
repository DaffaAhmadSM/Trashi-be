<?php

namespace App\Http\Requests;

use App\Enums\TimeSlot;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:addresses,address_id'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.category_id' => ['required', 'exists:waste_categories,category_id'],
            'scheduled_date' => ['required', 'date', 'after:today'],
            'time_slot' => ['required', Rule::enum(TimeSlot::class)],
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $date = Carbon::parse($this->scheduled_date);
                $dayOfWeek = $date->dayOfWeek;

                if ($dayOfWeek === Carbon::SUNDAY) {
                    $validator->errors()->add('scheduled_date', 'Scheduling is not available on Sunday.');
                }

                if ($dayOfWeek === Carbon::SATURDAY && $this->time_slot === TimeSlot::Slot_15_17->value) {
                    $validator->errors()->add('time_slot', 'The 3PM-5PM slot is not available on Saturday.');
                }
            },
        ];
    }
}
