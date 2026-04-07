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
        <div id="connectionStatus" class="badge bg-success badge-status">Live Connected</div>
    </div>
</nav>

<div class="card p-3 shadow-sm border-0 mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label small fw-bold">Dari (Tanggal & Jam):</label>
            <input type="datetime-local" id="startDate" class="form-control form-control-sm">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small fw-bold">Sampai (Tanggal & Jam):</label>
            <input type="datetime-local" id="endDate" class="form-control form-control-sm">
        </div>
        <div class="col-12 col-md-4 d-flex gap-2">
            <button onclick="applyFilter()" class="btn btn-primary btn-sm w-75">Filter Sekarang</button>
            <button onclick="resetFilter()" class="btn btn-outline-secondary btn-sm w-75">Reset</button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <button onclick="moveData('prev')" class="btn btn-outline-dark btn-sm shadow-sm">« Mundur 200</button>
    <span id="offsetInfo" class="badge rounded-pill bg-white text-dark border px-3">Data: 1 - 30</span>
    <button onclick="moveData('next')" class="btn btn-outline-dark btn-sm shadow-sm">Maju 200 »</button>
</div>

<div class="container-fluid px-3 px-md-4">
    <div class="row">
        <div class="col-12 col-xl-8 mb-4">
            <div class="card p-3 shadow-sm">
                <h6 class="fw-bold text-muted mb-3">TREN TEMPERATURE & HUMIDITY</h6>
                <div class="chart-wrapper">
                    <canvas id="chartTempHum"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card p-3 shadow-sm h-100">
                <h6 class="fw-bold text-muted mb-3">Humidity</h6>
                <div class="chart-wrapper">
                    <canvas id="chartHum"></canvas>
                </div>
            </div>
        </div>
        
                <div class="col-12 col-xl-8 mb-4">
            <div class="card p-3 shadow-sm">
                <h6 class="fw-bold text-muted mb-3">TEMPERATURE</h6>
                <div class="chart-wrapper">
                    <canvas id="chartTemp"></canvas>
                </div>
            </div>
        </div>



        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="card p-3 shadow-sm h-100">
                <h6 class="fw-bold text-muted mb-3">MOVING STATE</h6>
                <div class="chart-wrapper">
                    <canvas id="chartMoving"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-12 mb-4">
            <div class="card p-3 shadow-sm">
                <h6 class="fw-bold text-muted mb-3">VOLTAGE LEVEL (mV)</h6>
                <div class="chart-wrapper">
                    <canvas id="chartVoltage"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body p-0">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0">LAST 15 DATA LOGS</h6>
                <button onclick="updateDashboard()" class="btn btn-sm btn-outline-primary">Refresh Now</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr style="font-size: 0.85rem;">
                            <th>Time</th>
                            <th>Device</th>
                            <th>Temp</th>
                            <th>Hum</th>
                            <th>Volt</th>
                            <th>State</th>
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
    // Konfigurasi Chart.js yang Responsive
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false, // Penting agar mengikuti tinggi wrapper CSS
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: { x: { grid: { display: false } } }
    };

    // Chart 1: Temp & Hum
    const chartTempHum = new Chart(document.getElementById('chartTempHum'), {
        type: 'line',
        data: { labels: [], datasets: [
            { label: 'Temp (°C)', borderColor: '#e74c3c', backgroundColor: '#e74c3c', data: [], tension: 0.4 },
            { label: 'Hum (%)', borderColor: '#3498db', backgroundColor: '#3498db', data: [], tension: 0.4 }
        ]},
        options: chartOptions
    });
    
        // Chart 1B: humidity
    const chartHum = new Chart(document.getElementById('chartHum'), {
        type: 'line',
        data: { labels: [], datasets: [
            { label: 'Humidity (%)', borderColor: '#3498db', backgroundColor: '#3498db', data: [], tension: 0.4 },
        ]},
        options: chartOptions
    });



    // Chart 2A: Temp
    const chartTemp = new Chart(document.getElementById('chartTemp'), {
        type: 'line',
        data: { labels: [], datasets: [
            { label: 'Temp (°C)', borderColor: '#e74c3c', backgroundColor: '#e74c3c', data: [], tension: 0.4 },
        ]},
        options: chartOptions
    });



    // Chart 2B: Moving State
    const chartMoving = new Chart(document.getElementById('chartMoving'), {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Movement', backgroundColor: '#2ecc71', data: [] }]},
        options: { ...chartOptions, scales: { y: { max: 1, min: 0 } } }
    });

    // Chart 3: Voltage
    const chartVoltage = new Chart(document.getElementById('chartVoltage'), {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Voltage', borderColor: '#f39c12', fill: true, backgroundColor: 'rgba(243,156,18,0.1)', data: [] }]},
        options: chartOptions
    });
    
    let currentOffset = 0;
    const dataStep = 200; // Jumlah lompatan data

    // Fungsi navigasi Maju/Mundur
