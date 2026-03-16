<?php
// Business Permit

$extra = is_array($extra ?? null) ? $extra : [];

/* =========================
   EXTRA DATA
========================= */
$status           = trim((string)($extra['status'] ?? 'NEW'));
$yearValue        = trim((string)($extra['year'] ?? date('Y')));

$business_name    = trim((string)($extra['business_name'] ?? ''));
$owner_name       = trim((string)($extra['owner_name'] ?? ''));
$business_type    = trim((string)($extra['business_type'] ?? ''));
$building         = trim((string)($extra['building'] ?? ''));
$rent_amount      = trim((string)($extra['rent_amount'] ?? ''));
$capitalization   = trim((string)($extra['capitalization'] ?? ''));
$prior_sales      = trim((string)($extra['sales_prior_year'] ?? ($extra['prior_sales'] ?? '')));
$ownership_type   = trim((string)($extra['ownership_type'] ?? ''));
$owner_operator   = trim((string)($extra['operator'] ?? ($extra['owner_operator'] ?? $owner_name)));
$business_address = trim((string)($extra['business_address'] ?? ''));
$association_name = trim((string)($extra['association_name'] ?? ''));

/* =========================
   DOC / PAYMENT DATA
========================= */
$form_no           = trim((string)($doc['form_no'] ?? ''));
$or_no             = trim((string)($doc['or_no'] ?? ''));
$business_plate_no = trim((string)($doc['business_plate_no'] ?? ''));
$sticker_no        = trim((string)($doc['sticker_no'] ?? ''));
$contact_no        = trim((string)($doc['contact_no'] ?? ''));
$date_paid_raw     = trim((string)($doc['date_paid'] ?? ''));
$amount_paid_raw   = (string)($doc['amount_paid'] ?? '');

/* =========================
   DEFAULTS / FALLBACKS
========================= */
$punong_barangay = trim((string)($extra['punong_barangay'] ?? 'HON. MARILYN F. BURGOS'));

if ($business_name === '' && !empty($doc['document_name'])) {
    $business_name = trim((string)($doc['document_name']));
}

if ($owner_name === '' && !empty($doc['resident_name'])) {
    $owner_name = trim((string)($doc['resident_name']));
}

if ($business_address === '' && !empty($doc['resident_address'])) {
    $business_address = trim((string)($doc['resident_address']));
}

if ($owner_operator === '') {
    $owner_operator = $owner_name;
}

