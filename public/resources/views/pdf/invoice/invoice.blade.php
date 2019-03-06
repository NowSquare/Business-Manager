<?php
// Formatters
$currency = $invoice->currency_code;
$currencies = new \Money\Currencies\ISOCurrencies();
$numberFormatter = new \NumberFormatter(config('app.locale'), \NumberFormatter::CURRENCY);
$moneyFormatter = new \Money\Formatter\IntlMoneyFormatter($numberFormatter, $currencies);

// Proposition items
$proposition = [];
$taxes = [];
$combined_taxes = [];
$from = auth()->user()->getDefaultCompany();

// Totals
$sub_total = \Money\Money::{$currency}(0);
$tax_total = \Money\Money::{$currency}(0);
$grand_total = \Money\Money::{$currency}(0);
$discount_total = \Money\Money::{$currency}(0);

if (count($invoice->items) > 0) {
  foreach($invoice->items as $item) {
		if (
			$item->type == 'item' && 
			$item->quantity != '' && 
			$item->unit_price != '' && 
			$item->tax_rate != ''
		) {
			$row_total_excl_taxes = \Money\Money::{$currency}($item->unit_price)->multiply($item->quantity / 100);
			$tax = $row_total_excl_taxes->multiply($item->tax_rate / 10000);

			$sub_total = $sub_total->add($row_total_excl_taxes);
			$tax_total = $tax_total->add($tax);
			$grand_total = $grand_total->add($row_total_excl_taxes->add($tax));

			$taxes[] = [
				'rate' => $item->tax_rate / 100,
				'of' => $row_total_excl_taxes,
				'amount' => $tax
			];
			
			$proposition[] = [
				'type' => $item->type,
				'description' => $item->description,
				'quantity' => $item->quantity / 100,
				'unit' => $item->unit,
				'unit_price' => ($item->unit_price !== null) ? $item->unit_price : 0,
				'tax_rate' => $item->tax_rate,
				'discount_type' => $item->discount_type,
				'total' => $row_total_excl_taxes
			];
		}
  }

  foreach($invoice->items as $item) {
		if (
			$item->type == 'discount' && 
			$item->quantity != '' && 
			$item->discount_type != '' && 
			$item->tax_rate != ''
		) {
			if ($item->discount_type == '%') {
				$row_total_excl_taxes = $sub_total->multiply($item->quantity / 10000);
			} else {
				$row_total_excl_taxes = Money::{$currency}($item->quantity / 100);
			}

			$tax = $row_total_excl_taxes->multiply($item->tax_rate / 10000);

			$discount_total = $discount_total->add($row_total_excl_taxes);
			$tax_total = $tax_total->subtract($tax);
			$grand_total = $grand_total->subtract($row_total_excl_taxes->add($tax));

			$taxes[] = [
				'rate' => $item->tax_rate / 100,
				'of' => \Money\Money::{$currency}(-$row_total_excl_taxes->getAmount()),
				'amount' => \Money\Money::{$currency}(-$tax->getAmount())
			];

			$proposition[] = [
				'type' => $item->type,
				'description' => $item->description,
				'quantity' => $item->quantity / 100,
				'unit' => $item->unit,
				'unit_price' => ($item->unit_price !== null) ? $item->unit_price : 0,
				'tax_rate' => $item->tax_rate,
				'discount_type' => $item->discount_type,
				'total' => $row_total_excl_taxes
			];
		}
  }

	foreach ($taxes as $tax) {
		if (! isset($combined_taxes[$tax['rate']])) {
			$combined_taxes[$tax['rate']] = [
				'rate' => $tax['rate'],
				'of' => $tax['of'],
				'amount' => $tax['amount']
			];
		} else {
			$combined_tax = $combined_taxes[$tax['rate']];

			$combined_taxes[$tax['rate']] = [
				'rate' => $tax['rate'],
				'of' => $tax['of']->add($combined_tax['of']),
				'amount' => $tax['amount']->add($combined_tax['amount'])
			];
		}
	}
}
?>
@extends('../../layouts.pdf')

