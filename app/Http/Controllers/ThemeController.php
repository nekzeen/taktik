<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function toggle(Request $request)
    {
        $theme = $request->user()->theme === 'dark' ? 'light' : 'dark';
        $request->user()->update(['theme' => $theme]);
        
        return redirect()->back()->with('success', 'Thème mis à jour');
    }
}
