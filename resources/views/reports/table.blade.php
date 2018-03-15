<table class="table table-hover">
    <thead>
    <tr>
        <th>{{ studly_case($aggregator) }}</th>
        <th>Orders</th>
        <th>Revenue</th>
        <th>COG</th>
        <th>FVF</th>
        <th>PPF</th>
        <th>Cashback</th>
        <th>Profit</th>
        <th>Margin</th>
    </tr>
    </thead>
    <tbody>
    @foreach($records as $record)
        @php
            /** @var \App\Reporting\OrderReports $reporter */
            $reporter = $record['reporter'];
            $previous = $record['previous'];
            ${$aggregator} = $record[$aggregator];
            $profitableTextClass = $reporter->profit() > 0 ? 'text-success' : ($reporter->profit() == 0 ? 'text-warning' : 'text-danger');
        @endphp

        <tr>
            <td>{{ ${$aggregator}->toDateString() }}</td>
            <td>{{ number_format($reporter->count()) }}</td>
            <td>{{ usd($reporter->revenue()) }}</td>
            <td>{{ usd($reporter->costOfGoods()) }}</td>
            <td>{{ usd($reporter->finalVaueFee()) }}</td>
            <td>{{ usd($reporter->paypalFee()) }}</td>
            <td>{{ usd($reporter->cashback()) }}</td>
            <td class="{{ $profitableTextClass }}">{{ usd($reporter->profit()) }}</td>
            <td>{{ usd($reporter->margin()) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>