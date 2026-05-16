{{-- resources/views/admin/analytics/components/_kpi-card.blade.php --}}
<div class="kpi-card" style="
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: all 0.2s;
    animation: slideUp 0.35s ease both;
">
    <div style="display: flex; align-items: flex-start; justify-content: space-between;">
        <div>
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.7px; color: var(--text-soft); margin-bottom: 6px;">
                {{ $label }}
            </div>
            <div style="font-family: 'DM Serif Display', serif; font-size: 28px; font-weight: bold; color: var(--text-dark); line-height: 1;">
                {{ $value }}
            </div>
            @if(isset($unit))
            <div style="font-size: 12px; color: var(--text-soft); margin-top: 3px;">
                {{ $unit }}
            </div>
            @endif
        </div>
        @if(isset($icon))
        <div style="
            width: 48px;
            height: 48px;
            background: var(--gold-dim);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--gold);
        ">
            {!! $icon !!}
        </div>
        @endif
    </div>

    @if(isset($trend))
    <div style="
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: {{ $trend['positive'] ? 'var(--green)' : 'var(--red)' }};
        margin-top: 8px;
    ">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px;">
            @if($trend['positive'])
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                <polyline points="17 6 23 6 23 12"></polyline>
            @else
                <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                <polyline points="17 18 23 18 23 12"></polyline>
            @endif
        </svg>
        <span>{{ $trend['percentage'] }}% {{ $trend['positive'] ? 'increase' : 'decrease' }}</span>
    </div>
    @endif
</div>

<style>
    .kpi-card:hover {
        border-color: var(--gold);
        box-shadow: 0 4px 12px rgba(201,151,58,0.1);
    }
</style>
