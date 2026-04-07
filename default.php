<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Sensor IoT - Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .logo-img { height: 35px; width: auto; }
        
        /* Mengatur tinggi chart agar tidak terlalu tinggi di HP */
        .chart-wrapper { position: relative; height: 300px; width: 100%; }
        @media (max-width: 768px) {
            .chart-wrapper { height: 250px; }
            .navbar-brand span { font-size: 0.9rem; } /* Kecilkan teks judul di HP */
        }

        .card { border: none; border-radius: 15px; transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card:hover { transform: translateY(-5px); }
        
        /* Custom scrollbar untuk tabel di mobile */
        .table-responsive {
            border-radius: 10px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #stickyFooter { background-color: #F0F0F0; /* abu-abu */ }
        .badge-status { font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; }
    </style>
</head>
<body>

<nav class="navbar sticky-top mb-4">
    <div class="container-fluid d-flex justify-content-between">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="./logopamtek.png" alt="Logo Pamtek" class="logo-img me-2">
            <span class="fw-bold text-dark d-none d-sm-inline">Smart Sensor IoT - Monitoring</span>
            <span class="fw-bold text-dark d-inline d-sm-none">IoT Monitor</span>
        </a>
        <div class="d-flex align-items-end mb-2">
    <div id="refreshBadge" class="badge bg-success me-2">
        <span id="statusText">Auto-Refresh: ON</span>
    </div>
    <small class="text-muted" id="lastUpdateText">Update terakhir: -</small>
</div>
        <div id="connectionStatus" class="badge bg-success badge-status">Live Connected</div>
    </div>
</nav>

<div id="invertSticky" class="fixed-bottom bg-light border-top p-2 text-center shadow-sm ">
    <div class="container d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-dark btn-sm shadow"onclick="ShowFooter()">▲ Histori</button>

    </div>
</div>
<div id="stickyFooter" class="fixed-bottom  border-top p-2 text-center shadow-sm " >
    <div class="container d-flex justify-content-between align-items-end">
        <button type="button" class="btn-close btn-close-dark" onclick="hideFooter()"></button>
            <div class="form-check form-switch mb-2">

        <input class="form-check-input" type="checkbox" id="realtimeToggle" onchange="toggleRealtimeMode()">
    <label class="form-check-label fw-bold" for="realtimeToggle">Mode Real-time</label>
    
</div>
    </div>
    
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
    <label class="form-label small fw-bold">From:</label>
            <div class="input-group input-group-sm">
                <button class="btn btn-outline-secondary" onclick="stepTime('startDate', -60)">-1 Hr</button>
                <input type="datetime-local" id="startDate" class="form-control form-control-sm">
                <button class="btn btn-outline-secondary" onclick="stepTime('startDate', 60)">+1 Hr</button>
            </div>
        </div>
        <div class="col-12 col-md-5">
            <label class="form-label small fw-bold">To:</label>
            <div class="input-group input-group-sm">
                <button class="btn btn-outline-secondary" onclick="stepTime('endDate', -60)">-1 Hr</button>
                <input type="datetime-local" id="endDate" class="form-control form-control-sm">
                <button class="btn btn-outline-secondary" onclick="stepTime('endDate', 60)">+1 Hr</button>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <button onclick="applyFilter()" class="btn btn-primary btn-sm w-100">Apply</button>
            <button onclick="resetFilter()" class="btn btn-outline-secondary btn-sm w-100">Reset</button>
        </div>
    </div>
    
    <div class="d-flex justify-content-center gap-2 mt-3">
<div class="d-flex justify-content-center align-items-center gap-2 mt-3">
    <button id="prevBtn" onclick="shiftRange(-1)" class="btn btn-dark btn-sm px-3">« PREV</button>
    
    <div class="input-group input-group-sm" style="width: 150px;">
        <input type="number" id="jumpStep" class="form-control text-center" value="60">
        <span class="input-group-text">Min</span>
    </div>
    
    <button id="nextbtn" onclick="shiftRange(1)" class="btn btn-dark btn-sm px-3">NEXT »</button>

</div>

    </div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <span id="offsetInfo" class="badge rounded-pill bg-white text-dark border px-3">Data: 1 - 30</span>
</div>
</div>




<div class="container-fluid px-3 px-md-4">
    <div class="row">
        <div class="col-12 col-xl-8 mb-4">
            <div class="card p-3 shadow-sm">
                <h6 class="fw-bold text-muted mb-3">Current</h6>
                <div class="chart-wrapper">
                    <canvas id="chartCurrent"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card p-3 shadow-sm h-100">
                <h6 class="fw-bold text-muted mb-3">Frequency</h6>
                <div class="chart-wrapper">
                    <canvas id="chartFreq"></canvas>
                </div>
            </div>
        </div>
        
                <div class="col-12 col-xl-8 mb-4">
            <div class="card p-3 shadow-sm">
                <h6 class="fw-bold text-muted mb-3">Voltage</h6>
                <div class="chart-wrapper">
                    <canvas id="chartVolt"></canvas>
                </div>
            </div>
        </div>



        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card p-3 shadow-sm h-100">
                <h6 class="fw-bold text-muted mb-3">Temperature</h6>
                <div class="chart-wrapper">
                    <canvas id="chartTemp"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-12 mb-4">
            <div class="card p-3 shadow-sm">
                <h6 class="fw-bold text-muted mb-3">Vibration</h6>
                <div class="chart-wrapper">
                    <canvas id="chartVibra"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body p-0">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">LAST 15 DATA LOGS</h6>
                <button onclick="updateDashboard()" class="btn btn-sm btn-outline-primary">Refresh Now</button>
                <button onclick="exportToExcel()" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel"></i> Export Excel </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr style="font-size: 0.85rem;">
                            <th>Time</th>
                            <th>Device</th>
                            <th>Current</th>
                            <th>Freq</th>
                            <th>Volt</th>
                            <th>Temp</th>
                            <th>Vibration</th>
                            <th>Flow</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" style="font-size: 0.85rem;">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        resetFilter();


    };
    let realtimeInterval;

function exportToExcel() {
    // Ambil parameter waktu yang sedang aktif saat ini
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    
    // Arahkan ke file PHP terpisah dengan parameter yang sama
    window.location.href = `export_excel.php?start=${start}&end=${end}`;
}

function toggleRealtimeMode() {
    const isRealtimeToggle = document.getElementById('realtimeToggle').checked;
    const statusText = document.getElementById('statusText');
    const badge = document.getElementById('refreshBadge');

    if (isRealtimeToggle) {
        // Jika diaktifkan, langsung jalankan update
        updateDashboard();
        statusText.innerText = "Auto-Refresh: ON";
        badge.className = "badge bg-success me-2";
        
        // Nonaktifkan input manual agar tidak bentrok
        document.getElementById('startDate').disabled = true;
        document.getElementById('endDate').disabled = true;
        document.getElementById('nextbtn').style.display = 'none';
        document.getElementById('prevBtn').style.display = 'none';
        
    } else {
        statusText.innerText = "Auto-Refresh: OFF (Manual)";
        badge.className = "badge bg-warning text-dark me-2";
        
        // Aktifkan kembali input manual
        document.getElementById('startDate').disabled = false;
        document.getElementById('endDate').disabled = false;
        document.getElementById('nextbtn').style.display = 'block';
        document.getElementById('prevBtn').style.display = 'block';
    }
}
    function hideFooter() {
    document.getElementById('stickyFooter').style.display = 'none';
    }
    function ShowFooter() {
    document.getElementById('stickyFooter').style.display = 'block';
    }
    
    // Konfigurasi Chart.js yang Responsive
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false, // Penting agar mengikuti tinggi wrapper CSS
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: { x: { grid: { display: false } } }
    };

    // Chart 1: Current
    const chartCurrent = new Chart(document.getElementById('chartCurrent'), {
        type: 'line',
        data: { labels: [], datasets: [
            { label: 'Current (A)', borderColor: '#FB8C00' , backgroundColor: '#FB8C00'  , data: [], tension: 0.4 }
        ]},
        options: chartOptions
    });
    
        // Chart 1B: Frequency
    const chartFreq = new Chart(document.getElementById('chartFreq'), {
        type: 'line',
        data: { labels: [], datasets: [
            { label: 'Frequency (Hz)', borderColor: '#00ff00', backgroundColor: '#00ff00', data: [], tension: 0.4 },
        ]},
        options: chartOptions
    });



    // Chart 2A: Voltage
    const chartVolt = new Chart(document.getElementById('chartVolt'), {
        type: 'line',
        data: { labels: [], datasets: [
            { label: 'Voltage (V)', borderColor: '#1E88E5', backgroundColor: '#1E88E5', data: [], tension: 0.4 },
        ]},
        options: chartOptions
    });



    // Chart 2B: Temperature
    const chartTemp = new Chart(document.getElementById('chartTemp'), {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Temp (°C)',borderColor: '#db1717' ,backgroundColor: '#db1717', data: [] }]},
        options: { ...chartOptions, scales: { y: { max: 120, min: 0 } } }
    });

    // Chart 3: Vibration
    const chartVibra = new Chart(document.getElementById('chartVibra'), {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Vibration', borderColor: '#f39c12', fill: true, backgroundColor: 'rgba(243,156,18,0.1)', data: [] }]},
        options: chartOptions
    });
    
    let currentOffset = 0;
    
    let autoRefreshInterval;
  // Fungsi untuk mengecek apakah kita sedang di mode Real-time
    function isRealTime() {
    const endDateInput = document.getElementById('endDate').value;
    if (!endDateInput) return true;

    const selectedEnd = new Date(endDateInput);
    const now = new Date();
    
    // Jika waktu yang dipilih adalah sekarang atau masa depan, aktifkan real-time
    // Kita beri toleransi 30detik
    return selectedEnd >= new Date(now.getTime() - 30000);
    }

    function updateStatusIndicator() {
    const badge = document.getElementById('refreshBadge');
    const statusText = document.getElementById('statusText');
    
    if (isRealTime()) {
        badge.className = "badge bg-success me-2";
        statusText.innerText = "Auto-Refresh: ON";
    } else {
        badge.className = "badge bg-warning text-dark me-2";
        statusText.innerText = "Auto-Refresh: OFF (Mode Histori)";
    }
    }

    // Fungsi 1: Menggeser waktu pada satu input saja (Start atau End)
    function stepTime(inputId, minutes) {
        let input = document.getElementById(inputId);
        if (!input.value) {
            // Jika kosong, set ke waktu sekarang
            let now = new Date();
            input.value = now.toISOString().slice(0, 16);
        } else {
            let currentTime = new Date(input.value);
            currentTime.setMinutes(currentTime.getMinutes() + minutes);
            
            // Format balik ke YYYY-MM-DDTHH:MM sesuai format lokal browser
            let tzOffset = currentTime.getTimezoneOffset() * 60000;
            let localISOTime = new Date(currentTime - tzOffset).toISOString().slice(0, 16);
            input.value = localISOTime;
        }
        applyFilter();
    }

   // Fungsi untuk menggeser seluruh rentang waktu berdasarkan input menit
