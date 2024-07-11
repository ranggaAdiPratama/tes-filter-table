<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Table by Multiple Selects</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap4.min.css">
</head>

<body>
    <div class="container-fluid my-3">
        <div class="card card-primary">
            <div class="card-body">
                <div class="row my-2">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="mesinid">Mesin ID</label>
                            <select class="filterSelect form-control" id="mesinid" data-column="mesinid">
                                <option value="all">All Mesin ID</option>
                                @foreach ($machines as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="month">Month</label>
                            <select class="filterSelect form-control" id="month" data-column="month">
                                <option value="all">All Month</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="site">Site</label>
                            <select class="filterSelect form-control" id="site" data-column="site">
                                <option value="all">All Site</option>
                                @foreach ($sites as $row)
                                    <option value="{{ $row->id }}">{{ $row->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="operator">Operator</label>
                            <select class="filterSelect form-control" id="operator" data-column="operator">
                                <option value="all">All Operator</option>
                                @foreach ($operators as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="activity">Activity</label>
                            <select class="filterSelect form-control" id="activity" data-column="activity">
                                <option value="all">All Activity</option>
                                @foreach ($activities as $row)
                                    <option value="{{ $row }}">{{ $row }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Submitted By</th>
                                <th>Submitted When</th>
                                <th>Site Code</th>
                                <th>Activity</th>
                                <th>UOM</th>
                                <th>Block</th>
                                <th>Task</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Mesin ID</th>
                                <th>Fuel</th>
                                <th>Check By</th>
                                <th>When Check</th>
                                <th>Verified By</th>
                                <th>When Verified</th>
                                <th>Duty</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($query as $row)
                                <tr data-mesinid="{{ $row->machine_id }}"
                                    data-month="{{ date('m', strtotime($row->created_at)) }}"
                                    data-site="{{ $row->site_id }}" data-operator="{{ $row->operator_id }}"
                                    data-activity="{{ $row->activity }}">
                                    <td>
                                        <button type="button" class="btn btn-primary">Edit</button>
                                    </td>
                                    <td>{{ $row->operator }}</td>
                                    <td>{{ date('d F Y', strtotime($row->created_at)) }}</td>
                                    <td>{{ $row->site }}</td>
                                    <td>{{ $row->activity }}</td>
                                    <td>{{ $row->uom }}</td>
                                    <td>{{ $row->block }}</td>
                                    <td>{{ $row->task }}</td>
                                    <td>{{ $row->start }}</td>
                                    <td>{{ $row->end }}</td>
                                    <td>{{ $row->machine }}</td>
                                    <td>{{ $row->fuel }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ $row->duty }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.filterSelect').on('change', function() {
                var filters = {};

                $('.filterSelect').each(function() {
                    var column = $(this).data('column');
                    var value = $(this).val();
                    filters[column] = value;
                });

                $('#dataTable tbody tr').each(function() {
                    var showRow = true;

                    for (var column in filters) {
                        if (filters[column] !== 'all' && $(this).data(column) != filters[column]) {
                            showRow = false;
                            break;
                        }
                    }

                    if (showRow) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
</body>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
    integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
</script>

</html>
