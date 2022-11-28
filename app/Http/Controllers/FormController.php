<?php

namespace App\Http\Controllers;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Http\Requests\FormTamplateRequest;
use App\Models\AssignCamp;
use App\Models\Camp;
use App\Models\Company;
use App\Models\FormCategory;
use App\Models\FormSign;
use App\Models\FormSigner;
use App\Models\formsQuestions;
use App\Models\FormTamplate;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Square;
use App\Models\TasleemFormAnswers;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Entities\User;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;

class FormController extends Controller
{
    //
    public function forms(Request $request)
    {

        if ($request->assign_camps_id != null) {
            $forms = FormTamplate::
                // leftjoin('forms_signs', 'form_tamplates.id', 'forms_signs.form_id')
                leftjoin('forms_signs', function ($join) use ($request) {
                    $join->on('form_tamplates.id', '=', 'forms_signs.form_id');
                    $join->on('forms_signs.assign_camps_id', '=', DB::raw($request->assign_camps_id));
                })
                // ->leftjoin('assign_camps', 'assign_camps.id', 'forms_signs.assign_camps_id')
                // ->where('forms_signs.assign_camps_id',$request->assign_camps_id)

                ->select('form_tamplates.id', 'form_tamplates.name', DB::Raw('IFNULL(forms_signs.form_status,"unsigned") as forms_status'))
                ->groupBy('forms_signs.form_id', 'form_tamplates.id', 'form_tamplates.name', 'forms_status')
                ->get();
            $assign_camp = AssignCamp::find($request->assign_camps_id);
            $answered = 0;
            if ($assign_camp->status == 'answered' || $assign_camp->status == 'deliverd')
                $answered = 1;
            return response()->json(['forms' => $forms, 'assign_camp' => $assign_camp, 'answered' => $answered]);
        }
        $forms = FormTamplate::get();
        return response()->json(['forms' => $forms]);
    }
    public function index()
    {
        // $inquiries = RegisterFormInquiry::with('getInquiries.inputs', 'getTabs')->get();
        $forms = FormTamplate::with('Categories.getQuestion', 'Questions', 'Signers.Types')->get();
        return response()->json(['forms' => $forms]);
    }

    public function questionsByFrom($id)
    {
        // form with un-answered questions.
        $form_answers = TasleemFormAnswers::where('form_id', $id)->pluck('question_id')->toArray();
        $form = FormTamplate::with(['Questions' => function ($q) use ($form_answers) {
            return $q->whereNotIn('questions.id', $form_answers)->with('inputs');
        }])->find($id);
        if ($form != null)
            return response()->json(['data' => $form], 200);
        else
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
    }

    public function QuestionsWithAnswerIds(Request $request)
    {
        // $question = formsQuestions::leftjoin('tasleem_form_answers','tasleem_form_answers.question_id','form_questions.question_id')
        // ->where('tasleem_form_answers.form_id',$request->form_id)
        // ->where('tasleem_form_answers.assign_camps_id',$request->assign_camps_id)
        // ->select('form_questions.question_id','tasleem_form_answers.question_id as answerd_id')
        // ->get();

        // $question = formsQuestions::with(['Questions.Answer' => function ($q) use ($request) {
        //     return $q->where('assign_camps_id', $request->assign_camps_id)
        //         ->where('form_id', $request->form_id);
        // }, 'Questions.inputs'])
        //     ->where('form_id', $request->form_id)->get();
        $form = FormTamplate::find($request->form_id);
        // $isCategorized = $form->isCategorized;
        //yaser changes
        $questions = [];
        $categories = [];
        $isCategorized = $form->isCategorized;
        if ($isCategorized)
            $categories = FormCategory::with([
                'getCategory.getQuestion.Answer' => function ($q) use ($request) {
                    return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
                },
                'getCategory.getQuestion.inputs'
            ])->where('form_id', $request->form_id)->get();
        else
            $questions = formsQuestions::with(['Questions.Answer' => function ($q) use ($request) {
                return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
            }, 'Questions.inputs'])->where('form_id', $request->form_id)->get();
        // $question = formsQuestions::with(['Questions.Answer' => function ($q) use ($request) {
        //     return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
        // }, 'Questions.inputs'])->where('form_id', $request->form_id)->get();

        return response()->json([
            'questions' => $questions,
            'categories' => $categories,
            'form' => $form,
            'isCategorized' => $isCategorized
        ]);
    }

