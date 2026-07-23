<?php

namespace App\Models;

use Database\Factories\FakeOrderNotificationNameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'city',
])]
class FakeOrderNotificationName extends Model
{
    /** @use HasFactory<FakeOrderNotificationNameFactory> */
    use HasFactory;
}
