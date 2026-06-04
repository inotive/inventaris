<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ItemCategory;
use App\Models\ItemDisposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ItemDisposalController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('item_disposals.view'), 403, 'Anda tidak memiliki akses untuk melihat data disposal.');

        $filters = [
            'date_from'   => $request->input('date_from'),
            'date_to'     => $request->input('date_to'),
            'category_id' => $request->integer('category_id') ?: null,
            'branch_id'   => $request->integer('branch_id') ?: null,
            'q'           => (string) $request->get('q', ''),
        ];

        $tanggalAwal  = $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->startOfDay() : null;
        $tanggalAkhir = $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->endOfDay() : null;

        $canViewGlobal = Auth::user()->hasRole("Superadmin") || Auth::user()->can("item_disposals.view_global");
        $userBranchId = Auth::user()->employee->branch_id ?? null;

        $query = ItemDisposal::with(['item.category', 'item.unit', 'item.branch', 'reporter'])
            ->where('reason', 'rusak')
            ->orderBy('disposal_date', 'desc')
            ->orderBy('id', 'desc');

        // Apply filters
        if ($filters['q']) {
            $query->where(function ($q) use ($filters) {
                $q->where('disposal_no', 'like', '%' . $filters['q'] . '%')
                  ->orWhereHas('item', function ($qi) use ($filters) {
                      $qi->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['q'] . '%');
                  });
            });
        }

        if ($filters['category_id']) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if ($filters['branch_id']) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('branch_id', $filters['branch_id']);
            });
        } elseif (!$canViewGlobal && $userBranchId) {
            // Non-superadmin without view_global permission is restricted to their own branch
            $query->whereHas('item', function ($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            });
        }

        if ($tanggalAwal) {
            $query->where('disposal_date', '>=', $tanggalAwal->toDateString());
        }
        if ($tanggalAkhir) {
            $query->where('disposal_date', '<=', $tanggalAkhir->toDateString());
        }

        $disposals = $query->paginate($request->integer('per_page') ?: 15)->withQueryString();

        // Map data for clean response
        $disposals->getCollection()->transform(function ($d) {
            return [
                'id'            => $d->id,
                'disposal_no'   => $d->disposal_no,
                'disposal_date' => $d->disposal_date ? $d->disposal_date->format('Y-m-d') : null,
                'item_name'     => $d->item->name ?? '-',
                'item_code'     => $d->item->code ?? '-',
                'category'      => $d->item->category->name ?? '-',
                'branch'        => $d->item->branch->name ?? '-',
                'unit'          => $d->item->unit ? ($d->item->unit->short_name ?? $d->item->unit->name) : '-',
                'quantity'      => $d->quantity,
                'reason'        => $d->reason === 'rusak' ? 'Rusak / Cacat' : 'Hilang',
                'reason_raw'    => $d->reason,
                'description'   => $d->description ?? '-',
                'reporter'      => $d->reporter->name ?? '-',
                'source_type'   => $d->source_type ? class_basename($d->source_type) : null,
                'source_id'     => $d->source_id,
            ];
        });

        return Inertia::render('Admin/ItemDisposals/Index', [
            'disposals'  => $disposals,
            'filters'    => $filters,
            'categories' => ItemCategory::select('id', 'name')->get(),
            'branches'   => Branch::select('id', 'name')->get(),
        ]);
    }
}
