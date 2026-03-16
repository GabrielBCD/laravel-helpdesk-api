<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    /**
     * Store a newly created ticket.
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::create($request->validated());

        return response()->json([
            'message' => 'Ticket criado com sucesso.',
            'data'    => $ticket,
        ], 201);
    }

    /**
     * Display a listing of tickets with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by syndic_name, condominium_name, or email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('syndic_name', 'like', "%{$search}%")
                  ->orWhere('condominium_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
                'per_page'     => $tickets->perPage(),
                'total'        => $tickets->total(),
            ],
        ]);
    }

    /**
     * Display the specified ticket with its notes.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load('notes');

        return response()->json($ticket);
    }

    /**
     * Update the specified ticket.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket->update($request->validated());

        return response()->json([
            'message' => 'Ticket atualizado com sucesso.',
            'data'    => $ticket,
        ]);
    }

    /**
     * Soft delete the specified ticket.
     */
    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket removido com sucesso.',
        ]);
    }

    /**
     * Permanently delete the specified ticket (hard delete).
     */
    public function forceDestroy(Request $request): JsonResponse
    {
        $ticket = Ticket::withTrashed()->findOrFail($request->route('ticket'));
        $ticket->forceDelete();

        return response()->json([
            'message' => 'Ticket removido com sucesso.',
        ]);
    }

    /**
     * Get statistics of tickets grouped by status.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'open'      => Ticket::where('status', 'open')->count(),
            'verifying' => Ticket::where('status', 'verifying')->count(),
            'finished'  => Ticket::where('status', 'finished')->count(),
            'total'     => Ticket::count(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }
}

