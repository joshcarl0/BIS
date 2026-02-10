document.addEventListener("DOMContentLoaded", function () {
  const sel = document.getElementById('documentSelect');
  const dynamic = document.getElementById('dynamicFields');

  if (!sel || !dynamic) return;

  function updateDynamicFields() {
    const opt = sel.options[sel.selectedIndex];
    const text = (opt?.textContent || '').toLowerCase();

    dynamic.innerHTML = '';

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
    else if (text.includes('guardian')) {
      dynamic.innerHTML = `
        <div class="mt-3 p-3 border rounded bg-white">
          <h6 class="mb-3">Child Information</h6>
          <div class="mb-2">
            <label class="form-label">Child Name <span class="text-danger">*</span></label>
            <input type="text" name="extra[child_name]" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="extra[child_dob]" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Place of Birth <span class="text-danger">*</span></label>
            <input type="text" name="extra[child_pob]" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Mother Name <span class="text-danger">*</span></label>
            <input type="text" name="extra[mother_name]" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Father Name <span class="text-danger">*</span></label>
            <input type="text" name="extra[father_name]" class="form-control" required>
          </div>
        </div>
      `;
    }
  }

  function onChange() {
    updateDynamicFields();
  }

  //  event listener OUTSIDE updateDynamicFields
  sel.addEventListener('change', onChange);

  //  run once on load
  onChange();
});
