<?php

namespace iEducar\Packages\AdvancedReports\Services;

class DocumentSigningService
{
    public const VERSION = 1;

    /**
     * Gera um código público não sensível (alta entropia).
     */
    public function generateCode(int $bytes = 16): string
    {
        return strtoupper(bin2hex(random_bytes($bytes))); // 32 hex chars por default
    }

    /**
     * Calcula HMAC do documento usando APP_KEY.
     *
     * @param array<string, mixed> $payload
     */
    public function mac(string $code, string $type, string $issuedAtIso, array $payload): string
    {
        $key = $this->keyBytes();

        $data = json_encode([
            'v' => self::VERSION,
            'code' => $code,
            'type' => $type,
            'issued_at' => $issuedAtIso,
            'payload' => $payload,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash_hmac('sha256', $data ?: '', $key);
    }

    public function verify(string $expectedMac, string $code, string $type, string $issuedAtIso, array $payload): bool
    {
        $mac = $this->mac($code, $type, $issuedAtIso, $payload);

        return hash_equals($expectedMac, $mac);
    }

    private function keyBytes(): string
    {
        $appKey = (string) config('app.key');

        // Laravel app.key geralmente é "base64:...."
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $appKey;
    }
}

