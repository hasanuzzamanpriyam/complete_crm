<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'TimeSync Calendar' ?></h3>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <form method="get" class="form-inline mb-lg">
                            <div class="form-group">
                                <label>Year: </label>
                                <select name="year" class="form-control">
                                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Month: </label>
                                <select name="month" class="form-control">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= sprintf('%02d', $m) ?>" <?= sprintf('%02d', $m) == $month ? 'selected' : '' ?>>
                                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">View</button>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <?php
                    $days_in_month = date('t', strtotime($year . '-' . $month . '-01'));
                    $first_day = date('w', strtotime($year . '-' . $month . '-01'));
                    ?>
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $day = 1; $started = false; ?>
                            <?php for ($row = 0; $row < 6; $row++): ?>
                                <?php if ($day > $days_in_month) break; ?>
                                <tr>
                                    <?php for ($col = 0; $col < 7; $col++): ?>
                                        <?php if (!$started && $col == $first_day) $started = true; ?>
                                        <td style="height:80px; vertical-align:top;">
                                            <?php if ($started && $day <= $days_in_month): ?>
                                                <strong><?= $day++ ?></strong>
                                                <?php
                                                $date_key = sprintf('%04d-%02d-%02d', $year, $month, $day - 1);
                                                $day_seconds = $daily_totals[$date_key] ?? 0;
                                                ?>
                                                <?php if ($day_seconds > 0): ?>
                                                    <br><span class="label label-info">
                                                        <?= round($day_seconds / 3600, 1) ?>h
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>