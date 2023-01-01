<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Camp;
use App\Models\Type;
use App\Models\Square;
use App\Models\Company;
use App\Models\FormSign;
use App\Models\Question;
use App\Helper\LogHelper;
use App\Models\AssignCamp;
use App\Models\FormSigner;
use App\Models\FormCategory;
use App\Models\FormTamplate;
use Illuminate\Http\Request;
use App\Models\formsQuestions;
use Modules\Core\Entities\User;
use PhpParser\Node\Expr\Assign;
use App\Models\QuestionCategory;
use App\Helper\fileManagerHelper;
use App\Models\AnswersAttachement;
use App\Models\TasleemFormAnswers;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\AssignRef;
use Alkoumi\LaravelHijriDate\Hijri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\FormTamplateRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

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
                'getCategory.getQuestion.inputs', 'getCategory.getQuestion.Answer.Attachements'
            ])->where('form_id', $request->form_id)->get();
        else
            $questions = formsQuestions::with(['Questions.Answer' => function ($q) use ($request) {
                return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
            }, 'Questions.inputs', 'Questions.Answer.Attachements'])->where('form_id', $request->form_id)->orderBy('id')->get();
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
        ])->withoutDisabled()->get();
        $categories = QuestionCategory::get();
        return response()->json(['questions' => $questions, 'types' => $types, 'categories' => $categories]);
    }

    public function edit($id)
    {
        $questions = Question::with(['inputs'])->get();
        $categories = QuestionCategory::get();
        $types = Type::whereNotIn('code', [
            'raft_office', 'raft_company', 'service_provider'
        ])->withoutDisabled()->get();
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

            $user_id = Auth::user()->id;
            $old_value = null;
            $new_value = [
                'name' => $form->name,
                'body' => $form->body,
            ];
            $module = 'formTemplate';
            $method_id = 1;
            $message = __('logTr.addFormTemplate');

            LogHelper::storeLog(
                $user_id,
                json_decode(json_encode($old_value)),
                json_decode(json_encode($new_value)),
                $module,
                $method_id,
                $message,
            );


            $signers = $request->signers;
            foreach ($signers as $signer) {
                FormSigner::create([
                    'form_id' => $form->id,
                    'type_id' => $signer
                ]);
            }

            $isCategorized = $request->isCategorized;
            $category_ids = $request->category_ids;
            $question_ids = $request->question_ids;
            if ($isCategorized) {
                $categories = QuestionCategory::find($category_ids)
                    ->sortBy(function ($el) use ($category_ids) {
                        return array_search($el->getKey(), $category_ids);
                    });
                $form->Categories()->sync($categories);
            } else {
                $questions = Question::find($request->question_ids)
                    ->sortBy(function ($el) use ($question_ids) {
                        return array_search($el->getKey(), $question_ids);
                    });
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
        // return response()->json(['message' => 'لايمكنك التعديل في الوقت الراهن'], 500);

        $form = FormTamplate::find($id);
        if ($form) {
            $signers = $request->signers;
            try {
                DB::beginTransaction();

                $isCategorized = $request->isCategorized;

                if ($isCategorized) {
                    if ($isCategorized != $form->isCategorized)
                        DB::table('form_questions')->where('form_id', $form->id)->delete();

                    $category_ids = $request->category_ids;
                    $categories = QuestionCategory::find($category_ids)
                        ->sortBy(function ($el) use ($category_ids) {
                            return array_search($el->getKey(), $category_ids);
                        });
                    $form->Categories()->sync($categories);

                    //delete old answers for old questions
                    // $questions = DB::table('question_category_relations')
                    //     ->whereNotIn('question_category_id', $request->category_ids)
                    //     ->pluck('question_id');
                    // TasleemFormAnswers::where('form_id', $form->id)
                    //     ->whereNotIn('question_id', $questions)->delete();
                } else {
                    if ($isCategorized != $form->isCategorized)
                        DB::table('form_categories')->where('form_id', $form->id)->delete();
                    $question_ids = $request->question_ids;
                    $questions = Question::find($request->question_ids)
                        ->sortBy(function ($el) use ($question_ids) {
                            return array_search($el->getKey(), $question_ids);
                        });
                    $form->Questions()->sync($questions);

                    //delete old answers for old questions
                    // TasleemFormAnswers::where('form_id', $form->id)
                    //     ->whereNotIn('question_id', $request->question_ids)->delete();
                }

                if ($request->signers) {
                    FormSigner::where('form_id', $id)->delete();
                    foreach ($signers as $signer) {
                        FormSigner::create([
                            'form_id' => $form->id,
                            'type_id' => $signer
                        ]);
                    }
                    // people who have to sign
                    $countSigner = FormSigner::where('form_id', $id)->count();

                    $signFormsByAssignCamps = FormSign::where('form_id', $id)->groupBy('assign_camps_id')->pluck('assign_camps_id')->toArray(); //6
                    $formTamplate = FormTamplate::pluck('id')->count(); //2

                    foreach ($signFormsByAssignCamps as $signFormsByAssignCamp) {
                        // people who already signed
                        $signFormsByUsers = FormSign::where('assign_camps_id', $signFormsByAssignCamp)->where('form_id', $id)->groupBy('user_id')->pluck('user_id')->count(); //6
                        // formsSigns

                        $formsignes = FormSign::where('assign_camps_id', $signFormsByAssignCamp)->where('form_id', $id)->get();
                        $assigncamps = AssignCamp::find($signFormsByAssignCamp);
                        // update form status to signed
                        if ($countSigner + 1  <= $signFormsByUsers) {
                            foreach ($formsignes as $formsigne) {
                                $formsigne->update([
                                    'form_status' => 'signed'
                                ]);
                            }
                            // signed forms
                            $signFormsByFormId = FormSign::where('assign_camps_id', $signFormsByAssignCamp)->where('form_status', 'signed')->groupBy('form_id')->pluck('form_id')->count(); //2

                            if ($formTamplate == $signFormsByFormId) {
                                $assigncamps->update([
                                    'forms_status' => 'signed',
                                    'status' => 'deliverd'
                                ]);
                            }
                        } else {
                            foreach ($formsignes as $formsigne) {
                                $formsigne->update([
                                    'form_status' => 'unsigned'
                                ]);
                            }
                            // signed forms
                            $signFormsByFormId = FormSign::where('assign_camps_id', $signFormsByAssignCamp)->where('form_status', 'signed')->groupBy('form_id')->pluck('form_id')->count(); //2

                            if ($formTamplate != $signFormsByFormId) {
                                $assigncamps->update([
                                    'forms_status' => 'unsigned',
                                    'status' => 'answered'
                                ]);
                            }
                        }
                    }
                }


                $user_id = Auth::user()->id;
                $old_value = [
                    'name' => $form->name,
                    'body' => $form->body,
                ];
                $form->update([
                    'name' => $request->name,
                    'body' => $request->body,
                    'isCategorized' => $request->isCategorized
                ]);
                $new_value = [
                    'name' => $form->name,
                    'body' => $form->body,
                ];
                $module = 'formTemplate';
                $method_id = 2;
                $message = __('logTr.updateFormTemplate');

                LogHelper::storeLog(
                    $user_id,
                    json_decode(json_encode($old_value)),
                    json_decode(json_encode($new_value)),
                    $module,
                    $method_id,
                    $message,
                );
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
            $user_id = Auth::user()->id;
            $new_value = null;
            $old_value = [
                'name' => $question->name,
                'body' => $question->body,
            ];
            $module = 'formTemplate';
            $method_id = 3;
            $message = __('logTr.deleteFormTemplate');

            LogHelper::storeLog(
                $user_id,
                json_decode(json_encode($old_value)),
                json_decode(json_encode($new_value)),
                $module,
                $method_id,
                $message,
            );

            $question->delete();
            return response()->json(['message' => 'question has been deleted']);
        } else {
            return response()->json(['message' => 'question not deleted']);
        }
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
        $userIDByType = User::where('type_id', $uType->id)->pluck('id')->toArray();
        // $questionCategories = QuestionCategory::get();
        $Fsigns = FormSign::where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id)->whereIn('user_id', $userIDByType)->get();
        $userByType = User::where('type_id', $uType->id)->get();

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
                'getCategory.getQuestion.inputs', 'getCategory.getQuestion.Answer.Attachements'
            ])->where('form_id', $request->form_id)->get();
        else
            $questions = formsQuestions::with(['Questions.Answer' => function ($q) use ($request) {
                return $q->where('assign_camps_id', $request->assign_camps_id)->where('form_id', $request->form_id);
            }, 'Questions.inputs', 'Questions.Answer.Attachements'])
                ->where('form_id', $request->form_id)
                ->orderBy('id', 'desc')->get();

        $signature = FormSign::join('users', 'users.id', 'forms_signs.user_id')
            ->leftjoin('model_has_roles', 'users.id', 'model_has_roles.model_id')
            ->leftjoin('roles', 'model_has_roles.role_id', 'roles.id')
            ->join('types', 'types.id', 'forms_signs.type_id')
            ->select('users.name as username', 'types.name as type_name', 'sign')
            ->where('forms_signs.form_id', $request->form_id)
            ->where('forms_signs.assign_camps_id', $request->assign_camps_id)
            ->select('users.id as user_id', 'users.name as username', 'types.name as type_name', DB::raw("CONCAT(types.name_in_form,' (',roles.name,') ') AS name_in_form"), 'sign', 'roles.name as role_name')
            ->get();

        $sign_type = Type::join('form_signers', 'types.id', 'form_signers.type_id')->where('form_signers.form_id', $request->form_id)->pluck('types.id')->toArray();
        $types = Type::join('form_signers', 'types.id', 'form_signers.type_id')->where('form_id', $request->form_id)->select('types.id', 'types.name as type_name')->get();

        $signatured_type = FormSign::join('users', 'users.id', 'forms_signs.user_id')
            ->join('types', 'types.id', 'forms_signs.type_id')
            ->where('forms_signs.form_id', $request->form_id)
            ->where('forms_signs.assign_camps_id', $request->assign_camps_id)
            ->pluck('types.id')->toArray();

        if (Auth::user()->type_id == Type::where('code', 'admin')->value('id')) {

            $type_need_sign = Type::join('form_signers', 'types.id', 'form_signers.type_id')->with('users', function ($q) {
                $q->where('users.status', 'active');
            })->where('form_signers.form_id', $request->form_id)
                ->whereNotIn('types.id', $signatured_type)->select('types.id', 'types.name as type_name', 'types.name_in_form as name_in_form')
                ->get();
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
        } elseif (in_array(Auth::user()->id, $userIDByType) && count($Fsigns) == 0) {
            $type_need_sign = [];
            $receiver_sign = new StdClass();
            $receiver_sign->id = $uType->id;
            $receiver_sign->type_name = $uType->name;
            $receiver_sign->users = Auth::user();
            $receiver_sign->name_in_form = $uType->name_in_form;
            // $receiver_sign = {
            //     'id' => $uType->id,
            //     'type_name' => $uType->name,
            //     'users' => $userByType,
            //     'name_in_form' => $uType->name_in_form
            // };
            array_push($type_need_sign, $receiver_sign);
            // $type_need_sign = $receiver_sign;
            // $type_need_sign = new Collection($receiver_sign);

        } else {
            // $userByType = User::where('id', Auth::user()->id)->get();
            $type_need_sign = Type::join('form_signers', 'types.id', 'form_signers.type_id')->with('users', function ($q) {
                $q->where('users.status', 'active');
                $q->where('users.id', Auth::user()->id);
            })->where('form_signers.form_id', $request->form_id)
                ->whereNotIn('types.id', $signatured_type)->select('types.id', 'types.name as type_name', 'types.name_in_form as name_in_form')
                ->where('types.id', Auth::user()->type_id)
                ->get();
        }

        $camp = Camp::find($Acamps->camp_id);
        $square = Square::find($Acamps->square_id);
        $output = str_replace('==camp==', $camp->name, $form->body);
        $output = str_replace('==square==', $square->name, $output);
        $output = str_replace('==day==', Hijri::Date('j'), $output);
        $output = str_replace('==month==', Hijri::Date('m'), $output);
        $output = str_replace('==year==', Hijri::Date('Y'), $output);
        $output = str_replace('==date==', Hijri::Date('Y/m/d'), $output);
        $output = str_replace('==company==', $compType->name, $output);
        $output = str_replace('==license==', $compType->license, $output);
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

                            if ($request->note[$i][$y] == 'null') {
                                $note = null;
                            } else $note = $request->note[$i][$y];

                            $tsleem_answer->update([
                                'answer' => $answer,
                                'note' => $note
                            ]);
                        }
                    } else {
                        if ($answers[$i][$y] == 'null') $answer = null;
                        else $answer = $answers[$i][$y];

                        if ($request->note[$i][$y] == 'null') $note = null;
                        else $note = $request->note[$i][$y];

                        $tsleem_answer = TasleemFormAnswers::create([
                            'user_id' => Auth::user()->id,
                            'assign_camps_id' => $request->assign_camps_id,
                            'form_id' => $request->form_id,
                            'question_id' => $request->questions[$i][$y],
                            'answer' => $answer,
                            'note' => $note
                        ]);
                        $q = Question::find($tsleem_answer->question_id);
                        if ($q != null) {
                            if ($q->attachement == 1) {
                                foreach ($request->answer_attach as $attach)
                                    if ($request->hasFile($attach)) {
                                        $att = fileManagerHelper::storefile($answer->id, $request->ownerid_file, 'answers');
                                        AnswersAttachement::create([
                                            'path' => $att,
                                            'answer_id' => $tsleem_answer->id,
                                        ]);
                                    }
                            }
                        }
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

                        if ($request->note[$i] == 'null') {
                            $note = null;
                        } else $note = $request->note[$i];

                        $tsleem_answer->update([
                            'answer' => $answer,
                            'note' => $note
                        ]);
                        $q = Question::find($tsleem_answer->question_id);
                        if ($q != null) {
                            if ($q->attachement == 1) {
                                foreach ($request->answer_attach as $attach)
                                    if ($request->hasFile($attach)) {
                                        $att = fileManagerHelper::storefile($answer->id, $request->ownerid_file, 'answers');
                                        AnswersAttachement::create([
                                            'path' => $att,
                                            'answer_id' => $tsleem_answer->id,
                                        ]);
                                    }
                            }
                        }
                    }
                } else {
                    if ($answers[$i] == 'null') $answer = null;
                    else $answer = $answers[$i];
                    if ($request->note[$i] == 'null') $note = null;
                    else $note = $request->note[$i];

                    $tasleem_answer = TasleemFormAnswers::create([
                        'user_id' => Auth::user()->id,
                        'assign_camps_id' => $request->assign_camps_id,
                        'form_id' => $request->form_id,
                        'question_id' => $request->questions[$i],
                        'answer' => $answer,
                        'note' => $note
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
        return response()->json(['message' => 'تم الحفظ بنجاح'], 200);
    }

    public function UploadAnswerAttach(Request $request)
    {

        // $validator = Validator::make($request->all(), [
        //     'answer_attach' => 'nullable|image|mimes:jpg,png,jpeg',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        // }


        DB::beginTransaction();
        try {
            if ($request->id == "null") {
                $answer = TasleemFormAnswers::create([
                    'user_id' => Auth::user()->id,
                    'assign_camps_id' => $request->assign_camps_id,
                    'form_id' => $request->form_id,
                    'question_id' => $request->question_id,
                ]);
            } else {
                $answer = TasleemFormAnswers::find($request->id);
                if (!$answer) {
                    return response()->json(["message" => "not found"], 500);
                }
            }
            $q = Question::find($request->question_id);
            if ($q != null) {
                $file_array = array();
                foreach ($request->answer_attach as $attach) {
                    $att = $this->storefile($answer->id, $attach, 'answers');
                    if ($att['status']) {
                        // return $att = Storage::disk(env('DISK'))->put('answers/' . $answer->id, $attach, 'public');
                        $file = AnswersAttachement::create([
                            'path' => $att['file'],
                            'answer_id' => $answer->id,
                        ]);
                        array_push($file_array, $file);
                    }
                }
            } else
                return response()->json(["message" => "not found"], 500);
            DB::commit();
            return response()->json(["message" => "uploded successfully", 'files' => $file_array, 'id' => $answer->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["message" => "fail", "error" => $e->getMessage()], 500);
        }
    }
    private function storefile($customer_id, $request_file, $storage)
    {
        try {
            $filename = Storage::disk(env('DISK'))->put(
                $storage . '/' . $customer_id,
                $request_file,
                'public'
            );

            return array('message' => 'success', 'file' => $filename, 'status' => true);
        } catch (\Exception $e) {
            return array('file' => null, 'status' => false, 'message' => $e->getMessage());
        }
    }

    public function DeleteAnswerAttach($id)
    {
        $answerAttach = AnswersAttachement::find($id);
        $image = DB::table('answers_attachement')->find($id);
        if ($answerAttach != null) {
            if (Storage::disk(env('DISK'))->exists($image->path)) {
                Storage::disk(env('DISK'))->delete($image->path);
            }
            $answerAttach->delete();
            return response()->json(['message' => 'attachement deleted succesfully'], 200);
        } else
            return response()->json(['message' => 'data not found'], 500);
    }

    public function SendNotification(Request $request)
    {

        // Owner Notification
        $Acamps = AssignCamp::find($request->assign_camps_id);
        $compType = Company::find($Acamps->receiver_company_id);
        // $uType = Type::find($compType->type_id);
        $owner = User::find($compType->owner_id);
        // $userByType = User::where('type_id', $uType->id)->pluck('id')->toArray();

        // all signers

        $singer = [];
        foreach ($request->form_ids as $form_id) {
            $usersid_need_sign = DB::table('types')->join('form_signers', 'types.id', 'form_signers.type_id')->join('users', 'users.type_id', 'users.id')
                ->where('users.status', 'active')->where('form_signers.form_id', $form_id)->select('users.id as id')->pluck('id')->toArray();

            foreach ($usersid_need_sign as $userid) {
                if (!in_array($userid, $singer))
                    array_push($singer, $userid);
            }
        }
        if ($owner != '') {
            if (!in_array($owner->id, $singer))
                array_push($singer, $owner->id);
        }

        $notificationMessage = "يرجى توقيع محضر التسليم";
        $link = "/appointments/$request->assign_camps_id/form";

        foreach ($singer as $s) {
            (new NotificationController)->addNotification($s, $notificationMessage, $link);
        }

        $Acamps->update([
            'notified' => 1,
            'last_notified' => now()
        ]);
        return response()->json(['message' => 'تم إرسال الإشعارات بنجاح', 'assign_camp' => AssignCamp::find($request->assign_camps_id)], 200);
    }
}