@section('content')
<script type="text/php">
if ( isset($pdf) ) { 
    $pdf->page = trans('g.page');
    $pdf->of = trans('g._of_');
    $pdf->page_script('
      $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
      $size = 10;
      $pageText = $pdf->page . " " . $PAGE_NUM . " " . $pdf->of . " " . $PAGE_COUNT;
      $y = 15;
      $x = 520;
      $pdf->text($x, $y, $pageText, $font, $size);
    ');
}
</script>
<div class="container-fluid">
	<div class="row">
		<div class="col-12">

			<h2 class="mb-5">{{ trans('g.invoice') }}</h2>

			<table class="table table-borderless mb-5">
				<tr class="align-top">
					<td style="width: 35%; padding: 0;">
						{{ trans('g.to') }}<br>
            {!! $invoice->client->print_address !!}
					</td>
					<td style="width: 35%; padding: 0;">
<?php if ($from !== null) { ?>
						{{ trans('g.from') }}<br>
            {!! $from->print_address !!}
<?php } ?>
					</td>
					<td style="width: 30%; padding: 0;">
						{{ trans('g.invoice_number') }}<br>
						{{ $invoice->reference }}
						<br><br>

            <table class="table table-borderless">
              <tr>
                <td style="width: 70%; padding: 0;">
                  {{ trans('g.date') }}<br>
                  {{ auth()->user()->formatDate($invoice->issue_date, 'date_medium') }}
                </td>
                <td style="width: 30%; padding: 0;">
                  {{ trans('g.currency') }}<br>
                  {{ $currency }}
                </td>
              </tr>
            </table>

						{{ trans('g.due_date') }}<br>
						{{ auth()->user()->formatDate($invoice->due_date, 'date_medium') }}
					</td>
				</tr>
			</table>

			<table class="table table-sm">
				<thead>
					<tr>
						<th class="align-top">{{ trans('g.description') }}</th>
						<th class="text-right align-top">{{ trans('g.quantity') }}</th>
						<th class="align-top">{{ __(trans('g.unit')) }}</th>
						<th class="text-right align-top">{{ trans('g.price_per_unit') }}</th>
						<th class="text-right align-top">{{ trans('g.tax') }}</th>
						<th class="text-right align-top">{{ trans('g.total_excl_tax') }}</th>
					</tr>
				</thead>
				<tbody>
<?php
foreach ($proposition as $item) {

	if ($item['type'] == 'item') {
?>
					<tr class="align-top">
						<td>{{ $item['description'] }}</td>
						<td class="text-right">{{ $item['quantity'] }}</td>
						<td>{{ $item['unit'] }}</td>
						<td class="text-right">{{ $moneyFormatter->format(new \Money\Money($item['unit_price'], new \Money\Currency($currency))) }}</td>
						<td class="text-right">{{ $item['tax_rate'] / 100 }}%</td>
						<td class="text-right">{{ $moneyFormatter->format($item['total']) }}</td>
					</tr>
<?php
	} else {
?>
					<tr class="align-top">
						<td>{{ $item['description'] }}</td>
						<td class="text-right">{{ $item['quantity'] }}</td>
						<td>{{ $item['discount_type'] }}</td>
						<td></td>
						<td class="text-right">{{ $item['tax_rate'] / 100 }}%</td>
						<td class="text-right">{{ $moneyFormatter->format($item['total']) }}</td>
					</tr>
<?php
	}
}
?>
				</tbody>
				<tfoot class="table-borderless">
					<tr>
						<td colspan="6">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="3" class="align-top">{{ trans('g.subtotal_excl_taxes') }}</td>
						<td class="text-right align-top">{{ $moneyFormatter->format($sub_total) }}</td>
					</tr>
<?php
if ($discount_total->greaterThan(\Money\Money::{$currency}(0))) {
?>
					<tr>
						<td colspan="2"></td>
						<td colspan="3" class="align-top">{{ trans('g.discount') }}</td>
						<td class="text-right align-top">-{{ $moneyFormatter->format($discount_total) }}</td>
					</tr>
<?php
}
foreach ($combined_taxes as $tax) { 
?>
					<tr>
						<td colspan="2"></td>
						<td colspan="3" class="align-top">{{ trans('g.tax_of_row', ['percentage' => $tax['rate'], 'amount' => $moneyFormatter->format($tax['of'], new \Money\Currency($currency)), 'currency' => '']) }}</td>
						<td class="text-right align-top">{{ $moneyFormatter->format($tax['amount'], new \Money\Currency($currency)) }}</td>
					</tr>
<?php
}
?>
					<tr>
						<th colspan="2"></th>
						<th colspan="3" class="align-top">{{ trans('g.total') }} {{ $currency }}</th>
						<th class="text-right align-top">{{ $moneyFormatter->format($grand_total) }}</th>
					</tr>
					<tr>
						<th colspan="2"></th>
						<td colspan="4" class="small">{{ trans('g.total_taxes') }} {{ $moneyFormatter->format($tax_total, new \Money\Currency($currency)) }}</td>
					</tr>
				</tfoot>
			</table>

      <br>

			<table class="table table-borderless mb-5">
				<tr class="align-top">
					<td style="width: 35%; padding: 0;">
<?php
$payment_method_terms_selected = $invoice->additional_fields['payment_method_terms_selected'] ?? 0;
$payment_method_terms = $invoice->additional_fields['payment_method_terms'] ?? '';

$payment_method_bank_selected = $invoice->additional_fields['payment_method_bank_selected'] ?? 0;
$payment_method_bank_bic_swift = $invoice->additional_fields['payment_method_bank_bic_swift'] ?? '';
$payment_method_bank_iban = $invoice->additional_fields['payment_method_bank_iban'] ?? '';
$payment_method_bank_name = $invoice->additional_fields['payment_method_bank_name'] ?? '';

$payment_method_cash_selected = $invoice->additional_fields['payment_method_cash_selected'] ?? 0;

$payment_method_check_selected = $invoice->additional_fields['payment_method_check_selected'] ?? 0;

if ($payment_method_terms_selected == 1 || $payment_method_bank_selected == 1 || $payment_method_cash_selected == 1 || $payment_method_check_selected == 1) {
  echo '<div class="my-1 pb-2 border-bottom"><strong>' . trans('g.accepted_payment_methods') . '</strong></div>';

  if ($payment_method_terms_selected == 1) {
    echo '<div class="my-1 small"><strong>' . trans('g.payment_methods_choices.terms') . '</strong></div>';
    echo '<div class="my-1 small pb-2 border-bottom">' . nl2br($payment_method_terms) . '</div>';
  }

  if ($payment_method_bank_selected == 1) {
    echo '<div class="my-1 small"><strong>' . trans('g.payment_methods_choices.bank') . '</strong></div>';

		echo '<table class="table table-borderless m-0">';
		echo '	<tr class="align-top">';
		echo '		<td style="width: 35%; padding: 0;"><div class="small"><strong>' . trans('g.bic_swift') . '</strong></div></td>';
		echo '		<td style="width: 65%; padding: 0;"><div class="small">' . $payment_method_bank_bic_swift . '</div></td>';
    echo '  </tr>';
		echo '	<tr class="align-top">';
		echo '		<td style="width: 35%; padding: 0;"><div class="small"><strong>' . trans('g.iban') . '</strong></div></td>';
		echo '		<td style="width: 65%; padding: 0;"><div class="small">' . $payment_method_bank_iban . '</div></td>';
    echo '  </tr>';
		echo '	<tr class="align-top">';
		echo '		<td style="width: 35%; padding: 0;" class="border-bottom pb-2"><div class="small"><strong>' . trans('g.name') . '</strong></div></td>';
		echo '		<td style="width: 65%; padding: 0;" class="border-bottom pb-2"><div class="small">' . $payment_method_bank_name . '</div></td>';
    echo '  </tr>';
    echo '</table>';

    //echo '<div class="mt-2 pb-2 border-bottom"></div>';
  }

  if ($payment_method_cash_selected == 1) {
    echo '<div class="my-1 small pb-2 border-bottom"><strong>' . trans('g.payment_methods_choices.cash') . '</strong></div>';
  }

  if ($payment_method_check_selected == 1) {
    echo '<div class="my-1 small pb-2 border-bottom"><strong>' . trans('g.payment_methods_choices.check') . '</strong></div>';
  }
}
?>
          </td>
					<td style="width: 10%; padding: 0;">
          </td>
					<td style="width: 45%; padding: 0;">
<?php 
if ($invoice->notes !== null) {
  echo '<div class="my-1 pb-2 border-bottom"><strong>' . trans('g.notes') . '</strong></div>';
  echo nl2br($invoice->notes);
}
?>
          </td>
        </tr>
      </table>

		</div>
	</div>
</div>
@stop