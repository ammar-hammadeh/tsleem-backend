<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionRequest;
use App\Models\Input;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionsController extends Controller
{
    public function filters()
    {
        return [
            [
                'name' => 'start',
                'value' => '',
                'label' => __('general.Start'),
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'end',
                'value' => '',
                'label' => __('general.End'),
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'question',
                'value' => '',
                'label' => __('general.question'),
                'type' => 'text',
                'items' => ''
            ],
            [
                'name' => 'input',
                'value' => '',
                'label' => __('general.input type'),
                'type' => 'select',
                'items' => Input::all(),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],

        ];
    }
    public function index(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }
        $filter_input = 1;
        if ($request->input('input'))
            $filter_input .= ' and inputs.id =' . $request->input;
        $where = $this->filterData($request);
        $questions = Question::with('inputs')
            ->whereRaw($where)
            ->whereHas('inputs', function ($query) use ($filter_input) {
                $query->whereRaw($filter_input);
            })
            ->paginate($paginate);
        $data = ['questions' => $questions, 'filters' => $this->filters()];

        if ($request->first) {
            $inputs = Input::all();
            $data['inputs'] = $inputs;
        }
        return response()->json($data, 200);
    }
    private function filterData($request)
    {
        $data = 1;
        if ($request->start) {
            $data .= " and DATE(questions.created_at) >= '" . $request->start . "'";
        }
        if ($request->end) {
            $data .= " and DATE(questions.created_at) <= '" . $request->end . "'";
        }
        if ($request->input('question'))
            $data .= ' and questions.title like "%' . $request->input('question') . '%" ';

        // $data = $date_from . ' and ' . $date_to . ' and ' . $filters_question . 'and' . $filters_question_ar;
        return $data;
    }

    public function getInput()
    {
        return response()->json(['inputs' => Input::all()], 200);
    }
    public function store(QuestionRequest $request)
    {
        # code...
        $question = Question::create($request->all());
        $question->inputs = $question->inputs;
        return response()->json(['message' => 'تم إنشاء الاستفسار بنجاح', 'question' => $question], 200);
    }
    public function edit($id)
    {
        # code...
        $question = Question::find($id);
        $inputs = Input::all();
        return response()->json(['inputs' =>  $inputs, 'question' => $question], 200);
    }
    public function update(QuestionRequest $request, $id)
    {
        # code...
        $question = Question::find($id);
        if ($question  != null) {
            $question->update($request->all());
            $question->inputs = $question->inputs;
            return response()->json(['message' =>  'تم تعديل الاستفسار بنجاح', 'question' => $question], 200);
        } else {
            return response()->json(['message' => 'يوجد خطأ يرجى التأكد من البيانات'], 404);
        }
    }
    public function destroy($id)
    {
        # code...
        $question = Question::find($id);
        if (!$question) {
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
        }
        $question->delete();
        // $input->delete();
        return response()->json(['message' => 'تم حذف الاستفسار بنجاح'], 200);
    }

    public function filtersQuestion()
    {
        $filters = [
            [
                'name' => 'start',
                'value' => '',
                'label' => 'البدء',
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'end',
                'value' => '',
                'label' => 'الانتهاء',
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'question',
                'value' => '',
                'label' => 'الاستفسار',
                'type' => 'select',
                'items' => Question::all(),
                'itemText' => 'title',
                'itemValue' => 'id'
            ],

        ];
        return $filters;
    }
}
