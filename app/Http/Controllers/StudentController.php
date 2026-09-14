<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index()
    {
        $machines = Machine::where('status', 'available')->get();
        $reservations = Auth::user()->reservations()->with('machine')->orderBy('start_time', 'desc')->get();

        return view('student.dashboard', compact('machines', 'reservations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'start_time' => 'required|date|after_or_equal:now',
        ]);

        $user = Auth::user();

        // HU10: Max 3 active reservations
        $activeReservationsCount = $user->reservations()
            ->whereIn('status', ['pending', 'in_process'])
            ->count();

        if ($activeReservationsCount >= 3) {
            return back()->with('error', 'No puedes tener más de 3 reservas activas.');
        }

        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addHours(2);

        // HU9: Prevent overlapping reservations on the same machine
        $overlapping = Reservation::where('machine_id', $request->machine_id)
            ->whereIn('status', ['pending', 'in_process'])
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime->copy()->subSeconds(1)])
                      ->orWhereBetween('end_time', [$startTime->copy()->addSeconds(1), $endTime])
                      ->orWhere(function($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })->exists();

        if ($overlapping) {
            return back()->with('error', 'La máquina ya está reservada en este horario.');
        }

        Reservation::create([
            'user_id' => $user->id,
            'machine_id' => $request->machine_id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Reserva creada exitosamente por 2 horas.');
    }

    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Solo puedes cancelar reservas pendientes.');
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reserva cancelada.');
    }
}
