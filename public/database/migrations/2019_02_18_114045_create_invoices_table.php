<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('company_id')->unsigned()->index()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->integer('project_id')->unsigned()->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->string('reference', 128)->nullable();
            $table->char('currency_code', 3)->nullable();
            $table->boolean('credit_nota')->default(false);
            $table->integer('total')->nullable();
            $table->integer('total_discount')->nullable();
            $table->integer('total_tax')->nullable();
            $table->dateTime('issue_date')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->mediumText('sent_to_email')->nullable();
            $table->dateTime('sent_date')->nullable();
            $table->dateTime('resent_date')->nullable();
            $table->dateTime('partially_paid_date')->nullable();
            $table->dateTime('paid_date')->nullable();
            $table->dateTime('written_off_date')->nullable();
            $table->mediumText('notes')->nullable();
            $table->mediumText('description_head')->nullable();
            $table->mediumText('description_footer')->nullable();
            $table->json('additional_fields')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('invoice_id')->unsigned()->index()->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->string('type', 24);
            $table->text('description', 250)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('unit', 32)->nullable();
            $table->bigInteger('discount_quantity')->nullable();
            $table->string('discount_type', 12)->nullable(); // % or currency
            $table->bigInteger('unit_price')->nullable();
            $table->integer('tax_rate')->nullable()->unsigned();
            $table->json('additional_fields')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        // Create permissions
        $permissions = [
          // Invoices
          'list-invoices',
          'download-invoice',
          'create-invoice',
          'edit-invoice',
          'delete-invoice'
        ];

        foreach ($permissions as $permission) {
          Permission::create(['name' => $permission]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
}
