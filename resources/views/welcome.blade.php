<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Destek Merkezi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- بطاقة إرسال التذكرة -->
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">🎧 Akıllı Destek Merkezi</h4>
                </div>
                <div class="card-body p-4">
                    
                    <!-- رسالة النجاح -->
                    @if(session('success'))
                        <div class="alert alert-success fw-bold text-center">
                            ✅ {{ session('success') }}
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning fw-bold text-center">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <!-- بداية الفورم -->
                    <!-- ملاحظة هامة: أضفنا enctype="multipart/form-data" لكي يسمح الفورم برفع الملفات -->
                    <form action="{{ url('/tickets') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Adınız Soyadınız</label>
                            <input type="text" name="customer_name" class="form-control" required placeholder="Örn: Ahmet Yılmaz">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mesajınız / Şikayetiniz</label>
                            <textarea name="message" class="form-control" rows="4" required placeholder="Lütfen karşılaştığınız sorunu veya talebinizi detaylıca yazın..."></textarea>
                        </div>

                        <!-- الحقل الجديد: إرفاق ملف -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Dosya Ekle (Opsiyonel) 📎</label>
                            <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">Dekont, hasarlı ürün fotoğrafı veya fatura ekleyebilirsiniz.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Talebi Gönder 🚀</button>
                    </form>
                    <!-- نهاية الفورم -->

                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="{{ url('/admin') }}" class="text-muted text-decoration-none small">Yönetim Paneline Git</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>