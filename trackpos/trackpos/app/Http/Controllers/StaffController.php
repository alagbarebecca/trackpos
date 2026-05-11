<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * List all employees
     */
    public function index(Request $request)
    {
        $employees = Employee::with('user')->orderByDesc('created_at')->paginate(20);
        return view('staff.index', compact('employees'));
    }

    /**
     * Add new employee
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'designation' => 'required|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
        ]);

        $employee = Employee::create($validated);
        
        return redirect()->route('staff.index')->with('success', 'Employee added successfully!');
    }

    /**
     * Show employee details
     */
    public function show(Employee $employee)
    {
        $employee->load(['user', 'shifts' => function($q) {
            $q->orderByDesc('date')->limit(30);
        }]);
        
        $shifts = $employee->shifts()
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();
            
        $totalHours = $shifts->sum('hours_worked');
        $totalPay = $totalHours * ($employee->hourly_rate ?? 0);
        
        return view('staff.show', compact('employee', 'shifts', 'totalHours', 'totalPay'));
    }

    /**
     * Clock in employee
     */
    public function clockIn(Request $request)
    {
        $employee = $request->user()->employee;
        
        if (!$employee) {
            return back()->with('error', 'No employee record found!');
        }
        
        // Check if already clocked in
        if ($employee->activeShift) {
            return back()->with('error', 'Already clocked in!');
        }
        
        Shift::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'break_minutes' => 0,
        ]);
        
        return back()->with('success', 'Clocked in successfully!');
    }

    /**
     * Clock out employee
     */
    public function clockOut(Request $request)
    {
        $employee = $request->user()->employee;
        
        if (!$employee || !$employee->activeShift) {
            return back()->with('error', 'Not clocked in!');
        }
        
        $shift = $employee->activeShift;
        $shift->update(['clock_out' => now()]);
        
        return back()->with('success', 'Clocked out successfully!');
    }

    /**
     * Get timeclock view
     */
    public function timeclock(Request $request)
    {
        $employee = $request->user()->employee;
        $activeShift = $employee?->activeShift;
        
        return view('staff.timeclock', compact('activeShift'));
    }

    /**
     * Staff scheduling
     */
    public function schedule(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        
        $shifts = Shift::whereDate('date', $date)->with('employee.user')->get();
        
        return view('staff.schedule', compact('shifts', 'date'));
    }

    /**
     * Weekly schedule
     */
    public function weeklySchedule(Request $request)
    {
        $startDate = $request->start 
            ? \Carbon\Carbon::parse($request->start)->startOfDay()
            : now()->startOfWeek();
            
        $endDate = (clone $startDate)->addDays(6);
        
        $shifts = Shift::whereBetween('date', [$startDate, $endDate])
            ->with('employee.user')
            ->orderBy('clock_in')
            ->get();
            
        return view('staff.weekly-schedule', compact('shifts', 'startDate', 'endDate'));
    }
}