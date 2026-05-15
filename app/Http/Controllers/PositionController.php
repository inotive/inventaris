<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Store a newly created position
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:positions,name',
        ], [
            'name.required' => 'Nama position wajib diisi',
            'name.unique' => 'Position dengan nama tersebut sudah ada',
        ]);

        $position = Position::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'id' => $position->id,
            'name' => $position->name,
        ]);
    }

    public function checkExist(Request $request)
    {
        $position = Position::where('name', $request->name)->first();

        if ($position) {
            return response()->json([
                'exists' => true,
                'id' => $position->id,
            ]);
        }

        return response()->json([
            'exists' => false,
        ]);
    }
}