function shiftRange(direction) {
    let startInput = document.getElementById('startDate');
    let endInput = document.getElementById('endDate');
    // Ambil nilai menit dari input jumpStep (default 60 jika kosong)
    let minutes = parseInt(document.getElementById('jumpStep').value) || 60;
    
    // Tentukan lompatan (positif untuk NEXT, negatif untuk PREV)
    let totalMinutes = direction * minutes;

    if (startInput.value && endInput.value) {
        let startTime = new Date(startInput.value);
        let endTime = new Date(endInput.value);

        startTime.setMinutes(startTime.getMinutes() + totalMinutes);
        endTime.setMinutes(endTime.getMinutes() + totalMinutes);

        // Gunakan fungsi format ISO yang sudah aman dengan offset
        let tzOffset = startTime.getTimezoneOffset() * 60000;
        
        startInput.value = new Date(startTime - tzOffset).toISOString().slice(0, 16);
        endInput.value = new Date(endTime - tzOffset).toISOString().slice(0, 16);
        
        applyFilter();
    } else {
        alert("Silakan isi rentang waktu terlebih dahulu.");
    }
}
    // Fungsi pembantu untuk format waktu input datetime-local agar tidak error
    function getLocalISOString(date) {
        let d = new Date(date);
        d.setSeconds(0);
        d.setMilliseconds(0);
        // Offset Jakarta (WIB) adalah +7 jam
        const offset = 7 * 60 * 60 * 1000; 
        const localTime = new Date(d.getTime() + offset);
        return localTime.toISOString().slice(0, 16);
    }
    function applyFilter() {
        //currentOffset = 0; // Reset ke data terbaru saat filter diterapkan
        updateDashboard();
    }

    function resetFilter() {
        let now = new Date();
        let nowGMT = new Date(now.getTime() + (7 * 3600 * 1000) );
        let past = new Date(nowGMT.getTime() - (60 * 60 * 1000));

        document.getElementById('startDate').value = past.toISOString().slice(0, 16);
        document.getElementById('endDate').value = nowGMT.toISOString().slice(0, 16);
        currentOffset = 0;
        updateDashboard();
    }
    async function updateDashboard() {
        const isRealtimeToggle = document.getElementById('realtimeToggle').checked;

        if (isRealtimeToggle) {
            // Ambil waktu sekarang
            let now = new Date();
            // Misal kita ingin selalu menampilkan 1 jam terakhir (60 menit)
            let jumpMinutes = parseInt(document.getElementById('jumpStep').value) || 60;
            let pastJump = new Date(now.getTime() - (jumpMinutes * 60 * 1000));
            let nowplus = new Date(now.getTime() + (1 * 60 * 1000));
    
            // Update nilai input form secara otomatis
            document.getElementById('startDate').value = getLocalISOString(pastJump);
            document.getElementById('endDate').value = getLocalISOString(nowplus);
        }

    


    updateStatusIndicator();
    
    
        const lim = 10000; // Tetap ambil 30 data untuk chart/table
        const start = document.getElementById('startDate').value; // Hasilnya: 2025-10-27T14:30
        const end = document.getElementById('endDate').value;
        const url =`get_data.php?lim=${lim}&off=${currentOffset}&start=${start}&end=${end}`;
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            const labels = data.map(item => item.received_at.split(' ')[1]);
            document.getElementById('offsetInfo').innerText = `Data Ter-offset: ${currentOffset} (Showing ${data.length} records)`;


            chartCurrent.data.labels = labels;
            chartCurrent.data.datasets[0].data = data.map(item => item.CURRENT);
            chartCurrent.update();
            
            chartFreq.data.labels = labels;
            chartFreq.data.datasets[0].data = data.map(item => item.FREQUENCY);
            chartFreq.update();



            
            chartVolt.data.labels = labels;
            chartVolt.data.datasets[0].data = data.map(item => item.VOLTAGE);
            chartVolt.update();



            chartTemp.data.labels = labels;
            chartTemp.data.datasets[0].data = data.map(item => item.TEMPERATURE);
            chartTemp.update();

            chartVibra.data.labels = labels;
            chartVibra.data.datasets[0].data = data.map(item => item.VIBRATION);
            chartVibra.update();

            document.getElementById('lastUpdateText').innerText = "Update terakhir: " + new Date().toLocaleTimeString('id-ID');
        } catch (e) {
            document.getElementById('connectionStatus').className = "badge bg-danger badge-status";
            document.getElementById('connectionStatus').innerText = "Offline";

        }
    }
    
    async function updateTable() {
        let lim = 100; // Tetap ambil 30 data untuk chart/table
        let start = document.getElementById('startDate').value; // Hasilnya: 2025-10-27T14:30
        let end = document.getElementById('endDate').value;
        let url =`get_data.php?lim=${lim}&off=${currentOffset}&start=${start}&end=${end}`;
        try {
            let response = await fetch(url);
            let data = await response.json();
            let labels = data.map(item => item.received_at.split(' ')[1]);

let tableHTML = "";
            [...data].reverse().forEach(row => {
                tableHTML += `<tr>
                    <td><small>${row.received_at}</small></td>
                    <td><strong>${row.DEVICE_NAME}</strong></td>
                    <th>${row.CURRENT}</th>
                    <th>${row.FREQUENCY}</th>
                    <th>${row.VOLTAGE}</th>
                    <th>${row.TEMPERATURE}</th>
                    <th>${row.VIBRATION}</th>
                    <th>${row.FLOW}</th>
                </tr>`;
            });
            document.getElementById('tableBody').innerHTML = tableHTML;
            
        } catch (e) {
            document.getElementById('connectionStatus').className = "badge bg-danger badge-status";
            document.getElementById('connectionStatus').innerText = "Offline";
        }
    }

    window.onload = function () {
    resetFilter();

    setTimeout(() => {
        updateDashboard();
        updateTable();
        setInterval(updateDashboard, 5000);
        setInterval(updateTable, 10000);
    }, 300);
};

</script>

</body>
</html>


