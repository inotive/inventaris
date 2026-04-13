<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDocumentController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'file' => ['required','file','max:5120'], // 5MB
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $stored = $file->store('uploads/employee-documents', 'public');
            $path = 'storage/' . $stored;
        }

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'title' => $data['title'],
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }
}
