const sel = document.getElementById("documentSelect");
const extraBox = document.getElementById("extraFields");

function renderExtraFields() {
  if (!sel || !extraBox) return;

  const opt = sel.options[sel.selectedIndex];
  if (!opt) return;

  const label = (opt.textContent || "").toLowerCase();

  extraBox.innerHTML = "";

  // GUARDIAN
  if (label.includes("guardian")) {
    extraBox.innerHTML = `
      <hr>
      <h6 class="mb-3">Child Information</h6>

      <div class="mb-2">
        <label class="form-label">Name of Child <span class="text-danger">*</span></label>
        <input type="text" name="child_name" class="form-control" required>
      </div>

      <div class="mb-2">
        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
        <input type="date" name="child_dob" class="form-control" required>
      </div>

      <div class="mb-2">
        <label class="form-label">Place of Birth <span class="text-danger">*</span></label>
        <input type="text" name="child_pob" class="form-control" required>
      </div>

      <div class="mb-2">
        <label class="form-label">Name of Mother</label>
        <input type="text" name="mother_name" class="form-control">
      </div>

      <div class="mb-2">
        <label class="form-label">Name of Father</label>
        <input type="text" name="father_name" class="form-control">
      </div>
    `;
  }

  // COHABITATION / LIVE IN
  if (label.includes("cohabitation") || label.includes("live in")) {
    extraBox.innerHTML = `
      <hr>
      <h6 class="mb-3">Partner Information</h6>

      <div class="mb-2">
        <label class="form-label">Partner Name <span class="text-danger">*</span></label>
        <input type="text" name="partner_name" class="form-control" required>
      </div>

      <div class="mb-2">
        <label class="form-label">Living Together Since <span class="text-danger">*</span></label>
        <input type="date" name="since" class="form-control" required>
      </div>
    `;
  }
}

if (sel) {
  sel.addEventListener("change", renderExtraFields);
}
