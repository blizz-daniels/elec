<?php
$pollingUnits = $pollingUnits ?? [];
?>

<section class="auth-hero">
    <div class="auth-hero__panel">
        <div class="hero-badge mb-3">Membership enrollment</div>
        <h1 class="hero-title auth-hero__title">Join Yayi Youth Vanguard.</h1>
        <p class="hero-text auth-hero__text">
            Create a member profile with the required biodata and choose your polling unit from one searchable list.
        </p>
    </div>
    <div class="auth-card auth-card--theme">
        <div class="auth-card__head">
            <span class="info-label">Register</span>
            <h2>Create member account</h2>
            <p>All registrations default to Ogun Resident and require a passport attachment before submission.</p>
        </div>
        <form method="post" action="<?= url('/register'); ?>" enctype="multipart/form-data" class="auth-form row g-3">
            <?= csrf_field(); ?>

            <div class="col-md-6">
                <input class="form-control" name="surname" placeholder="Surname" value="<?= htmlspecialchars((string) old('surname'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <input class="form-control" name="first_name" placeholder="First Name" value="<?= htmlspecialchars((string) old('first_name'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <input class="form-control" name="other_name" placeholder="Other Name" value="<?= htmlspecialchars((string) old('other_name'), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-6">
                <input class="form-control" name="phone" placeholder="Mobile No" value="<?= htmlspecialchars((string) old('phone'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-12">
                <input class="form-control" type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars((string) old('email'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <input class="form-control" name="state_of_origin" placeholder="State of Origin" value="<?= htmlspecialchars((string) old('state_of_origin'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <input class="form-control" name="state_of_residence" value="Ogun Resident" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date of Birth</label>
                <input class="form-control" type="date" name="date_of_birth" value="<?= htmlspecialchars((string) old('date_of_birth'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <select class="form-select" name="gender" required>
                    <option value="">Sex</option>
                    <option value="male" <?= (string) old('gender') === 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?= (string) old('gender') === 'female' ? 'selected' : ''; ?>>Female</option>
                    <option value="other" <?= (string) old('gender') === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <input class="form-control" name="vin" placeholder="Voter Identification Number (VIN)" value="<?= htmlspecialchars((string) old('vin'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <input class="form-control" name="nin" placeholder="NIN" value="<?= htmlspecialchars((string) old('nin'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Voting Polling Unit</label>
                <select
                    class="form-select"
                    name="polling_unit_id"
                    id="pollingUnitSelect"
                    data-searchable-select
                    data-searchable-fields="polling_no,polling_code,ward,lga,district,name"
                    data-searchable-placeholder="Search by unit no, code, ward, LGA, district, or name"
                    required
                >
                    <option value="">Select polling unit</option>
                    <?php foreach ($pollingUnits as $pollingUnit): ?>
                        <option
                            value="<?= (int) $pollingUnit['id']; ?>"
                            data-search-name="<?= htmlspecialchars((string) $pollingUnit['polling_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-search-polling-no="<?= htmlspecialchars((string) ($pollingUnit['polling_unit_no'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            data-search-polling-code="<?= htmlspecialchars((string) $pollingUnit['polling_code'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-search-ward="<?= htmlspecialchars((string) $pollingUnit['ward_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-search-lga="<?= htmlspecialchars((string) $pollingUnit['lga_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-search-district="<?= htmlspecialchars((string) $pollingUnit['district_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?= (string) old('polling_unit_id') === (string) $pollingUnit['id'] ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars((string) $pollingUnit['polling_name'] . ' [' . (string) ($pollingUnit['polling_unit_no'] ?? '') . '] | ' . (string) $pollingUnit['polling_code'] . ' | ' . (string) $pollingUnit['ward_name'] . ' | ' . (string) $pollingUnit['lga_name'] . ' | ' . (string) $pollingUnit['district_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Search by any part of the unit number, code, ward, LGA, district, or unit name.</div>
            </div>

            <div class="col-12">
                <div class="border rounded-3 p-3 bg-light-subtle" id="pollingUnitSummary">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="small text-muted">Polling Unit No</div>
                            <div class="fw-semibold" data-summary-field="polling_no">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Polling Unit Code</div>
                            <div class="fw-semibold" data-summary-field="polling_code">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Polling Unit</div>
                            <div class="fw-semibold" data-summary-field="name">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Ward</div>
                            <div class="fw-semibold" data-summary-field="ward">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">LGA</div>
                            <div class="fw-semibold" data-summary-field="lga">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Senatorial District</div>
                            <div class="fw-semibold" data-summary-field="district">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <input class="form-control" type="password" name="password" placeholder="Password" required>
            </div>
            <div class="col-md-6">
                <input class="form-control" type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Passport Attachment (jpg, jpeg, png, max 5MB)</label>
                <input class="form-control" type="file" name="passport" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
            </div>
            <div class="col-12 form-check">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms">I agree to the terms</label>
            </div>
            <div class="col-12">
                <button class="btn btn--accent w-100" type="submit">Create Membership</button>
            </div>
        </form>
        <div class="auth-card__footer">
            <a href="<?= url('/login'); ?>">Already have an account? Sign in.</a>
        </div>
    </div>
</section>

<script>
(() => {
    const pollingUnitSelect = document.getElementById('pollingUnitSelect');
    const summary = document.getElementById('pollingUnitSummary');
    if (!pollingUnitSelect || !summary) {
        return;
    }

    const summaryFields = summary.querySelectorAll('[data-summary-field]');

    const updateSummary = () => {
        const selected = pollingUnitSelect.selectedOptions[0];
        const values = {
            polling_no: selected?.dataset.searchPollingNo || '',
            polling_code: selected?.dataset.searchPollingCode || '',
            name: selected?.dataset.searchName || '',
            ward: selected?.dataset.searchWard || '',
            lga: selected?.dataset.searchLga || '',
            district: selected?.dataset.searchDistrict || '',
        };

        summaryFields.forEach((field) => {
            const key = field.dataset.summaryField || '';
            field.textContent = values[key] || '-';
        });
    };

    pollingUnitSelect.addEventListener('change', updateSummary);
    updateSummary();
})();
</script>
