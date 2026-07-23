<?php
$districts = $districts ?? [];
$lgas = $lgas ?? [];
$wards = $wards ?? [];
$pollingUnits = $pollingUnits ?? [];
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Geography</h1>
    <p class="text-muted mb-0">Manage senatorial districts, LGAs, wards, and polling units.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Add District</h2>
            <form method="post" action="<?= url('/admin/geography'); ?>" class="vstack gap-3">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="add_district">
                <div>
                    <label class="form-label">District name</label>
                    <input class="form-control" name="district_name" placeholder="Ogun Central" required>
                </div>
                <button class="btn btn-primary" type="submit">Create district</button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Add LGA</h2>
            <form method="post" action="<?= url('/admin/geography'); ?>" class="vstack gap-3">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="add_lga">
                <div>
                    <label class="form-label">District</label>
                    <select class="form-select" name="senatorial_district_id" required>
                        <option value="">Select district</option>
                        <?php foreach ($districts as $district): ?>
                            <option value="<?= (int) $district['id']; ?>"><?= htmlspecialchars((string) $district['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">LGA name</label>
                    <input class="form-control" name="lga_name" placeholder="Abeokuta South" required>
                </div>
                <div>
                    <label class="form-label">LGA code</label>
                    <input class="form-control" name="lga_code" placeholder="OGN-LGA-001">
                </div>
                <button class="btn btn-primary" type="submit">Create LGA</button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Add Ward</h2>
            <form method="post" action="<?= url('/admin/geography'); ?>" class="vstack gap-3">
                <?= csrf_field(); ?>
                <input type="hidden" name="action" value="add_ward">
                <div>
                    <label class="form-label">LGA</label>
                    <select class="form-select" name="lga_id" required>
                        <option value="">Select LGA</option>
                        <?php foreach ($lgas as $lga): ?>
                            <option value="<?= (int) $lga['id']; ?>"><?= htmlspecialchars((string) $lga['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Ward name</label>
                    <input class="form-control" name="ward_name" placeholder="Ake Ward" required>
                </div>
                <div>
                    <label class="form-label">Ward code</label>
                    <input class="form-control" name="ward_code" placeholder="OGN-WRD-001">
                </div>
                <button class="btn btn-primary" type="submit">Create Ward</button>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Districts</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Name</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($districts as $district): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $district['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <form method="post" action="<?= url('/admin/geography'); ?>" onsubmit="return confirm('Delete this district?');">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="entity" value="district">
                                    <input type="hidden" name="entity_id" value="<?= (int) $district['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($districts === []): ?>
                        <tr><td colspan="2" class="text-muted">No districts.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">LGAs</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Name</th><th>District</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($lgas as $lga): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $lga['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $lga['district_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <form method="post" action="<?= url('/admin/geography'); ?>" onsubmit="return confirm('Delete this LGA?');">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="entity" value="lga">
                                    <input type="hidden" name="entity_id" value="<?= (int) $lga['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($lgas === []): ?>
                        <tr><td colspan="3" class="text-muted">No LGAs.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 mb-3">Wards</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Name</th><th>LGA</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($wards as $ward): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $ward['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $ward['lga_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <form method="post" action="<?= url('/admin/geography'); ?>" onsubmit="return confirm('Delete this ward?');">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="entity" value="ward">
                                    <input type="hidden" name="entity_id" value="<?= (int) $ward['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($wards === []): ?>
                        <tr><td colspan="3" class="text-muted">No wards.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card p-4">
            <h2 class="h5 mb-3">Polling Units Snapshot</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Code</th><th>Name</th><th>District</th><th>LGA</th><th>Ward</th></tr></thead>
                    <tbody>
                    <?php foreach ($pollingUnits as $unit): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $unit['polling_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $unit['polling_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $unit['district_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $unit['lga_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string) $unit['ward_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($pollingUnits === []): ?>
                        <tr><td colspan="5" class="text-muted">No polling units.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
