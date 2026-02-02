document.addEventListener('DOMContentLoaded', () => {

  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const r = JSON.parse(btn.getAttribute('data-resident'));

      // BASIC IDENTIFIERS
      document.getElementById('edit_id').value = r.id || '';

      // PERSONAL INFORMATION
      document.getElementById('edit_last_name').value   = r.last_name || '';
      document.getElementById('edit_first_name').value  = r.first_name || '';
      document.getElementById('edit_middle_name').value = r.middle_name || '';
      document.getElementById('edit_suffix').value      = r.suffix || '';
      document.getElementById('edit_birthdate').value   = r.birthdate || '';

      document.getElementById('edit_sex').value = r.sex || '';
      document.getElementById('edit_civil_status_id').value = r.civil_status_id || '';

      // CONTACT & ADDRESS
      document.getElementById('edit_contact').value = r.contact_number || '';
      document.getElementById('edit_email').value   = r.email || '';
      document.getElementById('edit_purok_id').value = r.purok_id || '';
      document.getElementById('edit_street_address').value = r.street_address || '';

      // RESIDENCY & HOUSEHOLD
      document.getElementById('edit_residency_type_id').value = r.residency_type_id || '';
      document.getElementById('edit_household_id').value = r.household_id || '';
      document.getElementById('edit_is_active').value =
        (parseInt(r.is_active || 0) === 1) ? '1' : '0';
      document.getElementById('edit_hoh').checked =
        (parseInt(r.is_head_of_household || 0) === 1);

      // OPTIONAL DETAILS
      if (document.getElementById('edit_occupation')) {
        document.getElementById('edit_occupation').value = r.occupation || '';
      }
      if (document.getElementById('edit_voter_status')) {
        document.getElementById('edit_voter_status').value =
          (r.voter_status === null || r.voter_status === undefined)
            ? ''
            : String(r.voter_status);
      }
      if (document.getElementById('edit_resident_code')) {
        document.getElementById('edit_resident_code').value = r.resident_code || '';
      }
      if (document.getElementById('edit_education_level_id')) {
        document.getElementById('edit_education_level_id').value = r.education_level_id || '';
      }
      if (document.getElementById('edit_employment_status_id')) {
        document.getElementById('edit_employment_status_id').value = r.employment_status_id || '';
      }

      // SPECIAL GROUPS
      document.querySelectorAll('.edit-sg').forEach(cb => cb.checked = false);
      let selected = r.special_group_ids || [];
      if (typeof selected === 'string') {
        selected = selected.split(',')
          .map(x => parseInt(x.trim()))
          .filter(n => !isNaN(n));
      }
      selected.forEach(id => {
        const cb = document.getElementById('edit_sg_' + id);
        if (cb) cb.checked = true;
      });
    });
  });

});
