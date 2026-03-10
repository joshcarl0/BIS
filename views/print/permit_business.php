<?php
// Business Permit

$extra = is_array($extra ?? null) ? $extra : [];

$status              = trim((string)($extra['status'] ?? 'NEW'));
$year                = trim((string)($extra['year'] ?? date('Y')));

$business_name       = trim((string)($extra['business_name'] ?? ''));
$owner_name          = trim((string)($extra['owner_name'] ?? ''));
$business_type       = trim((string)($extra['business_type'] ?? ''));
$building            = trim((string)($extra['building'] ?? ''));
$rent_amount         = trim((string)($extra['rent_amount'] ?? ''));
$capitalization      = trim((string)($extra['capitalization'] ?? ''));
$prior_sales         = trim((string)($extra['prior_sales'] ?? ''));
$ownership_type      = trim((string)($extra['ownership_type'] ?? ''));
$owner_operator      = trim((string)($extra['owner_operator'] ?? $owner_name));
$business_address    = trim((string)($extra['business_address'] ?? ''));
$association_name    = trim((string)($extra['association_name'] ?? ''));

// For printing only
$form_no             = trim((string)($extra['form_no'] ?? ''));
$or_no               = trim((string)($extra['or_no'] ?? ''));
$business_plate_no   = trim((string)($extra['business_plate_no'] ?? ''));
$sticker_no          = trim((string)($extra['sticker_no'] ?? ''));
$contact_no          = trim((string)($extra['contact_no'] ?? ''));
$date_paid           = trim((string)($extra['date_paid'] ?? ''));
$amount_paid         = trim((string)($extra['amount_paid'] ?? ''));

// Punong Barangay
$punong_barangay     = trim((string)($extra['punong_barangay'] ?? 'HON. MARILYN F. BURGOS'));

/**
 * Display date at upper-right
 */
$displayDateRaw = trim((string)($extra['display_date'] ?? ''));
if ($displayDateRaw === '') {
    $displayDateRaw = $date_paid !== '' ? $date_paid : (string)($doc['requested_at'] ?? '');
}

$displayDate = '';
if ($displayDateRaw !== '') {
    $ts = strtotime($displayDateRaw);
    $displayDate = $ts ? strtoupper(date('F d, Y', $ts)) : strtoupper($displayDateRaw);
} else {
    $displayDate = strtoupper(date('F d, Y'));
}

// Building label
$buildingLower = strtolower($building);
if ($buildingLower === 'own' || $buildingLower === 'owned') {
    $buildingText = 'Owned (/) Rented (/) if Rented how much';
} elseif ($buildingLower === 'rent' || $buildingLower === 'rented') {
    $buildingText = 'Owned (/) Rented (/) if Rented how much ' . $rent_amount;
} else {
    $buildingText = $building !== '' ? $building : 'Owned (/) Rented (/) if Rented how much';
}
?>

<style>
    .bp-doc-title{
        font-size:18pt;
        font-weight:bold;
        text-align:center;
        margin-bottom:20px;
    }

    .bp-doc-date{
        text-align:right;
        padding-right:15px;
        font-weight:700;
        font-size:15px;
        margin:0 0 12px;
    }

    .bp-center-line{
        text-align:right;
        padding-right:40px;
        font-size:16px;
        margin-bottom:18px;
        margin-top:10px;
    }

    .bp-status-text{
        font-weight:bold;
        color:#800303;
    }

    .bp-intro{
        font-size:11pt;
        font-weight:bold;
        padding-top:8px;
        margin-bottom:10px;
    }

    .bp-fields{
        width:100%;
        border-collapse:collapse;
        margin-bottom:14px;
        font-size:16px;
    }

    .bp-fields td{
        vertical-align:top;
        padding:1px 0;
    }

    .bp-fields .label{
        width:290px;
        font-weight:700;
    }

    .bp-fields .colon{
        width:18px;
        text-align:center;
        font-weight:700;
    }

    .bp-fields .value{
        font-weight:700;
    }

    .bp-paragraph{
        text-align:justify;
        line-height:1.5;
        font-size:16px;
        margin:10px 0;
    }

    .bp-owner-block{
        width:310px;
        margin:18px 0 22px auto;
        text-align:center;
        line-height:1.5;
        font-size:16px;
    }

    .bp-owner-name{
        margin-top:10px;
        font-weight:700;
        text-transform:uppercase;
    }

    .bp-section-title{
        font-weight:700;
        font-size:20px;
        margin:22px 0 8px;
    }

    .bp-sign-line{
        text-align:center;
        margin:20px 0 24px;
        font-size:16px;
        line-height:1.5;
    }

    .bp-office{
        line-height:1.5;
        font-size:16px;
        margin:6px 0 12px;
    }

    .bp-bottom{
        width:58%;
        margin-top:14px;
    }

    .bp-captain{
        width:300px;
        margin:30px 0 0 auto;
        text-align:center;
        font-size:16px;
        line-height:1.3;
    }

    .bp-captain strong{
        text-transform:uppercase;
        text-decoration:underline;
    }

    .bp-note{
        margin-top:26px;
        font-size:14px;
        font-weight:600;
        font-style:italic;
    }
