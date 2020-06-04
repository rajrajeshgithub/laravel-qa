<?php

namespace App\Http\Controllers;

use App\Answer;
use http\Env\Response;
use Illuminate\Http\Request;

class VoteAnswerController extends Controller
{
    public function __construct()
    {
        return $this->middleware('auth');
    }

    public function __invoke(Answer $answer)
    {
        // TODO: Implement __invoke() method.
        $vote = (int) request()->vote;
        $votesCount = auth()->user()->voteAnswer($answer, $vote);

        if(request()->expectsJson()){
            return response()->json([
                'message'=>'Thank for feedback',
                'votesCount' => $votesCount
            ]);
        }
        return back();
    }
}
