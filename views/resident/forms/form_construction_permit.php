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
        <label class="form-label">Construction Title *</label>
        <input type="text" 
            name="extra[permit_title]" 
            class="form-control"
            value="BARANGAY CLEARANCE FOR CONSTRUCTION PERMIT"
            required>
    </div>

    <div class="col-md-12">
        <label class="form-label">Construction Details *</label>
        <input type="text" 
            name="extra[construction_details]" 
            class="form-control"
            placeholder="e.g. PROPOSED 2 STOREY HOUSE"
            required>
    </div>

    <div class="col-md-12">
        <label class="form-label">Permit Type *</label>
        <select name="extra[permit_use]" class="form-control" required>
        <option value="">Select Permit</option>
        <option value="PERMIT TO CONSTRUCT">Permit to Construct</option>
        <option value="EXCAVATION PERMIT">Excavation Permit</option>
        <option value="DEMOLITION PERMIT">Demolition Permit</option>
        <option value="ELECTRICAL PERMIT">Electrical Permit</option>
        </select>
    </div>

    </div>