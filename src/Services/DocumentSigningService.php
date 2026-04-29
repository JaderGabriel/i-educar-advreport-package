<?php

namespace iEducar\Packages\AdvancedReports\Services;

use DateTimeInterface;
use Illuminate\Support\Carbon;

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
     * Instante da emissão no formato usado no HMAC (UTC, precisão de segundos).
     * Evita divergência entre o instante usado na assinatura e o recuperado após
     * persistência (ex.: timestamp sem microssegundos / jsonb reordenando chaves).
     */
    public static function issuedAtForMac(DateTimeInterface $issuedAt): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', Carbon::parse($issuedAt)->utc()->getTimestamp());
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function normalizePayloadForMac(array $payload): array
    {
        ksort($payload);

        foreach ($payload as $k => $v) {
            if (is_array($v) && $this->isAssociativeArray($v)) {
                $payload[$k] = $this->normalizePayloadForMac($v);
            }
        }

        return $payload;
    }

    /**
     * Calcula HMAC do documento usando APP_KEY.
     *
     * @param array<string, mixed> $payload
     */
    public function mac(string $code, string $type, string $issuedAtIso, array $payload, bool $normalizePayload = true): string
    {
        $key = $this->keyBytes();
        $payloadForSign = $normalizePayload ? $this->normalizePayloadForMac($payload) : $payload;

        $data = json_encode([
            'v' => self::VERSION,
            'code' => $code,
            'type' => $type,
            'issued_at' => $issuedAtIso,
            'payload' => $payloadForSign,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash_hmac('sha256', $data ?: '', $key);
    }

    public function verify(string $expectedMac, string $code, string $type, string $issuedAtIso, array $payload, bool $normalizePayload = true): bool
    {
        $mac = $this->mac($code, $type, $issuedAtIso, $payload, $normalizePayload);

        return hash_equals($expectedMac, $mac);
    }

    /**
     * Valida MAC após persistência: tenta combinações de instante (canônico / ISO
     * com micros) e de payload (normalizado ou ordem bruta, com ou sem validation_url),
     * cobrindo documentos novos e emissões anteriores à normalização.
     *
     * @param array<string, mixed> $payload Payload como recuperado do armazenamento.
     */
    public function verifyStoredDocument(string $expectedMac, string $code, string $type, ?DateTimeInterface $issuedAt, array $payload): bool
    {
        if ($expectedMac === '' || $issuedAt === null) {
            return false;
        }

        $issuedCandidates = array_values(array_unique(array_filter([
            self::issuedAtForMac($issuedAt),
            Carbon::parse($issuedAt)->utc()->toISOString(),
        ])));

        $payloadBases = [
            ['payload' => $payload, 'normalize' => true],
            ['payload' => $payload, 'normalize' => false],
        ];

        if (array_key_exists('validation_url', $payload)) {
            $without = $payload;
            unset($without['validation_url']);
            $payloadBases[] = ['payload' => $without, 'normalize' => true];
            $payloadBases[] = ['payload' => $without, 'normalize' => false];
        }

        foreach ($issuedCandidates as $issuedIso) {
            foreach ($payloadBases as $row) {
                if ($this->verify($expectedMac, $code, $type, $issuedIso, $row['payload'], $row['normalize'])) {
                    return true;
                }
            }
        }

        return false;
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

    /**
     * @param array<mixed> $arr
     */
    private function isAssociativeArray(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
