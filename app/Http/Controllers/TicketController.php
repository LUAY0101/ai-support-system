<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TicketsImport;

class TicketController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $attachmentPath = 'uploads/' . $filename;
        }

        // تحليل النص بالذكاء الاصطناعي
        $aiAnalysis = $this->analyzeTicketWithAI($request->message, $attachmentPath);

        // حفظ التذكرة
        // حفظ التذكرة مع البيانات المالية الجديدة
        $ticket = Ticket::create([
            'customer_name' => $request->customer_name,
            'message' => $request->message,
            'source' => 'web',
            'attachment' => $attachmentPath,
            'department' => $aiAnalysis['department'] ?? 'Genel',
            'priority' => $aiAnalysis['priority'] ?? 'Orta',
            'order_number' => $aiAnalysis['order_number'] ?? null,
            'amount' => $aiAnalysis['amount'] ?? null,
            'document_date' => $aiAnalysis['document_date'] ?? null,
            'invoice_number' => $aiAnalysis['invoice_number'] ?? null,
            'ai_analysis' => $aiAnalysis ?: null,
            'status' => 'Yeni',
        ]);

        // 🔥 إرسال التذكرة إلى n8n
        try {
            $n8nWebhookUrl = config('services.n8n.webhook_url');

            if ($n8nWebhookUrl) {
                $response = Http::timeout(10)->post($n8nWebhookUrl, [
                    'ticket_id' => $ticket->id,
                    'customer_name' => $ticket->customer_name,
                    'department' => $ticket->department,
                    'priority' => $ticket->priority,
                    'source' => $ticket->source,
                    'order_number' => $ticket->order_number,
                    'message' => $ticket->message,
                    'ai_analysis' => $ticket->ai_analysis,
                ]);

                if ($response->failed()) {
                    Log::error('n8n Webhook Hatası: HTTP ' . $response->status());
                }
            }
        } catch (\Exception $e) {
            // في حال كان n8n مغلقاً، لا تعطل الموقع، فقط سجل الخطأ
            \Illuminate\Support\Facades\Log::error('n8n Connection Failed: ' . $e->getMessage());
        }

        if (empty($aiAnalysis)) {
            return redirect()->back()
                ->with('success', 'Talebiniz başarıyla alındı.')
                ->with('warning', 'AI analizi kullanılamadığı için talebiniz varsayılan bilgilerle kaydedildi.');
        }

        return redirect()->back()->with('success', 'Talebiniz başarıyla alındı ve analiz edildi!');
    }

    public function storeFromN8n(Request $request)
    {
        $configuredToken = config('services.n8n.api_token');
        $providedToken = $request->bearerToken() ?: $request->header('X-N8N-Token');

        if (!$configuredToken) {
            return response()->json(['message' => 'n8n API token yapılandırılmamış.'], 503);
        }

        if (!$providedToken || !hash_equals($configuredToken, $providedToken)) {
            return response()->json(['message' => 'Geçersiz API token.'], 401);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'source' => ['required', 'string', 'in:web,whatsapp,telegram,email,instagram'],
            'department' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'in:Düşük,Orta,Yüksek,Acil'],
            'ai_analysis' => ['nullable', 'array'],
            'attachment' => ['nullable', 'string', 'max:2048'],
            'order_number' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric'],
            'document_date' => ['nullable', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:Yeni,İnceliyor,Beklemede,Çözüldü'],
        ]);

        $ticket = Ticket::create([
            ...$validated,
            'department' => $validated['department'] ?? 'Genel',
            'priority' => $validated['priority'] ?? 'Orta',
            'status' => $validated['status'] ?? 'Yeni',
        ]);

        return response()->json([
            'message' => 'Talep başarıyla kaydedildi.',
            'ticket' => $ticket,
        ], 201);
    }

    private function analyzeTicketWithAI($message, $attachmentPath = null)
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            Log::warning('Gemini API anahtarı tanımlı değil; varsayılan ticket analizi kullanılacak.');
            return [];
        }
        
        $model = config('services.gemini.model');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