</style>

<div class="bp-doc-title">BARANGAY BUSINESS CLEARANCE</div>

<div class="bp-doc-date"><?= htmlspecialchars($displayDate) ?></div>

<div class="bp-center-line">
    BUSINESS CLEARANCE STATUS :
    <span class="bp-status-text"><?= htmlspecialchars($status) ?></span>
</div>

<p class="bp-intro">
    The undersigned respectfully requests for License/Permit for the Year <?= htmlspecialchars($year) ?>
</p>

<table class="bp-fields">
    <tr>
        <td class="label">BUSINESS NAME</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($business_name) ?></td>
    </tr>
    <tr>
        <td class="label">OWNER'S NAME/ C/O</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($owner_name) ?></td>
    </tr>
    <tr>
        <td class="label">TYPE OF BUSINESS/ACTIVITY</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($business_type) ?></td>
    </tr>
    <tr>
        <td class="label">BUILDING</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($buildingText) ?></td>
    </tr>
    <tr>
        <td class="label">CAPITALIZATION</td>
        <td class="colon">:</td>
        <td class="value">
            <?= htmlspecialchars($capitalization) ?>
            &nbsp;&nbsp;&nbsp; Sales from Prior Year P <?= htmlspecialchars($prior_sales) ?>
        </td>
    </tr>
    <tr>
        <td class="label">TYPE OF OWNERSHIP</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($ownership_type) ?></td>
    </tr>
</table>

<p class="bp-paragraph">
    I hereby bind myself further subject to the provisions of the existing Barangay and City
    Ordinances / Rules and Regulations governing the issuance of this License / Permit
</p>

<div class="bp-owner-block">
    <div>Very truly yours,</div>
    <div class="bp-owner-name"><?= htmlspecialchars($owner_operator) ?></div>
    <div>Manager / Owner / Operator</div>
</div>

<div class="bp-section-title">FIRST ENDORSEMENT</div>

<p class="bp-paragraph">
    The application is a member of <?= htmlspecialchars($association_name) ?>
    and was briefed on the existing requirements anent the operation of business specified herein
    and adheres to the following Rules and Regulations of the association governing it. This
    therefore, is favorably endorsed for approval.
</p>

<div class="bp-sign-line">
    _______________________________<br>
    Association Homeowners President
</div>

<div class="bp-section-title">SECOND ENDORSEMENT</div>

<div class="bp-office">
    <div>The Business Permit and Licensing Officer</div>
    <div>Office of the City Mayor</div>
    <div>Parañaque City</div>
</div>

<p><strong>SIR/MADAM:</strong></p>

<p class="bp-paragraph">
    In compliance with the City requirement regarding the issuance of business license/permit and
    subject to the Rules and Regulations governing it, this endorsement is hereby forwarded to your
    office for favorable action in favor of <strong><?= htmlspecialchars($owner_name) ?></strong>
    ( <strong><?= htmlspecialchars($business_name) ?></strong> ) located
    <strong><?= htmlspecialchars($business_address) ?></strong>.
</p>

<table class="bp-fields bp-bottom">
    <tr>
        <td class="label">FORM NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($form_no) ?></td>
    </tr>
    <tr>
        <td class="label">OR NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($or_no) ?></td>
    </tr>
    <tr>
        <td class="label">BUSINESS PLATE NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($business_plate_no) ?></td>
    </tr>
    <tr>
        <td class="label">STICKER NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($sticker_no) ?></td>
    </tr>
    <tr>
        <td class="label">CONTACT NO.</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($contact_no) ?></td>
    </tr>
    <tr>
        <td class="label">DATE PAID</td>
        <td class="colon">:</td>
        <td class="value"><?= htmlspecialchars($date_paid) ?></td>
    </tr>
    <tr>
        <td class="label">AMOUNT PAID</td>
        <td class="colon">:</td>
        <td class="value">P <?= htmlspecialchars($amount_paid) ?></td>
    </tr>
</table>

<div class="bp-captain">
    <strong><?= htmlspecialchars($punong_barangay) ?></strong><br>
    Punong Barangay
</div>

<div class="bp-note">NOTE : Not valid without official seal</div>