    public function FormWithAnswerdQ(Request $request)
    {
        // form with answered questions.

        // $form_answers = TasleemFormAnswers::where('form_id', $request->form_id)->where('assign_camps_id',$request->assign_camps_id)->pluck('question_id')->toArray();
        // $form = FormTamplate::with(['Questions.inputs' => function ($q) use ($form_answers) {
        //     return $q->whereIn('id', $form_answers);
        // }])->find($request->form_id);

        $form = FormTamplate::join('tasleem_form_answers', 'form_tamplates.id', 'tasleem_form_answers.form_id')
            ->join('questions', 'tasleem_form_answers.question_id', 'questions.id')
            ->join('inputs', 'questions.input_id', 'inputs.id')
            ->join('assign_camps', 'tasleem_form_answers.assign_camps_id', 'assign_camps.id')
            ->leftjoin('forms_signs', 'form_tamplates.id', 'forms_signs.form_id')
            ->select('questions.title as question', 'tasleem_form_answers.answer', 'inputs.type')
            ->where('tasleem_form_answers.form_id', $request->form_id)->where('tasleem_form_answers.assign_camps_id', $request->assign_camps_id)
            ->groupBy('question', 'answer', 'inputs.type')
            ->get();

        if ($form != null)
            return response()->json(['data' => $form], 200);
        else
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
    }

    public function get_data()
    {
        $questions = Question::with(['inputs'])->get();
        $types = Type::whereNotIn('code', [
            'raft_office', 'raft_company', 'service_provider'
        ])->get();
        $categories = QuestionCategory::get();
        return response()->json(['questions' => $questions, 'types' => $types, 'categories' => $categories]);
    }

    public function edit($id)
    {
        $questions = Question::with(['inputs'])->get();
        $categories = QuestionCategory::get();
        $types = Type::whereNotIn('code', [
            'raft_office', 'raft_company', 'service_provider'
        ])->get();
        $form = FormTamplate::find($id);
        if ($form != null) {
            $isCategorized = $form->isCategorized;
            if ($isCategorized)
                $form = FormTamplate::with('Categories', 'Signers.Types')->find($id);
            else
                $form = FormTamplate::with('Questions', 'Signers.Types')->find($id);
            return response()->json(['questions' => $questions, 'types' => $types, 'form' => $form, 'categories' => $categories], 200);
        } else
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
    }

    public function store(FormTamplateRequest $request)
    {
        try {
            DB::beginTransaction();
            $form = FormTamplate::create([
                'name' => $request->name,
                'body' => $request->body,
                'isCategorized' => $request->isCategorized
            ]);

            $signers = $request->signers;
            foreach ($signers as $signer) {
                FormSigner::create([
                    'form_id' => $form->id,
                    'type_id' => $signer
                ]);
            }

            $isCategorized = $request->isCategorized;

            if ($isCategorized) {
                $categories = QuestionCategory::find($request->category_ids);
                $form->Categories()->sync($categories);
            } else {
                $questions = Question::find($request->question_ids);
                $form->Questions()->sync($questions);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ، يرجى المحاولة لاحقاً', 'error', $e->getMessage()], 500);
        }
        return response()->json(['message' => 'تم الحفظ', 'data' => $form]);
    }

    public function update(FormTamplateRequest $request, $id)
    {
        $form = FormTamplate::find($id);
        if ($form) {
            $signers = $request->signers;
            try {
                DB::beginTransaction();

                $isCategorized = $request->isCategorized;

                if ($isCategorized) {
                    if ($isCategorized != $form->isCategorized)
                        DB::table('form_questions')->where('form_id', $form->id)->delete();
                    $categories = QuestionCategory::find($request->category_ids);
                    $form->Categories()->sync($categories);
                } else {
                    if ($isCategorized != $form->isCategorized)
                        DB::table('form_categories')->where('form_id', $form->id)->delete();
                    $questions = Question::find($request->question_ids);
                    $form->Questions()->sync($questions);
                }

                FormSigner::where('form_id', $id)->delete();
                foreach ($signers as $signer) {
                    FormSigner::create([
                        'form_id' => $form->id,
                        'type_id' => $signer
                    ]);
                }
                $form->update([
                    'name' => $request->name,
                    'body' => $request->body,
                    'isCategorized' => $request->isCategorized
                ]);
                DB::commit();
                return response()->json(['message' => 'تم تعديل البيانات بنجاح'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'حدث خطأ، يرجى المحاولة لاحقاً'], 500);
            }
        } else {
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 401);
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
                'assign_camps_id' => $request->assign_camps_id[$i],
                'form_id' => $id,
                'question_id' => $request->question[$i],
                'answer' => $request->answer[$i],
            ]);

