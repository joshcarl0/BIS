    <div class="row g-3">

    <div class="alert alert-info">
        CONSTRUCTION PERMIT FORM LOADED
    </div>

    <div class="col-md-6">
        <label class="form-label">Applicant Name *</label>
        <input type="text" name="extra[applicant_name]" class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Applicant Address *</label>
        <input type="text" name="extra[applicant_address]" class="form-control" required>
    </div>

    <div class="col-md-12">
        <label class="form-label">Excavation Title *</label>
        <input type="text" 
            name="extra[permit_title]" 
            class="form-control"
            value="BARANGAY CLEARANCE FOR EXCAVATION PERMIT"
            required>
    </div>

    <div class="col-md-12">
        <label class="form-label">Excavation Details *</label>
        <input type="text" 
            name="extra[Excavation_details]" 
            class="form-control"
            placeholder="e.g. PROPOSED 2 STOREY HOUSE"
            required>
    </div>

    <div class="col-md-12">
        <label class="form-label">Permit Type *</label>
        <select name="extra[permit_use]" class="form-control" required>
        <option value="">Select Permit</option>
        <option value="PERMIT TO EXCAVATION">Permit to Excavation</option>
        <option value="CONSTRUCTION PERMIT">Construction Permit</option>
        <option value="RENOVATION PERMIT">Renovation Permit</option>
        </select>
    </div>

    </div>