<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class N8nWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_n8n_webhook_creates_a_ticket_with_analysis_data(): void
    {
        config(['services.n8n.api_token' => 'test-token']);

        $response = $this->withHeader('Authorization', 'Bearer test-token')
            ->postJson('/api/n8n/tickets', [
                'customer_name' => 'Ayşe Yılmaz',
                'message' => 'Kargom hâlâ ulaşmadı.',
                'source' => 'whatsapp',
                'department' => 'Kargo ve Teslimat',
                'priority' => 'Yüksek',
                'ai_analysis' => [
                    'category' => 'geciken kargo',
                    'summary' => 'Teslimat gecikmesi bildirildi.',
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('ticket.source', 'whatsapp')
            ->assertJsonPath('ticket.ai_analysis.category', 'geciken kargo');

        $this->assertDatabaseHas('tickets', [
            'customer_name' => 'Ayşe Yılmaz',
            'source' => 'whatsapp',
            'department' => 'Kargo ve Teslimat',
            'priority' => 'Yüksek',
        ]);
    }

    public function test_n8n_webhook_rejects_an_invalid_token(): void
    {
        config(['services.n8n.api_token' => 'test-token']);

        $response = $this->withHeader('Authorization', 'Bearer wrong-token')
            ->postJson('/api/n8n/tickets', [
                'customer_name' => 'Ayşe Yılmaz',
                'message' => 'Test',
                'source' => 'email',
            ]);

        $response->assertUnauthorized();
    }
}