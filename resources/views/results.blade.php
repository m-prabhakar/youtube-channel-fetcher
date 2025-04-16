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
    <h2>Fetched Videos</h2>
    <table>
        <thead>
            <tr>
                @foreach (array_keys($results[0]) as $heading)
                    <th>{{ $heading }}</th>
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
        <p>No videos could be fetched successfully.</p>
    @endif

    {{-- Display error categories below --}}
    @if (!empty($missingUrls))
        <h3 style="margin-top: 30px; color: red;">Could not fetch data for {{ count($missingUrls) }} video(s):</h3>
        <ul>
            @foreach ($missingUrls as $url)
                <li>{{ $url }}</li>
            @endforeach
        </ul>
    @endif

    @if (!empty($invalidUrls))
        <h3 style="margin-top: 20px; color: orange;">Invalid URLs ({{ count($invalidUrls) }}):</h3>
        <ul>
            @foreach ($invalidUrls as $url)
                <li>{{ $url }}</li>
            @endforeach
        </ul>
    @endif

    @if (!empty($duplicateUrls))
        <h3 style="margin-top: 20px; color: blue;">Duplicate URLs ({{ count($duplicateUrls) }}):</h3>
        <ul>
            @foreach ($duplicateUrls as $url)
                <li>{{ $url }}</li>
            @endforeach
        </ul>
    @endif

</body>
</html>
