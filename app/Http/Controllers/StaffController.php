<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class StaffController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'machine'])
            ->orderBy('start_time', 'desc')
            ->paginate(15);
            
        return view('staff.dashboard', compact('reservations'));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => 'required|in:pending,in_process,finished,cancelled'
        ]);

        $reservation->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Estado de la reserva actualizado.');
    }
}
