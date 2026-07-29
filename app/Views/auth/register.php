<?php
$districts = $districts ?? [];
$lgas = $lgas ?? [];
$wards = $wards ?? [];
$pollingUnits = $pollingUnits ?? [];
?>

<section class="auth-hero">
    <div class="auth-hero__panel">
        <div class="hero-badge mb-3">Membership enrollment</div>
        <h1 class="hero-title auth-hero__title">Join Yayi Youth Vanguard.</h1>
        <p class="hero-text auth-hero__text">
            Create a member profile with the required biodata, identity records, and polling details for approval.
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
            <div class="col-md-6">
                <label class="form-label fw-semibold">Voting Senatorial District</label>
                <select class="form-select" name="senatorial_district_id" required>
                    <option value="">Select district</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?= (int) $district['id']; ?>" <?= (string) old('senatorial_district_id') === (string) $district['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars((string) $district['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Voting Local Government Area (LGA)</label>
                <select class="form-select" name="lga_id" required>
                    <option value="">Select LGA</option>
                    <?php foreach ($lgas as $lga): ?>
                        <option value="<?= (int) $lga['id']; ?>" <?= (string) old('lga_id') === (string) $lga['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars((string) $lga['name'] . ' - ' . (string) $lga['district_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Voting Ward</label>
                <select class="form-select" name="ward_id" required>
                    <option value="">Select ward</option>
                    <?php foreach ($wards as $ward): ?>
                        <option value="<?= (int) $ward['id']; ?>" <?= (string) old('ward_id') === (string) $ward['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars((string) $ward['name'] . ' - ' . (string) $ward['lga_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Voting Polling Unit</label>
                <select class="form-select" name="polling_unit_id" id="pollingUnitSelect" required>
                    <option value="">Select polling unit</option>
                    <?php foreach ($pollingUnits as $pollingUnit): ?>
                        <option
                            value="<?= (int) $pollingUnit['id']; ?>"
                            data-unit-no="<?= htmlspecialchars((string) $pollingUnit['polling_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-unit-code="<?= htmlspecialchars((string) $pollingUnit['polling_code'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?= (string) old('polling_unit_id') === (string) $pollingUnit['id'] ? 'selected' : ''; ?>
                        >
                            <?= htmlspecialchars((string) $pollingUnit['polling_name'] . ' - ' . (string) $pollingUnit['polling_code'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Pick the polling unit from the directory. The unit number and code below will mirror your selection.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Voting Polling Unit No</label>
                <input class="form-control" type="text" id="pollingUnitNo" name="polling_unit_no" value="<?= htmlspecialchars((string) old('polling_unit_no'), ENT_QUOTES, 'UTF-8'); ?>" readonly placeholder="Select polling unit">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Voting Polling Unit Code</label>
                <input class="form-control" type="text" id="pollingUnitCode" name="polling_unit_code" value="<?= htmlspecialchars((string) old('polling_unit_code'), ENT_QUOTES, 'UTF-8'); ?>" readonly placeholder="Select polling unit">
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
    const pollingUnitNo = document.getElementById('pollingUnitNo');
    const pollingUnitCode = document.getElementById('pollingUnitCode');

    if (!pollingUnitSelect || !pollingUnitNo || !pollingUnitCode) {
        return;
    }

    const syncPollingUnit = () => {
        const selected = pollingUnitSelect.selectedOptions[0];
        pollingUnitNo.value = selected?.dataset.unitNo || '';
        pollingUnitCode.value = selected?.dataset.unitCode || '';
    };

    pollingUnitSelect.addEventListener('change', syncPollingUnit);
    syncPollingUnit();
})();
</script>
