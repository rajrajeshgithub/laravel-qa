<?php

namespace App\Http\Controllers;

use App\Question;
use Illuminate\Http\Request;

class VoteQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function __invoke(Question $question)
    {
        // TODO: Implement __invoke() method.
        $vote = (int) request()->vote;
        auth()->user()->voteQuestion($question, $vote);
        return back();
    }
}
