<!-- Can delete this -->
@php use Illuminate\Support\Str; @endphp      

<!DOCTYPE html>
<html>
<head>
    <title>YouTube Channel Results</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f3f3f3;
        }
    </style>
</head>
<body>
    <h1>YouTube Channel Results</h1>

    @if (count($results))
        <table>
                

            <thead>
                <tr>
                    @foreach (array_keys($results[0] ?? []) as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($results as $result)
                    <tr>
                        @foreach ($result as $value)
                            <td>{{ is_numeric($value) ? number_format($value) : $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No results found.</p>
    @endif
</body>
</html>
