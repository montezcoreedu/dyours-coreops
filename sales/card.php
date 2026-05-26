<?php if (isset($sales_access) && $sales_access == 1) { ?>
<div class="card">
    <div class="title">
        <h3>Sales Revenue</h3>
    </div>
    <div class="content">
    <?php
        $start_date = date("Y-m-d", strtotime("-4 months"));
        $end_date = date("Y-m-d");

        $stmt = $conn->prepare("SELECT DATE_FORMAT(work_date, '%Y-%m') AS month_key,
            DATE_FORMAT(work_date, '%M %Y') AS month_name, SUM(sales_amount) AS total_revenue
            FROM sales_entries
            WHERE work_date BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(work_date, '%Y-%m'), DATE_FORMAT(work_date, '%M %Y')
            ORDER BY DATE_FORMAT(work_date, '%Y-%m')");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "
            <div class='empty-state'>
                <img src='../images/empty-sales.png' alt='No sales records'>
                <p>No sale records yet</p>
            </div>
            ";
        } else {
            $previous_month_stmt = $conn->prepare("SELECT COALESCE(SUM(sales_amount), 0) AS previous_revenue
                FROM sales_entries
                WHERE MONTH(work_date) = MONTH(CURDATE() - INTERVAL 1 MONTH)
                AND YEAR(work_date) = YEAR(CURDATE() - INTERVAL 1 MONTH)");
            $previous_month_stmt->execute();
            $previous_month_result = $previous_month_stmt->get_result();
            $previous_month = $previous_month_result->fetch_assoc();
            $previous_revenue = (float) $previous_month['previous_revenue'];

            $current_month_stmt = $conn->prepare("SELECT SUM(sales_amount) AS month_revenue
                FROM sales_entries
                WHERE MONTH(work_date) = MONTH(CURDATE())
                AND YEAR(work_date) = YEAR(CURDATE())");
            $current_month_stmt->execute();
            $current_month_result = $current_month_stmt->get_result();
            $current_month = $current_month_result->fetch_assoc();
            $month_revenue = (float) ($current_month['month_revenue'] ?? 0);

            $labels = [];
            $revenue_data = [];

            while ($row = $result->fetch_assoc()) {
                $labels[] = date('F', strtotime($row['month_name']));
                $revenue_data[] = (float) $row['total_revenue'];
            }

            $labels_json = json_encode($labels);
            $revenue_json = json_encode($revenue_data);

            $start_label = date("F", strtotime($start_date));
            $end_label = date("F", strtotime($end_date));
            echo "<div class='caption'>Sales from $start_label to $end_label</div>";

            $change_icon = "No change";
            $change_class = "neutral";

            if ($previous_revenue > 0) {
                $percent_change = (($month_revenue - $previous_revenue) / $previous_revenue) * 100;
                $percent_change = round($percent_change, 1);

                if ($percent_change > 0) {
                    $change_icon = "<i class='fa-solid fa-circle-arrow-up'></i> {$percent_change}%";
                    $change_class = "positive";
                } elseif ($percent_change < 0) {
                    $change_icon = "<i class='fa-solid fa-circle-arrow-down'></i> " . abs($percent_change) . "%";
                    $change_class = "negative";
                }
            } elseif ($month_revenue > 0) {
                $change_icon = "<i class='fa-solid fa-circle-plus'></i> New revenue this month";
                $change_class = "positive";
            }

            echo "
            <div class='sheet-grid' style='padding: 0; margin-bottom: 1rem;'>
                <div class='sheet-item'>
                    <div class='item-value trend $change_class'>$change_icon</div>
                    <div class='item-label'>Last Month</div>
                </div>
                <div style='width: 1px; height: 32px; background-color: rgb(208, 208, 208); margin: 0 0.92rem;'></div>
                <div class='sheet-item'>
                    <div class='item-value'>$" . number_format($month_revenue, 2) . "</div>
                    <div class='item-label'>Month Revenue</div>
                </div>
            </div>
            ";

            echo "<canvas id='revenueChart' style='width: 100%; height: 200px;'></canvas>";
        }
    ?>
    </div>
</div>
<script>
    <?php if ($result->num_rows) { ?>
    var ctx = document.getElementById('revenueChart').getContext('2d');

    const labels = <?php echo isset($labels_json) ? $labels_json : '[]'; ?>;
    const revenueData = <?php echo isset($revenue_json) ? $revenue_json : '[]'; ?>;

    var revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,

            datasets: [{
                label: 'Sales Revenue',
                data: revenueData,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1.5,
                tension: 0.4,
                fill: true
            }]
        }
    });
    <?php } ?>
</script>
<?php } ?>
