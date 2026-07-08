<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case PickupScheduled = 'pickup_scheduled';
    case InProgress = 'in_progress';
    case Weighed = 'weighed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
