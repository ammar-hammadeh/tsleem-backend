<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\Question;
use App\Helper\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\QuestionRequest;

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

        $user_id = Auth::user()->id;
        $old_value = null;
        $new_value = [
            'title' => $question->title,
        ];
        $module = 'question';
        $method_id = 1;
        $message = __('logTr.addQuestion');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );

        return response()->json(['message' => '???? ?????????? ?????????????????? ??????????', 'question' => $question], 200);
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

            $user_id = Auth::user()->id;
            $old_value = [
                'title' => $question->title,
            ];
            $question->update($request->all());
            $question->inputs = $question->inputs;
            $new_value = [
                'title' => $question->title,
            ];
            $module = 'question';
            $method_id = 2;
            $message = __('logTr.updateQuestion');

            LogHelper::storeLog(
                $user_id,
                json_decode(json_encode($old_value)),
                json_decode(json_encode($new_value)),
                $module,
                $method_id,
                $message,
            );
            return response()->json(['message' =>  '???? ?????????? ?????????????????? ??????????', 'question' => $question], 200);
        } else {
            return response()->json(['message' => '???????? ?????? ???????? ???????????? ???? ????????????????'], 404);
        }
    }
    public function destroy($id)
    {
        # code...
        $question = Question::find($id);
        if (!$question) {
            return response()->json(['message' => '???????????? ???????????? ????????????'], 404);
        }
        $user_id = Auth::user()->id;
        $new_value = null;
        $old_value = [
            'title' => $question->title,
        ];
        $module = 'question';
        $method_id = 3;
        $message = __('logTr.deleteQuestion');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );

        $question->delete();
        // $input->delete();
        return response()->json(['message' => '???? ?????? ?????????????????? ??????????'], 200);
    }

    public function filtersQuestion()
    {
        $filters = [
            [
                'name' => 'start',
                'value' => '',
                'label' => '??????????',
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'end',
                'value' => '',
                'label' => '????????????????',
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'question',
                'value' => '',
                'label' => '??????????????????',
                'type' => 'select',
                'items' => Question::all(),
                'itemText' => 'title',
                'itemValue' => 'id'
            ],

        ];
        return $filters;
    }
}
