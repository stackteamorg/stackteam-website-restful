<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\TopBar;
use Illuminate\Http\Request;

class TopBarController extends Controller
{
    public function index()
    {
        return TopBar::latest()->first(); // Return the latest entry
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required',
            'button_name' => 'required',
            'link' => 'required|url'
        ]);

        // Delete existing entry if you only want one active topbar
        TopBar::truncate();

        return TopBar::create($request->all());
    }

    public function update(Request $request, TopBar $topBar)
    {
        $request->validate([
            'content' => 'sometimes|required',
            'button_name' => 'sometimes|required',
            'link' => 'sometimes|required|url'
        ]);

        $topBar->update($request->all());
        return $topBar;
    }

    public function destroy(TopBar $topBar)
    {
        $topBar->delete();
        return response()->noContent();
    }
}
