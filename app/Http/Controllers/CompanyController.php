<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Company;
use App\Helper\LogHelper;
use App\Models\AssignCamp;
use Illuminate\Http\Request;
use App\Helper\fileManagerHelper;
use App\Models\CompanyAttachement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    //
    public function index()
    {
        $paginate = env('PAGINATE');
        $company = Company::paginate($paginate);
        return response()->json($company, 200);
    }

    public function RaftCompany()
    {
        $raft_type = Type::where('code', 'raft_company')->value('id');
        $companies = Company::where('type_id', $raft_type)->get();
        return response()->json(['data' => $companies], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string',
            'commercial' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string',
            // 'commercial_expire' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|date',
            'owner_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor',
            'license' => 'required_if:type_id,service_provider,raft_company',
        ]);

        if ($validator->fails()) {
            return array('status' => 'false', 'message' => $validator->errors(), 'data' => null, 'code' => 422);
        }
        $company = Company::find(Auth::user()->company_id);
        // $type = Type::where('code', $request->type_id)->value('id');
        $company->update([
            'name' =>  $request->company_name,
            'commercial' => $request->commercial,
            'license' => $request->license,
            // 'commercial_expiration' => $request->commercial_expiration,
            // 'owner_id' => $request->owner_id,
            // 'type_id' => $type,
            'owner_name' => $request->owner_name,
            'owner_hardcopyid' => $request->owner_hardcopyid,

        ]);

        // log section
        $user_id = Auth::user()->id;;
        $old_value = [
            'name' => $request->name,
            'commercial' => $request->commercial,
            // 'license' => $prefix != null ? $prefix . '-' . $request->license : $request->license,
            'owner name' => $request->owner_name,
            // 'kroky' => $kroky == null ? $request->kroky : $kroky,
            'Owner identification number' => $request->owner_hardcopyid,
            // 'prefix' => $prefix == null ? $request->prefix : $prefix,
        ];
        $new_value = [
            'name' => $request->name,
            'commercial' => $request->commercial,
            // 'license' => $prefix != null ? $prefix . '-' . $request->license : $request->license,
            'owner name' => $request->owner_name,
            // 'kroky' => $kroky == null ? $request->kroky : $kroky,
            'Owner identification number' => $request->owner_hardcopyid,
            // 'prefix' => $prefix == null ? $request->prefix : $prefix,
        ];
        $module = 'users';
        $method_id = 2;
        $message = __('logTr.editProfile');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );
        $data['company_id'] = $company->id;
        $data['type'] = '0';

        if ($request->commercial_expiration) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ??????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->commercial_expiration,
                ]);
            }
        }
        if ($request->hasFile('commercial_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ??????????????')->first();
            // dd($attach);
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            $commercial_file = fileManagerHelper::storefile($company->id, $request->commercial_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ??????????????',
                'path' => $commercial_file,
                'expire' => $request->commercial_expiration,
            ]));
        }

        if ($request->classification_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ?????????? ????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->classification_expire,
                ]);
            }
        }

        if ($request->hasFile('classification_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ?????????? ????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            $classification_file = fileManagerHelper::storefile($company->id, $request->classification_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ?????????? ????????',
                'path' => $classification_file,
                'expire' => $request->classification_expire,
            ]));
        }

        // if ($request->commercial_expire) {
        //     $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????????? ????????????')->first();
        // if ($attach != null) {
        //     $attach->update([
        //         'expire' => $request->commercial_expire,
        //     ]);
        // }
        // }

        if ($request->hasFile('national_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????????? ????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }


            $national_file = fileManagerHelper::storefile($company->id, $request->national_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????????? ????????????',
                'path' => $national_file,
                'expire' => null,
            ]));
        }

        if ($request->practice_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ???????????? ????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->practice_expire,
                ]);
            }
        }

        if ($request->hasFile('practice_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ???????????? ????????????')->first();
            if ($attach != null) {

                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }


            $practice_file = fileManagerHelper::storefile($company->id, $request->practice_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ???????????? ????????????',
                'path' => $practice_file,
                'expire' => $request->practice_expire,
            ]));
        }

        if ($request->business_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ???????? ??????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->business_expire,
                ]);
            }
        }

        if ($request->hasFile('business_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ???????? ??????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }
            $business_file = fileManagerHelper::storefile($company->id, $request->business_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  '???????? ???????? ??????????',
                'path' => $business_file,
                'expire' => $request->business_expire,
            ]));
        }


        if ($request->social_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ?????????????????? ????????????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->social_expire,
                ]);
            }
        }

        if ($request->hasFile('social_security')) {
            $social_security = fileManagerHelper::storefile($company->id, $request->social_security, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ?????????????????? ????????????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '???????? ?????????????????? ????????????????????',
                'path' => $social_security,
                'expire' => $request->social_expire,
            ]));
        }


        if ($request->zakat_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ???????????? ????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->zakat_expire,
                ]);
            }
        }

        if ($request->hasFile('zakat_income')) {
            $zakat_income = fileManagerHelper::storefile($company->id, $request->zakat_income, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ???????????? ????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '???????? ???????????? ????????????',
                'path' => $zakat_income,
                'expire' => $request->zakat_expire,
            ]));
        }

        if ($request->saudization_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ??????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->saudization_expire,
                ]);
            }
        }

        if ($request->hasFile('saudization')) {
            $saudization = fileManagerHelper::storefile($company->id, $request->saudization, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ??????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }
            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ??????????????',
                'path' => $saudization,
                'expire' => $request->saudization_expire,
            ]));
        }

        if ($request->chamber_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ???????????? ????????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->chamber_expire,
                ]);
            }
        }

        if ($request->hasFile('chamber_commerce')) {
            $chamber_commerce = fileManagerHelper::storefile($company->id, $request->chamber_commerce, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ???????????? ????????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ???????????? ????????????????',
                'path' => $chamber_commerce,
                'expire' => $request->chamber_expire,
            ]));
        }

        if ($request->tax_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ?????????? ??????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->tax_expire,
                ]);
            }
        }

        if ($request->hasFile('tax_registration')) {
            $tax_registration = fileManagerHelper::storefile($company->id, $request->tax_registration, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ?????????? ??????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ?????????? ??????????????',
                'path' => $tax_registration,
                'expire' => $request->tax_expire,
            ]));
        }

        if ($request->wage_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ?????????? ????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->wage_expire,
                ]);
            }
        }

        if ($request->hasFile('wage_protection')) {
            $wage_protection = fileManagerHelper::storefile($company->id, $request->wage_protection, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ?????????? ????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ?????????? ????????????',
                'path' => $wage_protection,
                'expire' => $request->wage_expire,
            ]));
        }

        if ($request->memorandum_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????? ??????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->memorandum_expire,
                ]);
            }
        }

        if ($request->hasFile('memorandum_association')) {
            $memorandum_association = fileManagerHelper::storefile($company->id, $request->memorandum_association, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????? ??????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }
            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????? ??????????????',
                'path' => $memorandum_association,
                'expire' => $request->memorandum_expire,
            ]));
        }

        if ($request->seasonal_license_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????????? ????????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->seasonal_license_expire,
                ]);
            }
        }

        if ($request->hasFile('seasonal_license')) {
            $seasonal_license = fileManagerHelper::storefile($company->id, $request->seasonal_license, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????????? ????????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '???????????? ????????????????',
                'path' => $seasonal_license,
                'expire' => $request->seasonal_license_expire,
            ]));
        }

        if ($request->hasFile('assign_file')) {
            $assign_file = fileManagerHelper::storefile($company->id, $request->assign_file, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ??????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '???????? ??????????????',
                'path' => $assign_file,
                'expire' => null,
            ]));
        }



        if ($request->delegateid_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->delegateid_expire,
                ]);
            }
        }

        if ($request->hasFile('delegateid')) {
            $delegateid = fileManagerHelper::storefile($company->id, $request->delegateid, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '???????? ????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '???????? ????????????',
                'path' => $delegateid,
                'expire' => null,
            ]));
        }


        if ($request->delegation_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '??????????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->delegation_expire,
                ]);
            }
        }

        if ($request->hasFile('delegation')) {
            $delegation = fileManagerHelper::storefile($company->id, $request->delegation, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '??????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '??????????????',
                'path' => $delegation,
                'expire' => null,
            ]));
        }


        if ($request->hajj_license_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ???????? ????????')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->hajj_license_expire,
                ]);
            }
        }

        if ($request->hasFile('hajj_license')) {
            $hajj_license = fileManagerHelper::storefile($company->id, $request->hajj_license, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ???????? ????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ???????? ????????',
                'path' => $hajj_license,
                'expire' => $request->hajj_license_expire,
            ]));
        }

        if ($request->hasFile('secret_information')) {
            $secret_information = fileManagerHelper::storefile($company->id, $request->secret_information, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', '?????????? ?????????????? ???????? ?????????????????? NDA ???????????? ?????????????????? ???????????????? ???????? ???? ???????????? ????????????????')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  '?????????? ?????????????? ???????? ?????????????????? NDA ???????????? ?????????????????? ???????????????? ???????? ???? ???????????? ????????????????',
                'path' => $secret_information,
                'expire' => null,
            ]));
        }

        return response()->json(["message" => "Company updated successfully"], 200);
    }
}
