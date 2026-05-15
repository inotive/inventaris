<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnswerRequest;
use App\Models\Answer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Store or update answers
     */
    public function answer(AnswerRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $answers = $request->validated()['answers'];
            $savedAnswers = [];

            foreach ($answers as $answerData) {
                if ($answerData->answer_id) {
                    // Update existing answer
                    $answer = Answer::findOrFail($answerData->answer_id);
                    $answer->update([
                        'question_id' => $answerData->question_id,
                        'answer' => $answerData->answer,
                    ]);
                    $savedAnswers[] = $answer;
                } else {
                    // Create new answer
                    $answer = Answer::create([
                        'question_id' => $answerData->question_id,
                        'employee_id' => $answerData->employee_id,
                        'answer' => $answerData->answer,
                    ]);
                    $savedAnswers[] = $answer;
                }
            }

            DB::commit();

            return ResponseFormatter::success($savedAnswers, 'Jawaban Berhasil!');
        } catch (\Exception $e) {
            DB::rollback();

            return ResponseFormatter::error('Gagal Jawaban!', 500);
        }
    }
}
