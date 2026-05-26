<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.14.2/jquery-ui.js"></script>
    <style>
        body {
            font-family: avenir-regular, sans-serif;
            font-size: 0.95rem;
            padding: 0;
            margin: 0;
        }

        *, ::before, ::after {
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            scroll-behavior: smooth;
        }

        h1, h2, h3, h4, h5, h6, th, b, strong {
            font-family: avenir-bold, sans-serif;
            margin: 0;
        }

        a {
            color: rgb(4, 79, 145);
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.5rem;
            border-bottom: 1px solid rgb(218, 218, 218);
        }

        th.sortable {
            text-align: left;
            cursor: pointer;
            user-select: none;
        }

        th i {
            margin-left: 10px;
            color: rgb(182, 182, 182);
            font-size: 0.85rem;
        }

        .employee-header {
            color: rgb(20, 75, 123);
            padding: 0.65rem 2rem;
            border-bottom: 1px solid rgb(228, 228, 228);
        }

        .employee-header .title {
            font-size: 1.25rem;
        }

        .employee-header .name {
            font-size: 0.85rem;
        }

        .employee-tabs {
            display: flex;
            gap: 1.5rem;
            padding: 0 2rem;
            border-bottom: 1px solid rgb(228, 228, 228);
        }

        .employee-tabs a {
            padding: 0.5rem 0;
            color: rgb(92, 92, 92);
            border-bottom: 3.5px solid transparent;
        }

        .employee-tabs a:hover {
            border-bottom: 3.85px solid rgba(0, 0, 0, 0.15);
        }

        .employee-tabs a.active {
            border-bottom: 3.85px solid rgb(20, 75, 123);
            color: rgb(15, 65, 109);
        }

        .summary-cards {
            display: flex;
            gap: 2.5rem;
            margin: 1.5rem 0;
        }

        .stacked-data {
            display: flex;
            flex-direction: column;
        }

        .stacked-data .name {
            font-size: 0.92rem;
            color: rgb(26, 44, 68);
            padding-bottom: 0.45rem;
        }

        .stacked-data .data {
            font-size: 1.2rem;
            color: rgb(26, 44, 68);
        }

        #profile-main {
            padding: 1.5rem 2rem;
        }

        .btn {
            display: inline-block;
            font-family: avenir-bold, sans-serif;
            font-size: 0.92rem;
            color: rgb(8, 113, 179);
            letter-spacing: 0.05rem;
            text-align: center;
            text-transform: uppercase;
            padding: 0.5rem;
            background-color: rgb(255, 255, 255);
            border: 1px solid rgb(8, 113, 179);
            border-radius: 4px;
            transition: 0.4s background-color ease, 0.4s color ease;
        }

        .btn:hover {
            color: rgb(255, 255, 255);
            background-color: rgb(8, 113, 179);
        }

        .btn-row {
            display: flex;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .btn-row .btn {
            width: 100%;
        }
    </style>
    <script>
        $(document).ready(function () {
            let sortState = {};

            $("#timesheetTable tbody tr").each(function (index) {
                $(this).attr("data-original-index", index);
            });

            $(".sortable").click(function () {
                const table = $(this).closest("table");
                const tbody = table.find("tbody");
                const index = $(this).index();
                let rows = tbody.find("tr").toArray();

                sortState[index] = (sortState[index] || 0) + 1;
                if (sortState[index] > 2) sortState[index] = 0;

                if (sortState[index] === 0) {
                    rows.sort((a, b) => {
                        return $(a).data("original-index") - $(b).data("original-index");
                    });

                } else {
                    rows.sort(function (a, b) {
                        let valA = $(a).children("td").eq(index).text().trim();
                        let valB = $(b).children("td").eq(index).text().trim();

                        valA = valA.replace(/[$,]/g, "");
                        valB = valB.replace(/[$,]/g, "");

                        if (!isNaN(valA) && !isNaN(valB)) {
                            return sortState[index] === 1 ? valA - valB : valB - valA;
                        }

                        let dateA = new Date(valA);
                        let dateB = new Date(valB);
                        if (!isNaN(dateA) && !isNaN(dateB)) {
                            return sortState[index] === 1 ? dateA - dateB : dateB - dateA;
                        }

                        return sortState[index] === 1
                            ? valA.localeCompare(valB)
                            : valB.localeCompare(valA);
                    });
                }

                tbody.empty().append(rows);

                $(".sortable i")
                    .removeClass("fa-sort-up fa-sort-down")
                    .addClass("fa-sort");

                if (sortState[index] === 1) {
                    $(this).find("i")
                        .removeClass("fa-sort")
                        .addClass("fa-sort-up");
                } else if (sortState[index] === 2) {
                    $(this).find("i")
                        .removeClass("fa-sort")
                        .addClass("fa-sort-down");
                }
            });
        });
</script>
</head>
<body>
    <div class="employee-header">
        <span class="title">Employee Profile :</span>
        <span class="name">John Doe, Personal Trainer</span>
    </div>
    <div class="employee-tabs">
        <a href="#">Profile</a>
        <a href="#" class="active">Payroll</a>
        <a href="#">Tasks</a>
    </div>
    <div id="profile-main">
        <h2>Payroll</h2>
        <div class="summary-cards">
            <div class="stacked-data">
                <span class="name">Gross Pay</span>
                <span class="data">$42.50</span>
            </div>
            <div class="stacked-data">
                <span class="name">Net Pay</span>
                <span class="data">$35.10</span>
            </div>
            <div class="stacked-data">
                <span class="name">Total Hours</span>
                <span class="data">2.83</span>
            </div>
            <div class="stacked-data">
                <span class="name">Hourly Pay</span>
                <span class="data">$15/hr</span>
            </div>
            <div class="stacked-data">
                <span class="name">Sales Logged</span>
                <span class="data">4</span>
            </div>
        </div>
        <div class="btn-row">
            <a href="#" class="btn"><i class="fa-solid fa-calendar-plus"></i> Add Timesheet</a>
            <a href="#" class="btn"><i class="fa-solid fa-dollar-sign"></i> Add Hours</a>
            <a href="#" class="btn"><i class="fa-solid fa-gear"></i> Adjust Payroll</a>
        </div>
        <table id="timesheetTable">
            <thead>
                <tr>
                    <th class="sortable">Pay Period <i class="fa fa-sort"></i></th>
                    <th class="sortable">Hours <i class="fa fa-sort"></i></th>
                    <th class="sortable">Gross Pay <i class="fa fa-sort"></i></th>
                    <th class="sortable">Submitted On <i class="fa fa-sort"></i></th>
                    <th align="left">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1/16/2023 - 1/20/2023</td>
                    <td>2.83</td>
                    <td>$15.00</td>
                    <td></td>
                    <td>Pending</td>
                </tr>
                <tr>
                    <td>1/1/2023 - 1/15/2023</td>
                    <td>12</td>
                    <td>$825.00</td>
                    <td>1/16/2023</td>
                    <td>Denied</td>
                </tr>
                <tr>
                    <td>12/1/2022 - 12/15/2022</td>
                    <td>18</td>
                    <td>$550.00</td>
                    <td>12/18/2022</td>
                    <td>Approved</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>