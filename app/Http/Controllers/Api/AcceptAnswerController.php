<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Answer;
class AcceptAnswerController extends Controller
{
    public function __invoke(Answer $answer)
    {
        // TODO: Implement __invoke() method.
        $this->authorize('accept',$answer);
        $answer->question->acceptBestAnswer($answer);

        return response()->json([
            'message' => 'You have accepted this answer as Best Answer'
        ]);

    }
}
