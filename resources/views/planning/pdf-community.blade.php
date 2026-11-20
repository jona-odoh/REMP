<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Community Electrification Dossier — {{ $community->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
        .header { border-bottom: 2px solid #065f46; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 18px; font-weight: bold; color: #064e3b; }
        .section-title { font-size: 13px; font-weight: bold; color: #065f46; margin-top: 16px; margin-bottom: 6px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f1f5f9; text-align: left; padding: 6px; font-size: 10px; border: 1px solid #cbd5e1; }
        td { padding: 6px; font-size: 10px; border: 1px solid #cbd5e1; }
        .right { text-align: right; }
        .box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 10px; margin-top: 8px; }
    </style>
</head>
<body>
    @php
        $ass = $community->latestAssessment;
        $dem = $community->latestEnergyDemand;
        $rec = $community->latestRecommendation;
        $cost = $rec?->costEstimate;
        $p = $community->priorityRanking;
    @endphp

    <div class="header">
        <div class="title">KOGI STATE RURAL ELECTRIFICATION DOSSIER: {{ strtoupper($community->name) }}</div>
        <div>
            {{ $community->ward?->name }} Ward • {{ $community->ward?->lga?->name }} Local Government Area, Kogi State
            • Generated: {{ $generatedAt }}
        </div>
    </div>

    <div class="section-title">1. Geographic &amp; Demographic Profile</div>
    <table>
        <tr>
            <th>Community Name</th>
            <td>{{ $community->name }}</td>
            <th>Electricity Status</th>
            <td>{{ strtoupper(str_replace('_', ' ', $community->electricity_status_enum)) }}</td>
        </tr>
        <tr>
            <th>GPS Coordinates</th>
            <td>{{ $community->latitude }}, {{ $community->longitude }}</td>
            <th>Priority Ranking</th>
            <td>{{ strtoupper($p?->priority_level_enum ?? 'LOW') }} PRIORITY (Score: {{ number_format($p?->score ?? 0, 1) }}/100)</td>
        </tr>
        <tr>
            <th>Estimated Households</th>
            <td>{{ number_format($community->estimated_households) }} Households</td>
            <th>Estimated Population</th>
            <td>{{ number_format($community->estimated_population) }} Residents</td>
        </tr>
    </table>

    @if($dem && $rec)
        <div class="section-title">2. Energy Demand &amp; Renewable Energy Recommendation</div>
        <table>
            <tr>
                <th>Total Daily Electricity Demand</th>
                <td><strong>{{ number_format($dem->total_daily_kwh, 2) }} kWh/day</strong></td>
                <th>Estimated Peak Load</th>
                <td><strong>{{ number_format($dem->peak_load_kw, 2) }} kW</strong></td>
            </tr>
            <tr>
                <th>Recommended Intervention</th>
                <td><strong>{{ $rec->solution_label }}</strong></td>
                <th>Indicative PV System Capacity</th>
                <td><strong>{{ number_format($rec->estimated_capacity_kw, 2) }} kWp</strong></td>
            </tr>
        </table>
        <div class="box">
            <strong>Engineering Justification:</strong> {{ $rec->justification }}
        </div>
    @endif

    @if($cost)
        <div class="section-title">3. Indicative Implementation Cost Breakdown (Nigerian Naira - NGN)</div>
        <table>
            <thead>
                <tr>
                    <th>Cost Component</th>
                    <th class="right">Indicative Estimate (NGN)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Solar PV Panels (550W Monocrystalline Array)</td><td class="right">NGN {{ number_format($cost->solar_panels_cost, 2) }}</td></tr>
                <tr><td>Battery Storage Bank (LiFePO4)</td><td class="right">NGN {{ number_format($cost->batteries_cost, 2) }}</td></tr>
                <tr><td>Hybrid Inverters</td><td class="right">NGN {{ number_format($cost->inverters_cost, 2) }}</td></tr>
                <tr><td>MPPT Charge Controllers</td><td class="right">NGN {{ number_format($cost->charge_controllers_cost, 2) }}</td></tr>
                <tr><td>Mounting Structures &amp; Racking</td><td class="right">NGN {{ number_format($cost->mounting_structures_cost, 2) }}</td></tr>
                <tr><td>Distribution Network &amp; Metering</td><td class="right">NGN {{ number_format($cost->distribution_cost, 2) }}</td></tr>
                <tr><td>Protection Equipment &amp; Earthing</td><td class="right">NGN {{ number_format($cost->protection_equipment_cost, 2) }}</td></tr>
                <tr><td>Installation &amp; Civil Works</td><td class="right">NGN {{ number_format($cost->installation_cost, 2) }}</td></tr>
                <tr><td>Transportation &amp; Site Logistics</td><td class="right">NGN {{ number_format($cost->transportation_cost, 2) }}</td></tr>
                <tr>
                    <td><strong>TOTAL INDICATIVE INTERVENTION COST (NGN)</strong></td>
                    <td class="right"><strong>NGN {{ number_format($cost->total_estimated_cost_ngn, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <p style="font-size: 9px; color: #92400e; margin-top: 6px;">
            * Note: All cost estimates are labelled INDICATIVE for Kogi State rural electrification planning, not final supplier quotations.
        </p>
    @endif
</body>
</html>
