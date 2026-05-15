<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionGeneralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isDetail=$request->routeIs('*.show.mobile') ;

        return [
            'id' => $this->id,
            'submission_number' => 'GS-'. str_pad($this->id, 5, '0', STR_PAD_LEFT),
            'employee' => [
                'id' => $this->employee_id,
                'name' => $this->employee->user->name ?? $this->employee->name ?? '-',
                'position' => $this->employee->position ?? null,
                'branch_name' => $this->branch->name ?? '-',
            ],
            'branch' => [
                'id' => $this->branch_id,
                'name' => $this->branch->name ?? '-',
            ],
            'title' => $this->title,
            'tag' => $this->tag,
            'description' => $this->when($isDetail, $this->note),
            'status' => $this->status->value ?? $this->status,
            'admin_notes' => $this->admin_notes,
            'approved_by' => $this->approver->name ?? '-',
            'approved_at' => ($this->approved_at ?? $this->updated_at) ? ($this->approved_at ?? $this->updated_at)->format('Y-m-d H:i:s') : null,
            'tanggal_pengajuan' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'approver' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'approved_at' => ($this->approved_at ?? $this->updated_at) ? ($this->approved_at ?? $this->updated_at)->format('Y-m-d H:i:s') : null,
                'note' => $this->admin_notes,
            ] : null,
            'attachments' => $this->when($isDetail,function(){
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_path' => $attachment->file_path,
                        'url' => url('storage/' . $attachment->file_path),
                    ];
                });
            }),
        ];
    }
}
