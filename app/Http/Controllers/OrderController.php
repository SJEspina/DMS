<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('search')) {
            $query->where('customer', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        $orders->through(function ($order) {
            $total = (float) $order->price * (int) $order->qty;
            $downpayment = (float) ($order->downpayment ?? 0);

            if ($downpayment <= 0) {
                $order->payment_status = 'Unpaid';
            } elseif ($downpayment >= $total) {
                $order->payment_status = 'Paid';
            } else {
                $order->payment_status = 'Partially Paid';
            }

            return $order;
        });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Orders/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'deadline' => 'nullable|date',
            'customer' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'product' => 'required|in:TSHIRT,POLOSHIRT,HOODIE,LONGSLEEVE,SANDO ONLY,SHORT ONLY,SANDO UP & DOWN,TSHIRT UP & DOWN,PLAQUES,MUG,TARP,OTHERS',
            'qty' => 'required|integer|min:1',
            'downpayment' => 'nullable|numeric|min:0',
            'status' => 'required|in:Pending,Ongoing,Done',
        ]);

        $validated['downpayment'] = $validated['downpayment'] ?? 0;

        if ($validated['downpayment'] > ($validated['price'] * $validated['qty'])) {
            return back()->withErrors([
                'downpayment' => 'Downpayment cannot be greater than the total amount.',
            ])->withInput();
        }

        Order::create($validated);

        return redirect()->route('orders.index');
    }

    public function edit(Order $order)
    {
        return Inertia::render('Orders/Edit', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'deadline' => 'nullable|date',
            'customer' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'product' => 'required|in:TSHIRT,POLOSHIRT,HOODIE,LONGSLEEVE,SANDO ONLY,SHORT ONLY,SANDO UP & DOWN,TSHIRT UP & DOWN,PLAQUES,MUG,TARP,OTHERS',
            'qty' => 'required|integer|min:1',
            'downpayment' => 'nullable|numeric|min:0',
            'status' => 'required|in:Pending,Ongoing,Done',
        ]);

        $validated['downpayment'] = $validated['downpayment'] ?? 0;

        if ($validated['downpayment'] > ($validated['price'] * $validated['qty'])) {
            return back()->withErrors([
                'downpayment' => 'Downpayment cannot be greater than the total amount.',
            ])->withInput();
        }

        $order->update($validated);

        return redirect()->route('orders.index');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index');
    }

    public function payBalance(Order $order)
    {
        $total = $order->price * $order->qty;

        $order->update([
            'downpayment' => $total
        ]);

        return back();
    }
}