$parts = [
            [
                'text' => "Sen akıllı bir müşteri hizmetleri asistanı ve görsel kalite kontrol uzmanısın. Gönderilen metni ve (varsa) görseli analiz et. SADECE JSON formatında cevap ver.

Kurallar:
1. Görsel Analizi (Kritik): 
   - Eğer görsel fiziksel bir ürünse ve üzerinde kırık, çizik veya belirgin bir hasar varsa, department kesinlikle 'İptal ve İade', priority kesinlikle 'Acil' olmalıdır.
   - Eğer görsel bir fatura, fiş veya dekont ise, finansal bilgileri (amount, document_date, invoice_number) çıkar.
2. JSON Anahtarları:
- department: 'Teknik Destek', 'Kargo ve Teslimat', 'Muhasebe ve Fatura', 'İptal ve İade', 'Genel'
- priority: 'Düşük', 'Orta', 'Yüksek', 'Acil'
- order_number: Metinde sipariş no varsa yaz, yoksa null
- amount: Tutar varsa sadece rakam (örnek: 1250.50), yoksa null
- document_date: Tarih varsa 'YYYY-MM-DD', yoksa null
- invoice_number: Fatura/dekont no varsa yaz, yoksa null

Müşteri Mesajı: " . $message
            ]
        ];

        if ($attachmentPath && file_exists(public_path($attachmentPath))) {
            $mimeType = mime_content_type(public_path($attachmentPath));
            if ($mimeType == 'image/jpg') $mimeType = 'image/jpeg';

            if (in_array($mimeType, ['image/jpeg', 'image/png', 'application/pdf'])) {
                $base64Data = base64_encode(file_get_contents(public_path($attachmentPath)));
                $parts[] = [
                    'inlineData' => [
                        'mimeType' => $mimeType,
                        'data' => $base64Data
                    ]
                ];
            }
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => $parts]],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE']
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                $aiText = str_replace(['```json', '```'], '', $aiText);
                return json_decode(trim($aiText), true) ?? [];
            } else {
                Log::error('Gemini API Hatası: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Bağlantı Hatası: ' . $e->getMessage());
        }

        return [];
    }

    public function admin(Request $request)
    {
        $query = Ticket::query();

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->latest()->get();

        $stats = [
            'total' => Ticket::count(),
            'yeni' => Ticket::where('status', 'Yeni')->count(),
            'beklemede' => Ticket::where('status', 'Beklemede')->count(),
            'cozuldu' => Ticket::where('status', 'Çözüldü')->count(),
        ];

        $channelStats = Ticket::query()
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->orderByDesc('total')
            ->pluck('total', 'source');

        $repeatedComplaints = Ticket::query()
            ->whereNotNull('message')
            ->pluck('message')
            ->map(function ($message) {
                $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($message));
                return [
                    'message' => trim(preg_replace('/\s+/', ' ', $normalized)),
                    'original' => $message,
                ];
            })
            ->filter(fn ($complaint) => $complaint['message'] !== '')
            ->groupBy('message')
            ->map(function ($complaints) {
                return [
                    'message' => $complaints->first()['original'],
                    'count' => $complaints->count(),
                ];
            })
            ->filter(fn ($complaint) => $complaint['count'] > 1)
            ->sortByDesc('count')
            ->take(5)
            ->values();

        return view('admin', compact('tickets', 'stats', 'channelStats', 'repeatedComplaints'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Yeni,İnceliyor,Beklemede,Çözüldü'
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Talep durumu güncellendi!');
    }
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls'
        ]);

        // استدعاء ملف الاستيراد الذي يحتوي على ذكاء Gemini
        Excel::import(new TicketsImport, $request->file('excel_file'));

        return redirect()->back()->with('success', 'Excel dosyası başarıyla yüklendi ve AI tarafından işlendi!');
    }
    
}