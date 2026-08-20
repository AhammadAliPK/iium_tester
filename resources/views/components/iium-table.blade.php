@props(['headers' => []])

<div class="iium-table-wrap">
    <table class="iium-table">
        @if (count($headers) > 0)
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

<style>
    .iium-table-wrap {
        background: var(--color-iium-surface-raised);
        border: 1px solid var(--color-iium-border-subtle);
        border-radius: var(--radius-iium-lg);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .iium-table {
        width: 100%;
        border-collapse: collapse;
    }

    .iium-table th {
        text-align: left;
        font-family: var(--font-condensed);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-iium-text-secondary);
        padding: 16px 24px;
        background: var(--color-iium-glass);
        border-bottom: 2px solid var(--color-iium-border-active);
    }

    .iium-table td {
        padding: 16px 24px;
        font-size: 14px;
        color: var(--color-iium-text-primary);
        border-bottom: 1px solid var(--color-iium-border-subtle);
        vertical-align: middle;
    }

    .iium-table tbody tr:last-child td {
        border-bottom: none;
    }

    .iium-table tbody tr {
        transition: background var(--iium-duration, 250ms) ease;
    }

    .iium-table tbody tr:hover {
        background: var(--color-iium-glass);
    }

    /* DLS convention: trailing column (status, actions) anchors right */
    .iium-table th:last-child,
    .iium-table td:last-child {
        text-align: right;
    }
</style>
