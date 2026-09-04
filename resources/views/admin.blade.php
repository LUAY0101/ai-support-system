<!DOCTYPE html>
<html dir="ltr" lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli - Akıllı Destek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .table-hover tbody tr:hover { background-color: #f1f3f5; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-4 px-4">
        
        <!-- قسم العنوان والأزرار -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 text-primary fw-bold">📊 Akıllı Destek Yönetim Paneli</h2>
            
            <!-- قسم رفع ملف Excel -->
            <div class="p-2 bg-white rounded shadow-sm border">
                <form action="{{ url('/admin/import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-3 m-0">
                    @csrf
                    <label for="excel_file" class="fw-bold mb-0">Toplu Excel Yükle:</label>
                    <input type="file" name="excel_file" id="excel_file" class="form-control form-control-sm w-auto" accept=".xlsx, .xls" required>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">
                        <i class="fas fa-file-excel"></i> Yükle ve Analiz Et
                    </button>
                </form>
            </div>
            
            <a href="{{ url('/') }}" class="btn btn-outline-primary fw-bold">➕ Yeni Talep Ekle</a>
        </div>

        <!-- الإحصائيات السريعة -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Talep</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">📁</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Yeni</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['yeni'] ?? 0 }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">📩</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-dark bg-warning shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-dark-50 mb-1" style="opacity: 0.7">Beklemede</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['beklemede'] ?? 0 }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">⏳</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Çözüldü</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['cozuldu'] ?? 0 }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">✅</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">En çok talep gelen kanallar</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($channelStats as $source => $total)
                                <span class="badge bg-dark rounded-pill px-3 py-2">
                                    {{ ucfirst($source) }}: {{ $total }}
                                </span>
                            @empty
                                <span class="text-muted">Henüz kanal verisi yok.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Tekrarlanan şikayetler</h5>
                        @forelse($repeatedComplaints as $complaint)
                            <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                                <span>{{ Str::limit($complaint['message'], 70) }}</span>
                                <span class="badge bg-secondary align-self-start">{{ $complaint['count'] }}</span>
                            </div>
                        @empty
                            <span class="text-muted">Tekrarlanan şikayet bulunamadı.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- شريط الفلترة -->
        <form action="{{ url('/admin') }}" method="GET" class="mb-4" style="background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Ara (Sipariş No / İsim)" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select">
                        <option value="">Tüm Departmanlar</option>
                        <option value="Teknik Destek" {{ request('department') == 'Teknik Destek' ? 'selected' : '' }}>Teknik Destek</option>
                        <option value="Kargo ve Teslimat" {{ request('department') == 'Kargo ve Teslimat' ? 'selected' : '' }}>Kargo ve Teslimat</option>
                        <option value="Muhasebe ve Fatura" {{ request('department') == 'Muhasebe ve Fatura' ? 'selected' : '' }}>Muhasebe ve Fatura</option>
                        <option value="İptal ve İade" {{ request('department') == 'İptal ve İade' ? 'selected' : '' }}>İptal ve İade</option>
                        <option value="Genel" {{ request('department') == 'Genel' ? 'selected' : '' }}>Genel</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-select">
                        <option value="">Tüm Öncelikler</option>
                        <option value="Düşük" {{ request('priority') == 'Düşük' ? 'selected' : '' }}>Düşük</option>
                        <option value="Orta" {{ request('priority') == 'Orta' ? 'selected' : '' }}>Orta</option>
                        <option value="Yüksek" {{ request('priority') == 'Yüksek' ? 'selected' : '' }}>Yüksek</option>
                        <option value="Acil" {{ request('priority') == 'Acil' ? 'selected' : '' }}>Acil</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="source" class="form-select">
                        <option value="">Tüm Kaynaklar</option>
                        @foreach(['web', 'whatsapp', 'telegram', 'email', 'instagram'] as $source)
                            <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                {{ ucfirst($source) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filtrele</button>
                    <a href="{{ url('/admin') }}" class="btn btn-secondary w-100 fw-bold">Temizle</a>
                </div>
            </div>
        </form>

        <!-- جدول التذاكر -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#ID</th>
                                <th>Müşteri Adı</th>
                                <th>Mesaj / Şikayet</th>
                                <th>Kaynak</th>
                                <th>Ek (Dosya)</th>
                                <th>Departman (AI)</th>
                                <th>Öncelik (AI)</th>
                                <th>Sipariş No</th>
                                <th>Tutar (TL)</th>
                                <th>Fatura/Dekont No</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            @php
                                // تحديد إذا كانت التذكرة متأخرة (أكثر من 24 ساعة ولم تُحل)
                                $isDelayed = $ticket->status !== 'Çözüldü' && $ticket->created_at->diffInHours(now()) >= 24;
                            @endphp
                            <tr class="{{ $isDelayed ? 'table-danger' : '' }}">
                                <td>
                                    <strong>#{{ $ticket->id }}</strong>
                                    @if($isDelayed)
                                        <i class="fas fa-exclamation-triangle text-danger ms-1" title="24 Saatten Fazla Gecikmiş!"></i>
                                    @endif
                                </td>
                                <td>{{ $ticket->customer_name }}</td>
                                <td>{{ Str::limit($ticket->message, 40) }}</td>
                                <td>
                                    <span class="badge bg-dark rounded-pill">{{ ucfirst($ticket->source ?? 'web') }}</span>
                                </td>
                                <td>
                                    @if($ticket->attachment)
                                        <a href="{{ asset($ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                            <i class="fas fa-paperclip"></i> Gör
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark rounded-pill">{{ $ticket->department }}</span>
                                </td>
                                <td>
    <span class="badge rounded-pill shadow-sm
        {{ $ticket->priority == 'Acil' ? 'bg-danger text-white' : 
           ($ticket->priority == 'Yüksek' ? 'bg-warning text-dark' : 
           ($ticket->priority == 'Orta' ? 'bg-info text-dark' : 'bg-secondary text-white')) }}">
        {{ $ticket->priority }}
    </span>
</td>
                                <td>{{ $ticket->order_number ?? '-' }}</td>
                                
                                <td>
                                    @if($ticket->amount)
                                        <span class="badge bg-success rounded-pill">{{ $ticket->amount }} TL</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->invoice_number)
                                        <strong>{{ $ticket->invoice_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $ticket->document_date }}</small>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
    <form action="{{ url('/admin/tickets/'.$ticket->id.'/status') }}" method="POST" class="m-0">
        @csrf
        <select name="status" class="form-select form-select-sm rounded-pill fw-bold border-0 shadow-sm
            {{ $ticket->status == 'Yeni' ? 'bg-primary text-white' : 
               ($ticket->status == 'İnceliyor' ? 'bg-info text-dark' : 
               ($ticket->status == 'Beklemede' ? 'bg-warning text-dark' : 'bg-success text-white')) }}" 
            onchange="this.form.submit()" style="cursor: pointer; width: 120px;">
            <option value="Yeni" class="text-dark bg-white" {{ $ticket->status == 'Yeni' ? 'selected' : '' }}>Yeni</option>
            <option value="İnceliyor" class="text-dark bg-white" {{ $ticket->status == 'İnceliyor' ? 'selected' : '' }}>İnceliyor</option>
            <option value="Beklemede" class="text-dark bg-white" {{ $ticket->status == 'Beklemede' ? 'selected' : '' }}>Beklemede</option>
            <option value="Çözüldü" class="text-dark bg-white" {{ $ticket->status == 'Çözüldü' ? 'selected' : '' }}>Çözüldü</option>
        </select>
    </form>
</td>
                                <td>
                                    {{ $ticket->created_at->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- قسم الرسم البياني (Şikayet yoğunluğu analizi) - مكانه الصحيح في الأسفل -->
        <div class="row mt-5 mb-5">
            <div class="col-md-6 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold text-center">
                        <i class="fas fa-chart-pie text-primary ms-2"></i> Departmanlara Göre Şikayet Dağılımı (AI Analizi)
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <div style="width: 60%;">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- نهاية container-fluid -->

    <!-- استدعاء مكتبة الرسم البياني ومكاتب التصميم في أسفل الصفحة -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('departmentChart').getContext('2d');
            
            // جلب الإحصائيات آلياً من قاعدة البيانات
            @php
                $stats = \App\Models\Ticket::selectRaw('department, count(*) as total')
                            ->groupBy('department')
                            ->pluck('total', 'department');
            @endphp

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($stats->keys()) !!},
                    datasets: [{
                        label: 'Talep Sayısı',
                        data: {!! json_encode($stats->values()) !!},
                        backgroundColor: [
                            '#0dcaf0', // Genel
                            '#198754', // Muhasebe
                            '#ffc107', // Teknik Destek
                            '#dc3545', // İptal
                            '#6c757d'  // Diğer
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
</body>
</html>