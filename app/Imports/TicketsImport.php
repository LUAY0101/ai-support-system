<?php

namespace App\Imports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // تجاهل الصفوف الفارغة
        if (empty($row['isim']) || empty($row['mesaj'])) {
            return null;
        }

        $message = $row['mesaj'];
        
        // القيم الافتراضية في حال فشل الذكاء الاصطناعي
        $aiAnalysis = [
            'department' => 'Genel',
            'priority' => 'Orta',
            'order_number' => null,
            'amount' => null,
            'document_date' => null,
            'invoice_number' => null,
        ];

        try {
            $apiKey = env('GEMINI_API_KEY');
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Sen gelişmiş bir müşteri hizmetleri asistanısın. SADECE JSON formatında cevap ver.
Kurallar:
- department: 'Teknik Destek', 'Kargo', 'Muhasebe', 'İptal', 'Genel'
- priority: 'Düşük', 'Orta', 'Yüksek', 'Acil'
- order_number: Varsa yaz, yoksa null
- amount: Tutar varsa sadece rakam (örnek: 1250.50), yoksa null
- document_date: Tarih varsa 'YYYY-MM-DD', yoksa null
- invoice_number: Fatura/dekont no varsa yaz, yoksa null

Müşteri Mesajı: " . $message
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $jsonText = $response->json('candidates.0.content.parts.0.text');
                $jsonText = preg_replace('/```json|```/', '', $jsonText);
                $parsed = json_decode(trim($jsonText), true);
                if ($parsed) {
                    $aiAnalysis = array_merge($aiAnalysis, $parsed);
                }
            }
        } catch (\Exception $e) {
            Log::error('Excel Gemini API Hatası: ' . $e->getMessage());
        }

        return new Ticket([
            'customer_name'  => $row['isim'],
            'message'        => $message,
            'department'     => $aiAnalysis['department'],
            'priority'       => $aiAnalysis['priority'],
            'order_number'   => $aiAnalysis['order_number'],
            'amount'         => $aiAnalysis['amount'],
            'document_date'  => $aiAnalysis['document_date'],
            'invoice_number' => $aiAnalysis['invoice_number'],
            'status'         => 'Yeni',
        ]);
    }
}