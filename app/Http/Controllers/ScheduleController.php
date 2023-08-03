<?php

namespace App\Http\Controllers;

use App\Models\CriteriaSchedule;
use App\Models\FerryCriteria;
use App\Models\Schedule;
use App\Models\ShipType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{

    public function index()
    {
        $schedules = Schedule::all([
            'id',
            'construction_name',
            'completion_date',
        ]);

        $shipTypes = ShipType::all();

        return view('jadwal')
            ->with('schedules', $schedules)
            ->with('shipTypes', $shipTypes);
    }

    public function storeShipConstructionSchedule(Request $request)
    {
        $newScheduleData = $request->validate([
            'ship_type_id' => 'required',
            'construction_name' => 'required',
            'working_hours' => 'required|numeric',
            'start_date' => 'required|date',
            'completion_date' => 'required|date',
        ]);

        $newSchedule = Schedule::create($newScheduleData);

        if ($newSchedule->exists) {
            return redirect()->back()->with('success', 'Jadwal berhasil dibuat!');
        } else {
            return redirect()->back()->with('failed', 'Jadwal gagal dibuat!');
        }
    }

    public function showScheduleDataById(int $id)
    {
        $currentDate = (string) Carbon::now()->format('Y-m-d');
        $schedule = Schedule::find($id);

        $criterias = FerryCriteria::all();

        $existingScheduleCriterias = DB::table('criteria_schedules')
            ->select('criteria_id')
            ->where('schedule_id', '=', $id);

        $criteriaSchedulesAfter = DB::table('ferry_criterias')
            ->select()
            ->whereIn('id', $existingScheduleCriterias)
            ->orderBy('id', 'desc')
            ->get();

        $criteriaSchedulesBefore = DB::table('ferry_criterias')
            ->select()
            ->whereNotIn('id', $existingScheduleCriterias)
            ->get();

        $criteriaSchedules = CriteriaSchedule::where('schedule_id', $id)
            ->get();

        $nearestCriteriaSchedule = DB::table('criteria_schedules')
            ->join('ferry_criterias', 'criteria_schedules.criteria_id', '=', 'ferry_criterias.id')
            ->select('ferry_criterias.criteria', 'criteria_schedules.*')
            ->whereRaw("DATEDIFF(\"$currentDate\", criteria_schedules.start_date) <= ?", [2])
            ->where('schedule_id', $id)
            ->where('is_finished', false)
            ->first();

        $ongoingCriteriaSchedule = DB::table('ongoing_criteria_schedules')
            ->join('ferry_criterias', 'ongoing_criteria_schedules.criteria_id', '=', 'ferry_criterias.id')
            ->select(['ferry_criterias.criteria', 'ongoing_criteria_schedules.*'])
            ->where('schedule_id', $id)
            ->where('start_date', '<=', $currentDate)
            ->first();

        $finishedCriteriaSchedules = DB::table('finished_criteria_schedules')
            ->join('ferry_criterias', 'finished_criteria_schedules.criteria_id', '=', 'ferry_criterias.id')
            ->select(['finished_criteria_schedules.*', 'ferry_criterias.criteria'])
            ->where('schedule_id', $id)
            ->get();

        return view('progress-jadwal')
            ->with('schedule', $schedule)
            ->with('criterias', $criterias)
            ->with('criteriaSchedulesBefore', $criteriaSchedulesBefore)
            ->with('criteriaSchedulesAfter', $criteriaSchedulesAfter)
            ->with('criteriaSchedules', $criteriaSchedules)
            ->with('nearestCriteriaSchedule', $nearestCriteriaSchedule)
            ->with('ongoingCriteriaSchedule', $ongoingCriteriaSchedule)
            ->with('finishedCriteriaSchedules', $finishedCriteriaSchedules);
    }

    public function storeCriteriaSchedule($id, Request $request)
    {
        $request->validate([
            'criteria' => 'required',
            'days' => 'required|numeric'
        ]);

        $schedule = Schedule::find($id);

        $days = $request->input('days');

        $startDate = $schedule->start_date;

        $criteriaSchedulesCount = CriteriaSchedule::where('schedule_id', $id)->count();

        // dd($criteriaSchedulesCount);
        if ($criteriaSchedulesCount > 0) {
            $criteriaAfter = (int) $request->input('criteria_after');
            $date = CriteriaSchedule::where([
                'schedule_id' => $id,
                'criteria_id' => $criteriaAfter,
            ])->first();
            $startDate = Carbon::parse($date->completion_date)->addWeekday()->format('Y-m-d');
        }

        $completionDate = Carbon::parse($startDate)->addWeekdays($days)->format('Y-m-d');

        $criteriaSchedule = new CriteriaSchedule;
        $criteriaSchedule->schedule_id = $id;
        $criteriaSchedule->criteria_id = $request->input('criteria');
        $criteriaSchedule->start_date = $startDate;
        $criteriaSchedule->completion_date = $completionDate;
        $criteriaSchedule->save();

        return redirect()->back()->with('success', 'Rincian jadwal berhasil ditambah!');
    }

    public function storeFinishedCriteriaSchedule($id, $criteria)
    {
        $ongoingCriteriaSchedule = DB::table('ongoing_criteria_schedules')
            ->where('schedule_id', $id)
            ->where('criteria_id', $criteria)
            ->first(['completion_date']);

        $estimatedCompletionDate = Carbon::parse($ongoingCriteriaSchedule->completion_date);
        $completedDate = Carbon::now();
        $completionDelay = $estimatedCompletionDate->diffInDays($completedDate, false);

        if ($completionDelay < 0) {
            $completionDelay = 0;
        }

        DB::table('finished_criteria_schedules')->insert([
            'schedule_id' => $id,
            'criteria_id' => $criteria,
            'estimated_completion_date' => $estimatedCompletionDate->format('Y-m-d'),
            'completed_date' => $completedDate->format('Y-m-d'),
            'completion_delay' => $completionDelay
        ]);

        DB::table('ongoing_criteria_schedules')
            ->where('schedule_id', $id)
            ->where('criteria_id', $criteria)
            ->delete();

        DB::table('criteria_schedules')
            ->where('schedule_id', $id)
            ->where('criteria_id', $criteria)
            ->update(['is_finished' => '1']);

        return redirect()->back()->with('success', 'Kriteria jadwal telah selesai!');
    }

    public function showScheduleAnalysisById($id)
    {
        $schedule = Schedule::find($id);
        $workingHours = $schedule->working_hours;
        $startDate = Carbon::parse($schedule->start_date);
        $completionDate = Carbon::parse($schedule->completion_date);
        $monthDifferences = $startDate->diffInMonths($completionDate);

        $finishedCriteriaSchedules = DB::table('finished_criteria_schedules')
            ->join('ferry_criterias', 'finished_criteria_schedules.criteria_id', '=', 'ferry_criterias.id')
            ->select(['finished_criteria_schedules.*', 'ferry_criterias.criteria'])
            ->where('schedule_id', $id)
            ->orderBy('finished_criteria_schedules.criteria_id')
            ->get();

        // dd($finishedCriteriaSchedules);

        return view('analisis-jadwal')
            ->with('finishedCriteriaSchedules', $finishedCriteriaSchedules)
            ->with('workingHours', $workingHours)
            ->with('monthDifferences', $monthDifferences);
    }
}
