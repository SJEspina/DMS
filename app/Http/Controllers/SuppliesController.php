<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SuppliesController extends Controller
{
    private function getStatus($stock)
    {
        if ($stock <= 0) {
            return 'Out of Stock';
        } elseif ($stock <= 5) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    public function index(Request $request)
    {
        $supplies = Supply::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalExpenses = SupplyTransaction::where('type', 'in')->sum('total_cost');

        $monthlyExpenses = SupplyTransaction::where('type', 'in')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_cost');

        return Inertia::render('Supplies/Index', [
            'supplies' => $supplies,
            'filters' => [
                'search' => $request->search,
            ],
            'totalExpenses' => $totalExpenses,
            'monthlyExpenses' => $monthlyExpenses,
        ]);
    }

    public function create()
    {
        return Inertia::render('Supplies/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $validated['status'] = $this->getStatus($validated['stock']);

        DB::transaction(function () use ($validated) {
            $supply = Supply::create($validated);

            if ($validated['stock'] > 0) {
                SupplyTransaction::create([
                    'supply_id' => $supply->id,
                    'type' => 'in',
                    'quantity' => $validated['stock'],
                    'unit_price' => $validated['price'],
                    'total_cost' => $validated['stock'] * $validated['price'],
                    'notes' => 'Initial stock',
                ]);
            }
        });

        return redirect()->route('supplies.index');
    }

    public function edit(Supply $supply)
    {
        return Inertia::render('Supplies/Edit', [
            'supply' => $supply,
        ]);
    }

    public function update(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['status'] = $this->getStatus($supply->stock);

        $supply->update($validated);

        return redirect()->route('supplies.index');
    }

    public function destroy(Supply $supply)
    {
        $supply->delete();

        return redirect()->route('supplies.index');
    }

    public function addStock(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $supply) {
            SupplyTransaction::create([
                'supply_id' => $supply->id,
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['price'],
                'total_cost' => $validated['quantity'] * $validated['price'],
                'notes' => $validated['notes'] ?? 'Restock',
            ]);

            $newStock = $supply->stock + $validated['quantity'];

            $supply->update([
                'stock' => $newStock,
                'price' => $validated['price'],
                'status' => $this->getStatus($newStock),
            ]);
        });

        return redirect()->route('supplies.index');
    }

    public function useStock(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validated['quantity'] > $supply->stock) {
            return back()->withErrors([
                'quantity' => 'Not enough stock available.',
            ]);
        }

        DB::transaction(function () use ($validated, $supply) {
            SupplyTransaction::create([
                'supply_id' => $supply->id,
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'unit_price' => 0,
                'total_cost' => 0,
                'notes' => $validated['notes'] ?? 'Used in production',
            ]);

            $newStock = $supply->stock - $validated['quantity'];

            $supply->update([
                'stock' => $newStock,
                'status' => $this->getStatus($newStock),
            ]);
        });

        return redirect()->route('supplies.index');
    }
}