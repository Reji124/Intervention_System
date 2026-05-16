{{-- resources/views/admin/analytics/components/_chart-card.blade.php --}}
<div class="chart-card" style="
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    animation: slideUp 0.35s ease both;
">
    <div style="
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    ">
        <div>
            <div style="font-family: 'DM Serif Display', serif; font-size: 15px; color: var(--text-dark); font-weight: 500;">
                {{ $title }}
            </div>
            @if(isset($subtitle))
            <div style="font-size: 11px; color: var(--text-soft); margin-top: 2px;">
                {{ $subtitle }}
            </div>
            @endif
        </div>
        @if(isset($actionUrl))
        <a href="{{ $actionUrl }}" style="
            font-size: 12px;
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
            padding: 6px 12px;
            border: 1px solid var(--gold-dim);
            border-radius: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        ">
            View Details →
        </a>
        @endif
    </div>

    <div style="padding: 20px;">
        {{ $slot }}
    </div>
</div>

<style>
    .chart-card:hover {
        border-color: var(--gold);
    }
</style>
