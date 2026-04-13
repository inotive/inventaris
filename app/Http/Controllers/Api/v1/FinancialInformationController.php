<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialInformation;

class FinancialInformationController extends Controller
{
    public function index()
    {
        try {
            $data = FinancialInformation::with(['piutangs', 'bonuses'])->get()->map(function ($item) {

                $totalIncome =
                    floatval($item->basic_salary ?? 0) +
                    floatval($item->performance_allowance ?? 0) +
                    floatval($item->meal_allowance ?? 0) +
                    floatval($item->bpjs_health_allowance ?? 0) +
                    floatval($item->bpjs_employment_allowance ?? 0) +
                    floatval($item->operational_allowance ?? 0) +
                    floatval($item->overtime_allowance ?? 0) +
                    floatval($item->housing_allowance ?? 0) +
                    floatval($item->holiday_allowance ?? 0) +
                    floatval($item->other_allowance ?? 0);

                // Hitung total_deduction jika perlu
                $totalDeduction =
                    floatval($item->loan_deduction ?? 0) +
                    floatval($item->catering_deduction ?? 0) +
                    floatval($item->bpjs_health_deduction ?? 0) +
                    floatval($item->bpjs_employment_deduction ?? 0) +
                    floatval($item->pph21_deduction ?? 0) +
                    floatval($item->cash_advance_deduction ?? 0) +
                    floatval($item->operational_deduction ?? 0) +
                    floatval($item->other_deduction ?? 0);

                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'basic_salary' => $item->basic_salary,
                    'performance_allowance' => $item->performance_allowance,
                    'meal_allowance' => $item->meal_allowance,
                    'bpjs_health_allowance' => $item->bpjs_health_allowance,
                    'bpjs_employment_allowance' => $item->bpjs_employment_allowance,
                    'operational_allowance' => $item->operational_allowance,
                    'overtime_allowance' => $item->overtime_allowance,
                    'housing_allowance' => $item->housing_allowance,
                    'holiday_allowance' => $item->holiday_allowance,
                    'other_allowance' => $item->other_allowance,
                    'total_income' => number_format($totalIncome, 2, '.', ''),
                    'loan_deduction' => $item->loan_deduction,
                    'catering_deduction' => $item->catering_deduction,
                    'bpjs_health_deduction' => $item->bpjs_health_deduction,
                    'bpjs_employment_deduction' => $item->bpjs_employment_deduction,
                    'pph21_deduction' => $item->pph21_deduction,
                    'cash_advance_deduction' => $item->cash_advance_deduction,
                    'operational_deduction' => $item->operational_deduction,
                    'other_deduction' => $item->other_deduction,
                    'total_deduction' => number_format($totalDeduction, 2, '.', ''),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'piutangs' => $item->piutangs,
                    'bonuses' => $item->bonuses,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List informasi keuangan',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $item = FinancialInformation::with(['piutangs', 'bonuses', 'user.employee'])
                ->where('id', $id)
                ->orWhere('user_id', $id)
                ->orWhereHas('user.employee', function ($q) use ($id) {
                    $q->where('id', $id);
                })
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $totalIncome =
                floatval($item->basic_salary ?? 0) +
                floatval($item->performance_allowance ?? 0) +
                floatval($item->meal_allowance ?? 0) +
                floatval($item->bpjs_health_allowance ?? 0) +
                floatval($item->bpjs_employment_allowance ?? 0) +
                floatval($item->operational_allowance ?? 0) +
                floatval($item->overtime_allowance ?? 0) +
                floatval($item->housing_allowance ?? 0) +
                floatval($item->holiday_allowance ?? 0) +
                floatval($item->other_allowance ?? 0);

            $totalDeduction =
                floatval($item->loan_deduction ?? 0) +
                floatval($item->catering_deduction ?? 0) +
                floatval($item->bpjs_health_deduction ?? 0) +
                floatval($item->bpjs_employment_deduction ?? 0) +
                floatval($item->pph21_deduction ?? 0) +
                floatval($item->cash_advance_deduction ?? 0) +
                floatval($item->operational_deduction ?? 0) +
                floatval($item->other_deduction ?? 0);

            $data = [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'basic_salary' => $item->basic_salary,
                'performance_allowance' => $item->performance_allowance,
                'meal_allowance' => $item->meal_allowance,
                'bpjs_health_allowance' => $item->bpjs_health_allowance,
                'bpjs_employment_allowance' => $item->bpjs_employment_allowance,
                'operational_allowance' => $item->operational_allowance,
                'overtime_allowance' => $item->overtime_allowance,
                'housing_allowance' => $item->housing_allowance,
                'holiday_allowance' => $item->holiday_allowance,
                'other_allowance' => $item->other_allowance,
                'total_income' => number_format($totalIncome, 2, '.', ''),
                'loan_deduction' => $item->loan_deduction,
                'catering_deduction' => $item->catering_deduction,
                'bpjs_health_deduction' => $item->bpjs_health_deduction,
                'bpjs_employment_deduction' => $item->bpjs_employment_deduction,
                'pph21_deduction' => $item->pph21_deduction,
                'cash_advance_deduction' => $item->cash_advance_deduction,
                'operational_deduction' => $item->operational_deduction,
                'other_deduction' => $item->other_deduction,
                'total_deduction' => number_format($totalDeduction, 2, '.', ''),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'piutangs' => $item->piutangs,
                'bonuses' => $item->bonuses,
                'employee' => $item->user?->employee, // supaya kelihatan employee-nya
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail informasi keuangan',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
