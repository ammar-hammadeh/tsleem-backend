<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormTamplateRequest;
use App\Models\FormTamplate;
use App\Models\Question;
use App\Models\TasleemFormAnswers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    //
    public function index()
    {
        // $inquiries = RegisterFormInquiry::with('getInquiries.inputs', 'getTabs')->get();
        $questions = FormTamplate::with('Questions')->get();

        return response()->json($questions);
    }

    public function store(FormTamplateRequest $request)
    {
        $form = FormTamplate::create([
            'name' => $request->name
        ]);
        $questions = Question::find($request->question_ids);
        $form->Questions->sync($questions);

        return response()->json($form);
    }

    public function update(FormTamplateRequest $request, $id)
    {
        $form = FormTamplate::find($id);
        if ($form) {
            $questions = Question::find($request->question_ids);
            $form->Questions->sync($questions);
        } else {
            return response()->json('لايوجد بيانات مطابقة', 200);
        }
    }

    public function destroy($id)
    {
        $question = FormTamplate::find($id);
        if ($question) {
            $question->delete();
            return response()->json(['message' => 'question has been deleted']);
        } else {
            return response()->json(['message' => 'question not deleted']);
        }
    }

    public function FormAnswer(Request $request, $id)
    {
        $answers = $request->answer;

        for ($i = 0; $i < count($answers); $i++)
            TasleemFormAnswers::create([
                'user_id' => Auth::user()->id,
                'assign_camps_id' => $request->assign_camps_id,
                'form_id' => $id,
                'question_id' => $request->question[$i],
                'answer' => $request->answers[$i],
            ]);
    }
}
