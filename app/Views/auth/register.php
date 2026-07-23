<section class="auth-hero">
    <div class="auth-hero__panel">
        <div class="hero-badge mb-3">Membership enrollment</div>
        <h1 class="hero-title auth-hero__title">Join the Ogun State political membership system.</h1>
        <p class="hero-text auth-hero__text">
            Create a member profile, receive a membership number, and prepare for card generation and approval workflows.
        </p>
    </div>
    <div class="auth-card auth-card--theme">
        <div class="auth-card__head">
            <span class="info-label">Register</span>
            <h2>Create member account</h2>
            <p>Use the demo structure now and we can tighten the approval flow later when you are ready to go live.</p>
        </div>
        <form method="post" action="<?= url('/register'); ?>" enctype="multipart/form-data" class="auth-form row g-3">
            <?= csrf_field(); ?>
            <div class="col-md-6"><input class="form-control" name="surname" placeholder="Surname" required></div>
            <div class="col-md-6"><input class="form-control" name="first_name" placeholder="First Name" required></div>
            <div class="col-md-6"><input class="form-control" name="other_name" placeholder="Other Name"></div>
            <div class="col-md-6"><input class="form-control" name="phone" placeholder="Phone Number" required></div>
            <div class="col-12"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
            <div class="col-md-6"><input class="form-control" name="state_of_origin" placeholder="State of Origin" required></div>
            <div class="col-md-6"><input class="form-control" name="state_of_residence" value="Ogun State" placeholder="State of Residence"></div>
            <div class="col-md-6"><input class="form-control" type="date" name="date_of_birth"></div>
            <div class="col-md-6">
                <select class="form-select" name="gender">
                    <option value="">Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-6"><input class="form-control" name="vin" placeholder="VIN"></div>
            <div class="col-md-6"><input class="form-control" name="occupation" placeholder="Occupation"></div>
            <div class="col-12"><textarea class="form-control" name="residential_address" placeholder="Residential Address" rows="3"></textarea></div>
            <div class="col-md-4"><input class="form-control" name="lga_id" placeholder="LGA ID"></div>
            <div class="col-md-4"><input class="form-control" name="ward_id" placeholder="Ward ID"></div>
            <div class="col-md-4"><input class="form-control" name="polling_unit_id" placeholder="Polling Unit ID"></div>
            <div class="col-md-6"><input class="form-control" type="password" name="password" placeholder="Password" required></div>
            <div class="col-md-6"><input class="form-control" type="password" name="confirm_password" placeholder="Confirm Password" required></div>
            <div class="col-12">
                <label class="form-label fw-semibold">Passport Upload (jpg, jpeg, png, max 100KB)</label>
                <input class="form-control" type="file" name="passport">
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
