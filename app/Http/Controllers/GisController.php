<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GisController extends Controller
{
    //

    public function index(Request $request)
    {
        $data = DB::table('camps')
            ->leftjoin('establishment_plots_lookup', 'camps.est_plot_lookup_id', 'establishment_plots_lookup.id')
            ->leftjoin('square', 'camps.square_id', 'square.id')
            ->leftjoin('assign_camps', 'camps.id', 'assign_camps.camp_id')
            ->leftjoin('companies', 'companies.id', 'assign_camps.receiver_company_id')
            ->leftjoin('establishments', 'establishment_plots_lookup.establishment_id', 'establishments.id')
            ->leftjoin('plots', 'establishment_plots_lookup.plot_id', 'plots.id')
            ->leftjoin('zones', 'plots.zone_id', 'zones.id')
            ->leftjoin('locations', 'camps.location_id', 'locations.id')
            ->leftjoin('camp_tents_lookup', 'camps.id', 'camp_tents_lookup.camp_id')
            ->leftjoin('tents', 'tents.id', 'camp_tents_lookup.tent_id')
            ->leftjoin('camp_washroom_lookup', 'camp_washroom_lookup.camp_id', 'camp_washroom_lookup.washroom_id')
            ->leftjoin('washrooms', 'washrooms.id', 'camp_washroom_lookup.washroom_id')
            ->leftjoin('camp_electrical_meter_lookup', 'camp_electrical_meter_lookup.camp_id', 'camps.id')
            ->leftjoin('electrical_meters', 'camp_electrical_meter_lookup.electrical_meter_id', 'electrical_meters.id')
            ->select('plots.plot_number', 'camps.name as label', 'establishments.id as establishment_id', 'establishments.name as establishment_name', 'camps.location_id as location_id', 'locations.name as location_name', 'camps.is_developed', 'square.name as developed_camp_label', 'camps.gate', 'tents.name as tent_type', 'zones.id as zone', 'washrooms.wc_number', 'washrooms.wc_category', 'camps.street as street_name', 'washrooms.located_in_gov_area', 'washrooms.toilets_count', 'washrooms.internal_water_tapes_count', 'washrooms.external_water_tapes_count', 'washrooms.total_water_tapes_count', 'washrooms.seated_toilets_count', 'washrooms.urinal_tapes_count', 'washrooms.showers_count', 'washrooms.upper_water_tank', 'electrical_meters.last_read as electrical_meters_number','electrical_meters.id as electrical_meter_id', 'electrical_meters.subscription_number', 'electrical_meters.metric_capacity');


        // ->select('camps.name as label')->get();

        if ($request->square != '')
            $data->where('square.id', $request->square);
        if ($request->camp != '')
            $data->where('camps.id', $request->camp);
        if ($request->camp_status != '')
            $data->where('camps.status', $request->camp_status);
        if ($request->electrical_meters_number != '')
            $data->where('electrical_meters.last_read', $request->electrical_meters_number);
        if ($request->company != '')
            $data->where('companies.id', $request->company);


        $data = $data->get();
        return response()->json(['message' => 'data found successfuly', 'data' => $data], 200);
    }

    public function CheckToken()
    {
        $user_check = Auth::check();
        if ($user_check) {
            $user = Auth::user();
            if ($user->hasPermissionTo('gis-map-index'))
                return true;
            else
                return false;
        } else
            return false;
    }

}
