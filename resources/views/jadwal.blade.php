@section('title', 'Daftar Jadwal Pembangunan Kapal - Aplikasi Penjadwalan Perkapalan')

@inject('carbon', 'Carbon\Carbon')

@extends('layouts.main')

@section('main')
    <div class="container mt-3">
        <h1>Daftar Jadwal Pembangunan Kapal</h1>

        @if (session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check"></i>&nbsp;{{ session('success') }}
            </div>
        @elseif (session('failed'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>&nbsp;{{ session('failed') }}
            </div>
        @endif

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createShipConstructionModal">
            <i class="bi bi-plus-circle"></i>&nbsp;Tambah Pembangunan Kapal
        </button>

        <section class="modal fade" id="createShipConstructionModal" tabindex="-1"
            aria-labelledby="createShipConstructionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('schedules.store_construction_schedule') }} " method="POST">
                        @csrf
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="createShipConstructionModalLabel">Tambah Jadwal Pembangunan
                            </h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="ship_type_id">Jenis Pembangunan Kapal</label>
                                <select name="ship_type_id" id="ship_type_id" class="form-select">
                                    @foreach ($shipTypes as $shipType)
                                        <option value="{{ $shipType->id }}">{{ $shipType->ship_types_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label for="construction_name">Nama Pembangunan Kapal</label>
                                <input type="text" name="construction_name" id="construction_name" class="form-control"
                                    required>
                            </div>

                            <div class="form-group mt-3">
                                <label for="working_hours">Jam Pengerjaan</label>
                                <input type="number" name="working_hours" min="1" max="24" id="working_hours"
                                    class="form-control" required>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="start_date">Tanggal Mulai</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control"
                                            required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="completion_date">Tanggal Selesai</label>
                                        <input type="date" name="completion_date" id="completion_date"
                                            class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="create" class="btn btn-primary">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <table class="table table-bordered mt-3">
            <thead>
                <td>No.</td>
                <td>Jadwal Pembangunan Kapal</td>
                <td>Estimasi Selesai</td>
                <td>Tindakan</td>
            </thead>
            <tbody>
                @foreach ($schedules as $schedule)
                    <tr>
                        <td>{{ $schedule->id }}</td>
                        <td>{{ $schedule->construction_name }}</td>
                        <td>{{ $carbon::parse($schedule->completion_date)->format('d F Y') }}</td>
                        <td>
                            <a href="{{ route('schedules.schedule_progress', ['id' => $schedule->id]) }}"
                                class="btn btn-primary"><i class="bi bi-info-circle"></i>&nbsp;Detail</a>
                            {{-- <a href="#" class="btn btn-warning"><i class="bi bi-pencil-square"></i>&nbsp;Ubah</a> --}}
                            {{-- <a href="#" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#confirmDeleteModel"><i class="bi bi-trash"></i>&nbsp;Hapus</a> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
