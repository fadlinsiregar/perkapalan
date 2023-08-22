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

        return view('jadwal')->with([
            'schedules' => $schedules,
            'shipTypes' => $shipTypes,
        ]);
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
        $message = $newSchedule->exists ? 'Jadwal berhasil dibuat!' : 'Jadwal gagal dibuat!';

        return redirect()->back()->with($newSchedule->exists ? 'success' : 'failed', $message);
    }

    public function showScheduleDataById(int $id)
    {
        $currentDate = now()->format('Y-m-d');

        $schedule = Schedule::find($id);
        $criterias = FerryCriteria::all();

        $existingScheduleCriterias = CriteriaSchedule::select('criteria_id')
            ->where('schedule_id', $id)
            ->get();

        $criteriaSchedulesAfter = FerryCriteria::whereIn('id', $existingScheduleCriterias)
            ->get();

        $criteriaSchedulesBefore = FerryCriteria::whereNotIn('id', $existingScheduleCriterias)
            ->get();

        $criteriaSchedules = CriteriaSchedule::where('schedule_id', $id)
            ->get();

        $nearestCriteriaSchedule = CriteriaSchedule::join('ferry_criterias', 'criteria_schedules.criteria_id', '=', 'ferry_criterias.id')
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

        return view('progress-jadwal')->with([
            'schedule' => $schedule,
            'criterias' => $criterias,
            'criteriaSchedulesBefore' => $criteriaSchedulesBefore,
            'criteriaSchedulesAfter' => $criteriaSchedulesAfter,
            'criteriaSchedules' => $criteriaSchedules,
            'nearestCriteriaSchedule' => $nearestCriteriaSchedule,
            'ongoingCriteriaSchedule' => $ongoingCriteriaSchedule,
            'finishedCriteriaSchedules' => $finishedCriteriaSchedules,
        ]);
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

        if ($criteriaSchedulesCount > 0) {
            $criteriaAfter = (int) $request->input('criteria_after');
            $date = CriteriaSchedule::where([
                'schedule_id' => $id,
                'criteria_id' => $criteriaAfter,
            ])->first();
            $startDate = Carbon::parse($date->completion_date)->addWeekday()->format('Y-m-d');
        }

        $completionDate = Carbon::parse($startDate)->addWeekdays($days)->format('Y-m-d');

        $criteriaSchedule = new CriteriaSchedule([
            'schedule_id' => $id,
            'criteria_id' => $request->input('criteria'),
            'start_date' => $startDate,
            'completion_date' => $completionDate,
        ]);

        $criteriaSchedule->save();

        return redirect()->back()->with('success', 'Rincian jadwal berhasil ditambah!');
    }

    public function storeFinishedCriteriaSchedule($id, $criteria)
    {
        $ongoingCriteriaSchedule = DB::table('ongoing_criteria_schedules')
            ->where('schedule_id', $id)
            ->where('criteria_id', $criteria)
            ->first(['start_date', 'completion_date']);

        $startDate = Carbon::parse($ongoingCriteriaSchedule->start_date);
        $estimatedCompletionDate = Carbon::parse($ongoingCriteriaSchedule->completion_date);
        $completedDate = now();
        $completionDelay = max($estimatedCompletionDate->diffInDays($completedDate, false), 0);

        DB::table('finished_criteria_schedules')->insert([
            'schedule_id' => $id,
            'criteria_id' => $criteria,
            'start_date' => $startDate,
            'estimated_completion_date' => $estimatedCompletionDate->format('Y-m-d'),
            'completed_date' => $completedDate->format('Y-m-d'),
            'completion_delay' => $completionDelay,
        ]);

        DB::table('ongoing_criteria_schedules')
            ->where('schedule_id', $id)
            ->where('criteria_id', $criteria)
            ->delete();

        DB::table('criteria_schedules')
            ->where('schedule_id', $id)
            ->where('criteria_id', $criteria)
            ->update(['is_finished' => true]);

        return redirect()->back()->with('success', 'Kriteria jadwal telah selesai!');
    }

    public function storeWorkFinishPrediction($id, Request $request)
    {
        $criteriaId = $request->input('criteria');
        $criteriaSchedule = CriteriaSchedule::where('schedule_id', $id)
            ->where('criteria_id', $criteriaId)
            ->first();

        $days = $request->input('days');

        $startDate = $criteriaSchedule->start_date;
        $estimatedCompletionDate = Carbon::parse($criteriaSchedule->completion_date);
        $completedDate = $estimatedCompletionDate->addWeekdays($days);

        // Store into finished criteria schedules table
        $result = DB::table('finished_criteria_schedules')->insert([
            'schedule_id' => $id,
            'criteria_id' => $criteriaId,
            'start_date' => $startDate,
            'estimated_completion_date' => $estimatedCompletionDate,
            'completed_date' => $completedDate,
            'completion_delay' => $days,
        ]);

        return redirect()->back()->with($result ? 'success' : 'error', $result ? 'Berhasil ditambahkan!' : 'Gagal ditambahkan!');
    }

    public function showScheduleAnalysisById($id)
    {
        $schedule = Schedule::find($id);
        $workingHours = $schedule->working_hours;
        $startDate = Carbon::parse($schedule->start_date);
        $completionDate = Carbon::parse($schedule->completion_date);
        $monthDifferences = $startDate->diffInMonths($completionDate);
        $scheduleName = $schedule->construction_name;

        $finishedCriteriaSchedules = DB::table('finished_criteria_schedules')
            ->join('ferry_criterias', 'finished_criteria_schedules.criteria_id', '=', 'ferry_criterias.id')
            ->select(['finished_criteria_schedules.*', 'ferry_criterias.criteria'])
            ->where('schedule_id', $id)
            ->orderBy('finished_criteria_schedules.criteria_id')
            ->get();

        return view('analisis-jadwal')->with([
            'finishedCriteriaSchedules' => $finishedCriteriaSchedules,
            'workingHours' => $workingHours,
            'monthDifferences' => $monthDifferences,
            'scheduleName' => $scheduleName,
        ]);
    }

    public function deleteSchedule($id)
    {
        $schedule = Schedule::find($id);

        $schedule->delete();
    }
}
