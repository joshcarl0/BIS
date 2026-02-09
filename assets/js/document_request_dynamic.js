(function () {
  const sel = document.getElementById("documentSelect");
  const box = document.getElementById("dynamicFields");
  if (!sel || !box) return;

  function field(name, label, type = "text", placeholder = "") {
    const id = "ex_" + name;
    return `
      <div class="col-md-6">
        <label class="form-label">${label}</label>
        <input type="${type}" class="form-control"
               name="extra[${name}]"
               id="${id}"
               placeholder="${placeholder}">
      </div>
    `;
  }

  function render() {
    const opt = sel.options[sel.selectedIndex];
    const text = (opt ? opt.textContent : "").toLowerCase();

    // default empty
    box.innerHTML = "";

    // SOLO PARENT example
    if (text.includes("solo parent")) {
      box.innerHTML = `
        <div class="border rounded p-3 bg-white">
          <div class="fw-semibold mb-2">Additional Information</div>
          <div class="row g-3">
            ${field("sp_reason", "Reason (e.g. separated / widowed / abandoned)", "text")}
            ${field("sp_children_count", "No. of Children", "number")}
            ${field("sp_since", "Since when (date or year)", "text", "e.g. 2022")}
            <div class="col-12">
              <label class="form-label">Children Names (optional)</label>
              <textarea class="form-control" rows="3" name="extra[sp_children_names]"></textarea>
            </div>
          </div>
        </div>
      `;
    }

    // COHABITATION example
    if (text.includes("cohabitation") || text.includes("live in") || text.includes("live-in")) {
      box.innerHTML = `
        <div class="border rounded p-3 bg-white">
          <div class="fw-semibold mb-2">Additional Information</div>
          <div class="row g-3">
            ${field("partner_name", "Partner Name")}
            ${field("since", "Living together since", "text", "e.g. January 2020")}
          </div>
        </div>
      `;
    }

    // GUARDIANSHIP example
    if (text.includes("guardian")) {
      box.innerHTML = `
        <div class="border rounded p-3 bg-white">
          <div class="fw-semibold mb-2">Child Information</div>
          <div class="row g-3">
            ${field("child_name", "Child Name")}
            ${field("child_dob", "Date of Birth", "date")}
            ${field("child_pob", "Place of Birth")}
            ${field("mother_name", "Mother Name")}
            ${field("father_name", "Father Name")}
          </div>
        </div>
      `;
    }
  }

  sel.addEventListener("change", render);
  render(); // run once
})();
