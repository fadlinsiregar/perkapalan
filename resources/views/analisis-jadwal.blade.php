@section('title', 'Analisis Pembangunan Kapal')
@extends('layouts.main')

@section('main')
    <div class="container mt-3">
        <h2>Analisis Resiko</h2>
        <p>Nama Pembangunan Kapal: {{ $scheduleName }}</p>
        <p>Jam Pengerjaan: {{ $workingHours }} jam</p>
        <p>Selisih Bulan Pengerjaan: {{ $monthDifferences }} bulan</p>

        <section class="mt-5">
            <h4><i class="bi bi-exclamation-circle"></i> Keterlambatan Berdasarkan Jam Pengerjaan</h4>
            <table class="table table-bordered">
                <thead>
                    <th>Kriteria</th>
                    <th>Faktor Kali Konsekuensi</th>
                    <th>Likelihood</th>
                    <th>Consequence per Kejadian</th>
                </thead>
                <tbody>
                    @foreach ($finishedCriteriaSchedules as $finishedCriteriaSchedule)
                        <tr>
                            <td>{{ $finishedCriteriaSchedule->criteria }}</td>
                            <td>{{ number_format($finishedCriteriaSchedule->completion_delay, 2) }}</td>
                            <td>{{ getLikelihood($finishedCriteriaSchedule->criteria_id) }}</td>
                            <td>{{ calculateConsequenceWorkingHours($finishedCriteriaSchedule->criteria_id, $workingHours, $finishedCriteriaSchedule->completion_delay) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="mt-5">
            <h4><i class="bi bi-exclamation-circle"></i> Berdasarkan Lama Pembangunan</h4>
            <table class="table table-bordered">
                <thead>
                    <th>Kriteria</th>
                    <th>Faktor Kali Konsekuensi</th>
                    <th>Likelihood</th>
                    <th>Consequence per Kejadian</th>
                </thead>
                <tbody>
                    @foreach ($finishedCriteriaSchedules as $finishedCriteriaSchedule)
                        <tr>
                            <td>{{ $finishedCriteriaSchedule->criteria }}</td>
                            <td>{{ number_format($finishedCriteriaSchedule->completion_delay, 2) }}</td>
                            <td>{{ getNewLikelihood($finishedCriteriaSchedule->criteria_id, $monthDifferences) }}</td>
                            <td>{{ calculateConsequenceMonthDifferences($finishedCriteriaSchedule->criteria_id, $workingHours, $finishedCriteriaSchedule->completion_delay) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="mt-5">
            <h4><i class="bi bi-exclamation-circle"></i> Tingkat Likelihood dan Consequences</h4>
            <table class="table table-bordered">
                <thead>
                    <th>Kriteria</th>
                    <th>Faktor Kali Konsekuensi</th>
                    <th>Likelihood</th>
                    <th>Likelihood Level</th>
                    <th>Level Consequence per Kejadian</th>
                </thead>
                <tbody>
                    @foreach ($finishedCriteriaSchedules as $finishedCriteriaSchedule)
                        @php
                            $likelihoodLevel = calculateLikelihoodLevel($finishedCriteriaSchedule->criteria_id, $monthDifferences);
                            $consequencesLevel = calculateConsequenceLevel($finishedCriteriaSchedule->completion_delay);
                            $newLikelihood = getNewLikelihood($finishedCriteriaSchedule->criteria_id, $monthDifferences);
                        @endphp
                        <tr>
                            <td>{{ $finishedCriteriaSchedule->criteria }}</td>
                            <td>{{ number_format($finishedCriteriaSchedule->completion_delay, 2) }}</td>
                            <td>{{ $newLikelihood }}</td>
                            <td style="color: {{ $likelihoodLevel['colorCode'] }}"><strong>{{ $likelihoodLevel['likelihoodLevel'] }}</strong></td>
                            <td style="color: {{ $consequencesLevel['colorCode'] }}"><strong>{{ $consequencesLevel['consequencesLevel'] }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
        <section>
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="text-center">Tabel Kriteria Likelihood</h5>
                    <table class="table table-bordered" id="table-likelihood-definition">
                        <thead>
                            <th class="w-50">Likelihood</th>
                            <th class="w-50">Keterangan Likelihood</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Rare</td>
                                <td>&lt; 1%</td>
                            </tr>
                            <tr>
                                <td>Unlikely</td>
                                <td>1% &mdash; 5 %</td>
                            </tr>
                            <tr>
                                <td>Possible</td>
                                <td>5% &mdash; 25%</td>
                            </tr>
                            <tr>
                                <td>Likely</td>
                                <td>25% &mdash; 60%</td>
                            </tr>
                            <tr>
                                <td>Almost Certain</td>
                                <td>&gt; 60%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <h5 class="text-center">Tabel Kriteria Consequences</h5>
                    <table class="table table-bordered" id="table-likelihood-definition">
                        <thead>
                            <th class="w-50">Consequences</th>
                            <th class="w-50">Keterangan Consequences</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Insignificant</td>
                                <td>Waktu terbuang &lt; 10 hari</td>
                            </tr>
                            <tr>
                                <td>Minor</td>
                                <td>Waktu terbuang 10 s/d 20 hari</td>
                            </tr>
                            <tr>
                                <td>Moderate</td>
                                <td>Waktu terbuang 20 s/d 50 hari</td>
                            </tr>
                            <tr>
                                <td>Major</td>
                                <td>Waktu terbuang 50 s/d 100 hari</td>
                            </tr>
                            <tr>
                                <td>Catastrophic</td>
                                <td>Waktu terbuang &gt; 100 hari</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
