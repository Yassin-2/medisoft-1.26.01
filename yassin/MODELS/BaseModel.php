<?php
declare(strict_types=1);

/**
 * Base Model class for database operations
 */
class BaseModel {
    protected PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    protected function respond(bool $ok, string $msg = '', array $data = [], int $code = 200): void {
        http_response_code($code);
        echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
        exit;
    }

    protected function toNullableString(mixed $v): ?string {
        if ($v === null) return null;
        $s = trim((string)$v);
        return $s === '' ? null : $s;
    }

    protected function toNullableInt(mixed $v): ?int {
        if ($v === null || $v === '') return null;
        if (!is_numeric($v)) return null;
        return (int)$v;
    }

    protected function toNullableFloat(mixed $v): ?float {
        if ($v === null || $v === '') return null;
        if (!is_numeric($v)) return null;
        return (float)$v;
    }

    protected function calcBmi(?float $heightCm, ?float $weightKg): ?float {
        if (!$heightCm || !$weightKg) return null;
        if ($heightCm <= 0 || $weightKg <= 0) return null;
        $hm = $heightCm / 100.0;
        return round($weightKg / ($hm * $hm), 2);
    }

    protected function bmiStatus(?float $bmi): ?string {
        if ($bmi === null) return null;
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }

    protected function bpStatus(?int $sys, ?int $dia): ?string {
        if ($sys === null || $dia === null) return null;
        if ($sys < 120 && $dia < 80) return 'Normal';
        if ($sys < 130 && $dia < 80) return 'Elevated';
        if ($sys < 140 || $dia < 90) return 'High Stage 1';
        return 'High Stage 2';
    }
}
