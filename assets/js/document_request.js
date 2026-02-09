document.addEventListener("DOMContentLoaded", function () {
  const sel = document.getElementById('documentSelect');
  const dynamic = document.getElementById('dynamicFields');

  function updateInfo() {
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.dataset) return;

    document.getElementById('feeTxt').textContent  = opt.dataset.fee ? opt.dataset.fee : '-';
    document.getElementById('timeTxt').textContent = opt.dataset.time ? opt.dataset.time : '-';

    let req = '-';
    try { req = JSON.parse(opt.dataset.req || '""') || '-'; } catch(e) { req = '-'; }

    const reqBox = document.getElementById('reqTxt');
    reqBox.innerHTML = (req && req !== '-') ? String(req).replace(/\n/g, "<br>") : "-";
  }

  function updateDynamicFields() {
    if (!dynamic) return;

    const opt = sel.options[sel.selectedIndex];
    const text = (opt?.textContent || '').toLowerCase();

    dynamic.innerHTML = '';

    // Example: Cohabitation fields
    if (text.includes('cohabitation')) {
      dynamic.innerHTML = `
        <div class="mt-3 p-3 border rounded bg-white">
          <h6 class="mb-3">Additional Information</h6>

          <div class="mb-3">
            <label class="form-label">Partner Full Name <span class="text-danger">*</span></label>
            <input type="text" name="extra[partner_name]" class="form-control" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Living Together Since <span class="text-danger">*</span></label>
            <input type="date" name="extra[since]" class="form-control" required>
          </div>
        </div>
      `;
    }
  }

  function onChange() {
    updateInfo();
    updateDynamicFields();
  }

  if (sel) {
    sel.addEventListener('change', onChange);
  }
});

if (text.includes('guardianship')) {
  dynamic.innerHTML = `
    <div class="mt-3 p-3 border rounded bg-white">
      <h6>Child Information</h6>

      <div class="mb-2">
        <label>Child Name</label>
        <input type="text" name="extra[child_name]" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Date of Birth</label>
        <input type="date" name="extra[child_dob]" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Place of Birth</label>
        <input type="text" name="extra[child_pob]" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Mother Name</label>
        <input type="text" name="extra[mother_name]" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Father Name</label>
        <input type="text" name="extra[father_name]" class="form-control" required>
      </div>
    </div>
  `;
}


