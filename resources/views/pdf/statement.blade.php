<!DOCTYPE html>
<html>
<head>
    <title>Card Statement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Card Statement</h1>
    <p>Below is your card transaction history:</p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction['created_at'] }}</td>
                    <td>{{ $transaction['description'] }}</td>
                    <td>${{ $transaction['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
