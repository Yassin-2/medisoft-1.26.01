<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Patient Model - Handles patient-related database operations
 */
class PatientModel extends BaseModel {

    /**
     * Rwanda province names indexed by province_id (1–5)
     */
    private const PROVINCES = [
        1 => 'Eastern Province',
        2 => 'Kigali City',
        3 => 'Southern Province',
        4 => 'Western Province',
        5 => 'Northern Province',
    ];

    /**
     * Get patient details by ID from the real patients table.
     *
     * Joins:
     *  - districts_client  → district name + province_id
     *  - cells_client      → cell name  (cell_id = patients.cellule)
     *
     * Falls back to anc_consultation for catchment_area and
     * dental_consultation for latest consultation_date.
     */
    public function getPatient(int $patientId): array {
        if ($patientId <= 0) {
            $this->respond(false, 'patient_id required', [], 422);
        }

        // ── Core patient row ──────────────────────────────────────────────
        $stmt = $this->db->prepare("
            SELECT
                p.patient_id,
                p.family_name,
                p.given_name,
                p.beneficiary,
                p.insurance_code,
                p.district   AS district_id,
                p.sector     AS sector_id,
                p.cellule    AS cell_id,
                p.village    AS village_code,
                p.sex,
                p.age,
                dc.district  AS district_name,
                dc.province_id,
                cc.cell      AS cell_name
            FROM patients p
            LEFT JOIN districts_client dc ON dc.district_id = p.district
            LEFT JOIN cells_client     cc ON cc.cell_id     = p.cellule
            WHERE p.patient_id = :pid
            LIMIT 1
        ");
        $stmt->execute([':pid' => $patientId]);
        $p = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Build display name
        $patientName = null;
        if ($p) {
            if (!empty($p['given_name']) || !empty($p['family_name'])) {
                $patientName = trim(($p['given_name'] ?? '') . ' ' . ($p['family_name'] ?? ''));
            } elseif (!empty($p['beneficiary'])) {
                $patientName = $p['beneficiary'];
            }
        }

        // Province name from province_id
        $province = null;
        if ($p && !empty($p['province_id'])) {
            $pid2 = (int)$p['province_id'];
            $province = self::PROVINCES[$pid2] ?? null;
        }

        // Insurance display value – use the insurance card number / code
        $insurance = ($p && !empty($p['insurance_code'])) ? $p['insurance_code'] : null;

        // District / cell names
        $districtName = ($p && !empty($p['district_name'])) ? $p['district_name'] : null;
        $cellName     = ($p && !empty($p['cell_name']))     ? $p['cell_name']     : null;

        // Sector: no name table available – show the numeric code
        $sectorCode = ($p && !empty($p['sector_id'])) ? (string)$p['sector_id'] : null;

        // Village: no name lookup table – show the code as-is
        $villageCode = ($p && !empty($p['village_code'])) ? (string)$p['village_code'] : null;

        $row = [
            'patient_id'        => $patientId,
            'patient_name'      => $patientName,
            'insurance'         => $insurance,
            'consultation_date' => null,
            'catchment_area'    => null,
            'province'          => $province,
            'district'          => $districtName,
            'sector'            => $sectorCode,
            'cell'              => $cellName,
            'village'           => $villageCode,
        ];

        // ── Latest dental consultation date ───────────────────────────────
        $stmt2 = $this->db->prepare(
            "SELECT created_at FROM dental_consultation
             WHERE patient_id = :id
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt2->execute([':id' => $patientId]);
        $r2 = $stmt2->fetch(\PDO::FETCH_ASSOC);
        if ($r2 && !empty($r2['created_at'])) {
            $row['consultation_date'] = $r2['created_at'];
        }

        // ── Catchment area from ANC consultation (best available proxy) ──
        $stmt3 = $this->db->prepare(
            "SELECT catchment_area, visit_date
             FROM anc_consultation
             WHERE patient_id = :id
             ORDER BY visit_date DESC, created_at DESC
             LIMIT 1"
        );
        $stmt3->execute([':id' => $patientId]);
        $r3 = $stmt3->fetch(\PDO::FETCH_ASSOC);
        if ($r3) {
            $row['catchment_area'] = $r3['catchment_area'] ?? null;
            // Fall back to ANC visit date if no dental consultation date
            if ($row['consultation_date'] === null && !empty($r3['visit_date'])) {
                $row['consultation_date'] = $r3['visit_date'];
            }
        }

        return $row;
    }
}
