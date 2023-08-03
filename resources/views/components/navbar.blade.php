<nav class="navbar navbar-expand-lg" style="background-color: #e3f2fd;">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar"
            aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('dashboard') }}">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('schedules.home') }}">Jadwal</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
