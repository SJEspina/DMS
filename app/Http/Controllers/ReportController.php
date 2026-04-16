<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SupplyTransaction;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $daysInMonth = Carbon::now()->daysInMonth;

        // Today's sale - match dashboard logic (price * qty)
        $todaySale = Order::whereDate('created_at', $today)
            ->get()
            ->sum(function ($order) {
                return (float) $order->price * (int) $order->qty;
            });

        // Today's total orders
        $todayTotalOrders = Order::whereDate('created_at', $today)->count();

        // Monthly sales total - match dashboard logic (price * qty)
        $monthlySales = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->sum(function ($order) {
                return (float) $order->price * (int) $order->qty;
            });

        // Monthly expense total
        $monthlyExpense = SupplyTransaction::where('type', 'in')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_cost');

        // Order statistics - all days of current month
        $orderStatistics = collect(range(1, $daysInMonth))->map(function ($day) use ($currentYear, $currentMonth) {
            $date = Carbon::create($currentYear, $currentMonth, $day);

            return [
                'label' => $date->format('d M'),
                'day' => $day,
                'orders' => Order::whereDate('created_at', $date)->count(),
            ];
        })->values();

        // Sales overview - all days of current month
        $salesOverview = collect(range(1, $daysInMonth))->map(function ($day) use ($currentYear, $currentMonth) {
            $date = Carbon::create($currentYear, $currentMonth, $day);

            $sales = Order::whereDate('created_at', $date)
                ->get()
                ->sum(function ($order) {
                    return (float) $order->price * (int) $order->qty;
                });

            return [
                'label' => $date->format('d M'),
                'day' => $day,
                'sales' => (float) $sales,
            ];
        })->values();

        // Expense overview - January to December
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ];

        $expenseOverview = collect($months)->map(function ($label, $monthNumber) use ($currentYear) {
            $expense = SupplyTransaction::where('type', 'in')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $monthNumber)
                ->sum('total_cost');

            return [
                'label' => $label,
                'expense' => (float) $expense,
            ];
        })->values();

        // Recent expense details
        $expenseDetails = SupplyTransaction::with('supply')
            ->where('type', 'in')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->created_at->format('M d, Y'),
                    'name' => $item->supply ? $item->supply->name : 'N/A',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->unit_price,
                    'total' => (float) $item->total_cost,
                    'notes' => $item->notes,
                ];
            });

        return Inertia::render('Reports/Index', [
            'summary' => [
                'todaySale' => (float) $todaySale,
                'todayTotalOrders' => $todayTotalOrders,
                'monthlySales' => (float) $monthlySales,
                'monthlyExpense' => (float) $monthlyExpense,
            ],
            'orderStatistics' => $orderStatistics,
            'salesOverview' => $salesOverview,
            'expenseOverview' => $expenseOverview,
            'expenseDetails' => $expenseDetails,
        ]);
    }
}