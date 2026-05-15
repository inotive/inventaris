<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeePerformanceResource;
use App\Http\Resources\EmployeeHistoryResource;
use App\Models\Employee;
use App\Models\EmployeePerformance;
use App\Actions\Data\EmployeePerformance\CalculateKPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmployeePerformanceController extends Controller
{
    Public function index(Request $request)
    {
        $month = $request->input('month',now()->month);
        $year = $request->input('year', now()->year);
        $search = $request->input('search');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $perPage = $request->input('per_page', 15);
        $reporterId = Auth::id();

        $query = Employee::query() 
            ->with(['branch','department', 'position'])
            ->with(['performances' => function($q) use ($month, $year) {
                $q->where('month', $month)->where('year', $year);
            }])
            ->whereHas('performances', function($q)use ($month,$year,$reporterId){
                $q->where('month', $month)
                ->where('year', $year)
                ->where('reported_by', $reporterId)
                ->where(function($subQ){
                    $subQ->whereIn('category', ['Keterampilan','Kerjasama','Disiplin'])
                    ->orWhere(function($autoQ){
                        $autoQ->whereIn('category', ['Kehadiran','Kuantitas'])
                            ->where('score', 'not like', '%:0}%')
                            ->where('score', 'not like', '%: 0}%');
                    });
                });
            });
             
        if ($branchId) $query->where('branch_id', $branchId);
        if ($departmentId) $query->where('department_id', $departmentId);
        if ($search) $query->where('name', 'like', "%{$search}%");

        $employees = $query->orderBy('name')->paginate($perPage);
        return EmployeeHistoryResource::collection($employees)->additional([
            'meta' => [
                'month' => (int)$month,
                'year' => (int)$year,
            ]
        ]);
        
    }

    public function sync(Request $request, CalculateKPI $calculator)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $result = $calculator->execute(
                $request->month, 
                $request->year, 
                $request->branch_id, 
                null, 
                true  
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'details' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung KPI: ' . $e->getMessage()
            ], 500);
        }
    }


    Public function show(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer',
            'year' => 'required|integer',
            'category' => 'required|in:Keterampilan,Kerjasama,Disiplin',
            'reported_by' => 'nullable|integer|exists:users,id',
        ]);

        $query = EmployeePerformance::where('employee_id', $request->employee_id)
            ->where('category', $request->category)
            ->where('month', $request->month)
            ->where('year', $request->year);

        if ($request->has('reported_by')) {
            $performance = $query->where('reported_by', $request->reported_by)->first();
        } else {
            $myPerformance = clone $query; 
            $performance = $myPerformance->where('reported_by', Auth::id())->first();

            
            if (!$performance) {
                $performance = $query->orderBy('updated_at', 'desc')->first();
            }
        }

        $indicators = $this->getIndicators($request->category);
        $scoreData = $performance?->score ?? [];
        
        if (empty($scoreData) || !is_array($scoreData)) {
            $scoreData = [];
        }

        $detailForm = [];
        foreach ($indicators as $question) {
            $val = $scoreData[$question] ?? null;
            $detailForm[] = [
                'label' => $question,
                'value' => $val,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [   
                'id' => $performance?->id,
                'employee_id' => $request->employee_id,
                'reported_by' => $performance?->reported_by, 
                'is_editable' => ($performance?->reported_by == Auth::id()), 
                'category_display' => $request->category,
                'category_db' => $request->category,
                'form_items' => $detailForm,
                'notes' => $performance?->notes ?? '',
            ]        
        ]);
    }

    Public function store(Request $request){
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'category' => 'required|in:Keterampilan,Kerjasama,Disiplin',
            'score' => 'required|array',
            'notes' => 'nullable|string',
        ]);
    
        // Validasi setiap nilai indikator harus 1-5
        foreach($request->score as $key => $value) {
            if (!is_null($value) && $value !== '') {
                if ($value < 1 || $value > 5) {
                    return response()->json([
                        'success' => false,
                        'message' => "Nilai untuk '{$key}' harus antara 1-5"
                    ], 422);
                }
            }
        }

        $finalScore = $request->score;
        foreach($finalScore as $key =>  $val){
            if(is_null($val)) $finalScore[$key] = 1;
        }
        $reporterId = Auth::id();

        $performance = EmployeePerformance::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'category' => $request->category,
                'month' => $request->month,
                'year' => $request->year,
                'reported_by' => $reporterId,
            ],
            [
                'score' => $finalScore,
                'notes' => $request->notes,
            ]
        );
        return response()->json([
            'success'=>true,
            'message' => 'penilaian berhasil disimpan',
            'data' => $performance,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'month' => 'required',
            'year' => 'required',
        ]);

        $userId = Auth::id();

        $deleted = EmployeePerformance::where('employee_id', $request->employee_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->where('reported_by', $userId)
            ->whereIn('category', ['Keterampilan','Kerjasama','Disiplin'])
            ->delete();

        return response()->json([
            'success' => $deleted ? true : false,
            'message' => $deleted ? 'Data penilaian anda berhasil dihapus' : 'Data tidak ditemukan',
        ]);
    }


    private function getIndicators($category)
    {
        $data=[
            'Keterampilan'=>[
                'Kualitas pekerjaan (rapi, sesuai SOP)',
                'Kecepatan dan hasil kerja',
                'Zero mistake / zero repeat job',
                'Inisiatif menyelesaikan masalah',
                'Produktivitas per jam/per shift',
            ],

            'Kerjasama'=>[
                'Kerjasama tim',
                'Komunikasi',
                'Etika kerja',
                'Tidak menyalahkan orang lain',
                'Tidak menciptakan konflik',
                'Sopan santun terhadap atasan & rekan'
            ],

            'Disiplin'=>[
                'Ide perbaikan',
                'Meningkatkan efisiensi kerja',
                'Membantu tugas tambahan',
                'Kemauan belajar & upgrade skill',
                'Melatih junior/anggota tim',
            ],
        ];
        foreach($data as $key => $val){
            if (strtolower($key) === strtolower($category)){
                return $val;
            }
        }

        return $data[$category] ?? [];
    }
}