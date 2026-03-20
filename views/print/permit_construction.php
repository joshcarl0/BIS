<?php
// Construction Permit

$extra = is_array($extra ?? null) ? $extra : [];

/* =========================
   EXTRA DATA
========================= */
$permit_title         = trim((string)($extra['permit_title'] ?? 'BARANGAY CLEARANCE FOR CONSTRUCTION PERMIT'));
$construction_details = trim((string)($extra['construction_details'] ?? 'PROPOSED CONSTRUCTION'));
$applicant_name       = trim((string)($extra['applicant_name'] ?? ''));
$applicant_address    = trim((string)($extra['applicant_address'] ?? ''));
$permit_use           = trim((string)($extra['permit_use'] ?? 'PERMIT TO CONSTRUCT'));
$issued_at_text       = trim((string)($extra['issued_at_text'] ?? 'PARAÑAQUE CITY'));
$punong_barangay      = trim((string)($extra['punong_barangay'] ?? 'HON. MARILYN F. BURGOS'));

/* =========================
   DOC / PAYMENT DATA
========================= */
$clearance_no      = trim((string)($doc['ref_no'] ?? ''));
$official_receipt  = trim((string)($doc['official_receipt'] ?? ($doc['or_no'] ?? '')));
$issued_on_raw     = trim((string)($doc['issued_at'] ?? ($doc['date_paid'] ?? '')));
$amount_paid_raw   = (string)($doc['amount_paid'] ?? ($doc['fee_snapshot'] ?? ''));

/* =========================
   DEFAULTS / FALLBACKS
========================= */
if ($applicant_name === '' && !empty($doc['resident_name'])) {
    $applicant_name = trim((string)$doc['resident_name']);
}

if ($applicant_address === '' && !empty($doc['resident_address'])) {
    $applicant_address = trim((string)$doc['resident_address']);
}

/* =========================
   FORMATTERS
========================= */
$displayDateRaw = trim((string)($extra['display_date'] ?? ''));
if ($displayDateRaw === '') {
    $displayDateRaw = $issued_on_raw !== '' ? $issued_on_raw : (string)($doc['requested_at'] ?? '');
}

$displayDate = strtoupper(date('F d, Y'));
if ($displayDateRaw !== '') {
    $ts = strtotime($displayDateRaw);
    if ($ts) {
        $displayDate = strtoupper(date('F d, Y', $ts));
    } else {
        $displayDate = strtoupper($displayDateRaw);
    }
}

$issued_on = '';
if ($issued_on_raw !== '') {
    $tsIssued = strtotime($issued_on_raw);
    $issued_on = $tsIssued ? date('F d, Y', $tsIssued) : $issued_on_raw;
}

