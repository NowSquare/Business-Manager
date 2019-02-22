@extends('../../layouts.app')

@section('page_title', $title . ' - ' . config('system.name'))

@section('page_head')
<script>
$(function() {
  var submitted = false;

  $("form").submit(function() {
    submitted = true;
  });

  $(window).bind('beforeunload', function() {
    if (! submitted && $('#form_changes_detected').val() == '1') {
      return true;
    }
  });

  $('body').on('keyup change paste', 'input, select, textarea', function(){
    $('#form_changes_detected').val('1');
  });

});
</script>
@stop

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="row">

        <div class="col-12">

          @if(session()->has('success'))
          <div class="alert alert-success rounded-0">
            {!! session()->get('success') !!}
          </div>
          @endif

          @if ($errors->any())
          <div class="alert alert-danger rounded-0">
            {!! trans('g.form_error') !!}
          </div>
          @endif

          {!! form_start($form) !!}

          <input type="hidden" name="form_changes_detected" id="form_changes_detected" value="0">

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ $title }}</h3>
            </div>

            <div class="card-body p-5">

              <div class="row">
                <div class="col-md-6 col-lg-4">
                  {!! form_row($form->company_id) !!}
                </div>
                <div class="col-md-6 col-lg-4">
<?php if ($from !== null) { ?>
                  {{ trans('g.from') }}<br>
                  {!! $from->print_address !!}
<?php } ?>
                </div>
                <div class="col-md-6 col-lg-4 mb-5">
                  {!! form_row($form->reference) !!}
                  {!! form_row($form->issue_date) !!}
                  {!! form_row($form->currency_code) !!}
                  {!! form_row($form->due_date) !!}
                </div>
              </div>

              <table class="table table-borderless table-sm my-5" id="table-invoice">
                <thead>
                  <tr>
                    <th width="36"></th>
                    <th width="120">{{ trans('g.type') }}</th>
                    <th>{{ trans('g.description') }}</th>
                    <th width="90">{{ trans('g.quantity') }}</th>
                    <th width="100">{{ trans('g.unit') }}</th>
                    <th width="100">{{ trans('g.price_per_unit') }}</th>
                    <th width="100">{{ trans('g.tax') }}</th>
                    <th width="100" class="text-right">{{ trans('g.total_excl_tax') }}</th>
                    <th width="52"></th>
                  </tr>
                </thead>
                <tbody id="invoice-items" class="reorder-rows">
                </tbody>
                <tbody>
                  <tr>
                    <td colspan="9">
                      <button type="button" class="btn btn-success btn-block btn-lg btn-add-item rounded-0 my-4">{{ trans('g.add_new_line') }}</button>
                    </td>
                  </tr>
                </tbody>
                <tbody id="invoice-sub">
                  <tr>
                    <td colspan="5"></td>
                    <td colspan="2" class="text-left align-middle">{{ trans('g.subtotal_excl_taxes') }}</td>
                    <td class="text-right align-middle invoice-total-ex-tax"></td>
                    <td></td>
                  </tr>
                </tbody>
                <tbody id="invoice-totals-discount">
                </tbody>
                <tbody id="invoice-totals-taxes">
                </tbody>
                <tbody>
                  <tr>
                    <td colspan="5"></td>
                    <td colspan="2" class="text-left align-middle font-weight-bold border-top border-bottom">{{ trans('g.total') }} <span class="currency_code"></span></td>
                    <td class="text-right align-middle invoice-total font-weight-bold border-top border-bottom"></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="5"></td>
                    <td colspan="2" class="text-left align-middle"><small>{{ trans('g.total_taxes') }} <span class="currency_code"></span> <span class="invoice-total-taxes"></span></small></td>
                    <td></td>
                    <td></td>
                  </tr>
                </tbody>
              </table>

              <br>
<?php
$payment_method_terms_selected = $invoice->additional_fields['payment_method_terms_selected'] ?? 0;
$payment_method_bank_selected = $invoice->additional_fields['payment_method_bank_selected'] ?? 0;
$payment_method_cash_selected = $invoice->additional_fields['payment_method_cash_selected'] ?? 0;
$payment_method_check_selected = $invoice->additional_fields['payment_method_check_selected'] ?? 0;

