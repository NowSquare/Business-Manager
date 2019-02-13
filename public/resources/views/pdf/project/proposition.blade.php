<?php
// Formatters
$currency = $project->currency_code;
$currencies = new \Money\Currencies\ISOCurrencies();
$numberFormatter = new \NumberFormatter(config('app.locale'), \NumberFormatter::CURRENCY);
$moneyFormatter = new \Money\Formatter\IntlMoneyFormatter($numberFormatter, $currencies);

// Proposition items
$proposition = [];
$taxes = [];
$combined_taxes = [];
$from = \Platform\Models\Company::where('default', 1)->first();

// Totals
$sub_total = \Money\Money::{$currency}(0);
$tax_total = \Money\Money::{$currency}(0);
$grand_total = \Money\Money::{$currency}(0);
$discount_total = \Money\Money::{$currency}(0);

if (count($project->propositions) > 0) {
  foreach($project->propositions->last()->items as $item) {
		if (
			$item->type == 'item' && 
			$item->quantity != '' && 
			$item->unit_price != '' && 
			$item->tax_rate != ''
		) {
			$row_total_excl_taxes = \Money\Money::{$currency}($item->unit_price)->multiply($item->quantity);
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
				'quantity' => $item->quantity,
				'unit' => $item->unit,
				'unit_price' => ($item->unit_price !== null) ? $item->unit_price : 0,
				'tax_rate' => $item->tax_rate,
				'discount_type' => $item->discount_type,
				'total' => $row_total_excl_taxes
			];
		}
  }

  foreach($project->propositions->last()->items as $item) {
		if (
			$item->type == 'discount' && 
			$item->quantity != '' && 
			$item->discount_type != '' && 
			$item->tax_rate != ''
		) {
			if ($item->discount_type == '%') {
				$row_total_excl_taxes = $sub_total->multiply($item->quantity / 100);
			} else {
				$row_total_excl_taxes = Money::{$currency}($item->quantity);
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
				'quantity' => $item->quantity,
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
<div class="container-fluid">
	<div class="row">
		<div class="col-12">

			<h2 class="mb-3">{{ trans('g.proposition') }}</h2>
			<h5 class="mb-5">{{ $project->name }}</h5>

			<table class="table table-borderless mb-5">
				<tr class="align-top">
					<td style="width: 40%">
						{{ trans('g.to') }}<br>
						<div class="my-1"><strong>{{ $project->client->name }}</strong></div>
<?php if ($project->client->street1 !== null) { ?>
						{{ $project->client->street1 }}
<?php } ?>
<?php if ($project->client->postal_code !== null) { ?>
						<br>{{ $project->client->postal_code }}
<?php } ?>
<?php if ($project->client->city !== null) { ?>
						{{ $project->client->city }}
<?php } ?>
<?php if ($project->client->state !== null) { ?>
						<br>{{ $project->client->state }}
<?php } ?>
<?php if ($project->client->country_code !== null) { ?>
						<br>{{ $project->client->country_code }}
<?php } ?>
					</td>
					<td style="width: 40%">
<?php if ($from !== null) { ?>
						{{ trans('g.from') }}<br>
						<div class="my-1"><strong>{{ $from->name }}</strong></div>
<?php if ($from->street1 !== null) { ?>
						{{ $from->street1 }}
<?php } ?>
<?php if ($from->postal_code !== null) { ?>
						<br>{{ $from->postal_code }}
<?php } ?>
<?php if ($from->city !== null) { ?>
						{{ $from->city }}
<?php } ?>
<?php if ($from->state !== null) { ?>
						<br>{{ $from->state }}
<?php } ?>
<?php if ($from->country_code !== null) { ?>
						<br>{{ $from->country_code }}
<?php } ?>
<?php } ?>
					</td>
					<td style="width: 20%">
<?php if ($project->reference !== null) { ?>
						{{ trans('g.reference') }}<br>
						{{ $project->reference }}
						<br><br>
<?php } ?>
						{{ trans('g.currency') }}<br>
						{{ $currency }}
						<br><br>
<?php if ($project->estimate_valid_until !== null) { ?>
						{{ trans('g.valid_until') }}<br>
						{{ $project->estimate_valid_until }}
<?php } ?>
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

		</div>
	</div>
</div>
@stop