$amount_paid = '';
if ($amount_paid_raw !== '') {
    $amount_paid = is_numeric($amount_paid_raw)
        ? number_format((float)$amount_paid_raw, 2)
        : $amount_paid_raw;
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
    .cp-doc-title{
        font-size:16pt;
        font-weight:bold;
        text-align:center;
        margin:0 0 10px;
        letter-spacing:.5px;
    }

    .cp-doc-date{
        text-align:right;
        padding-right:8px;
        font-weight:700;
        font-size:12px;
        margin:0 0 10px;
    }

    .cp-greeting{
        font-size:10.5pt;
        font-weight:bold;
        margin:0 0 8px;
    }

    .cp-paragraph{
        text-align:justify;
        line-height:1.35;
        font-size:12px;
        margin:6px 0;
    }

    .cp-center-block{
        text-align:center;
        margin:14px 0 12px;
        font-size:12px;
        line-height:1.25;
    }

    .cp-main-title{
        font-weight:700;
        text-transform:uppercase;
        text-decoration:underline;
        margin-bottom:10px;
    }

    .cp-line{
        width:62%;
        margin:8px auto 2px;
        border-bottom:1px solid #000;
        min-height:18px;
        font-weight:700;
        padding-bottom:2px;
    }

    .cp-caption{
        font-size:11px;
        font-style:italic;
        margin-bottom:6px;
    }

    .cp-signature{
        width:240px;
        margin:18px 0 0 auto;
        text-align:center;
        font-size:12px;
        line-height:1.2;
    }

    .cp-signature strong{
        text-transform:uppercase;
        text-decoration:underline;
    }

    .cp-bottom{
        width:58%;
        margin-top:14px;
        font-size:12px;
    }

    .cp-fields{
        width:100%;
        border-collapse:collapse;
    }

    .cp-fields td{
        vertical-align:top;
        padding:1px 0;
        line-height:1.2;
    }

    .cp-fields .label{
        width:170px;
        font-weight:700;
    }

    .cp-fields .colon{
        width:14px;
        text-align:center;
        font-weight:700;
    }

    .cp-fields .value{
        font-weight:700;
    }

    .cp-note{
        margin-top:10px;
        font-size:10px;
        font-style:italic;
    }
</style>

<div class="cp-doc-title">BARANGAY PERMIT</div>

<div class="cp-doc-date"><?= e($displayDate) ?></div>

<p class="cp-greeting">TO WHOM IT MAY CONCERN:</p>

<p class="cp-paragraph">
    This is to certify that the business or trade activity or transaction described below:
</p>

<div class="cp-center-block">
    <div class="cp-main-title">
        <?= e($permit_title) ?><br>
        <?= e($construction_details) ?>
    </div>

    <div class="cp-line"><?= e($applicant_name) ?></div>
    <div class="cp-caption">(Name of Applicant)</div>

    <div class="cp-line"><?= e($applicant_address) ?></div>
    <div class="cp-caption">(Address / Location of Applicant)</div>
</div>

<p class="cp-paragraph">
    Proposed to be established in this Barangay and is being applied for a Barangay Clearance
    to be used in securing the corresponding City Mayor’s Permit / Renovation Permit /
    Excavation Permit / Permit to Construct / Installation, Maintain and Operate /
    Demolition Permit / Police Traffic Permit / Electrical Permit / Maynilad Permit /
    DENR Permit (No Objection to Cut or Move Trees) / Installation of Fiber Optic Cable /
    Installation / Re-installation of Aerial Cable / Repair, and has been found to be in
    conformity with the provisions of existing Barangay ordinances, rules and regulations
    being enforced in the Barangay.
</p>

<p class="cp-paragraph">
    In view of the foregoing, this Barangay, through the undersigned, interposes no objection
    for the issuance of the corresponding <strong><?= e($permit_use) ?></strong> being applied for.
</p>

<div class="cp-signature">
    <strong><?= e($punong_barangay) ?></strong><br>
    Punong Barangay
</div>

<div class="cp-bottom">
    <table class="cp-fields">
        <tr>
            <td class="label">BRGY. CLEARANCE NO.</td>
            <td class="colon">:</td>
            <td class="value"><?= e($clearance_no) ?></td>
        </tr>
        <tr>
            <td class="label">AMOUNT PAID</td>
            <td class="colon">:</td>
            <td class="value">P <?= e($amount_paid !== '' ? $amount_paid : '0.00') ?></td>
        </tr>
        <tr>
            <td class="label">OFFICIAL RECEIPT</td>
            <td class="colon">:</td>
            <td class="value"><?= e($official_receipt) ?></td>
        </tr>
        <tr>
            <td class="label">ISSUED ON</td>
            <td class="colon">:</td>
            <td class="value"><?= e($issued_on) ?></td>
        </tr>
        <tr>
            <td class="label">ISSUED AT</td>
            <td class="colon">:</td>
            <td class="value"><?= e($issued_at_text) ?></td>
        </tr>
    </table>
</div>

<div class="cp-note">NOTE: Not valid without official seal.</div>