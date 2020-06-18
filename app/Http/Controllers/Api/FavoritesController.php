<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Question;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Question $question)
    {
        $question->favorites()->attach(\Auth::id());
        return response()->json(null,204);
    }

    public function destroy(Question $question)
    {
        $question->favorites()->detach(\Auth::id());
        return response()->json(null,204);
    }
}