// Check saved defaults
if ($invoice === null) {
  $invoice_payment_methods = \Platform\Controllers\Core\Settings::get('invoice_payment_methods', 'json');
  if ($invoice_payment_methods !== null) {
    $payment_method_terms_selected = $invoice_payment_methods['payment_method_terms_selected'] ?? 0;
    $payment_method_bank_selected = $invoice_payment_methods['payment_method_bank_selected'] ?? 0;
    $payment_method_cash_selected = $invoice_payment_methods['payment_method_cash_selected'] ?? 0;
    $payment_method_check_selected = $invoice_payment_methods['payment_method_check_selected'] ?? 0;
  }
}
?>
              <div class="row">
                <div class="col-md-4 col-lg-4">             
                  {!! form_row($form->payment_methods) !!}
                  <div class="my-5<?php if ($payment_method_terms_selected != 1) echo ' d-none'; ?>" id="payment_method_terms">
                    <div class="font-weight-bold mb-4 pb-2 border-bottom">{{ trans('g.payment_methods_choices.terms') }} <span class="close" data-close="payment_method_terms"></span></div>
                    <input type="hidden" name="payment_method_terms_selected" id="payment_method_terms_selected" value="{{ $payment_method_terms_selected }}">
                    {!! form_row($form->payment_method_terms) !!}
                  </div>

                  <div class="my-5<?php if ($payment_method_bank_selected != 1) echo ' d-none'; ?>" id="payment_method_bank">
                    <div class="font-weight-bold mb-4 pb-2 border-bottom">{{ trans('g.payment_methods_choices.bank') }} <span class="close" data-close="payment_method_bank"></span></div>
                    <input type="hidden" name="payment_method_bank_selected" id="payment_method_bank_selected" value="{{ $payment_method_bank_selected }}">
                    {!! form_row($form->payment_method_bank_bic_swift) !!}
                    {!! form_row($form->payment_method_bank_iban) !!}
                    {!! form_row($form->payment_method_bank_name) !!}
                  </div>

                  <div class="my-5<?php if ($payment_method_cash_selected != 1) echo ' d-none'; ?>" id="payment_method_cash">
                    <div class="font-weight-bold mb-4 pb-2 border-bottom">{{ trans('g.payment_methods_choices.cash') }} <span class="close" data-close="payment_method_cash"></span></div>
                    <input type="hidden" name="payment_method_cash_selected" id="payment_method_cash_selected" value="{{ $payment_method_cash_selected }}">
                  </div>

                  <div class="my-5<?php if ($payment_method_check_selected != 1) echo ' d-none'; ?>" id="payment_method_check">
                    <div class="font-weight-bold mb-4 pb-2 border-bottom">{{ trans('g.payment_methods_choices.check') }} <span class="close" data-close="payment_method_check"></span></div>
                    <input type="hidden" name="payment_method_check_selected" id="payment_method_check_selected" value="{{ $payment_method_check_selected }}">
                  </div>

                  {!! ($invoice === null) ? form_row($form->save_payment_methods) : '' !!}
                </div>
                <div class="col-md-6 col-lg-6 offset-md-2 offset-lg-2">
                  {!! form_row($form->notes) !!}
                  {!! ($invoice === null) ? form_row($form->save_notes) : '' !!}
                </div>
              </div>

            </div>

            <div class="card-footer text-right">
                {!! form_row($form->back) !!}
                {!! form_row($form->submit) !!}
                {!! form_row($form->save_and_send) !!}
            </div>
          </div>

          <input type="hidden" name="send" id="send" value="0">
          </form>

        </div>

      </div>
    </div>
  </div>

@stop

@section('page_bottom')
<script>
$(function() {
  $('#payment_methods').on('change', function() {
    var method = $(this).val();

    $('#payment_method_' + method).removeClass('d-none');
    $('#payment_method_' + method + '_selected').val('1');
  });

  $('.close').on('click', function() {
    var close = $(this).attr('data-close');

    if (typeof close !== 'undefined') {
      $('#' + close).addClass('d-none');
      $('#' + close + '_selected').val('0');
    }
  });
});
</script>

