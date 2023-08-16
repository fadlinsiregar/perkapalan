@section('title', "Informasi Detail untuk $schedule->construction_name")

@inject('carbon', 'Carbon\Carbon')

@extends('layouts.main')

@section('main')
    <div class="container-fluid mt-3">
        <div class="container">
            <section class="row">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <h3 class="mt-3"><i class="bi bi-info-circle"></i>&nbsp;Informasi Pembangunan</h3>

                <table class="table table-borderless" id="table-info">
                    <tbody>
                        <tr>
                            <td class="w-50">Nama Pembangunan Kapal</td>
                            <td class="w-50">{{ $schedule->construction_name }}</td>
                        </tr>
                        <tr>
                            <td class="w-50">Jam Pengerjaan (per hari)</td>
                            <td class="w-50">{{ $schedule->working_hours }} jam</td>
                        </tr>
                        <tr>
                            <td class="w-50">Waktu Pengerjaan</td>
                            <td class="w-50">{{ formatDate($schedule->start_date) }} &mdash;
                                {{ formatDate($schedule->completion_date) }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3><i class="bi bi-calendar-date"></i>&nbsp;Informasi Jadwal per Kriteria</h3>

                <table class="table table-borderless" id="table-info">
                    <tbody>
                        <tr>
                            <td class="w-50">Jadwal Mendatang</td>
                            <td class="w-50">
                                @if ($nearestCriteriaSchedule != null)
                                    {{ $nearestCriteriaSchedule->criteria }} ({!! formatDate($nearestCriteriaSchedule->start_date) !!})
                                @else
                                    Belum ada jadwal mendatang
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Jadwal Saat Ini</strong></td>
                            <td>
                                @if ($ongoingCriteriaSchedule != null)
                                    {{ $ongoingCriteriaSchedule->criteria }} (Berakhir pada {!! formatDate($ongoingCriteriaSchedule->completion_date) !!})
                                    <form
                                        action="{{ route('schedules.store_finished_criteria_schedule', ['id' => $ongoingCriteriaSchedule->schedule_id, 'criteria' => $ongoingCriteriaSchedule->criteria_id]) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Selesai</button>
                                    </form>
                                @else
                                    Belum ada jadwal saat ini
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-primary mb-3 w-25 m-auto" data-bs-toggle="modal"
                    data-bs-target="#addCriteriaScheduleModal">
                    <i class="bi bi-calendar-plus"></i> Tambah Jadwal Kriteria
                </button>

                <div class="modal fade" id="addCriteriaScheduleModal" tabindex="-1"
                    aria-labelledby="addCriteriaScheduleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="addCriteriaScheduleModalLabel">Tambah Jadwal per Kriteria
                                </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('schedules.store_criteria_schedule', ['id' => $schedule->id]) }}"
                                method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group mt-3">
                                        <label for="criteria">Kriteria Pembangunan Kapal</label>
                                        <select name="criteria" id="criteria" class="form-select">
                                            @foreach ($criteriaSchedulesBefore as $criteriaSchedule)
                                                <option value="{{ $criteriaSchedule->id }}">
                                                    {{ $criteriaSchedule->criteria }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="days">Jumlah Hari</label>
                                        <input type="number" name="days" id="days" class="form-control"
                                            class="@error('days')
                                                is-invalid
                                            @enderror"
                                            value="{{ @old('days') }}" required>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="criteria_after">Kriteria Dilakukan Setelah</label>
                                        <select name="criteria_after" id="criteria_after" class="form-select" readonly
                                            @if ($criteriaSchedulesAfter->count() == 0) disabled @endif>
                                            <option value="" disabled>Pilih...</option>
                                            @foreach ($criteriaSchedulesAfter as $criteriaSchedule)
                                                <option value="{{ $criteriaSchedule->id }}">
                                                    {{ $criteriaSchedule->criteria }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Tambah</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section>
            <h3 class="container"><i class="bi bi-graph-up"></i>&nbsp;Grafik Pelaksanaan Jadwal</h3>

            <div>
                <canvas id="ganttChart"></canvas>
            </div>
        </section>

        <div class="container">
            <section class="row">
                <h3><i class="bi bi-check2"></i>&nbsp;Daftar Jadwal per Kriteria yang Selesai</h3>
                <table class="table table-bordered">
                    <thead>
                        <th>No.</th>
                        <th>Kriteria</th>
                        <th>Estimasi Tanggal Selesai</th>
                        <th>Diselesaikan Pada</th>
                        <th>Keterlambatan</th>
                    </thead>
                    <tbody>
                        @foreach ($finishedCriteriaSchedules as $finishedCriteriaSchedule)
                            <tr>
                                <td>1</td>
                                <td>{{ $finishedCriteriaSchedule->criteria }}</td>
                                <td>{{ formatDate($finishedCriteriaSchedule->estimated_completion_date) }}</td>
                                <td>{{ formatDate($finishedCriteriaSchedule->completed_date) }}</td>
                                <td>{{ $finishedCriteriaSchedule->completion_delay }} hari</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="{{ route('schedules.schedule_analysis', ['id' => $schedule->id]) }}"
                    class="btn btn-primary w-25 m-auto mt-3"><i class="bi bi-clipboard-data"></i> Analisis
                    Resiko</a>
            </section>
        </div>
    </div>
@endsection

@section('chart')
    <script>
        const ctx = document.getElementById('ganttChart').getContext('2d');

        const labels = [
            @foreach ($criterias as $criteria)
                '{!! $criteria->criteria !!}',
            @endforeach
        ];

        const data = {
            datasets: [{
                    label: "Estimasi Pekerjaan",
                    data: [
                        @foreach ($criteriaSchedules as $criteriaSchedule)
                            ['{!! $criteriaSchedule->start_date !!}', '{!! $criteriaSchedule->completion_date !!}'],
                        @endforeach
                    ],
                    borderWidth: 2,
                    backgroundColor: "#B4E4FF",
                    borderColor: "#81D2FF",
                    borderSkipped: false,
                },
                {
                    label: "Realisasi Pekerjaan",
                    data: [
                        @foreach ($finishedCriteriaSchedules as $finishedCriteriaSchedule)
                            ['{!! $finishedCriteriaSchedule->start_date !!}', '{!! $finishedCriteriaSchedule->completed_date !!}']
                        @endforeach
                    ],
                    backgroundColor: "#36AE7C",
                    borderSkipped: false,
                },
            ],
        }

        const options = {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = JSON.parse(context.formattedValue);

                                const options = {
                                    year: "numeric",
                                    month: "short",
                                    day: "numeric"
                                }

                                const formatDate = date => new Date(date).toLocaleDateString("id-ID", options);

                                const startDate = formatDate(value[0]);
                                const completionDate = formatDate(value[1]);

                                const label = `${context.dataset.label}: ${startDate} - ${completionDate}` ||
                                '';

                                return label;
                            }
                        }
                    }
                },
                indexAxis: 'y',
                scales: {
                    x: {
                        position: 'top',
                        type: 'time',
                        time: {
                            unit: 'month'
                        },
                        min: '{{ $schedule->start_date }}',
                        max: '{{ $schedule->completion_date }}',
                        grid: {
                            borderDash: [5, 5]
                        }
                    },
                    y: {
                        beginAtZero: true,
                        labels: labels,
                        ticks: {
                            callback(value, index) {
                                const maxLength = 30;
                                const strValue = this.getLabelForValue(value).toString();
                                return strValue.length > maxLength ? `${strValue.substring(0, maxLength)}...` : strValue;
                            }
                        }
                    }
                },
            }

        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options,
        })
    </script>
@endsection
