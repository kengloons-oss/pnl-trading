@extends('layouts.app', ['title' => 'Live markets'])

@section('content')
    <div class="page-heading-row compact-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> MARKET WATCH</div><h1>Follow the<br><em>markets you trade.</em></h1><p>View charts for the first four supported trading pairs.</p></div><a class="text-link" href="{{ route('trades.index') }}">Open trade journal →</a></div>
    <div class="markets-layout">
        <aside class="panel market-watchlist">
            <div class="watchlist-heading"><span>WATCHLIST</span><span>04</span></div>
            @foreach ($symbols as $ticker => $details)
                <button class="watchlist-item {{ $selectedSymbol === $ticker ? 'selected' : '' }}" type="button" data-symbol="{{ $ticker }}">
                    <span class="watchlist-icon">{{ substr($ticker, 0, 1) }}</span><span class="watchlist-meta"><strong>{{ $ticker }}</strong><small>{{ $details['market'] }}</small></span><span class="watchlist-arrow">↗</span>
                </button>
            @endforeach
            <div class="watchlist-footnote">Select an instrument to load its chart.</div>
        </aside>
        <section class="panel market-chart-panel">
            <div class="market-chart-heading"><div><div class="eyebrow">SELECTED INSTRUMENT</div><h2 id="market-symbol-title">{{ $selectedSymbol }}</h2><p id="market-symbol-description">{{ $symbols[$selectedSymbol]['label'] }}</p></div><span class="market-source">Chart by TradingView</span></div>
            <div class="market-chart-frame" id="market-chart" data-symbols="{{ json_encode($symbols, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}" data-selected="{{ $selectedSymbol }}">
                <div class="chart-loading">Loading chart…</div>
            </div>
            <div class="market-chart-note">Forex and crypto widget data is real-time according to TradingView. Availability and timing can vary by instrument and market source. This embedded chart does not provide price data to the PnL app.</div>
        </section>
    </div>
    <script>
        (() => {
            const chart = document.getElementById('market-chart');
            const symbols = JSON.parse(chart.dataset.symbols);
            const title = document.getElementById('market-symbol-title');
            const description = document.getElementById('market-symbol-description');

            const loadChart = (ticker) => {
                const selected = symbols[ticker];
                if (!selected) return;

                title.textContent = ticker;
                description.textContent = selected.label;
                chart.replaceChildren();

                const container = document.createElement('div');
                container.className = 'tradingview-widget-container';
                container.style.height = '100%';
                container.style.width = '100%';

                const widget = document.createElement('div');
                widget.className = 'tradingview-widget-container__widget';
                widget.style.height = 'calc(100% - 28px)';
                widget.style.width = '100%';
                container.append(widget);

                const copyright = document.createElement('div');
                copyright.className = 'tradingview-widget-copyright';
                const link = document.createElement('a');
                link.href = `https://www.tradingview.com/symbols/${ticker}/`;
                link.target = '_blank';
                link.rel = 'noopener nofollow';
                link.textContent = `${ticker} chart`;
                const brand = document.createElement('span');
                brand.className = 'trademark';
                brand.textContent = ' by TradingView';
                copyright.append(link, brand);
                container.append(copyright);

                const script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
                script.async = true;
                script.textContent = JSON.stringify({
                    autosize: true,
                    symbol: selected.chart_symbol,
                    interval: '15',
                    timezone: 'Asia/Kuala_Lumpur',
                    theme: 'light',
                    style: '1',
                    locale: 'en',
                    allow_symbol_change: false,
                    calendar: false,
                    details: false,
                    hide_side_toolbar: true,
                    hide_top_toolbar: false,
                    hide_legend: false,
                    hide_volume: false,
                    hotlist: false,
                    save_image: false,
                    withdateranges: true,
                    backgroundColor: '#ffffff',
                    gridColor: 'rgba(46, 46, 46, 0.06)',
                    watchlist: [],
                    compareSymbols: [],
                    studies: [],
                });
                container.append(script);
                chart.append(container);
            };

            document.querySelectorAll('.watchlist-item').forEach((button) => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('.watchlist-item').forEach((item) => item.classList.remove('selected'));
                    button.classList.add('selected');
                    loadChart(button.dataset.symbol);
                });
            });

            loadChart(chart.dataset.selected);
        })();
    </script>
@endsection
