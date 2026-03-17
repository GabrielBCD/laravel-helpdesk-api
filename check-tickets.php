<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = \App\Models\Ticket::count();
echo "Total de tickets: $count\n";

if ($count > 0) {
    echo "\nTickets criados:\n";
    \App\Models\Ticket::all(['id', 'syndic_name', 'email', 'status'])->each(function($ticket) {
        echo "- ID: {$ticket->id}, Nome: {$ticket->syndic_name}, Email: {$ticket->email}, Status: {$ticket->status}\n";
    });
}