<script>
$(function() {
  $('.send-invoice').on('click', function() {

    Swal({
      title: "{!! trans('g.send_invoice') !!}",
      text: "{!! trans('g.send_invoice_msg') !!}",
      imageUrl: "{{ url('assets/img/icons/fe/send.svg') }}",
      imageWidth: 48,
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: "{!! trans('g.send') !!}"
    }).then((result) => {
      if (result.value) {
        $('#send').val('1');
        $('#btn_save').trigger('click');
      }
    });

  });
});
</script>

<script>
var item_row_id = 1;
var item_discount_row_id = 1;
var item_tax_row_id = 1;

function formatCurrency(amount) {
  return currency(amount, { precision: "{{ auth()->user()->getDecimals() }}", separator: "{{ auth()->user()->getThousandsSep() }}", decimal: "{{ auth()->user()->getDecimalSep() }}", formatWithSymbol: false }).format();
}

$(function() {
<?php
// Add existing items
if (count($items) > 0) {
?>
  var source = document.getElementById('invoice-item').innerHTML;
  var template = Handlebars.compile(source);
<?php
  foreach($items as $item) {
?>
    var context = {
      id: item_row_id,
      type: '<?php echo $item->type; ?>',
      description: "<?php echo str_replace('"', '&quot;', $item->description); ?>",
      quantity: "<?php if ($item->quantity !== null) echo $item->quantity / 100; ?>",
      unit: "<?php echo $item->unit; ?>",
      unit_price: "<?php if ($item->unit_price !== null) echo $item->unit_price / 100; ?>",
      tax_rate: "<?php echo $item->tax_rate; ?>",
      discount_type: "<?php echo $item->discount_type; ?>",
      total: formatCurrency(0)
    };

    var html = template(context);
    $(html).appendTo($('#invoice-items'));
    item_row_id++;
<?php
  }
?>
  setTimeout(calculateEstimate, 500);
<?php
}
?>

  $('#invoice-items').on('keypress', 'input', function(e) {
    if (e.keyCode == 13) {
      $('.btn-add-item').trigger('click');
      return false;
    }
  });

  setCurrencyCode();

  $('.invoice-total-ex-tax').text(formatCurrency(0));
  $('.invoice-total').text(formatCurrency(0));

  $('.btn-add-item').on('click', addItem);
  $('.btn-add-item').on('change', calculateEstimate);

  $('#invoice-items').on('change', '.select-type', changeRowType);

  $('#invoice-items').on('change keydown', '.select-type,.input-quantity,.input-unit_price,.select-discount-unit,.select-tax', calculateEstimate);

  $('#invoice-items').on('click', '.btn-delete-row', function() {
    var row_id = $(this).attr('data-row');
    $('#' + row_id).remove();
    calculateEstimate();
  });

  function addItem() {
    var source = document.getElementById('invoice-item').innerHTML;
    var template = Handlebars.compile(source);

    var context = {
      id: item_row_id,
      type: "item",
      description: "",
      quantity: "",
      unit: "<?php echo \Platform\Models\Core\Unit::getDefault(); ?>",
      unit_price: "",
      tax_rate: "<?php echo \Platform\Models\Core\TaxRate::getDefault(); ?>",
      discount_type: "currency",
      total: formatCurrency(0)
    };

    var html = template(context);
    $(html).appendTo($('#invoice-items'));
    calculateEstimate();
    item_row_id++;
  }

  // Change currency code
  $('#currency_code').on('change', setCurrencyCode);

  function setCurrencyCode() {
    $('.currency_code').text($('#currency_code').val());
  }

  function calculateEstimate() {
    var sub_total = 0;
    var tax_total = 0;
    var grand_total = 0;
    var discount_total = 0;

    var taxes = [];

    // First calculate all items
    $('.item-row').each(function() {
      var type = $(this).find('.select-type').val();
      var quantity = $(this).find('.input-quantity').val();
      var unit_price = $(this).find('.input-unit_price').val();
      var tax_rate = $(this).find('.select-tax').val();
      var discount_unit = $(this).find('.select-discount-unit').val();
      var $row_total = $(this).find('.row-total');

      if (type == 'item') {
        if (quantity != '' && unit_price != '' && tax_rate != '') {
          var row_total_excl_taxes = currency(unit_price).multiply(quantity);
          var tax = currency(row_total_excl_taxes).multiply(tax_rate / 10000);

          sub_total = currency(sub_total).add(row_total_excl_taxes);
          tax_total = currency(tax_total).add(tax);
          grand_total = currency(grand_total).add(currency(row_total_excl_taxes).add(tax));

          $row_total.text(formatCurrency(row_total_excl_taxes));
          taxes.push({rate: (tax_rate / 100), of: row_total_excl_taxes, amount: tax});
        } else {
          // Set total to 0
          $row_total.text(formatCurrency(0));
        }
      }
    });

    // Calculate discount
    $('.item-row').each(function() {
      var type = $(this).find('.select-type').val();
      var quantity = $(this).find('.input-quantity').val();
      var tax_rate = $(this).find('.select-tax').val();
      var discount_unit = $(this).find('.select-discount-unit').val();
      var $row_total = $(this).find('.row-total');

      if (type == 'discount') {
        if (quantity != '' && tax_rate != '') {
          if (discount_unit == '%') {
            var row_total_excl_taxes = currency(sub_total).multiply(quantity / 100);
          } else {
            var row_total_excl_taxes = currency(quantity);
          }

          var tax = currency(row_total_excl_taxes).multiply(tax_rate / 10000);

          discount_total = currency(discount_total).add(row_total_excl_taxes);
          tax_total = currency(tax_total).subtract(tax);
          grand_total = currency(grand_total).subtract(currency(row_total_excl_taxes).add(tax));

          $row_total.text(formatCurrency(row_total_excl_taxes));
          
          taxes.push({rate: (tax_rate / 100), of: -row_total_excl_taxes, amount: -tax});
        } else {
          // Set total to 0
          $row_total.text(formatCurrency(0));
        }
      }
    });

    // Add discount row, first remove existing
    $('#invoice-totals-discount').html('');

    if (discount_total > 0) {
      var source = document.getElementById('invoice-item-discount').innerHTML;
      var template = Handlebars.compile(source);

      var context = {
        total: formatCurrency(-discount_total)
      };

      var html = template(context);
      $(html).appendTo($('#invoice-totals-discount'));
      item_tax_row_id++;
    }

    // Add tax row(s), first remove existing
    $('#invoice-totals-taxes').html('');

    // Item tax
    var combined_taxes = [];

    for (var i in taxes) {
      var tax = taxes[i];

      var combined_tax = combined_taxes[tax.rate];

      if (typeof combined_tax === 'undefined') {
        combined_taxes[tax.rate] = {rate: tax.rate, of: tax.of, amount: tax.amount};
      } else {
        var of = currency(combined_tax.of).add(tax.of);
        var amount = currency(combined_tax.amount).add(tax.amount);

        combined_taxes[tax.rate] = {rate: combined_tax.rate, of: of, amount: amount};
      }
    }

    for (var i in combined_taxes) {
      var source = document.getElementById('invoice-item-tax').innerHTML;
      var template = Handlebars.compile(source);

      var context = {
        id: item_tax_row_id,
        rate: formatCurrency(combined_taxes[i].rate),
        of: formatCurrency(combined_taxes[i].of),
        total: formatCurrency(combined_taxes[i].amount)
      };

      var html = template(context);
      $(html).appendTo($('#invoice-totals-taxes'));
      item_tax_row_id++;
    }

    // Update totals
    $('.invoice-total-ex-tax').text(formatCurrency(sub_total));
    $('.invoice-total-taxes').text(formatCurrency(tax_total));
    $('.invoice-total').text(formatCurrency(grand_total));

    // Set currency
    setCurrencyCode();
  }

  function changeRowType() {
    var type = $(this).val();
    var row_id = $(this).attr('data-row-id');

    if (type == 'item') {
      $('#' + row_id).find('.select-unit').show();
      $('#' + row_id).find('.input-unit_price').show();
      $('#' + row_id).find('.select-discount-unit').hide();
    } else if (type == 'discount') {
      $('#' + row_id).find('.select-unit').hide();
      $('#' + row_id).find('.input-unit_price').hide();
      $('#' + row_id).find('.select-discount-unit').show();
    }
  }
});
</script>


