<?php

namespace App\Http\Controllers;

use App\Models\TypeOfWork;
use Illuminate\Http\Request;

class TypeOfWorkController extends Controller
{
    public function index()
    {
        $typeofworks = TypeOfWork::all();
        return view('typeofwork.index', compact('typeofworks'));
    }


    public function create()
    {
        return view('typeofwork.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        TypeOfWork::create($request->all());

        return redirect()->route('typeofworks.index')->with('success', 'Type of Work created successfully.');
    }

    public function edit(TypeOfWork $typeofwork)
    {
        return view('typeofwork.edit', compact('typeofwork'));
    }

    public function update(Request $request, TypeOfWork $typeofwork)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $typeofwork->update($request->all());

        return redirect()->route('typeofworks.index')->with('success', 'Type of Work updated successfully.');
    }

    public function destroy(TypeOfWork $typeofwork)
    {
        $typeofwork->delete();

        return redirect()->route('typeofworks.index')->with('success', 'Type of Work deleted successfully.');
    }
}
