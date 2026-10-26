<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class TradeImportController extends Controller
{
    private const FIELDS = [
        'symbol' => 'Symbol',
        'market' => 'Market',
        'side' => 'Direction',
        'status' => 'Status',
        'opened_at' => 'Opened at',
        'closed_at' => 'Closed at',
        'entry_price' => 'Entry price',
        'exit_price' => 'Exit price',
        'quantity' => 'Quantity',
        'manual_profit_loss' => 'Reported PnL',
        'fees' => 'Fees',
        'currency_code' => 'Currency code',
        'external_position_id' => 'Position / trade ID',
    ];

    public function create(Request $request): View|RedirectResponse
    {
        $accounts = $request->user()->tradingAccounts()->orderBy('name')->get();

        if ($accounts->isEmpty()) {
            return redirect()->route('accounts.index')->with('status', 'Add a trading account before importing trades.');
        }

        return view('imports.create', compact('accounts'));
    }

    public function upload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trading_account_id' => ['required', 'integer', Rule::exists('trading_accounts', 'id')->where('user_id', $request->user()->id)],
            'file' => ['required', 'file', 'max:10240', 'mimes:csv,tsv,txt'],
        ]);

        $file = $request->file('file');
        $storedPath = $file->storeAs('trade-imports/'.$request->user()->id, Str::uuid().'.'.$file->extension(), 'local');

        if ($storedPath === false) {
            return back()->withErrors(['file' => 'The uploaded file could not be saved.'])->withInput();
        }

        try {
            [$headers, $rows] = $this->readTable(Storage::disk('local')->path($storedPath));
        } catch (Throwable) {
            Storage::disk('local')->delete($storedPath);

            return back()->withErrors(['file' => 'We could not read this table. Save it as CSV or TSV and try again.']);
        }

        if (count($headers) < 2 || $rows === []) {
            Storage::disk('local')->delete($storedPath);

            return back()->withErrors(['file' => 'The file needs a header row and at least one data row.']);
        }

        $importKey = Str::uuid()->toString();
        $request->session()->put('trade_import.'.$importKey, [
            'path' => $storedPath,
            'account_id' => $validated['trading_account_id'],
            'headers' => $headers,
            'preview' => array_slice($rows, 0, 5),
            'row_count' => count($rows),
            'original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('imports.preview', ['key' => $importKey]);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $key = $request->query('key');
        $import = is_string($key) ? $request->session()->get('trade_import.'.$key) : null;

        if (! is_array($import)) {
            return redirect()->route('imports.create')->withErrors(['file' => 'This import session expired. Upload the file again.']);
        }

        $headers = $import['headers'];
        $suggested = $this->suggestMapping($headers);

        return view('imports.preview', [
            'key' => $key,
            'import' => $import,
            'fields' => self::FIELDS,
            'headers' => $headers,
            'suggested' => $suggested,
            'accounts' => $request->user()->tradingAccounts()->findOrFail($import['account_id']),
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $key = $request->input('key');
        $import = is_string($key) ? $request->session()->get('trade_import.'.$key) : null;

        if (! is_array($import)) {
            return redirect()->route('imports.create')->withErrors(['file' => 'This import session expired. Upload the file again.']);
        }

        $validated = $request->validate([
            'key' => ['required', 'uuid'],
            'mapping' => ['required', 'array'],
            'mapping.symbol' => ['required', 'integer', 'between:0,'.(count($import['headers']) - 1)],
            'mapping.side' => ['required', 'integer', 'between:0,'.(count($import['headers']) - 1)],
            'mapping.opened_at' => ['required', 'integer', 'between:0,'.(count($import['headers']) - 1)],
            'mapping.*' => ['nullable', 'integer', 'between:0,'.(count($import['headers']) - 1)],
        ]);

        $absolutePath = Storage::disk('local')->path($import['path']);

        if (! is_file($absolutePath)) {
            $request->session()->forget('trade_import.'.$key);

            return redirect()->route('imports.create')->withErrors(['file' => 'The temporary upload has expired. Upload the file again.']);
        }

        [$headers, $rows] = $this->readTable($absolutePath);
        $mapping = $validated['mapping'];
        $user = $request->user();
        $account = $user->tradingAccounts()->findOrFail($import['account_id']);
        $created = 0;
        $skipped = 0;
        $failed = 0;
        $failureReasons = [];

        foreach ($rows as $offset => $row) {
            $rowNumber = $offset + 2;
            $values = [];

            foreach ($mapping as $field => $columnIndex) {
                if ($columnIndex !== '' && $columnIndex !== null && isset(self::FIELDS[$field])) {
                    $values[$field] = trim((string) ($row[(int) $columnIndex] ?? ''));
                }
            }

            try {
                $trade = $this->normalizeTrade($values, $user->timezone, $account->base_currency);
                $fingerprint = hash('sha256', json_encode([
                    $account->id,
                    $trade['symbol'],
                    $trade['side'],
                    $trade['opened_at']->format('Y-m-d H:i:s'),
                    $trade['closed_at']?->format('Y-m-d H:i:s'),
                    $trade['entry_price'],
                    $trade['exit_price'],
                    $trade['quantity'],
                    $trade['manual_profit_loss'],
                    $trade['currency_code'],
                    $trade['external_position_id'],
                ], JSON_THROW_ON_ERROR));

                $duplicate = $user->trades()
                    ->where('trading_account_id', $account->id)
                    ->where(function ($query) use ($fingerprint, $trade): void {
                        $query->where('import_fingerprint', $fingerprint);

                        $query->orWhere(function ($existing) use ($trade): void {
                            $existing->where('symbol', $trade['symbol'])
                                ->where('side', $trade['side'])
                                ->where('opened_at', $trade['opened_at'])
                                ->where('currency_code', $trade['currency_code']);

                            foreach (['closed_at', 'entry_price', 'exit_price', 'quantity', 'manual_profit_loss'] as $field) {
                                if ($trade[$field] === null) {
                                    $existing->whereNull($field);
                                } else {
                                    $existing->where($field, $trade[$field]);
                                }
                            }
                        });

                        if ($trade['external_position_id'] !== null) {
                            $query->orWhere('external_position_id', $trade['external_position_id']);
                        }
                    })
                    ->exists();

                if ($duplicate) {
                    $skipped++;

                    continue;
                }

                $trade['import_fingerprint'] = $fingerprint;
                $trade['user_id'] = $user->id;
                $trade['trading_account_id'] = $account->id;
                $user->trades()->create($trade);
                $created++;
            } catch (Throwable $exception) {
                $failed++;

                if (count($failureReasons) < 8) {
                    $failureReasons[] = 'Row '.$rowNumber.': the data could not be validated or saved. Check the mapped values and try again.';
                }
            }
        }

        Storage::disk('local')->delete($import['path']);
        $request->session()->forget('trade_import.'.$key);
        $summary = "Import finished: {$created} added, {$skipped} duplicates skipped, {$failed} failed.";
        $request->session()->flash('status', $summary);

        if ($failureReasons !== []) {
            $request->session()->flash('import_failures', $failureReasons);
        }

        return redirect()->route('trades.index');
    }

    private function normalizeTrade(array $values, string $timezone, string $defaultCurrency): array
    {
        $symbol = strtoupper(trim($values['symbol'] ?? ''));
        $side = strtolower(trim($values['side'] ?? ''));
        $side = match ($side) {
            'buy', 'long', 'b' => 'long',
            'sell', 'short', 's' => 'short',
            default => '',
        };

        if ($symbol === '' || $side === '' || trim($values['opened_at'] ?? '') === '') {
            throw new \InvalidArgumentException('Symbol, direction, and opened time are required.');
        }

        $currency = strtoupper(trim($values['currency_code'] ?? '')) ?: strtoupper($defaultCurrency);

        if (preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw new \InvalidArgumentException('Currency must be a three-letter code.');
        }

        $closedAt = trim($values['closed_at'] ?? '');
        $status = strtolower(trim($values['status'] ?? ''));
        $status = in_array($status, ['open', 'closed'], true) ? $status : ($closedAt === '' ? 'open' : 'closed');
        $opened = CarbonImmutable::parse($values['opened_at'], $timezone)->utc();
        $closed = $closedAt === '' ? null : CarbonImmutable::parse($closedAt, $timezone)->utc();

        if ($status === 'closed' && $closed === null) {
            throw new \InvalidArgumentException('A closed position needs a closed time.');
        }

        if ($closed !== null && $closed->lessThan($opened)) {
            throw new \InvalidArgumentException('Closed time must be after opened time.');
        }

        $market = strtolower(trim($values['market'] ?? 'other'));
        $market = match ($market) {
            'forex', 'fx' => 'forex',
            'crypto', 'cryptocurrency' => 'crypto',
            'stock', 'stocks', 'equity', 'equities' => 'stock',
            default => 'other',
        };

        return [
            'symbol' => $symbol,
            'market' => $market,
            'side' => $side,
            'status' => $status,
            'opened_at' => $opened,
            'closed_at' => $closed,
            'entry_price' => $this->nullableNumber($values['entry_price'] ?? null),
            'exit_price' => $this->nullableNumber($values['exit_price'] ?? null),
            'quantity' => $this->nullableNumber($values['quantity'] ?? null),
            'manual_profit_loss' => $this->nullableNumber($values['manual_profit_loss'] ?? null),
            'fees' => $this->nullableNumber($values['fees'] ?? null) ?? 0,
            'currency_code' => $currency,
            'external_position_id' => trim($values['external_position_id'] ?? '') ?: null,
        ];
    }

    private function nullableNumber(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = str_replace([',', '$', ' '], '', trim($value));

        if (! is_numeric($normalized)) {
            throw new \InvalidArgumentException('A mapped number contains an invalid value.');
        }

        return $normalized;
    }

    private function readTable(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open uploaded file.');
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = "\t";

        foreach ([',', ';', "\t"] as $candidate) {
            if (substr_count((string) $firstLine, $candidate) > substr_count((string) $firstLine, $delimiter)) {
                $delimiter = $candidate;
            }
        }

        $headers = fgetcsv($handle, 0, $delimiter, '"', '\\');
        if (! is_array($headers)) {
            fclose($handle);
            throw new \RuntimeException('Missing header row.');
        }

        $headers = array_map(static fn ($header): string => trim((string) $header, " \t\n\r\0\x0B\xEF\xBB\xBF"), $headers);
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            if (count(array_filter($row, static fn ($value): bool => trim((string) $value) !== '')) > 0) {
                $rows[] = $row;
            }
        }

        fclose($handle);

        return [$headers, $rows];
    }

    private function suggestMapping(array $headers): array
    {
        $synonyms = [
            'symbol' => ['symbol', 'instrument', 'pair', 'asset', 'ticker'],
            'market' => ['market', 'asset class', 'category'],
            'side' => ['side', 'direction', 'type', 'action'],
            'status' => ['status', 'state'],
            'opened_at' => ['opened at', 'open time', 'open date', 'entry time', 'date opened'],
            'closed_at' => ['closed at', 'close time', 'close date', 'exit time', 'date closed'],
            'entry_price' => ['entry price', 'open price', 'price open'],
            'exit_price' => ['exit price', 'close price', 'price close'],
            'quantity' => ['quantity', 'qty', 'size', 'volume', 'lots'],
            'manual_profit_loss' => ['pnl', 'profit loss', 'profit/loss', 'profit', 'net profit'],
            'fees' => ['fees', 'commission', 'costs'],
            'currency_code' => ['currency', 'currency code'],
            'external_position_id' => ['position id', 'trade id', 'ticket', 'order id', 'deal id'],
        ];
        $mapping = [];

        foreach ($synonyms as $field => $names) {
            foreach ($headers as $index => $header) {
                $normalized = strtolower(trim(preg_replace('/[_-]+/', ' ', $header) ?? $header));

                if (in_array($normalized, $names, true)) {
                    $mapping[$field] = $index;
                    break;
                }
            }
        }

        return $mapping;
    }
}
