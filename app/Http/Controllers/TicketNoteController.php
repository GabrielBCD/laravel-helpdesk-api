<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketNote\StoreTicketNoteRequest;
use App\Http\Requests\TicketNote\UpdateTicketNoteRequest;
use App\Models\Ticket;
use App\Models\TicketNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketNoteController extends Controller
{
    /**
     * Store a newly created note for a ticket.
     */
    public function store(StoreTicketNoteRequest $request, Ticket $ticket): JsonResponse
    {
        $note = $ticket->notes()->create([
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => 'Nota criada com sucesso.',
            'data'    => $note,
        ], 201);
    }

    /**
     * Update the specified note.
     */
    public function update(UpdateTicketNoteRequest $request, Ticket $ticket, TicketNote $note): JsonResponse
    {
        // Verify that the note belongs to the ticket
        if ($note->ticket_id !== $ticket->id) {
            return response()->json([
                'message' => 'A nota não pertence a este ticket.',
            ], 404);
        }

        $note->update([
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => 'Nota atualizada com sucesso.',
            'data'    => $note,
        ]);
    }

    /**
     * Soft delete the specified note.
     */
    public function destroy(Ticket $ticket, TicketNote $note): JsonResponse
    {
        // Verify that the note belongs to the ticket
        if ($note->ticket_id !== $ticket->id) {
            return response()->json([
                'message' => 'A nota não pertence a este ticket.',
            ], 404);
        }

        $note->delete();

        return response()->json([
            'message' => 'Nota removida com sucesso.',
        ]);
    }

    /**
     * Permanently delete the specified note (hard delete).
     */
    public function forceDestroy(Ticket $ticket, Request $request): JsonResponse
    {
        $note = TicketNote::withTrashed()->findOrFail($request->route('note'));

        // Verify that the note belongs to the ticket
        if ($note->ticket_id !== $ticket->id) {
            return response()->json([
                'message' => 'A nota não pertence a este ticket.',
            ], 404);
        }

        $note->forceDelete();

        return response()->json([
            'message' => 'Nota removida com sucesso.',
        ]);
    }
}