//    function moveData(direction) {
 //       if (direction === 'prev') {
  //          currentOffset += dataStep; // Mundur ke data yang lebih lama (offset bertambah)
    //    } else {
     //       currentOffset = Math.max(0, currentOffset - dataStep); // Maju ke data terbaru
     //   }
     //   updateDashboard();
    //}

    function applyFilter() {
        currentOffset = 0; // Reset ke data terbaru saat filter diterapkan
        updateDashboard();
    }

    function resetFilter() {
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        currentOffset = 0;
        updateDashboard();
    }
    async function updateDashboard() {
        const lim = 3600; // Tetap ambil 30 data untuk chart/table
        const start = document.getElementById('startDate').value; // Hasilnya: 2025-10-27T14:30
        const end = document.getElementById('endDate').value;
        const url =`get_data.php?lim=${lim}&off=${currentOffset}&start=${start}&end=${end}`;
        try {
            const response = await fetch(url); //('get_data.php?lim=3600');
            const data = await response.json();
            const labels = data.map(item => item.received_at.split(' ')[1]);
            document.getElementById('offsetInfo').innerText = `Data Ter-offset: ${currentOffset} (Showing ${data.length} records)`;


            chartTempHum.data.labels = labels;
            chartTempHum.data.datasets[0].data = data.map(item => item.temperature);
            chartTempHum.data.datasets[1].data = data.map(item => item.humidity);
            chartTempHum.update();
            
            chartHum.data.labels = labels;
            chartHum.data.datasets[0].data = data.map(item => item.humidity);
            chartHum.update();



            
            chartTemp.data.labels = labels;
            chartTemp.data.datasets[0].data = data.map(item => item.temperature);
            chartTemp.update();



            chartMoving.data.labels = labels;
            chartMoving.data.datasets[0].data = data.map(item => item.moving_state);
            chartMoving.update();

            chartVoltage.data.labels = labels;
            chartVoltage.data.datasets[0].data = data.map(item => item.voltage);
            chartVoltage.update();

            
        } catch (e) {
            document.getElementById('connectionStatus').className = "badge bg-danger badge-status";
            document.getElementById('connectionStatus').innerText = "Offline";
        }
    }
    
    async function updateTable() {
        try {
            const response = await fetch('get_data.php?lim=20');
            const data = await response.json();
            const labels = data.map(item => item.received_at.split(' ')[1]);

let tableHTML = "";
            [...data].reverse().forEach(row => {
                tableHTML += `<tr>
                    <td><small>${row.received_at}</small></td>
                    <td><strong>${row.device_name}</strong></td>
                    <td>${row.temperature}°C</td>
                    <td>${row.humidity}%</td>
                    <td>${row.voltage}</td>
                    <td><span class="badge ${row.moving_state == 1 ? 'bg-success' : 'bg-secondary'} rounded-pill" style="font-size:0.6rem;">${row.moving_state == 1 ? 'MOVING' : 'IDLE'}</span></td>
                </tr>`;
            });
            document.getElementById('tableBody').innerHTML = tableHTML;
            
        } catch (e) {
            document.getElementById('connectionStatus').className = "badge bg-danger badge-status";
            document.getElementById('connectionStatus').innerText = "Offline";
        }
    }

    setInterval(updateTable, 1000);
    updateTable();
    setInterval(updateDashboard, 1000);
    updateDashboard();
</script>

</body>
</html>


