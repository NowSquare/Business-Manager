<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_statuses', function(Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('sort')->unsigned()->nullable();
            $table->boolean('active')->default(true);
            $table->string('name', 250);
            $table->string('color', 24);
            $table->boolean('default_project')->default(false);
            $table->boolean('default_task')->default(false);
            $table->text('description')->nullable();
        });

        Schema::create('projects', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->default(1)->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('parent_id')->unsigned()->index()->nullable();
            $table->foreign('parent_id')->references('id')->on('projects')->onDelete('set null');
            $table->integer('company_id')->unsigned()->index()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->integer('project_status_id')->unsigned()->index();
            $table->foreign('project_status_id')->references('id')->on('project_statuses')->onDelete('cascade');
            $table->string('reference', 128)->nullable();
            $table->string('files_dir', 164)->nullable();
            $table->boolean('active')->default(true);
            $table->string('name', 64)->nullable();
            $table->string('category', 128)->nullable();
            $table->text('short_description')->nullable();
            $table->mediumText('description')->nullable();
            $table->mediumText('notes')->nullable();
            $table->tinyInteger('progress')->nullable();
            $table->string('billing_type', 128)->nullable(); // Fixed Rate (total rate), Projects Hours (hourly rate, estimated hours), Task Hours (estimated hours)
            $table->integer('total_rate')->nullable()->unsigned();
            $table->integer('hourly_rate')->nullable()->unsigned();
            $table->integer('estimated_hours')->nullable()->unsigned();
            $table->integer('budgeted_hours')->nullable()->unsigned();
            $table->integer('actual_hours')->nullable()->unsigned();
            $table->integer('actual_costs')->nullable()->unsigned();
            $table->dateTime('estimate_valid_until')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->integer('completed_by_id')->nullable()->unsigned();
            $table->foreign('completed_by_id')->references('id')->on('users')->onDelete('set null');

            $table->boolean('client_can_comment')->default(false);
            $table->boolean('client_can_view_tasks')->default(false);
            $table->boolean('client_can_edit_tasks')->default(false);
            $table->boolean('client_can_view_description')->default(true);
            $table->boolean('client_can_upload_files')->default(false);
            $table->boolean('client_can_view_proposition')->default(true);
            $table->boolean('client_can_approve_proposition')->default(true);
            $table->boolean('notify_people_involved')->default(false);

            // Locales
            $table->char('currency_code', 3)->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('timezone', 32)->nullable();
            $table->string('date_format', 24)->nullable();
            $table->string('time_format', 10)->nullable();
            $table->string('number_format', 10)->nullable();
            $table->tinyInteger('decimals')->nullable();
            $table->string('decimal_seperator', 6)->nullable();
            $table->string('thousands_seperator', 6)->nullable();
            $table->string('minus_sign', 6)->nullable();
            $table->boolean('use_parentheses_for_negative_numbers')->default(false);
            $table->tinyInteger('first_day_of_week')->nullable();

            // Image
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();

            $table->json('additional_fields')->nullable();
            $table->json('tags')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        // Many-to-many relation
        Schema::create('project_manager', function(Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('project_id')->unsigned()->index();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        Schema::create('project_tasks', function (Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('parent_id')->unsigned()->index()->nullable();
            $table->foreign('parent_id')->references('id')->on('project_tasks')->onDelete('set null');
            $table->integer('project_id')->unsigned()->index();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->integer('project_status_id')->unsigned()->index()->nullable();
            $table->foreign('project_status_id')->references('id')->on('project_statuses')->onDelete('set null');
            $table->integer('assigned_to_id')->unsigned()->index()->nullable();
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');
            $table->integer('for_verification_id')->unsigned()->index()->nullable();
            $table->foreign('for_verification_id')->references('id')->on('users')->onDelete('set null');
            $table->string('reference', 128)->nullable();
            $table->boolean('public')->nullable();
            $table->boolean('billable')->nullable();
            $table->boolean('optional')->nullable();
            $table->string('subject', 250);
            $table->tinyInteger('priority')->unsigned()->nullable(); // 0 = low, 1 = normal, 2 = high, 3 = urgent
            $table->tinyInteger('progress')->unsigned()->nullable();
            $table->boolean('recurring')->nullable();
            $table->string('repeat_every', 24)->nullable(); // week, 2 weeks, 1 month, 2 months, 3 months, 6 months, 1 year
            $table->mediumText('description')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->integer('completed_by_id')->nullable()->unsigned();
            $table->foreign('completed_by_id')->references('id')->on('users')->onDelete('set null');
            $table->integer('hourly_rate')->unsigned()->nullable();
            $table->integer('hours')->nullable()->unsigned();
            $table->integer('estimated_hours')->nullable()->unsigned();
            $table->integer('budgeted_hours')->nullable()->unsigned();
            $table->integer('actual_hours')->nullable()->unsigned();
            $table->json('additional_fields')->nullable();
            $table->json('tags')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        // Many-to-many relation
        Schema::create('project_task_user', function(Blueprint $table) {
          
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('project_task_id')->unsigned()->index();
            $table->foreign('project_task_id')->references('id')->on('project_tasks')->onDelete('cascade');
        });

        Schema::create('project_propositions', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('account_id')->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('project_id')->unsigned()->index();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('reference', 128)->nullable();
            $table->boolean('locked')->default(false);
            $table->integer('total')->nullable();
            $table->integer('total_discount')->nullable();
            $table->integer('total_tax')->nullable();
            $table->dateTime('proposition_valid_until')->nullable();
            $table->dateTime('approved')->nullable();
            $table->integer('approved_by')->nullable();
            $table->dateTime('decline')->nullable();
            $table->integer('declined_by')->nullable();
            $table->mediumText('description_head')->nullable();
            $table->mediumText('description_footer')->nullable();
            $table->json('additional_fields')->nullable();
            $table->json('tags')->nullable();
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->timestamps();
        });

        Schema::create('project_proposition_items', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('project_proposition_id')->unsigned()->index()->nullable();
            $table->foreign('project_proposition_id')->references('id')->on('project_propositions')->onDelete('cascade');
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_proposition_items');
        Schema::dropIfExists('project_propositions');
        Schema::dropIfExists('project_task_user');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_manager');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_statuses');
    }
}
