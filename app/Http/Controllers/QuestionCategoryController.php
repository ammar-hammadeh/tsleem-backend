<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\QuestionCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\QuestionCategoryRequest;
use App\Models\Category;

class QuestionCategoryController extends Controller
{

    public function filters()
    {

        $filters = [
            [
                'name' => 'name',
                'value' => '',
                'label' => __('general.category name'),
                'type' => 'text',
                'items' => ''
            ],

        ];
        return $filters;
    }


    public function index(Request $request)
    {
        if ($request->has('paginate'))
            $paginate = $request->paginate;
        else
            $paginate = env('PAGINATE');

        $query = QuestionCategory::with('getQuestion');

        if ($request->name != '')
            $query->where('name', $request->name);

        $categories = $query->paginate($paginate);
        return response()->json(['message' => 'Categories got successfully', 'filters' => $this->filters(), 'data' => $categories]);
    }


    public function getCategoryByID($id)
    {
        $category = QuestionCategory::with('getQuestion')->find($id);
        if (!$category)
            return response()->json(['message' => 'عذراً هذا العنصر غير موجود'], 500);

        $unusedQuestion = Question::doesntHave('getCategory')->get();
        $thisFormQuestion = Question::whereHas('getCategory', function ($query) use ($id) {
            $query->where('question_category_id', $id);
        })->get();
        $questions = $unusedQuestion->merge($thisFormQuestion);


        return response()->json(['message' => 'Category got successfully', 'data' => $category, 'questions' => $questions]);
    }

    public function store(QuestionCategoryRequest $request)
    {
        try {
            DB::beginTransaction();
            $question_ids = $request->question_ids;
            $category = QuestionCategory::create(['name' => $request->name]);
            $questions = Question::find($question_ids)->sortBy(function ($el) use ($question_ids) {
                return array_search($el->getKey(), $question_ids);
            });
            $category->getQuestion()->sync($questions);
            DB::commit();
            return response()->json(['message' => 'تمت اللإضافة بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'يرجى المحاولة لاحقاً', 'data' => $e], 500);
        }
    }


    public function update($id, QuestionCategoryRequest $request)
    {
        $category = QuestionCategory::find($id);
        if (!$category)
            return response()->json(['message' => 'عذراً هذا العنصر غير موجود'], 500);

        try {
            DB::beginTransaction();
            $questions = Question::find($request->question_ids);
            $category->getQuestion()->sync($questions);
            $category->update(['name' => $request->name]);
            DB::commit();
            return response()->json(['message' => 'تم التعديل بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'يرجى المحاولة لاحقاً', 'data' => $e], 500);
        }
    }


    public function destroy($id)
    {
        $category = QuestionCategory::find($id);
        if (!$category)
            return response()->json(['message' => 'عذراً هذا العنصر غير موجود'], 500);

        $category->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }


    public function get_data()
    {
        $categories = QuestionCategory::get();
        return response()->json(['categories' => $categories]);
    }


    public function getQuestions()
    {
        $questions = Question::doesntHave('getCategory')->get();
        return response()->json(['questions' => $questions]);
    }
}