/* =========================
   FORMATTERS
========================= */
$displayDateRaw = trim((string)($extra['display_date'] ?? ''));
if ($displayDateRaw === '') {
    $displayDateRaw = $date_paid_raw !== '' ? $date_paid_raw : (string)($doc['requested_at'] ?? '');
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

$date_paid = '';
if ($date_paid_raw !== '') {
    $tsPaid = strtotime($date_paid_raw);
    $date_paid = $tsPaid ? date('F d, Y', $tsPaid) : $date_paid_raw;
}

$amount_paid = '';
if ($amount_paid_raw !== '') {
    $amount_paid = is_numeric($amount_paid_raw)
        ? number_format((float)$amount_paid_raw, 2)
        : $amount_paid_raw;
}

$capText = $capitalization !== '' ? $capitalization : '________________';
$priorSalesText = $prior_sales !== '' ? $prior_sales : '________________';

$buildingLower = strtolower($building);
if ($buildingLower === 'own' || $buildingLower === 'owned') {
    $buildingText = 'Owned (/)   Rented ( )';
} elseif ($buildingLower === 'rent' || $buildingLower === 'rented') {
    $buildingText = 'Owned ( )   Rented (/)';
    if ($rent_amount !== '') {
        $buildingText .= '   Rent Amount: ' . $rent_amount;
    }
} else {
    $buildingText = $building !== '' ? $building : '________________';
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
    .bp-doc-title{
        font-size:16pt;
        font-weight:bold;
        text-align:center;
        margin:0 0 10px;
    }

    .bp-doc-date{
        text-align:right;
        padding-right:8px;
        font-weight:700;
        font-size:12px;
        margin:0 0 4px;
    }

    .bp-center-line{
        text-align:right;
        padding-right:10px;
        font-size:12px;
        margin:0 0 8px;
    }

    .bp-status-text{
        font-weight:bold;
        color:#800303;
    }

    .bp-intro{
        font-size:10.5pt;
        font-weight:bold;
        margin:0 0 6px;
    }

    .bp-fields{
        width:100%;
        border-collapse:collapse;
        margin-bottom:8px;
        font-size:12px;
    }

    .bp-fields td{
        vertical-align:top;
        padding:1px 0;
        line-height:1.2;
    }

    .bp-fields .label{
        width:220px;
        font-weight:700;
    }

    .bp-fields .colon{
        width:14px;
        text-align:center;
        font-weight:700;
    }

    .bp-fields .value{
        font-weight:700;
    }

    .bp-paragraph{
        text-align:justify;
        line-height:1.3;
        font-size:12px;
        margin:6px 0;
    }

    .bp-owner-block{
        width:250px;
        margin:8px 0 10px auto;
        text-align:center;
        line-height:1.25;
        font-size:12px;
    }

    .bp-owner-name{
        margin-top:4px;
        font-weight:700;
        text-transform:uppercase;
    }

    .bp-section-title{
        font-weight:700;
        font-size:14px;
        margin:10px 0 4px;
    }

    .bp-sign-line{
        text-align:center;
        margin:8px 0 10px;
        font-size:12px;
        line-height:1.25;
    }

    .bp-office{
        line-height:1.25;
        font-size:12px;
        margin:2px 0 6px;
    }

    .bp-bottom{
        width:58%;
        margin-top:6px;
    }

    .bp-captain{
        width:240px;
        margin:12px 0 0 auto;
        text-align:center;
        font-size:12px;
        line-height:1.2;
    }

    .bp-captain strong{
        text-transform:uppercase;
        text-decoration:underline;
    }

    .bp-note{
        margin-top:10px;
        font-size:10px;
        font-style:italic;
    }
</style>

<div class="bp-doc-title">BARANGAY BUSINESS CLEARANCE</div>

<div class="bp-doc-date"><?= e($displayDate) ?></div>

<div class="bp-center-line">
    BUSINESS CLEARANCE STATUS :
    <span class="bp-status-text"><?= e($status) ?></span>
</div>

<p class="bp-intro">
    The undersigned respectfully requests for License/Permit for the Year <?= e($yearValue) ?>
</p>

<table class="bp-fields">
    <tr>
        <td class="label">BUSINESS NAME</td>
        <td class="colon">:</td>
        <td class="value"><?= e($business_name) ?></td>
    </tr>
    <tr>
        <td class="label">OWNER'S NAME / C/O</td>
        <td class="colon">:</td>
        <td class="value"><?= e($owner_name) ?></td>
    </tr>
    <tr>
        <td class="label">TYPE OF BUSINESS / ACTIVITY</td>
        <td class="colon">:</td>
        <td class="value"><?= e($business_type) ?></td>
    </tr>
    <tr>
        <td class="label">BUILDING</td>
        <td class="colon">:</td>
        <td class="value"><?= e($buildingText) ?></td>
    </tr>
    <tr>
        <td class="label">CAPITALIZATION</td>
        <td class="colon">:</td>
        <td class="value"><?= e($capText) ?></td>
    </tr>
    <tr>
        <td class="label">SALES FROM PRIOR YEAR</td>
        <td class="colon">:</td>
        <td class="value">P <?= e($priorSalesText) ?></td>
    </tr>
    <tr>
        <td class="label">TYPE OF OWNERSHIP</td>
        <td class="colon">:</td>
        <td class="value"><?= e($ownership_type) ?></td>
    </tr>
</table>

<p class="bp-paragraph">
    I hereby bind myself further subject to the provisions of the existing Barangay and City
    Ordinances and Rules and Regulations governing the issuance of this License / Permit.
</p>

<div class="bp-owner-block">
    <div>Very truly yours,</div>
    <div class="bp-owner-name"><?= e($owner_operator) ?></div>
    <div>Manager / Owner / Operator</div>
</div>

<div class="bp-section-title">FIRST ENDORSEMENT</div>

<p class="bp-paragraph">
    The application is a member of <?= e($association_name !== '' ? $association_name : '________________') ?>
    and was briefed on the existing requirements concerning the operation of the business specified
    herein and agrees to comply with the Rules and Regulations of the association governing it.
    This is therefore favorably endorsed for approval.
</p>

<div class="bp-sign-line">
    _______________________________<br>
    Association / Homeowners President
</div>

<div class="bp-section-title">SECOND ENDORSEMENT</div>

<div class="bp-office">
    <div>The Business Permit and Licensing Officer</div>
    <div>Office of the City Mayor</div>
    <div>Parañaque City</div>
</div>

<p><strong>SIR/MADAM:</strong></p>

<p class="bp-paragraph">
    In compliance with the City requirement regarding the issuance of business license / permit and
    subject to the Rules and Regulations governing it, this endorsement is hereby forwarded to your
    office for favorable action in favor of <strong><?= e($owner_name) ?></strong>
    for <strong><?= e($business_name) ?></strong> located at
    <strong><?= e($business_address) ?></strong>.
</p>

<table class="bp-fields bp-bottom">
    <tr>
        <td class="label">FORM NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= e($form_no) ?></td>
    </tr>
    <tr>
        <td class="label">OR NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= e($or_no) ?></td>
    </tr>
    <tr>
        <td class="label">BUSINESS PLATE NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= e($business_plate_no) ?></td>
    </tr>
    <tr>
        <td class="label">STICKER NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= e($sticker_no) ?></td>
    </tr>
    <tr>
        <td class="label">CONTACT NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= e($contact_no) ?></td>
    </tr>
    <tr>
        <td class="label">DATE PAID</td>
        <td class="colon">:</td>
        <td class="value"><?= e($date_paid) ?></td>
    </tr>
    <tr>
        <td class="label">AMOUNT PAID</td>
        <td class="colon">:</td>
        <td class="value">P <?= e($amount_paid !== '' ? $amount_paid : '0.00') ?></td>
    </tr>
</table>

<div class="bp-captain">
    <strong><?= e($punong_barangay) ?></strong><br>
    Punong Barangay
</div>

<div class="bp-note">NOTE: Not valid without official seal.</div>