<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleInspectionRecapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check user role and branch
        $isSuperadmin = $user->hasRole('Superadmin');
        $userBranchId = $user->employee->branch_id ?? null;

        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'desc');

        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);


        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);
        $tab = $request->get('tab', 'inspection');

        // Pisahkan eager loading berdasarkan tab
        $with = ['type'];

        if ($tab === 'inspection') {
            // Tab Rekap Inspeksi: load inspections beserta relasi yang dibutuhkan modal
            $with['inspections'] = function ($iq) use ($year, $month) {
                $iq->with([
                    'checklist.category',
                    'location',
                    'answers',
                ])
                    ->whereYear('submit_date', $year)
                    ->whereMonth('submit_date', $month);
            };
        } elseif ($tab === 'kilometer') {
            // Tab Rekap Kilo Meter: hanya load odometers
            $with['odometers'] = function ($oq) use ($year, $month) {
                // Load odometer untuk bulan ini DAN bulan sebelumnya
                // Agar bisa menampilkan "kilometer terakhir" untuk tanggal yang belum ada data
                $oq->with('creator')
                    ->where(function ($q) use ($year, $month) {
                        // Odometer bulan ini
                        $q->where(function ($subQ) use ($year, $month) {
                            $subQ->whereYear('tanggal', $year)
                                ->whereMonth('tanggal', $month);
                        })
                            // ATAU odometer sebelum bulan ini (untuk fallback)
                            ->orWhere(function ($subQ) use ($year, $month) {
                                $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
                                $subQ->where('tanggal', '<', $firstDayOfMonth);
                            });
                    })
                    // Urutkan ASC supaya "pembacaan terakhir sampai tanggal X" bisa diambil sebagai elemen terakhir
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
            };
        } elseif ($tab === 'service') {
            // Tab Rekap Servis: load services untuk tahun yang dipilih
            $with['services'] = function ($sq) use ($year) {
                $sq->whereYear('date', $year)
                    ->orderBy('date', 'asc');
            };
        }

        $query = Vehicle::with($with)
            ->when(!$isSuperadmin && $userBranchId, function ($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            })
            ->where(function ($q) use ($search) {
                $q->where('license_code', 'like', "%{$search}%")
                    ->orWhereHas('type', function ($typeQ) use ($search) {
                        $typeQ->where('name', 'like', "%{$search}%");
                    });
            });

        if (str_contains($sortBy, '.')) {
            $query->orderByRelation($sortBy, $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $vehicles = $query
            ->paginate($perPage)
            ->withQueryString();

        // Buat daftar tanggal dalam bulan (YYYY-MM-DD)
        // Hanya tampilkan sampai hari ini jika bulan/tahun = bulan/tahun sekarang
        $now = Carbon::now();
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        // Jika bulan/tahun yang dipilih adalah bulan/tahun sekarang, hanya tampilkan sampai hari ini
        if ($year == $now->year && $month == $now->month) {
            $daysInMonth = min($daysInMonth, $now->day);
        }

        $dates = collect(range(1, $daysInMonth))
            ->map(function ($d) use ($year, $month) {
                return sprintf('%04d-%02d-%02d', $year, $month, $d);
            });

        // Transform data berdasarkan tab
        if ($tab === 'inspection') {
            // Tab Rekap Inspeksi: lampirkan foto dan hitung persentase kondisi per inspeksi
            $vehicles->getCollection()->transform(function ($item) {
                // Foto kendaraan
                $item->photo = optional($item->files->where('vehicle_id', $item->id)->last())->file_path;

                // Hitung condition_percentage untuk setiap inspeksi yang akan dipakai di modal
                if ($item->inspections) {
                    $item->inspections = $item->inspections->map(function ($inspection) {
                        $totalAnswers = $inspection->answers ? $inspection->answers->count() : 0;
                        $conditionPercentage = 0;

                        if ($totalAnswers > 0) {
                            $goodAnswers = $inspection->answers->filter(function ($ans) {
                                $answer = strtolower(trim($ans->answer ?? ''));
                                return in_array($answer, ['baik', 'true', 'ya', 'yes', '1', 'ok']);
                            })->count();

                            $conditionPercentage = round(($goodAnswers / $totalAnswers) * 100, 1);
                        }

                        // Tambahkan field ke objek inspeksi
                        $inspection->setAttribute('condition_percentage', $conditionPercentage);

                        return $inspection;
                    });
                }

                return $item;
            });
        } elseif ($tab === 'kilometer') {
            // Tab Rekap Kilo Meter: lampirkan foto + precompute daily_km
            $vehicles->getCollection()->transform(function ($item) use ($dates) {
                $item->photo = optional($item->files->where('vehicle_id', $item->id)->last())->file_path;

                // Susun odometer ASC berdasarkan tanggal, created_at, id
                $odometers = ($item->odometers ?? collect())
                    ->sortBy([['tanggal', 'asc'], ['created_at', 'asc'], ['id', 'asc']])
                    ->values();

                // Cari tanggal odometer pertama di bulan ini (untuk menentukan kapan mulai tampil data)
                $firstOdometerDate = null;
                $currentYearMonth = substr($dates->first(), 0, 7); // e.g. "2026-10"

                foreach ($odometers as $o) {
                    $oDate = substr((string) $o->tanggal, 0, 10);
                    // Hanya ambil odometer yang ada di bulan ini
                    if (str_starts_with($oDate, $currentYearMonth)) {
                        $firstOdometerDate = $oDate;
                        break;
                    }
                }

                // Precompute daily_km: nilai terakhir yang diketahui hingga tanggal tsb
                // Juga track apakah ada perubahan km di tanggal tersebut (untuk highlight)
                $daily = [];
                $dailyUpdated = []; // Track tanggal mana yang ada update
                $lastKm = null;
                $previousKm = null;
                $idx = 0;
                $count = $odometers->count();

                foreach ($dates as $d) {
                    $hasUpdateOnThisDate = false;
                    $previousKm = $lastKm;

                    // Maju pointer odometer selama tanggal <= d
                    while ($idx < $count) {
                        $o = $odometers[$idx];
                        $oDate = substr((string) $o->tanggal, 0, 10);
                        if ($oDate <= $d) {
                            // Jika odometer ini tepat di tanggal $d, tandai ada update
                            if ($oDate === $d) {
                                $hasUpdateOnThisDate = true;
                            }
                            $lastKm = $o->current_km;
                            $idx++;
                        } else {
                            break;
                        }
                    }

                    // Hanya tampilkan data jika:
                    // 1. Ada odometer di bulan ini, DAN
                    // 2. Tanggal >= tanggal odometer pertama di bulan ini
                    if ($firstOdometerDate && $d >= $firstOdometerDate) {
                        $daily[$d] = $lastKm;

                        // Tandai sebagai "updated" jika:
                        // 1. Ada odometer entry di tanggal ini, DAN
                        // 2. Nilai km berubah dari hari sebelumnya (atau hari pertama)
                        if ($hasUpdateOnThisDate && ($previousKm === null || $lastKm != $previousKm)) {
                            $dailyUpdated[$d] = true;
                        } else {
                            $dailyUpdated[$d] = false;
                        }
                    } else {
                        $daily[$d] = null; // Tanggal sebelum data pertama = kosong
                        $dailyUpdated[$d] = false;
                    }
                }

                $item->setAttribute('daily_km', $daily);
                $item->setAttribute('daily_km_updated', $dailyUpdated);
                return $item;
            });
        }

        return Inertia::render('Admin/VehicleInspectionRecaps/Index', compact('vehicles', 'dates', 'month', 'year', 'search', 'tab'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
