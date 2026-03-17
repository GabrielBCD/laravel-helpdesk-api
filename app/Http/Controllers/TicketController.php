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
        // Validar parâmetros de entrada
        $validated = $request->validate([
            'status' => ['nullable', 'in:open,verifying,finished'],
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Ticket::query();

        // Filter by status
        if ($request->has('status') && !is_null($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Search by syndic_name, condominium_name, or email
        if ($request->has('search') && !is_null($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('syndic_name', 'like', "%{$search}%")
                  ->orWhere('condominium_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->has('from') && !is_null($validated['from'])) {
            $query->where('created_at', '>=', $validated['from'] . ' 00:00:00');
        }

        if ($request->has('to') && !is_null($validated['to'])) {
            $query->where('created_at', '<=', $validated['to'] . ' 23:59:59');
        }

        // Pagination
        $perPage = $validated['per_page'] ?? 15;
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
    public function forceDestroy(Ticket $ticket): JsonResponse
    {
        // Fetch trashed ticket if it was soft-deleted
        $trashedTicket = Ticket::withTrashed()->find($ticket->id);

        if (!$trashedTicket) {
            return response()->json([
                'message' => 'Ticket não encontrado.',
            ], 404);
        }

        $trashedTicket->forceDelete();

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