<script>
$('.selectize-invoice-statuses').selectize({
  render: {
    option: function (data, escape) {
      return '<div>' +
      '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
      '<span class="title">' + escape(data.text) + '</span>' +
      '</div>';
    },
  item: function (data, escape) {
    return '<div>' +
      '<span style=\'display:inline-block;position:relative;top:-1px;border-radius:50%;width:8px;height:8px;margin-right:8px;background-color:' + data.color + '\'></span>' +
      escape(data.text) +
      '</div>';
    }
  }
});
</script>

<script id="invoice-item" type="text/x-handlebars-template">
  <tr id="item-row-@{{ id }}" class="item-row">
    <td class="align-middle">
      <a href="javascript:void(0);" style="margin:7px 0 0 0; float:left; cursor: move" class="sortable-handle"><i class="material-icons text-muted">reorder</i></a>
    </td>
    <td class="align-middle">
      <select class="form-control select-type" name="invoice_type[]" data-row-id="item-row-@{{ id }}">
        <option value="item"@{{#ifvalue type value="item"}} selected="1"@{{/ifvalue}}><?php echo trans('g.item') ?></option>
        <option value="discount"@{{#ifvalue type value="discount"}} selected@{{/ifvalue}}><?php echo trans('g.discount') ?></option>
      </select>
    </td>
    <td class="align-middle">
      <input class="form-control" type="text" value="@{{ description }}" name="invoice_description[]">
    </td>
    <td class="align-middle">
      <input class="form-control text-right input-quantity" type="number" min="-100000" max="100000" step="0.25" value="@{{ quantity }}" name="invoice_quantity[]">
    </td>
    <td class="align-middle">
      <select class="form-control select-unit" name="invoice_unit[]" @{{#ifvalue type value="discount"}} style="display:none"@{{/ifvalue}}>
        <option value=""></option>
<?php
$units = \Platform\Models\Core\Unit::orderBy('name', 'asc')->get();

foreach ($units as $unit) {
?>
        <option value="{{ $unit->name }}"@{{#ifvalue unit value="<?php echo $unit->name; ?>"}} selected@{{/ifvalue}}>{{ __($unit->name) }}</option>
<?php } ?>
      </select>

      <select class="form-control select-discount-unit" name="invoice_discount_unit[]" @{{#ifvalue type value="item"}} style="display:none"@{{/ifvalue}}>
        <option value="currency" class="currency_code"@{{#ifvalue discount_type value="currency"}} selected@{{/ifvalue}}></option>
        <option value="%"@{{#ifvalue discount_type value="%"}} selected@{{/ifvalue}}>%</option>

      </select>
    </td>
    <td class="align-middle"><input class="form-control text-right input-unit_price" name="invoice_unit_price[]" type="number" min="-10000" max="10000" step="0.01" value="@{{ unit_price }}" @{{#ifvalue type value="discount"}} style="display:none"@{{/ifvalue}}></td>
    <td class="align-middle">
      <select class="form-control select-tax" name="invoice_tax_rate[]">
        <option value=""></option>
<?php
$tax_rates = \Platform\Models\Core\TaxRate::orderBy('rate', 'desc')->get();

foreach ($tax_rates as $rate) {
?>
        <option value="{{ $rate->rate }}"@{{#ifvalue tax_rate value="<?php echo $rate->rate; ?>"}} selected@{{/ifvalue}}>{{ $rate->percentage }}</option>
<?php } ?>
      </select>
    </td>
    <td class="text-right align-middle row-total">
      @{{ total }}
    </td>
    <td class="text-right align-middle">
      <a href="javascript:void(0);" class="btn btn-danger rounded-0 btn-delete-row" data-row="item-row-@{{ id }}"><i class="material-icons">delete</i></a>
    </td>
  </tr>
</script>

<script id="invoice-item-discount" type="text/x-handlebars-template">
  <tr class="item-discount-row">
    <td colspan="5"></td>
    <td colspan="2" class="text-left align-middle"><?php echo trans('g.discount') ?></td>
    <td class="text-right align-middle">@{{ total }}</td>
    <td></td>
  </tr>
</script>

<script id="invoice-item-tax" type="text/x-handlebars-template">
  <tr id="item-tax-row-@{{ id }}" class="item-tax-row">
    <td colspan="5"></td>
    <td colspan="2" class="text-left align-middle"><?php echo trans('g.tax_of_row', ['percentage' => '{{ rate }}', 'amount' => '{{ of }}', 'currency' => '<span class="currency_code"></span>']) ?></td>
    <td class="text-right align-middle">@{{ total }}</td>
    <td></td>
  </tr>
</script>
@stop