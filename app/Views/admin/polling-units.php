<?php
$pollingUnits = $pollingUnits ?? [];
$districts = $districts ?? [];
$lgas = $lgas ?? [];
$wards = $wards ?? [];
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Polling Units</h1>
    <p class="text-muted mb-0">Create and manage polling units for result submissions.</p>
</div>

<div class="card p-4 mb-4">
    <h2 class="h5 mb-3">Add Polling Unit</h2>
    <form method="post" action="<?= url('/admin/polling-units'); ?>" class="row g-3">
        <?= csrf_field(); ?>
        <input type="hidden" name="action" value="add_polling_unit">
        <div class="col-md-4">
            <label class="form-label">District</label>
            <select class="form-select" name="senatorial_district_id" required>
                <option value="">Select district</option>
                <?php foreach ($districts as $district): ?>
                    <option value="<?= (int) $district['id']; ?>"><?= htmlspecialchars((string) $district['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">LGA</label>
            <select class="form-select" name="lga_id" required>
                <option value="">Select LGA</option>
                <?php foreach ($lgas as $lga): ?>
                    <option value="<?= (int) $lga['id']; ?>"><?= htmlspecialchars((string) $lga['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ward</label>
            <select class="form-select" name="ward_id" required>
                <option value="">Select ward</option>
                <?php foreach ($wards as $ward): ?>
                    <option value="<?= (int) $ward['id']; ?>"><?= htmlspecialchars((string) $ward['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Polling code</label>
            <input class="form-control" name="polling_code" placeholder="OGN-PU-001" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Polling name</label>
            <input class="form-control" name="polling_name" placeholder="Ake Primary School PU" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Latitude</label>
            <input class="form-control" name="latitude" placeholder="7.1500000">
        </div>
        <div class="col-md-4">
            <label class="form-label">Longitude</label>
            <input class="form-control" name="longitude" placeholder="3.3500000">
        </div>
        <div class="col-md-4">
            <label class="form-label">GPS address</label>
            <input class="form-control" name="gps_address" placeholder="Ake, Abeokuta South">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Create polling unit</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>District</th>
                    <th>LGA</th>
                    <th>Ward</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pollingUnits as $unit): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $unit['polling_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars((string) $unit['polling_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-muted small"><?= htmlspecialchars((string) ($unit['gps_address'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td><?= htmlspecialchars((string) $unit['district_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $unit['lga_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string) $unit['ward_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="text-end">
                        <form method="post" action="<?= url('/admin/polling-units'); ?>" onsubmit="return confirm('Delete this polling unit?');">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="entity_id" value="<?= (int) $unit['id']; ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($pollingUnits === []): ?>
                <tr><td colspan="6" class="text-muted">No polling units.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
