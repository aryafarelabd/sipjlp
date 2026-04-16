<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a1a; }
    .header { margin-bottom: 12px; }
    .header h2 { font-size: 13px; font-weight: bold; }
    .header p  { font-size: 9px; color: #555; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; }
    th {
        background: #1a56db; color: #fff;
        padding: 5px 6px; text-align: left;
        font-size: 8.5px; font-weight: bold;
        border: 1px solid #1245b5;
    }
    td { padding: 4px 6px; border: 1px solid #d1d5db; vertical-align: top; }
    tr:nth-child(even) td { background: #f3f6ff; }
    .footer { margin-top: 10px; font-size: 8px; color: #777; text-align: right; }
    .no-data { text-align: center; color: #999; padding: 20px; }
</style>
</head>
<body>
<div class="header">
    <h2>{{ $title }}</h2>
    <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th style="width:24px;">#</th>
            @foreach($headings as $h)
            <th>{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            @foreach($row as $cell)
            <td>{{ $cell }}</td>
            @endforeach
        </tr>
        @empty
        <tr><td colspan="{{ count($headings) + 1 }}" class="no-data">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Total {{ count($rows) }} baris &mdash; SIPJLP &copy; {{ date('Y') }}
</div>
</body>
</html>
