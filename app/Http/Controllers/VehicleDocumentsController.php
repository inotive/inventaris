<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use App\Models\VehicleFilePath;
use App\Models\VehicleDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VehicleDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $data = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'name'            => ['required', 'string', 'max:255'],
            'renewal_date'    => ['required', 'date', 'before:expired_date'],
            'expired_date'    => ['required', 'date', 'after:renewal_date'],
            'files'           => ['required', 'array'],
            'files.*'         => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'vehicle_id.exists' => 'Kendaraan yang dipilih tidak valid.',
            'name.required' => 'Nama dokumen wajib diisi.',
            'name.unique' => 'Nama dokumen sudah digunakan.',
            'renewal_date.required' => 'Tanggal perpanjangan wajib diisi.',
            'renewal_date.date' => 'Format tanggal perpanjangan tidak valid.',
            'renewal_date.before' => 'Tanggal perpanjangan harus sebelum tanggal kadaluarsa.',
            'expired_date.required' => 'Tanggal kadaluarsa wajib diisi.',
            'expired_date.date' => 'Format tanggal kadaluarsa tidak valid.',
            'expired_date.after' => 'Tanggal kadaluarsa harus setelah tanggal perpanjangan.',
            'files.required' => 'File dokumen wajib diunggah.',
            'files.array' => 'File dokumen harus berupa array.',
            'files.*.file' => 'File yang diunggah tidak valid.',
            'files.*.mimes' => 'File harus berformat JPG, JPEG, atau PNG.',
            'files.*.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file_paths = [];

        try {

            // Check for duplicate document name for the same vehicle
            $existingDocument = VehicleDocument::where('vehicle_id', $data['vehicle_id'])
                ->where('name', $data['name'])
                ->first();

            if ($existingDocument) {
                return redirect()->back()
                    ->withErrors(['name' => 'Nama dokumen sudah digunakan untuk kendaraan ini.'])
                    ->withInput();
            }

            DB::transaction(function () use ($data, $request, &$file_paths) {
                $document = VehicleDocument::create([
                    'vehicle_id'            => $data['vehicle_id'],
                    'name'            => $data['name'],
                    'renewal_date'       => $data['renewal_date'] ?? null,
                    'expired_date'     => $data['expired_date'] ?? null,
                ]);

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $file_path = $file->store('vehicle_documents', 'public');
                        $file_paths[] = $file_path;

                        VehicleFilePath::create([
                            'document_id' => $document->id,
                            'file_path'  => $file_path,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Dokumen berhasil disimpan.');
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan dokumen', ['err' => $e->getMessage()]);

            foreach ($file_paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return redirect()->back()->with('error', 'Gagal menyimpan dokumen.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleDocument $vehicleDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleDocument $vehicleDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleDocument $vehicleDocument)
    {
        // Build validation rules dynamically based on what's being updated
        $data = $request->validate([
            'vehicle_id'      => ['required', 'exists:vehicles,id'],
            'name'            => ['required', 'string', 'max:255'],
            'renewal_date'    => ['required', 'date', 'before:expired_date'],
            'expired_date'    => ['required', 'date', 'after:renewal_date'],
            'files'           => ['nullable', 'array'],
            'files.*'         => ['file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'vehicle_id.exists' => 'Kendaraan yang dipilih tidak valid.',
            'name.required' => 'Nama dokumen wajib diisi.',
            'name.unique' => 'Nama dokumen sudah digunakan.',
            'renewal_date.required' => 'Tanggal perpanjangan wajib diisi.',
            'renewal_date.date' => 'Format tanggal perpanjangan tidak valid.',
            'renewal_date.before' => 'Tanggal perpanjangan harus sebelum tanggal kadaluarsa.',
            'expired_date.required' => 'Tanggal kadaluarsa wajib diisi.',
            'expired_date.date' => 'Format tanggal kadaluarsa tidak valid.',
            'expired_date.after' => 'Tanggal kadaluarsa harus setelah tanggal perpanjangan.',
            'files.array' => 'File dokumen harus berupa array.',
            'files.*.file' => 'File yang diunggah tidak valid.',
            'files.*.mimes' => 'File harus berformat JPG, JPEG, atau PNG.',
            'files.*.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file_paths = [];

        try {
            DB::transaction(function () use ($vehicleDocument, $data, $request, &$file_paths) {
                // Only update fields that are provided in the request
                $updateData = [];
                if (isset($data['vehicle_id'])) $updateData['vehicle_id'] = $data['vehicle_id'];
                if (isset($data['name'])) $updateData['name'] = $data['name'];
                if (isset($data['renewal_date'])) $updateData['renewal_date'] = $data['renewal_date'];
                if (isset($data['expired_date'])) $updateData['expired_date'] = $data['expired_date'];

                if (!empty($updateData)) {
                    $vehicleDocument->update($updateData);
                }

                // Only update files if new files are uploaded
                if ($request->hasFile('files')) {
                    // Delete old files
                    $files = $vehicleDocument->files->where('document_id', $vehicleDocument->id);
                    foreach ($files as $file) {
                        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                            Storage::disk('public')->delete($file->file_path);
                        }
                        $file->delete();
                    }

                    // Upload new files
                    foreach ($request->file('files') as $file) {
                        $file_path = $file->store('vehicle_documents', 'public');
                        $file_paths[] = $file_path;

                        VehicleFilePath::create([
                            'document_id' => $vehicleDocument->id,
                            'file_path'  => $file_path,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui dokumen', ['err' => $e->getMessage()]);

            foreach ($file_paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return redirect()->back()->with('error', 'Gagal memperbarui dokumen.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleDocument $vehicleDocument)
    {
        try {
            DB::transaction(function () use ($vehicleDocument) {
                foreach ($vehicleDocument->files as $file) {
                    if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }
                    $file->delete();
                }

                $vehicleDocument->delete();
            });
            return redirect()->back()->with('success', 'Dokumen kendaraan berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus dokumen kendaraan.', ['err' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menghapus dokumen kendaraan.');
        }
    }
}
