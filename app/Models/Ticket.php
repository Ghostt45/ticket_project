<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'order_id',
        'ticket_type_id',  // Добавляем новый тип билета
        'ticket_price',
        'ticket_quantity',
        'barcode',
    ];

    // Связь с моделью TicketType
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }
}

