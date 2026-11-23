<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #0f172a; }
        .header { border-bottom: 2px solid #047857; padding-bottom: 8px; margin-bottom: 14px; }
        .ministry { font-size: 12px; font-weight: bold; color: #047857; text-transform: uppercase; }
        .title { font-size: 15px; font-weight: bold; margin-top: 3px; }
        .subtitle { font-size: 10px; color: #475569; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background-color: #f1f5f9; text-align: left; padding: 6px; border: 1px solid #cbd5e1; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: top; }
        .footer { margin-top: 14px; font-size: 9px; color: #64748b; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="ministry">Kogi State Ministry of Culture and Tourism — Internal Report</div>
        <div class="title">{{ $title }}</div>
        <div class="subtitle">{{ $subtitle }} · Generated: {{ $generated_at }} (WAT)</div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headings as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headings) }}" style="text-align:center;">No records matched.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">KSMCT Back-Office System — Confidential Internal Document</div>
</body>
</html>