        return response()->json(['message' => 'submitted successfully']);
    }

    public function SignForm(Request $request)
    {
        $FormSigncheck = FormSign::where('assign_camps_id', $request->assign_camps_id)
            ->where('form_id', $request->form_id)
            ->where('user_id', $request->user_id)->first();
        if ($FormSigncheck != null) {
            return response()->json(['message' => 'المستخدم موقع مسبقاً'], 500);
        }

        $signer = FormSigner::where('form_id', $request->form_id)->count(); //3
        $formTamplate = FormTamplate::pluck('id')->count(); //2
        $signFormsByFormId = FormSign::where('assign_camps_id', $request->assign_camps_id)->groupBy('form_id')->pluck('form_id')->count(); //2
        $signFormsByUsers = FormSign::where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id)->groupBy('user_id')->pluck('user_id')->count(); //6
        // return response()->json(['1' => $formTamplate, '2' => $signFormsByFormId, '3' => $signer + 1, '4' => $signFormsByUsers]);
        if ($formTamplate == $signFormsByFormId && $signer + 1   == $signFormsByUsers) {
            return response()->json(['message' => 'المحضر موقع مسبقاً'], 500);
        }

        $signForm = FormSign::create([
            'assign_camps_id' => $request->assign_camps_id,
            'form_id' => $request->form_id,
            'user_id' => $request->user_id,
            'type_id' => User::find($request->user_id)->type_id,
        ]);
        // Signature Section
        if ($request->signature) {
            if (Auth::guard()->user()->signature != null) {
                if (file_exists(public_path('storage/' . Auth::guard()->user()->signature))) {
                    unlink(public_path('storage/' . Auth::guard()->user()->signature));
                }
            }
            // $user = User::find($request->user_id);
            $image_parts = explode(';base64,', $request->signature);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $fileName =  uniqid() . '.' . $image_type;

            if (env('DISK') == 's3')
                $name = Storage::disk(env('DISK'))->put(
                    'signatures/' . $fileName,
                    $image_base64,
                    'public'
                );
            else
                Storage::put('signatures/' . $fileName, $image_base64);

            // Storage::put('public/signatures/' . $fileName, $image_base64);

            $signature = 'signatures/' . $fileName;
            $data['sign'] =  $signature;
            $signForm->update($data);
        }

        $signer = FormSigner::where('form_id', $request->form_id)->count(); //3

        $signFormsByFormId = FormSign::where('assign_camps_id', $request->assign_camps_id)->groupBy('form_id')->pluck('form_id')->count(); //2

        $signFormsByUsers = FormSign::where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id)->groupBy('user_id')->pluck('user_id')->count(); //6

        $formTamplate = FormTamplate::pluck('id')->count(); //2

        $user_signature = FormSign::join('users', 'users.id', 'forms_signs.user_id')
            ->join('types', 'types.id', 'forms_signs.type_id')
            ->select('users.name as username', 'types.name as type_name', 'sign')
            // ->where('forms_signs.form_id', $request->form_id)
            // ->where('forms_signs.assign_camps_id', $request->assign_camps_id)
            ->where('forms_signs.id', $signForm->id)
            ->first();

        // return response()->json(['1' => $signer + 1, '2' => $signFormsByFormId, '3' => $signFormsByUsers]);

        if ($signer + 1  == $signFormsByUsers) {
            $formsignes = FormSign::where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id)->get();
            foreach ($formsignes as $formsigne) {
                $formsigne->update([
                    'form_status' => 'signed'
                ]);
            }
        }
        if ($formTamplate == $signFormsByFormId && $signer + 1  == $signFormsByUsers) {
            $assigncamps = AssignCamp::find($request->assign_camps_id);
            $assigncamps->update([
                'forms_status' => 'signed',
                'status' => 'deliverd'
            ]);
            return response()->json(['message' => 'تم توقيع محضر التسليم بالكامل', 'user_signature' => $user_signature], 200);
        }
        return response()->json(['message' => 'تم توقيع هذا الملف', 'user_signature' => $user_signature], 200);
    }

    public function AllotmentNeedSign(Request $request)
    {
        // $assign_camps = AssignCamp::where('forms_status', 'unsigned')->pluck('id')->toArray();
        // $signFomrs = FormSign::whereIn('assign_camps_id', $assign_camps)->groupBy('form_id')->pluck('form_id')->toArray();
        // $forms = FormTamplate::whereIn('id', $signFomrs)->with('Questions.inputs')->get();
        // // $type = Type::find(Auth::user()->type_id);
        // return response()->json(['data' => $forms]);
        $type = Type::find(Auth::user()->type_id);
        if ($type->code != "admin") {
            return response()->json(['message' => 'يجب أن تكون مسؤول لتسطيع الدخول إلى هذه الصفحة'], 403);
        }

        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        } else
            $paginate = env('PAGINATE');

        $assignedCampsID =  AssignCamp::query();

        //check user type
        // if (Auth::guard('api')->check()) {
        //     $userType = Type::where('id', Auth::user()->type_id)->value('code');
        //     // $userCompany = Company::where('id', Auth::user()->company_id)->value('license');
        //     if ($userType != 'admin' && $userType != 'raft_company') {
        //         $assignedCampsID->where('receiver_company_id', Auth::user()->company_id);
        //     } elseif ($userType == 'raft_company') {
        //         // dd();
        //         $assignedCampsID->where('assigner_company_id', Auth::user()->company_id);
        //     }
        // }

        if ($request->start != '')
            $assignedCampsID->whereDate('created_at', '>=', $request->start);
        if ($request->end != '')
            $assignedCampsID->whereDate('created_at', '<=', $request->end);
        if ($request->status != '')
            $assignedCampsID->where('status', $request->status);
        if ($request->receiver_company_id != '') {
            $company_id = $request->receiver_company_id;
            $assignedCampsID->whereHas('getCompany', function ($query) use ($company_id) {
                $query->where('id', $company_id);
            });
        } else
            $assignedCampsID->with('getCompany.Type');

        if ($request->square != '') {
            $square_id = $request->square;
            $assignedCampsID->whereHas('getSquare', function ($query) use ($square_id) {
                $query->where('id', $square_id);
            });
        } else
            $assignedCampsID->with('getSquare');

        if ($request->camp != '') {
            $camp_id = $request->camp;
            $assignedCampsID->whereHas('getCamp', function ($query) use ($camp_id) {
                $query->where('id', $camp_id);
            });
        } else
            $assignedCampsID->with('getCamp');

        $assignedCampsID = $assignedCampsID->where('forms_status', 'unsigned')->paginate($paginate);

        // $assign_camps = AssignCamp::where('forms_status','unsigned')->get();
        return response()->json(['data' => $assignedCampsID]);
    }

    public function FormDetails(Request $request)
    {

        // Type of assign_camps
        $Acamps = AssignCamp::find($request->assign_camps_id);
        $compType = Company::find($Acamps->receiver_company_id);
        $uType = Type::find($compType->type_id);
        $userByType = User::where('type_id', $uType->id)->get();
        $userIDByType = User::where('type_id', $uType->id)->pluck('id')->toArray();
        // $questionCategories = QuestionCategory::get();
        $Fsigns = FormSign::where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id)->whereIn('user_id', $userIDByType)->get();
        // return response()->json($Fsigns);
        //

        $form = FormTamplate::find($request->form_id);

        $questions = [];
        $categories = [];
        //yaser changes
        $isCategorized = $form->isCategorized;
        if ($isCategorized)
            // $categories = FormCategory::with([
            //     'getCategory.getQuestion.Questions.Answer' => function ($q) use ($request) {
            //         return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
            //     },
            //     'getCategory.getQuestion.Questions.inputs'
            // ])->where('form_id', $request->form_id)->get();
            $categories = FormCategory::with([
                'getCategory.getQuestion.Answer' => function ($q) use ($request) {
                    return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
                },
                'getCategory.getQuestion.inputs'
            ])->where('form_id', $request->form_id)->get();
        else
            $questions = formsQuestions::with(['Questions.Answer' => function ($q) use ($request) {
                return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
            }, 'Questions.inputs'])->where('form_id', $request->form_id)->get();

        $signature = FormSign::join('users', 'users.id', 'forms_signs.user_id')
            ->join('types', 'types.id', 'forms_signs.type_id')
            ->select('users.name as username', 'types.name as type_name', 'sign')
            ->where('forms_signs.form_id', $request->form_id)
            ->where('forms_signs.assign_camps_id', $request->assign_camps_id)
            ->select('users.id as user_id', 'users.name as username', 'types.name as type_name', 'types.name_in_form as name_in_form', 'sign')
            ->get();

        $sign_type = Type::join('form_signers', 'types.id', 'form_signers.type_id')->where('form_signers.form_id', $request->form_id)->pluck('types.id')->toArray();
        $types = Type::join('form_signers', 'types.id', 'form_signers.type_id')->where('form_id', $request->form_id)->select('types.id', 'types.name as type_name')->get();

        $signatured_type = FormSign::join('users', 'users.id', 'forms_signs.user_id')
            ->join('types', 'types.id', 'forms_signs.type_id')
            ->where('forms_signs.form_id', $request->form_id)
            ->where('forms_signs.assign_camps_id', $request->assign_camps_id)
            ->pluck('types.id')->toArray();

        if (Auth::user()->type_id == Type::where('code', 'admin')->value('id')) {
            $type_need_sign = Type::join('form_signers', 'types.id', 'form_signers.type_id')->with('users')->where('form_signers.form_id', $request->form_id)
                ->whereNotIn('types.id', $signatured_type)->select('types.id', 'types.name as type_name', 'types.name_in_form as name_in_form')
                ->get();
        } else {
            $type_need_sign = Type::join('form_signers', 'types.id', 'form_signers.type_id')->with('users')->where('form_signers.form_id', $request->form_id)
                ->whereNotIn('types.id', $signatured_type)->select('types.id', 'types.name as type_name', 'types.name_in_form as name_in_form')
                ->where('types.id', Auth::user()->type_id)
                ->get();
        }
        if (count($Fsigns) == 0) {
            // return response()->json($Fsigns);
            $receiver_sign = [
                'id' => $uType->id,
                'type_name' => $uType->name,
                'users' => $userByType,
                'name_in_form' => $uType->name_in_form
            ];
            $type_need_sign->push($receiver_sign);
        }
        $camp = Camp::find($Acamps->camp_id);
        $square = Square::find($Acamps->square_id);
        $output = str_replace('==camp==', $camp->name, $form->body);
        $output = str_replace('==square==', $square->name, $output);
        $output = str_replace('==day==', Hijri::Date('j'), $output);
        $output = str_replace('==month==', Hijri::Date('m'), $output);
        $output = str_replace('==year==', Hijri::Date('Y'), $output);
        $output = str_replace('==date==', Hijri::Date('Y/m/d'), $output);
        // return response()->json([$form->body]);

        $users =  Type::join('form_signers', 'types.id', 'form_signers.type_id')->with('users')->get();
        if ($form != null)
            return response()->json([
                'name' => $form->name,
                'body' => $output,
                'questions' => $questions,
                'categories' => $categories,
                'signature' => $signature,
                'users' => $users,
                'types' => $types,
                'type_count' => count($sign_type),
                'type_need_sign' => $type_need_sign,
                'isCategorized' => $isCategorized
            ], 200);
        else
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
    }

    public function FormUpdateAnswer(Request $request)
    {
        //     $questions_by_form = formsQuestions::join('questions', 'questions.id', 'form_questions.question_id')
        //     ->where('form_id',$request->form_id)->pluck('questions.id')->toArray();

        //     $answers = TasleemFormAnswers::where('assign_camps_id',$request->assign_camps_id)->where('form_id',$request->form_id)->whereIn('question_id')
        //     // ->pluck('question_id')
        //     ->toArray();

        //     $questions = $request->questions;
        //     foreach($questions_by_form as $id){

        //         if(in_array($id,$questions)){
        //             $formAnswer = TasleemFormAnswers::where('assign_camps_id',$request->assign_camps_id)->where('form_id',$request->form_id)->where('question_id',$id)->first();

        //             $formAnswer->update([
        //                 'answer'=>$request->answer
        //             ]);
        //         }


        //     }

        // $questions_by_form = formsQuestions::join('questions', 'questions.id', 'form_questions.question_id')
        //     ->where('form_id', $request->form_id)->pluck('questions.id')->toArray();

        $form = FormTamplate::find($request->form_id);

        $answers = $request->answer;
        $ids = $request->ids;
        $answers;
        if ($form->isCategorized) {
            for ($i = 0; $i < count($answers); $i++) {
                for ($y = 0; $y < count($answers[$i]); $y++) {
                    if ($ids[$i][$y] != 'null') {
                        $tsleem_answer = TasleemFormAnswers::find($ids[$i][$y]);
                        if ($tsleem_answer != null) {
                            if ($answers[$i][$y] == 'null') {
                                $answer = null;
                            } else $answer = $answers[$i][$y];
                            $tsleem_answer->update([
                                'answer' => $answer
                            ]);
                        }
                    } else {
                        if ($answers[$i][$y] == 'null') $answer = null;
                        else $answer = $answers[$i][$y];
                        TasleemFormAnswers::create([
                            'user_id' => Auth::user()->id,
                            'assign_camps_id' => $request->assign_camps_id,
                            'form_id' => $request->form_id,
                            'question_id' => $request->questions[$i][$y],
                            'answer' => $answer,
                        ]);
                    }
                }
            }

            $tasleem_from_answer = TasleemFormAnswers::where('assign_camps_id', $request->assign_camps_id)->groupby('form_id')->pluck('form_id')->count();
            $assign_camp = AssignCamp::find($request->assign_camps_id);
            $form_tamplate = FormTamplate::count();
            if ($tasleem_from_answer == $form_tamplate) {
                $assign_camp->update([
                    'status' => 'answered'
                ]);
            }
        } else {

            $answers = $request->answer;
            $ids = $request->ids;
            for ($i = 0; $i < count($answers); $i++) {
                if ($ids[$i] != 'null') {
                    $tsleem_answer = TasleemFormAnswers::find($ids[$i]);
                    if ($tsleem_answer != null) {
                        if ($answers[$i] == 'null') {
                            $answer = null;
                        } else $answer = $answers[$i];
                        $tsleem_answer->update([
                            'answer' => $answer
                        ]);
                    }
                } else {
                    if ($answers[$i] == 'null') $answer = null;
                    else $answer = $answers[$i];
                    TasleemFormAnswers::create([
                        'user_id' => Auth::user()->id,
                        'assign_camps_id' => $request->assign_camps_id,
                        'form_id' => $request->form_id,
                        'question_id' => $request->questions[$i],
                        'answer' => $answer,
                    ]);
                }
            }

            $tasleem_from_answer = TasleemFormAnswers::where('assign_camps_id', $request->assign_camps_id)->groupby('form_id')->pluck('form_id')->count();
            $assign_camp = AssignCamp::find($request->assign_camps_id);
            $form_tamplate = FormTamplate::count();
            if ($tasleem_from_answer == $form_tamplate) {
                $assign_camp->update([
                    'status' => 'answered'
                ]);
            }
        }
        // return response()->json(['a'=> $tasleem_from_answer,'b'=>$form_tamplate]);
        return response()->json(['message' => 'تم الحفظ بنجاح'], 200);
    }
}
